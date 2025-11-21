<?php

namespace App\Controllers\Agent\Gudang;

use App\Controllers\BaseController;
use App\Models\SalesModel;
use App\Models\SalesDetailModel;
use App\Models\SalesFeeModel;
use App\Models\FeeTypeModel;
use App\Models\SalesPaymentsModel;
use App\Models\SalesItemSnModel;
use App\Models\ItemSnModel;
use CodeIgniter\HTTP\ResponseInterface;

class Stok extends BaseController
{
    protected $salesModel;
    protected $salesDetailModel;
    protected $salesFeeModel;
    protected $feeTypeModel;
    protected $salesPaymentsModel;
    protected $salesItemSnModel;
    protected $itemSnModel;

    public function __construct()
    {
        parent::__construct();
        $this->salesModel = new SalesModel();
        $this->salesDetailModel = new SalesDetailModel();
        $this->salesFeeModel = new SalesFeeModel();
        $this->feeTypeModel = new FeeTypeModel();
        $this->salesPaymentsModel = new SalesPaymentsModel();
        $this->salesItemSnModel = new SalesItemSnModel();
        $this->itemSnModel = new ItemSnModel();
    }

    public function index(): void
    {
        $this->data = array_merge($this->data, [
            'title'         => 'Gudang - Stok Masuk',
            'currentModule' => 'agent/gudang/stok-masuk',
            'config'        => $this->config,
            'msg'           => $this->session->getFlashdata('message'),
            'read_all'      => true,
        ]);

        $this->data['breadcrumb'] = [
            'Home'       => $this->config->baseURL . 'agent/dashboard',
            'Gudang'     => $this->config->baseURL . 'agent/gudang/sn',
            'Stok Masuk' => '',
        ];

        $this->view('sales/agent/gudang/stok-result', $this->data);
    }

