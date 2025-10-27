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
 * User Token Model
 * Handles remember-me functionality and user tokens
 * 
 * CI 4.3.1 compliant | PSR-4
 * 
 * @package    CodeIgniter
 * @category   Model
 * @author     Agus Prawoto Hadi
 * @table      user_token
 * @version    4.3.1
 */
class UserTokenModel extends Model
{
    protected $table = 'user_token';
    protected $primaryKey = 'id_user_token';
    protected $allowedFields = ['id_user', 'selector', 'token', 'action', 'created', 'expires'];
    protected $useTimestamps = false;
    
    protected $validationRules = [
        'id_user' => 'required|integer',
        'selector' => 'required|max_length[12]',
        'token' => 'required',
        'action' => 'required|max_length[50]',
    ];
    
    protected $validationMessages = [
        'id_user' => [
            'required' => 'User ID is required',
            'integer' => 'User ID must be an integer'
        ],
    ];

    /**
     * Create a new user token
     * 
     * @param array $data Token data
     * @return bool|int Insert ID or false on failure
     */
    public function createToken(array $data)
    {
        return $this->insert($data);
    }

    /**
     * Delete token by user ID and action
     * 
     * @param int $id_user User ID
     * @param string $action Action type
     * @return bool
     */
    public function deleteByUserAndAction(int $id_user, string $action = 'remember')
    {
        return $this->where('id_user', $id_user)
                    ->where('action', $action)
                    ->delete();
    }

    /**
     * Find token by selector
     * 
     * @param string $selector Token selector
     * @return array|null
     */
    public function findBySelector(string $selector)
    {
        return $this->where('selector', $selector)->first();
    }

    /**
     * Delete expired tokens
     * 
     * @return bool
     */
    public function deleteExpiredTokens()
    {
        return $this->where('expires <', date('Y-m-d H:i:s'))->delete();
    }
}

