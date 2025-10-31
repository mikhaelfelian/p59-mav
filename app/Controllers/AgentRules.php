<?php

namespace App\Controllers;

use App\Models\AgentCashbackRuleModel;
use App\Models\AgentModel;

class AgentRules extends BaseController
{
    protected $ruleModel;
    protected $agentModel;

    public function __construct()
    {
        parent::__construct();
        $this->ruleModel  = new AgentCashbackRuleModel();
        $this->agentModel = new AgentModel();
    }

    public function index()
    {
        if (!$this->hasPermissionPrefix('read')) {
            return $this->view('errors/403', [
                'title'   => 'Akses Ditolak',
                'message' => 'Anda tidak memiliki izin untuk melihat halaman ini.'
            ]);
        }

        $this->data['title']          = 'Aturan Cashback/Akumulasi Agen';
        $this->data['current_module'] = $this->currentModule;

        // Simple list
        $this->data['rules'] = $this->ruleModel
            ->select('agent_cashback_rule.*, agent.name AS agent_name, agent.code AS agent_code')
            ->join('agent', 'agent.id = agent_cashback_rule.agent_id', 'left')
            ->orderBy('agent_cashback_rule.created_at', 'DESC')
            ->findAll();

        return $this->view('themes/modern/agent_rules/index', $this->data);
    }

    public function form($id = null)
    {
        if (!$this->hasPermissionPrefix('write')) {
            return $this->view('errors/403', [
                'title'   => 'Akses Ditolak',
                'message' => 'Anda tidak memiliki izin untuk mengubah data.'
            ]);
        }

        $this->data['title'] = $id ? 'Ubah Aturan Agen' : 'Tambah Aturan Agen';
        $this->data['rule']  = $id ? $this->ruleModel->find($id) : null;
        $this->data['agents'] = $this->agentModel->where('is_active', '1')->findAll();

        return $this->view('themes/modern/agent_rules/form', $this->data);
    }

    public function save()
    {
        if (!$this->hasPermissionPrefix('write')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Tidak memiliki izin.']);
        }

        $id = $this->request->getPost('id');
        $data = [
            'agent_id'        => $this->request->getPost('agent_id'),
            'rule_type'       => $this->request->getPost('rule_type'),
            'min_transaction' => $this->request->getPost('min_transaction') ?: 0,
            'cashback_amount' => $this->request->getPost('cashback_amount') ?: 0,
            'is_active'       => $this->request->getPost('is_active') ? 1 : 0,
            'start_date'      => $this->request->getPost('start_date') ?: null,
            'end_date'        => $this->request->getPost('end_date') ?: null,
            'notes'           => $this->request->getPost('notes'),
        ];

        // Normalize currency numbers using angka helper if present
        helper('angka');
        $data['min_transaction'] = format_angka_db($data['min_transaction']);
        $data['cashback_amount'] = format_angka_db($data['cashback_amount']);

        if ($id) {
            $result = $this->ruleModel->update($id, $data);
            $msg    = $result ? 'Aturan berhasil diupdate' : 'Gagal mengupdate aturan';
        } else {
            $result = $this->ruleModel->insert($data);
            $msg    = $result ? 'Aturan berhasil disimpan' : 'Gagal menyimpan aturan';
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['status' => $result ? 'success' : 'error', 'message' => $msg]);
        }
        return redirect()->to('agent-rules')->with('message', $msg);
    }

    public function delete($id)
    {
        if (!$this->hasPermissionPrefix('delete')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Tidak memiliki izin.']);
        }
        $result = $this->ruleModel->delete($id);
        return $this->response->setJSON(['status' => $result ? 'success' : 'error']);
    }
}


