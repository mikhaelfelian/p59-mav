<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-10-24
 * Github: github.com/mikhaelfelian
 * Description: Model for managing user roles within agent organizations
 * This file represents the Model for UserRoleAgentModel.
 */
class UserRoleAgentModel extends Model
{
    protected $table = 'user_role_agent';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'user_id',
        'agent_id',
        'role'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [
        'user_id' => 'required|integer|is_not_unique[user.id_user]',
        'agent_id' => 'required|integer|is_not_unique[agent.id]',
        'role' => 'required|in_list[1,2]'
    ];

    protected $validationMessages = [
        'user_id' => [
            'required' => 'User ID harus diisi',
            'integer' => 'User ID harus berupa angka',
            'is_not_unique' => 'User tidak ditemukan'
        ],
        'agent_id' => [
            'required' => 'Agent ID harus diisi',
            'integer' => 'Agent ID harus berupa angka',
            'is_not_unique' => 'Agent tidak ditemukan'
        ],
        'role' => [
            'required' => 'Role harus diisi',
            'in_list' => 'Role harus 1 (owner) atau 2 (staff)'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    /**
     * Get user roles for a specific agent
     * 
     * @param int $agentId
     * @return array
     */
    public function getUserRolesForAgent($agentId)
    {
        return $this->select('user_role_agent.*, user.nama as user_name, user.email as user_email')
                    ->join('user', 'user.id_user = user_role_agent.user_id')
                    ->where('user_role_agent.agent_id', $agentId)
                    ->orderBy('user_role_agent.role', 'ASC')
                    ->orderBy('user.nama', 'ASC')
                    ->findAll();
    }

    /**
     * Get agent roles for a specific user
     * 
     * @param int $userId
     * @return array
     */
    public function getAgentRolesForUser($userId)
    {
        return $this->select('user_role_agent.*, agent.name as agent_name, agent.code as agent_code')
                    ->join('agent', 'agent.id = user_role_agent.agent_id')
                    ->where('user_role_agent.user_id', $userId)
                    ->where('agent.is_active', '1')
                    ->orderBy('user_role_agent.role', 'ASC')
                    ->orderBy('agent.name', 'ASC')
                    ->findAll();
    }

    /**
     * Check if user has specific role in agent
     * 
     * @param int $userId
     * @param int $agentId
     * @param string $role
     * @return bool
     */
    public function hasRole($userId, $agentId, $role = null)
    {
        $query = $this->where('user_id', $userId)
                      ->where('agent_id', $agentId);
        
        if ($role) {
            $query->where('role', $role);
        }
        
        return $query->first() !== null;
    }

    /**
     * Check if user is owner of agent
     * 
     * @param int $userId
     * @param int $agentId
     * @return bool
     */
    public function isOwner($userId, $agentId)
    {
        return $this->hasRole($userId, $agentId, '1');
    }

    /**
     * Check if user is staff of agent
     * 
     * @param int $userId
     * @param int $agentId
     * @return bool
     */
    public function isStaff($userId, $agentId)
    {
        return $this->hasRole($userId, $agentId, '2');
    }

    /**
     * Get role name
     * 
     * @param string $role
     * @return string
     */
    public function getRoleName($role)
    {
        $roles = [
            '1' => 'Owner',
            '2' => 'Staff'
        ];
        
        return $roles[$role] ?? 'Unknown';
    }

    /**
     * Assign user to agent with role
     * 
     * @param int $userId
     * @param int $agentId
     * @param string $role
     * @return bool|int
     */
    public function assignUserToAgent($userId, $agentId, $role)
    {
        // Check if relationship already exists
        $existing = $this->where('user_id', $userId)
                         ->where('agent_id', $agentId)
                         ->first();
        
        if ($existing) {
            // Update existing role
            return $this->update($existing->id, ['role' => $role]);
        } else {
            // Create new relationship
            return $this->insert([
                'user_id' => $userId,
                'agent_id' => $agentId,
                'role' => $role
            ]);
        }
    }

    /**
     * Remove user from agent
     * 
     * @param int $userId
     * @param int $agentId
     * @return bool
     */
    public function removeUserFromAgent($userId, $agentId)
    {
        return $this->where('user_id', $userId)
                    ->where('agent_id', $agentId)
                    ->delete();
    }

    /**
     * Get all owners of an agent
     * 
     * @param int $agentId
     * @return array
     */
    public function getAgentOwners($agentId)
    {
        return $this->select('user_role_agent.*, user.nama as user_name, user.email as user_email')
                    ->join('user', 'user.id_user = user_role_agent.user_id')
                    ->where('user_role_agent.agent_id', $agentId)
                    ->where('user_role_agent.role', '1')
                    ->orderBy('user.nama', 'ASC')
                    ->findAll();
    }

    /**
     * Get all staff of an agent
     * 
     * @param int $agentId
     * @return array
     */
    public function getAgentStaff($agentId)
    {
        return $this->select('user_role_agent.*, user.nama as user_name, user.email as user_email')
                    ->join('user', 'user.id_user = user_role_agent.user_id')
                    ->where('user_role_agent.agent_id', $agentId)
                    ->where('user_role_agent.role', '2')
                    ->orderBy('user.nama', 'ASC')
                    ->findAll();
    }

    /**
     * Get role statistics
     * 
     * @return object
     */
    public function getRoleStats()
    {
        $total = $this->countAllResults();
        $owners = $this->where('role', '1')->countAllResults();
        $staff = $this->where('role', '2')->countAllResults();
        
        return (object) [
            'total' => $total,
            'owners' => $owners,
            'staff' => $staff
        ];
    }
}
