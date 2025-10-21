<?php
/**
*	App Name	: Admin Template Codeigniter 4	
*	Author		: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2020-2023
*/

namespace App\Controllers;
use App\Models\PdfkirimemailModel;

class Pdfkirimemail extends \App\Controllers\BaseController
{
	public function __construct() {
		
		parent::__construct();
		// $this->mustLoggedIn();
		
		$this->model = new PdfkirimemailModel;	
		$this->data['site_title'] = 'PDF & Kirim Email';

		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/data-tables-ajax.js');		
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/pdfkirimemail.js');
	}
	
	public function index()
	{
		$this->hasPermissionPrefix('read');
		
		$data = $this->data;
		if (!empty($_POST['delete'])) 
		{
			
			$result = $this->model->deleteData();
						
			// $result = true;
			if ($result) {
				$data['msg'] = ['status' => 'ok', 'message' => 'Data akta berhasil dihapus'];
			} else {
				$data['msg'] = ['status' => 'error', 'message' => 'Data akta gagal dihapus'];
			}
		}
		
		$data['result'] = $this->model->getMahasiswa();
		
		$this->view('pdfkirimemail.php', $data);
	}
	
	public function pdf() 
	{
		$nama = $this->model->getMahasiswaById($_GET['id']);

		require_once ROOTPATH . 'app/ThirdParty/MPdf/autoload.php';

		$mpdf = new \Mpdf\Mpdf();

		$html = '
		<style>
		body {
			font-size: 10px;
			font-family:arial;
		}

		td {
			padding:0;
			padding-right: 5px;
			padding-bottom: 0;
		}

		label {width: 200px; display: block}
		</style>

		<div class="container" style="margin-top:-155px;margin-left:85px">
			<table cellspacing="0" cellpadding="0">
				<tr style="height: 5px;">
					<td>Nama</td>
					<td>:</td>
					<td>' . $nama['nama'] . '</td>
				</tr>
				<tr>
					<td>TTL</td>
					<td>:</td>
					<td>' . $nama['tempat_lahir']. ', ' . format_tanggal($nama['tgl_lahir']) . '</td>
				</tr>
				<tr>
					<td>NPM</td>
					<td>:</td>
					<td>' . $nama['npm'] . '</td>
				</tr>
				<tr>
					<td>Prodi</td>
					<td>:</td>
					<td>' . $nama['prodi'] . '</td>
				</tr>
				<tr>
					<td>Fakultas</td>
					<td>:</td>
					<td>' . $nama['fakultas'] . '</td>
				</tr>
				<tr>
					<td>Alamat</td>
					<td>:</td>
					<td>' . $nama['alamat'] . '</td>
				</tr>
				
			</table>
		</div>';

		$html_tandatangan = '
		<style>
		body {
			font-size: 10px;
			font-family:arial;
		}

		.tanggal {
			text-align:center;
			margin-left: -190px;
			margin-top: 5px;
			line-height: 11px;
		}

		</style>

		<div class="tanggal">
		Solo, ' . format_tanggal(date('Y-m-d')) . '<br/>
		Rektor,
		<br/>
		<br/>
		<br/>
		Agus Prawoto Hadi
		<br/>
		NIP. 19900829 201301 1003</div>';
		
		$html_masaberlaku = '
		<style>
		.masa-berlaku {
			margin-left: 30px;
			margin-top: -20px;
		}
		</style>
		<div class="masa-berlaku">Berlaku s.d ' . format_tanggal( date('Y-m-d', strtotime('+ 5 year', time())) ) . '</div>';

		$mpdf->addPage();
		$x = 10;
		$y = 15;
		
		$photo_path = 'public/images/foto/' . $nama['foto'];
		if (!file_exists($photo_path)) {
			$nama['foto'] = 'noimage.png';
		}

		$mpdf->Image('public/images/kartu/kartu_depan.png' , $x, $y, 90, 0, 'png');
		$mpdf->Image('public/images/kartu/kartu_belakang.png', $x + 90 + 10, $y, 90, 0, 'png');
		$mpdf->WriteHTML($html);
		$mpdf->WriteHTML($html_tandatangan);
		$mpdf->WriteHTML($html_masaberlaku);
		$mpdf->Image('public/images/foto/' . $nama['foto'], $x + 5.3, $y + 16.4 , 20, 0, 'jpg');
		$mpdf->Image('public/images/kartu/tanda_tangan_kartu.png', $x + 57, $y + 37, 18.5, 0, 'png');
		$mpdf->Image('public/images/kartu/stempel.png', $x + 45, $y + 35, 18.5, 0, 'png');
		$mpdf->Image('public/images/kartu/qrcode.png', $x + 90 + 10 + 70, $y + 33, 15, 0, 'png');
		
		// $mpdf->debug = true;
		$mpdf->showImageErrors = true;

		if (!empty($_POST['email'])) 
		{
			$filename = 'public/tmp/kartu_'. time() . '.pdf';
			$mpdf->Output($filename,'F');
			
			$email_config = new \Config\EmailConfig;
			$email_data = array('from_email' => $email_config->from
							, 'from_title' => 'Aplikasi Kartu Elektronik'
							, 'to_email' => $_POST['email']
							, 'to_name' => $nama['nama']
							, 'email_subject' => 'Permintaan Kartu Elektronik'
							, 'email_content' => '<h1>KARTU ELEKTRONIK</h1><h2>Hi, ' . $nama['nama'] . '</h2><p>Berikut kami sertakan kartu elektronik atas nama Anda. Anda dapat mengunduhnya pada bagian Attachment.<br/><br/><p>Salam</p>'
							, 'attachment' => ['path' => $filename, 'name' => 'Kartu Elektronik.pdf']
			);
			
			require_once('app/Libraries/SendEmail.php');
			
			$emaillib = new \App\Libraries\SendEmail;
			$emaillib->init();
			$send_email =  $emaillib->send($email_data);

			unlink($filename);
			if ($send_email['status'] == 'ok') {
				$message['status'] = 'ok';
				$message['message'] = 'Kartu elektronik berhasil dikirim ke alamat email: ' . $_POST['email'];
			} else {
				$message['status'] = 'error';
				$message['message'] = 'Kartu elektronik gagal dikirim ke alamat email: ' . $_POST['email'] . '<br/>Error: ' . $send_email['message'];
			}
			
			echo json_encode($message);
			exit();
		}
		// $mpdf->Output();
		$mpdf->Output('Kartu Elektronik.pdf', 'D');

		exit();
	}
	public function getDataDT() {
		
		$this->hasPermissionPrefix('read');
		
		$num_data = $this->model->countAllData();
		$result['draw'] = $start = $this->request->getPost('draw') ?: 1;
		$result['recordsTotal'] = $num_data;
		$result['recordsFiltered'] = $num_data;
		$query = $this->model->getListData( ' WHERE 1 = 1 ');
				
		helper('html');
		
		foreach ($query as $key => &$val) 
		{
			$image = 'noimage.png';
			if ($val['foto']) {
				if (file_exists('public/images/foto/' . $val['foto'])) {
					$image = $val['foto'];
				}
			}
			
			$val['foto'] = '<div class="list-foto"><img src="'. $this->config->baseURL.'public/images/foto/' . $image . '"/></div>';
			$val['tgl_lahir'] = $val['tempat_lahir'] . ', '. format_tanggal($val['tgl_lahir']);
			
			$val['ignore_search_action'] = btn_action([
												'pdf' => ['url' => $this->config->baseURL . $this->currentModule['nama_module'] . '/pdf?id='. $val['id_mahasiswa']
													, 'btn_class' => 'btn-danger me-1'
													, 'icon' => 'fas fa-file-pdf'
													, 'text' => 'PDF'
												],
												'Email' => ['url' => '#'
																, 'btn_class' => 'btn-primary me-1 kirim-email'
																, 'icon' => 'fas fa-paper-plane'
																, 'text' => 'Email'
																, 'attr' => ['data-id' => $val['id_mahasiswa'], 'data-email' => $val['email'], 'target' => '_blank']
												]
										
											]);
		}
					
		$result['data'] = $query;
		echo json_encode($result); exit();
	}
	
}
