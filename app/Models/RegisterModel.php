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
 * Register Model
 * Handles user registration operations
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
class RegisterModel extends BaseModel
{
    protected $table = 'user_registration';
    protected $primaryKey = 'id_registration';
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
     * Resend activation link
     * 
     * @return array
     */
    public function resendLink(): array
    {
        $error = false;
        $message['status'] = 'error';

        $email = $this->request->getPost('email');
        $user = $this->getUserByEmail($email);

        $this->db->transBegin();

        $this->db->table('user_token')->delete(['action' => 'activation', 'id_user' => $user['id_user']]);

        $auth = new Auth();
        $token = $auth->generateDbToken();
        $data_db['selector'] = $token['selector'];
        $data_db['token'] = $token['db'];
        $data_db['action'] = 'activation';
        $data_db['id_user'] = $user['id_user'];
        $data_db['created'] = date('Y-m-d H:i:s');
        $data_db['expires'] = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $insert_token = $this->db->table('user_token')->insert($data_db);

        $email_config = new \Config\EmailConfig();

        if ($insert_token) {
            $send_email = $this->sendConfirmEmail($token, $user, 'link_aktivasi');

            if ($send_email['status'] == 'ok') {
                $this->db->transCommit();
                $message['status'] = 'ok';
                $message['message'] = '
                Link aktivasi berhasil dikirim ke alamat email: <strong>' . $email . '</strong>, silakan gunakan link tersebut untuk aktivasi akun Anda<br/><br/>Biasanya, email akan sampai kurang dari satu menit, namun jika lebih dari lima menit email belum sampai, coba cek folder spam. Jika email benar benar tidak sampai, silakan hubungi kami di <a href="mailto:' . $email_config->emailSupport . '" target="_blank">' . $email_config->emailSupport . '</a>';
            } else {
                $message['message'] = 'Error: Link aktivasi gagal dikirim... <strong>' . $send_email['message'] . '</strong>';
                $error = true;
            }
        } else {
            $message['message'] = 'Gagal menyimpan data token, silakan hubungi kami di: <a href="mailto:' . $email_config->emailSupport . '" target="_blank">' . $email_config->emailSupport . '</a>';
            $error = true;
        }

        if ($error) {
            $this->db->transRollback();
        }

        return $message;
    }

    /**
     * Check user by ID
     * 
     * @param int $id_user User ID
     * @return array|null
     */
    public function checkUserById(int $id_user): ?array
    {
        return $this->builder('user')
                   ->where('id_user', $id_user)
                   ->get()
                   ->getRowArray();
    }

    /**
     * Update user (activate)
     * 
     * @param array $dbtoken Token data
     * @return bool
     */
    public function updateUser(array $dbtoken): bool
    {
        $this->db->transStart();

        $this->db->table('user_token')->delete(['selector' => $dbtoken['selector']]);
        $this->db->table('user_token')->delete(['action' => 'register', 'id_user' => $dbtoken['id_user']]);

        $this->db->table('user')->update(['verified' => 1], ['id_user' => $dbtoken['id_user']]);

        return $this->db->transComplete();
    }

