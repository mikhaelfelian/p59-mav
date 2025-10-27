<?php
/**
 * Admin Template Codeigniter 4
 * Author: Agus Prawoto Hadi
 * Website: https://jagowebdev.com
 * Year: 2020-2023
 */

namespace App\Models;

use App\Libraries\Auth;

/**
 * Recovery Model
 * Handles password recovery operations
 * 
 * CI 4.3.1 compliant | PSR-4
 * 
 * @package    CodeIgniter
 * @category   Model
 * @author     Agus Prawoto Hadi
 * @version    4.3.1
 *
 * Modified by Mikhael Felian Waskito
 * @link       https://github.com/mikhaelfelian/p59-mav
 * @notes      Refactored to CI4-compliant structure using BaseModel inheritance
 */
class RecoveryModel extends BaseModel
{
    protected $table = 'user_recovery';
    protected $primaryKey = 'id_recovery';
    protected $allowedFields = [];
    protected $useTimestamps = false;

    /**
     * Get user by email
     * 
     * @param string $email User email
     * @return array|null
     */
    public function getUserByEmail(string $email): ?array
    {
        return $this->builder('user')
                   ->where('email', $email)
                   ->get()
                   ->getRowArray();
    }

    /**
     * Check token by selector
     * 
     * @param string $selector Token selector
     * @return array|null
     */
    public function checkToken(string $selector): ?array
    {
        return $this->builder('user_token')
                   ->where('selector', $selector)
                   ->get()
                   ->getRowArray();
    }

    /**
     * Send recovery link
     * 
     * @return array
     */
    public function sendLink(): array
    {
        $error = false;
        $message['status'] = 'error';

        $email = $this->request->getPost('email');
        $user = $this->getUserByEmail($email);

        $this->db->transStart();

        $this->db->table('user_token')->delete(['action' => 'recovery', 'id_user' => $user['id_user']]);
        $auth = new Auth();
        $token = $auth->generateDbToken();
        $data_db['selector'] = $token['selector'];
        $data_db['token'] = $token['db'];
        $data_db['action'] = 'recovery';
        $data_db['id_user'] = $user['id_user'];
        $data_db['created'] = date('Y-m-d H:i:s');
        $data_db['expires'] = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $insert_token = $this->db->table('user_token')->insert($data_db);

        if ($insert_token) {
            $url_token = $token['selector'] . ':' . $token['external'];
            $url = base_url() . '/recovery/reset?token=' . $url_token;

            helper('email_registrasi');
            $email_config = new \Config\EmailConfig();
            $email_data = [
                'from_email' => $email_config->from,
                'from_title' => 'Jagowebdev',
                'to_email' => $email,
                'to_name' => $email,
                'email_subject' => 'Reset Password',
                'email_content' => str_replace('{{url}}', $url, email_recovery_content()),
                'images' => ['logo_text' => ROOTPATH . 'public/images/logo_text.png']
            ];

            require_once('app/Libraries/SendEmail.php');
            $emaillib = new \App\Libraries\SendEmail();
            $emaillib->init();
            $emaillib->setProvider($email_config->provider);
            $send_email = $emaillib->send($email_data);

            if ($send_email['status'] == 'ok') {
                $this->db->transCommit();

                $message['status'] = 'ok';
                $message['message'] = '
                Link reset password berhasil dikirim ke alamat email: <strong>' . $email . '</strong>, silakan gunakan link tersebut untuk mereset password Anda<br/><br/>Biasanya, email akan sampai kurang dari satu menit, namun jika lebih dari lima menit email belum sampai, coba cek folder spam. Jika email benar benar tidak sampai, silakan hubungi kami di <a href="mailto:' . $email_config->emailSupport . '" target="_blank">' . $email_config->emailSupport . '</a>';
            } else {
                $message['message'] = 'Error: Link reset password gagal dikirim... <strong>' . $send_email['message'] . '</strong>';
            }
        } else {
            $email_config = new \Config\EmailConfig();
            $message['message'] = 'Gagal menyimpan data token, silakan hubungi kami di: <a href="mailto:' . $email_config->emailSupport . '" target="_blank">' . $email_config->emailSupport . '</a>';
        }

        return $message;
    }

    /**
     * Update password
     * 
     * @param array $dbtoken Token data
     * @return bool
     */
    public function updatePassword(array $dbtoken): bool
    {
        $this->db->table('user_token')->delete(['selector' => $dbtoken['selector']]);
        $password = $this->request->getPost('password');
        $update = $this->db->table('user')->update(['password' => password_hash($password, PASSWORD_DEFAULT)], ['id_user' => $dbtoken['id_user']]);
        return $update;
    }
}
