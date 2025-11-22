<?php

namespace App\Controllers\Warranty;

use CodeIgniter\HTTP\ResponseInterface;

class Claim extends BaseWarrantyController
{
    public function form(): void
    {
        $agentId = $this->getUserAgentId();

        $this->data = array_merge($this->data, [
            'title'         => 'Klaim Garansi',
            'currentModule' => $this->currentModule,
            'config'        => $this->config,
            'msg'           => $this->session->getFlashdata('message'),
            'agent_id'      => $agentId,
        ]);

        $this->data['breadcrumb'] = [
            'Home'         => $this->config->baseURL,
            'Warranty'     => $this->config->baseURL . 'warranty/history',
            'Klaim Garansi' => '',
        ];

        $this->view('warranty/claim-form', $this->data);
    }

    public function submit(): ResponseInterface
    {
        $isAjax = $this->request->isAJAX();

        try {
            $serialNumber = $this->request->getPost('serial_number');
            $issueReason = $this->request->getPost('issue_reason');

            // Basic validation: only check required fields
            if (empty($serialNumber) || empty($issueReason)) {
                $message = empty($serialNumber) ? 'Serial number harus diisi.' : 'Alasan klaim harus diisi.';
                return $this->handleErrorResponse($message, $isAjax);
            }

            // Get old SN for routing (validation will happen in system validation step)
            $oldSn = $this->itemSnModel->where('sn', $serialNumber)->first();
            if (!$oldSn) {
                return $this->handleErrorResponse('Serial number tidak ditemukan.', $isAjax);
            }

            // Handle photo upload
            $photoPath = null;
            $file = $this->request->getFile('photo');

            if ($file && $file->isValid() && !$file->hasMoved()) {
                if ($file->getSize() > 5242880) {
                    return $this->handleErrorResponse('Ukuran file maksimal 5MB.', $isAjax);
                }

                $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!in_array($file->getMimeType(), $allowedTypes)) {
                    return $this->handleErrorResponse('Format file harus JPG, PNG, atau GIF.', $isAjax);
                }

                $uploadPath = ROOTPATH . 'public/uploads/warranty/';
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                $newName = $file->getRandomName();
                if ($file->move($uploadPath, $newName)) {
                    $photoPath = 'warranty/' . $newName;
                } else {
                    return $this->handleErrorResponse('Gagal mengupload file.', $isAjax);
                }
            }

            $agentId = $this->request->getPost('agent_id') ?: $this->getUserAgentId();
            if (empty($agentId)) {
                return $this->handleErrorResponse('Agen tidak ditemukan.', $isAjax);
            }

            // Create claim record immediately with status='pending'
            // System validation will happen automatically after creation
            $claimData = [
                'agent_id'        => (int) $agentId,
                'old_sn_id'       => $oldSn->id,
                'issue_reason'    => $issueReason,
                'photo_path'      => $photoPath,
                'status'          => 'pending',
                'routed_store_id' => $oldSn->agent_id, // Route to store that owns the stock
            ];

            $this->warrantyClaimModel->skipValidation(true);
            $claimId = $this->warrantyClaimModel->insert($claimData);
            $this->warrantyClaimModel->skipValidation(false);

            if (!$claimId) {
                $errors = $this->warrantyClaimModel->errors();
                $errorMsg = 'Gagal membuat klaim garansi.';
                if ($errors && is_array($errors)) {
                    $errorMsg .= ' ' . implode(', ', $errors);
                }
                throw new \Exception($errorMsg);
            }

            // Trigger automatic system validation after claim creation
            $this->validateClaim($claimId);

            $message = 'Klaim garansi berhasil diajukan.';
            if ($isAjax) {
                return $this->response->setJSON([
                    'status'  => 'success',
                    'message' => $message,
                    'data'    => ['id' => $claimId],
                ]);
            }

            return redirect()->to('warranty/history')->with('message', [
                'status'  => 'success',
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            return $this->handleErrorResponse('Gagal mengajukan klaim: ' . $e->getMessage(), $isAjax, true);
        }
    }

    private function handleErrorResponse(string $message, bool $isAjax, bool $withInput = false): ResponseInterface
    {
        if ($isAjax) {
            return $this->response->setJSON(['status' => 'error', 'message' => $message]);
        }
        $redirect = redirect()->back();
        if ($withInput) {
            $redirect = $redirect->withInput();
        }
        return $redirect->with('message', [
            'status'  => 'error',
            'message' => $message,
        ]);
    }
}