    public function getDataDT(): ResponseInterface
    {
        $draw   = (int) ($this->request->getPost('draw') ?? 0);
        $start  = (int) ($this->request->getPost('start') ?? 0);
        $length = (int) ($this->request->getPost('length') ?? 10);
        $searchValue = $this->request->getPost('search')['value'] ?? '';
        $order       = $this->request->getPost('order')[0] ?? [];
        $columns     = [
            'sales.invoice_no',
            'customer.name',
            'sales.grand_total',
            'sales.created_at',
        ];

        $baseBuilder = $this->salesModel->builder();
        $baseBuilder->select('sales.id, sales.invoice_no, sales.grand_total, sales.balance_due, sales.payment_status, sales.created_at, customer.name as customer_name')
            ->join('customer', 'customer.id = sales.customer_id', 'left')
            ->where('sales.is_receive', '0')
            ->where('sales.user_id', $this->user['id_user']);

        $recordsTotal = (clone $baseBuilder)->countAllResults(false);

        $filteredBuilder = clone $baseBuilder;
        if (!empty($searchValue)) {
            $filteredBuilder->groupStart()
                ->like('sales.invoice_no', $searchValue)
                ->orLike('customer.name', $searchValue)
                ->groupEnd();
        }

        $recordsFiltered = (clone $filteredBuilder)->countAllResults(false);

        // Map DataTables column index to orderable columns array index
        // View columns: 0=No (non-orderable), 1=invoice_no, 2=customer_name, 3=grand_total, 4=created_at, 5=Action (non-orderable)
        $columnMapping = [
            1 => 0, // invoice_no -> $columns[0]
            2 => 1, // customer_name -> $columns[1]
            3 => 2, // grand_total -> $columns[2]
            4 => 3, // created_at -> $columns[3]
        ];
        $orderColumnIndex = $columnMapping[$order['column'] ?? 4] ?? 3; // default to created_at (index 3)
        $orderColumn = $columns[$orderColumnIndex] ?? 'sales.created_at';
        $orderDir    = strtoupper($order['dir'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';

        $data = $filteredBuilder
            ->orderBy($orderColumn, $orderDir)
            ->limit($length, $start)
            ->get()
            ->getResultArray();

        $resultData = [];
        $no = $start + 1;
        foreach ($data as $row) {
            $statusText = 'Belum Bayar';
            $statusClass = 'secondary';
            if (($row['payment_status'] ?? '0') === '2') {
                $statusText = 'Lunas';
                $statusClass = 'success';
            } elseif (($row['payment_status'] ?? '0') === '1') {
                $statusText = 'Parsial';
                $statusClass = 'warning';
            }

            $resultData[] = [
                'ignore_search_urut'   => $no++,
                'invoice_no'           => esc($row['invoice_no']),
                'customer_name'        => esc($row['customer_name'] ?? '-'),
                'grand_total'          => number_format($row['grand_total'] ?? 0, 0, ',', '.'),
                'created_at'           => tgl_indo8($row['created_at'] ?? 'now'),
                'ignore_search_action' => '<a href="' . $this->config->baseURL . 'agent/gudang/stok-masuk/' . $row['id'] . '" class="btn btn-sm btn-success"><i class="fas fa-plus"></i></a>',
            ];
        }

        return $this->response->setJSON([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $resultData,
        ]);
    }

    public function masuk(int $id)
    {
        if ($id <= 0) {
            return redirect()->to('agent/gudang/sn')->with('message', [
                'status' => 'error',
                'message' => 'ID penjualan tidak valid.'
            ]);
        }

        $sale = $this->salesModel->getSalesWithRelations($id);
        if (!$sale) {
            return redirect()->to('agent/gudang/sn')->with('message', [
                'status' => 'error',
                'message' => 'Data penjualan tidak ditemukan.'
            ]);
        }

        $items = $this->salesDetailModel->getDetailsBySale($id);
        $fees = $this->salesFeeModel->getFeesBySale($id);
        $feeTypes = $this->feeTypeModel->getActiveFeeTypes();
        $payments = $this->salesPaymentsModel->getPaymentsBySale($id);
        $paymentInfo = !empty($payments) ? $payments[0] : null;
        $gatewayResponse = null;
        if (!empty($paymentInfo['response'])) {
            $decoded = json_decode($paymentInfo['response'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $gatewayResponse = $decoded;
            }
        }

        $receivableSn = $this->getReceivableSn($id);

        $this->data = array_merge($this->data, [
            'title'         => 'Gudang - Stok Masuk',
            'currentModule' => $this->currentModule,
            'config'        => $this->config,
            'sale'          => $sale,
            'items'         => $items,
            'fees'          => $fees,
            'feeTypes'      => $feeTypes,
            'payment'       => $paymentInfo,
            'gatewayResponse' => $gatewayResponse,
            'isAgent'       => false,
            'isAdmin'       => true,
            'receivableSn'  => $receivableSn,
        ]);

        $this->data['breadcrumb'] = [
            'Home' => $this->config->baseURL . 'agent/dashboard',
            'Gudang' => $this->config->baseURL . 'agent/gudang/sn',
            'Stok Masuk' => '',
        ];

        return $this->view('sales/agent/gudang/stok-masuk', $this->data);
    }

    public function receiveSn(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Metode permintaan tidak valid.'
            ])->setStatusCode(400);
        }

        $snId = (int) ($this->request->getPost('sn_id') ?? 0);
        if ($snId <= 0) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'ID SN tidak valid.'
            ])->setStatusCode(400);
        }

        $snData = $this->salesItemSnModel
            ->select('sales_item_sn.*, sales_detail.sale_id, sales_detail.item_id, item_sn.id as base_item_sn_id')
            ->join('sales_detail', 'sales_detail.id = sales_item_sn.sales_item_id', 'left')
            ->join('item_sn', 'item_sn.id = sales_item_sn.item_sn_id', 'left')
            ->where('sales_item_sn.id', $snId)
            ->first();

        if (!$snData) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data SN tidak ditemukan.'
            ])->setStatusCode(404);
        }

        if (($snData['is_receive'] ?? '0') === '1') {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'SN sudah diterima sebelumnya.'
            ])->setStatusCode(400);
        }

        $now = date('Y-m-d H:i:s');

        $this->salesItemSnModel->skipValidation(true);
        $this->salesItemSnModel->update($snId, [
            'is_receive' => '1',
            'receive_at' => $now,
            'updated_at' => $now,
        ]);
        $this->salesItemSnModel->skipValidation(false);

        if (!empty($snData['base_item_sn_id'])) {
            $this->itemSnModel->skipValidation(true);
            $this->itemSnModel->update($snData['base_item_sn_id'], [
                'is_sell' => '1',
                'updated_at' => $now,
            ]);
            $this->itemSnModel->skipValidation(false);
        }

        $pending = $this->salesItemSnModel
            ->join('sales_detail', 'sales_detail.id = sales_item_sn.sales_item_id')
            ->where('sales_detail.sale_id', $snData['sale_id'])
            ->where('sales_item_sn.is_receive', '0')
            ->countAllResults();

        if ($pending === 0) {
            $this->salesModel->skipValidation(true);
            $this->salesModel->update($snData['sale_id'], ['is_receive' => '1']);
            $this->salesModel->skipValidation(false);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'SN berhasil diterima.',
            'pending' => $pending
        ]);
    }

    protected function getReceivableSn(int $saleId): array
    {
        return $this->salesItemSnModel
            ->select('sales_item_sn.*, item.sku, item.name as item_name')
            ->join('sales_detail', 'sales_detail.id = sales_item_sn.sales_item_id', 'inner')
            ->join('item', 'item.id = sales_detail.item_id', 'left')
            ->where('sales_detail.sale_id', $saleId)
            ->where('sales_item_sn.is_receive', '0')
            ->orderBy('sales_item_sn.id', 'ASC')
            ->findAll();
    }
}


