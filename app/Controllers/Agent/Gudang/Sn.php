<?php

/**
 * Agent Gudang SN Controller (Serial Number Management)
 * 
 * Handles serial number management for warehouse operations
 * 
 * @package    App\Controllers\Agent\Gudang
 * @author     Mikhael Felian Waskito <mikhaelfelian@gmail.com>
 * @copyright  2025
 * @license    MIT
 * @version    1.0.0
 * @since      2025-11-10
 */

namespace App\Controllers\Agent\Gudang;

use App\Controllers\BaseController;
use App\Models\UserRoleAgentModel;

class Sn extends BaseController
{
    protected $userRoleAgentModel;
    
    /**
     * Sales channel constants
     */
    protected const CHANNEL_ONLINE = '2';
    
    /**
     * Initialize models
     */
    public function __construct()
    {
        parent::__construct();
        $this->userRoleAgentModel = new UserRoleAgentModel();
    }
    
    /**
     * Display serial number management page
     * 
     * @return void
     */
    public function index(): void
    {
        // Get agent IDs for current user
        // Check if user is admin (has read_all or update_all permission)
        // Defensive check: ensure userPermission is an array
        $userPermission = is_array($this->userPermission) ? $this->userPermission : [];
        $isAdmin = key_exists('read_all', $userPermission) || key_exists('update_all', $userPermission);
        
        $agentIds = [];
        $userId = !empty($this->user) && is_array($this->user) ? ($this->user['id_user'] ?? null) : null;
        if (!$isAdmin && !empty($userId)) {
            $agentRows = $this->userRoleAgentModel
                ->select('agent_id')
                ->where('user_id', $userId)
                ->findAll();

            $agentIds = array_values(array_unique(array_map(
                static function ($row) {
                    if (is_object($row)) {
                        return (int)($row->agent_id ?? 0);
                    }
                    if (is_array($row)) {
                        return (int)($row['agent_id'] ?? 0);
                    }
                    return 0;
                },
                $agentRows
            )));
        }

        // Count unreceived SNs for current agent
        $db = \Config\Database::connect();
        $unreceivedBuilder = $db->table('sales_item_sn')
            ->select('sales_item_sn.id')
            ->join('sales_detail', 'sales_detail.id = sales_item_sn.sales_item_id', 'inner')
            ->join('sales', 'sales.id = sales_detail.sale_id', 'inner')
            ->where('sales.sale_channel', self::CHANNEL_ONLINE)
            ->where('sales_item_sn.is_receive', '0');
        
        // Apply agent filter only for non-admin users
        if (!$isAdmin && !empty($agentIds)) {
            $unreceivedBuilder->whereIn('sales.warehouse_id', $agentIds);
        }
        
        $totalUnreceivedCount = $unreceivedBuilder->countAllResults();

        $this->data = array_merge($this->data, [
            'title'               => 'Data Serial Number',
            'currentModule'       => $this->currentModule,
            'config'              => $this->config,
            'isAdmin'             => $isAdmin,
            'agentIds'            => $agentIds,
            'totalUnreceivedCount'=> $totalUnreceivedCount,
            'isAgent'             => (!$isAdmin && !empty($agentIds)),
        ]);

        $this->data['breadcrumb'] = [
            'Home'                 => $this->config->baseURL.'agent/dashboard',
            'Data Serial Number'   => '', // Current page, no link
        ];

        $this->view('sales/agent/sales-sn', $this->data);
    }
}