    /**
     * Check token
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
     * Insert new user
     * 
     * @return array
     */
    public function insertUser(): array
    {
        $error = false;
        $message['status'] = 'error';

        $this->db->transBegin();
        $setting_register = $this->getSettingRegistrasi();
        $verified = $setting_register['metode_aktivasi'] == 'langsung' ? 1 : 0;

        $data_db = [
            'nama' => $this->request->getPost('nama'),
            'email' => $this->request->getPost('email'),
            'username' => $this->request->getPost('username'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'verified' => $verified,
            'status' => 'active',
            'created' => date('Y-m-d H:i:s'),
            'default_page_id_module' => $setting_register['default_page_id_module'],
            'default_page_type' => $setting_register['default_page_type'],
            'default_page_id_role' => $setting_register['default_page_id_role'],
            'default_page_url' => $setting_register['default_page_url']
        ];

        $insert_user = $this->db->table('user')->insert($data_db);
        $id_user = $this->db->insertID();

        if (!$id_user) {
            $message['message'] = 'System error, please try again later...';
            $error = true;
        } else {
            // Default role
            $setting = $this->builder('setting')
                          ->where('type', 'register')
                          ->where('param', 'id_role')
                          ->get()
                          ->getRowArray();
            $id_role = $setting['value'];

            $data_db = [
                'id_user' => $id_user,
                'id_role' => $id_role
            ];
            $insert_user = $this->db->table('user_role')->insert($data_db);

            $email = $this->request->getPost('email');

            if ($setting_register['metode_aktivasi'] == 'manual') {
                $message['message'] = 'Terima kasih telah melakukan registrasi, aktivasi akun Anda menunggu persetujuan Administrator. Terima Kasih';
            } else if ($setting_register['metode_aktivasi'] == 'langsung') {
                $message['message'] = 'Terima kasih telah melakukan registrasi, akun Anda otomatis aktif dan langsung dapat digunakan, silakan <a href="' . base_url() . '/login">login disini</a>';
            } else if ($setting_register['metode_aktivasi'] == 'email') {
                $auth = new Auth();
                $token = $auth->generateDbToken();
                $data_db = [
                    'selector' => $token['selector'],
                    'token' => $token['db'],
                    'action' => 'register',
                    'id_user' => $id_user,
                    'created' => date('Y-m-d H:i:s'),
                    'expires' => date('Y-m-d H:i:s', strtotime('+1 hour'))
                ];

                $this->db->table('user_token')->insert($data_db);

                $postData = [
                    'nama' => $this->request->getPost('nama'),
                    'email' => $email
                ];
                $send_email = $this->sendConfirmEmail($token, $postData);

                if ($send_email['status'] == 'error') {
                    $message['message'] = 'Error: Link konfirmasi gagal dikirim... <strong>' . $send_email['message'] . '</strong>';
                    $error = true;
                } else {
                    $message['message'] = 'Terima kasih telah melakukan registrasi, untuk memastikan bahwa kamu adalah pemilik alamat email <strong>' . $email . '</strong>, mohon klik link konfirmasi yang baru saja kami kirimkan ke alamat email tersebut<br/><br/>Biasanya, email akan sampai kurang dari satu menit, namun jika lebih dari lima menit email belum sampai, coba cek folder spam. Jika email benar benar tidak sampai, silakan hubungi kami di support@jagowebdev.com';
                }
            }
        }

        if ($error) {
            $this->db->transRollback();
        } else {
            $this->db->transCommit();
            $message['status'] = 'ok';
        }

        return $message;
    }

    /**
     * Send confirmation email
     * 
     * @param array $token Token data
     * @param array $user User data
     * @param string $type Email type
     * @return array
     */
    private function sendConfirmEmail(array $token, array $user, string $type = 'email_confirm'): array
    {
        helper('email_registrasi');

        if ($type == 'email_confirm') {
            $email_text = email_registration_content();
        } else {
            $email_text = email_resendlink_content();
        }

        $url_token = $token['selector'] . ':' . $token['external'];
        $url = base_url() . '/register/confirm?token=' . $url_token;
        $email_content = str_replace('{{NAME}}', $user['nama'], $email_text);

        $email_content = str_replace('{{url}}', $url, $email_content);

        $email_config = new \Config\EmailConfig();
        $email_data = [
            'from_email' => $email_config->from,
            'from_title' => 'Jagowebdev',
            'to_email' => $user['email'],
            'to_name' => $user['nama'],
            'email_subject' => 'Konfirmasi Registrasi Akun',
            'email_content' => $email_content,
            'images' => ['logo_text' => ROOTPATH . 'public/images/logo_text.png']
        ];

        require_once('app/Libraries/SendEmail.php');

        $emaillib = new \App\Libraries\SendEmail();
        $emaillib->init();
        $emaillib->setProvider($email_config->provider);
        $send_email = $emaillib->send($email_data);

        return $send_email;
    }
}
