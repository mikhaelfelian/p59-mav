<?php
/**
 * Admin Template Codeigniter 4 	
 * Author: Agus Prawoto Hadi
 * Website: https://jagowebdev.com
 * Year: 2020-2023
 */

namespace App\Models\Builtin;

use App\Libraries\Auth;
use App\Models\Builtin\UserLoginActivityModel;
use App\Models\Builtin\UserModel;
use App\Models\Builtin\UserTokenModel;

/**
 * Login Model
 * Orchestrates login-related operations using specialized models
 * 
 * CI 4.3.1 compliant | PSR-4
 * 
 * @package    CodeIgniter
 * @category   Model
 * @author     Agus Prawoto Hadi
 * @version    4.3.1
 */
class LoginModel extends \App\Models\BaseModel
{
    /**
     * @var UserLoginActivityModel
     */
    protected $userLoginActivityModel;
    
    /**
     * @var UserTokenModel
     */
    protected $userTokenModel;
    
    
    /**
     * @var UserModel
     */
    protected $userModel;
    
    /**
     * @var Auth
     */
    protected $auth;
    
    public function __construct()
    {
        parent::__construct();
        $this->userLoginActivityModel = new UserLoginActivityModel();
        $this->userTokenModel = new UserTokenModel();
        $this->userModel = new UserModel();
        $this->auth = new Auth();
    }
    
    /**
     * Record user login activity
     * 
     * @return bool
     */
    public function recordLogin() 
    {
        $username = $this->request->getPost('username'); 
        
        // Get user ID from username
        $user = $this->userModel->where('username', $username)->first();
        
        if (!$user) {
            return false;
        }

        // Record login activity
        return $this->userLoginActivityModel->recordActivity($user->id_user, 1);
    }
    
    /**
     * Set user remember token
     * 
     * @param array $user User data
     * @return bool
     */
    public function setUserToken($user) 
    {
        $token = $this->auth->generateDbToken();
        $expired_time = time() + (7*24*3600); // 7 days
        
        // Set cookie
        setcookie('remember', $token['selector'] . ':' . $token['external'], $expired_time, '/');
        
        // Prepare data for database
        $data = [
            'id_user' => $user['id_user'],
            'selector' => $token['selector'],
            'token' => $token['db'],
            'action' => 'remember',
            'created' => date('Y-m-d H:i:s'),
            'expires' => date('Y-m-d H:i:s', $expired_time)
        ];

        return $this->userTokenModel->createToken($data);
    }
    
    /**
     * Delete authentication cookie and token
     * 
     * @param int $id_user User ID
     * @return bool
     */
    public function deleteAuthCookie($id_user) 
    {
        // Delete from database
        $deleted = $this->userTokenModel->deleteByUserAndAction($id_user, 'remember');
        
        // Delete cookie
        setcookie('remember', '', time() - 360000, '/');
        
        return $deleted;
    }
    
    /**
     * Get registration settings
     * 
     * @return array
     */
    public function getSettingRegistrasi() 
    {
        $sql = 'SELECT * FROM setting WHERE type="register"';
        return $this->db->query($sql)->getResultArray();
    }
}
