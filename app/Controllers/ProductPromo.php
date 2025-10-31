<?php

namespace App\Controllers;

use App\Models\ProductPromoRuleModel;
use App\Models\ItemModel;

class ProductPromo extends BaseController
{
    protected $promoModel;
    protected $itemModel;

    public function __construct()
    {
        parent::__construct();
        $this->promoModel = new ProductPromoRuleModel();
        $this->itemModel  = new ItemModel();
    }

    public function listByItem($itemId)
    {
        if (!$this->hasPermissionPrefix('read')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Tidak memiliki izin.']);
        }
        $rows = $this->promoModel
            ->select('product_promo_rule.*, i1.name AS item_name, i2.name AS bonus_name')
            ->join('item i1', 'i1.id = product_promo_rule.item_id', 'left')
            ->join('item i2', 'i2.id = product_promo_rule.bonus_item_id', 'left')
            ->where('product_promo_rule.item_id', $itemId)
            ->orderBy('product_promo_rule.created_at', 'DESC')
            ->findAll();
        return $this->response->setJSON(['status' => 'success', 'data' => $rows]);
    }

    public function save()
    {
        if (!$this->hasPermissionPrefix('write')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Tidak memiliki izin.']);
        }
        $id = $this->request->getPost('id');
        $data = [
            'item_id'       => $this->request->getPost('item_id'),
            'bonus_item_id' => $this->request->getPost('bonus_item_id'),
            'min_qty'       => $this->request->getPost('min_qty') ?: 1,
            'bonus_qty'     => $this->request->getPost('bonus_qty') ?: 1,
            'is_multiple'   => $this->request->getPost('is_multiple') ? 1 : 0,
            'start_date'    => $this->request->getPost('start_date') ?: null,
            'end_date'      => $this->request->getPost('end_date') ?: null,
            'status'        => $this->request->getPost('status') ?: 'active',
            'notes'         => $this->request->getPost('notes'),
        ];
        if ($id) {
            $result = $this->promoModel->update($id, $data);
        } else {
            $result = $this->promoModel->insert($data);
        }
        return $this->response->setJSON(['status' => $result ? 'success' : 'error']);
    }

    public function delete($id)
    {
        if (!$this->hasPermissionPrefix('delete')) {
            return $this->response->setJSON(['status' => 'error']);
        }
        $result = $this->promoModel->delete($id);
        return $this->response->setJSON(['status' => $result ? 'success' : 'error']);
    }
}


