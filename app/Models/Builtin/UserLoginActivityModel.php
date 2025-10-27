<?php
/**
 * Admin Template Codeigniter 4 	
 * Author: Agus Prawoto Hadi
 * Website: https://jagowebdev.com
 * Year: 2020-2023
 */

namespace App\Models\Builtin;

use CodeIgniter\Model;

/**
 * User Login Activity Model
 * Tracks user login activities for audit purposes
 * 
 * CI 4.3.1 compliant | PSR-4
 * 
 * @package    CodeIgniter
 * @category   Model
 * @author     Agus Prawoto Hadi
 * @table      user_login_activity
 * @version    4.3.1
 */
class UserLoginActivityModel extends Model
{
    protected $table = 'user_login_activity';
    protected $primaryKey = 'id_user_login_activity';
    protected $allowedFields = ['id_user', 'id_activity', 'time'];
    protected $useTimestamps = false;
    
    protected $validationRules = [
        'id_user' => 'required|integer',
        'id_activity' => 'required|integer',
    ];
    
    protected $validationMessages = [
        'id_user' => [
            'required' => 'User ID is required',
            'integer' => 'User ID must be an integer'
        ],
        'id_activity' => [
            'required' => 'Activity ID is required',
            'integer' => 'Activity ID must be an integer'
        ],
    ];

    /**
     * Record a login activity
     * 
     * @param int $id_user User ID
     * @param int $id_activity Activity ID (1 = login, etc.)
     * @return bool|int Insert ID or false on failure
     */
    public function recordActivity(int $id_user, int $id_activity = 1)
    {
        $data = [
            'id_user' => $id_user,
            'id_activity' => $id_activity,
            'time' => date('Y-m-d H:i:s')
        ];
        
        return $this->insert($data);
    }

    /**
     * Get user login history
     * 
     * @param int $id_user User ID
     * @param int $limit Number of records to retrieve
     * @return array
     */
    public function getUserHistory(int $id_user, int $limit = 10)
    {
        return $this->where('id_user', $id_user)
                    ->orderBy('time', 'DESC')
                    ->findAll($limit);
    }
}

