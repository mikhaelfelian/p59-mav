<?php
/**
* PHP Admin Template
*	Author		: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2021-2023
*/

namespace App\Controllers;
use App\Models\GalleryModel;

class Gallery extends BaseController
{
	private $configFilepicker;
	
	public function __construct() 
	{
		parent::__construct();
		$this->model = new GalleryModel;
		$this->configFilepicker = new \Config\Filepicker();
		
		$ajax = false;
		if( !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' ) {
			$ajax = true;
		}

		if (!$ajax) {
			$this->addJs('
				var filepicker_server_url = "' . $this->configFilepicker->serverURL . '";
				var filepicker_icon_url = "' . $this->configFilepicker->iconURL . '";', true
			);
		}
		
		$this->addJs($this->config->baseURL . 'public/vendors/dragula/dragula.min.js');
		$this->addJs($this->config->baseURL . 'public/themes/modern/js/gallery.js');
		$this->addJs($this->config->baseURL . 'public/vendors/tinymce/tinymce.js');
		$this->addJs($this->config->baseURL . 'public/vendors/jwdfilepicker/jwdfilepicker.js');
		$this->addJs($this->config->baseURL . 'public/themes/modern/js/jwdfilepicker-defaults.js');
		$this->addJs($this->config->baseURL . 'public/vendors/dropzone/dropzone.min.js');

		$this->addStyle($this->config->baseURL . 'public/vendors/dragula/dragula.min.css');
		$this->addStyle($this->config->baseURL . 'public/vendors/dropzone/dropzone.min.css');
		$this->addStyle($this->config->baseURL . 'public/themes/modern/css/gallery.css');
		$this->addStyle($this->config->baseURL . 'public/vendors/jwdfilepicker/jwdfilepicker.css');
		$this->addStyle($this->config->baseURL . 'public/vendors/jwdfilepicker/jwdfilepicker-loader.css');
		$this->addStyle($this->config->baseURL . 'public/vendors/jwdfilepicker/jwdfilepicker-modal.css');
	}
	
	public function index()
	{
		$message = [];
				
		$kategori = $this->model->getKategori();
		$gallery = $this->model->getGallery();

        $this->data['title'] = 'Gallery';
        $this->data['message'] = $message;
        $this->data['gallery'] = $gallery;
		$this->data = array_merge ($this->data, $kategori);
		
        $this->view('gallery-kategori-result.php', $this->data);
	}
	
	public function ajaxUpdateKategoriSort() 
	{
		$result = $this->model->updateKategoriSort();
		
		echo json_encode($result);
		exit();
	}
	
	// Kategori
	public function addKategori() 
	{
		$message = [];
		$kategori = [];
		$id = '';
		
		if (!empty($_POST['submit'])) 
		{
			$save = $this->saveDataKategori();
			$message = $save['message'];
			if ($save['message']['status'] == 'ok') {
				$id = $save['id_gallery_kategori'];
			}
		}
		
		if ($id) {
			$kategori = $this->model->getKategoriById($id);
		}
		
        $this->data['title'] = 'Add Kategori';
        $this->data['kategori'] = $kategori;
        $this->data['id_gallery_kategori'] = $id;
        $this->data['message'] = $message;
		
        $this->view('gallery-kategori-form.php', $this->data);
		
	}
	
	public function editKategori() 
	{
		$message = [];
		if (!empty($_POST['submit'])) {
			$save = $this->saveDataKategori();
			$message = $save['message'];
		}

        $kategori = $this->model->getKategoriById($_GET['id']);
		
		if (!$kategori) {
            $this->errorDataNotfound();
			return;
		}
		
        $this->data['title'] = 'Edit Kategori';
        $this->data['kategori'] = $kategori;
        $this->data['id_gallery_kategori'] = $_GET['id'];
        $this->data['message'] = $message;
		
        $this->view('gallery-kategori-form.php', $this->data);
		
	}
	
	private function saveDataKategori() 
	{
		global $db;
		$message = [];
		$id_gallery_kategori = '';

		if (!empty($_POST['submit'])) 
		{
			$error = $this->validateForm();
			if ($error) {
				$message['status'] = 'error';
				$message['message'] = $error;
			} else {
				
				$result = $this->model->saveKategori();
				$message = $result['message'];
				$id_gallery_kategori = $result['id_gallery_kategori'];
			}
		}
		return ['message' => $message, 'id_gallery_kategori' => $id_gallery_kategori];
	}
	
	private function validateForm() 
	{
		 $error = false;
		if (empty(trim($_POST['judul_kategori']))) {
			$error[] = 'Judul kategori harus diisi';
		}

		if (empty(trim($_POST['deskripsi']))) {
			$error[] = 'Deskripsi artikel harus diisi';
		}
		
		if (empty(trim($_POST['layout']))) {
			$error[] = 'Opsi Layout harus dipilih';
		}
		
		if (empty($_POST['aktif'])) {
			$error[] = 'Opsi Aktif harus dipilih';
		}

		return $error;
	}
	
	public function editGallery() 
	{
		$message = [];
		if (!empty($_POST['submit'])) {
			$save = save_data();
			$message = $save['message'];
		}
		
		// Kategori
		$kategori = $this->model->getAllKategori();
		
		$id_kategori = '';
		if (!empty($_GET['id_kategori'])) {
			$id_kategori = $_GET['id_kategori'];
		}
		
        // Gallery
		$gallery = $this->model->getGalleryByKategori($id_kategori);
		
		if ($gallery) {
			foreach ($gallery as &$val) 
			{
				$meta_file = json_decode($val['meta_file'], true);
				// echo '<pre>'; print_r($gallery); die;
				if (key_exists('thumbnail', $meta_file)) {
					$thumbnail_file = $meta_file['thumbnail']['small']['filename'];
				} else {
					$thumbnail_file = $val['nama_file'];
				}
				
				$config = new \Config\Filepicker();
				$thumbnail_url = $config->uploadURL . $thumbnail_file;
				$val['thumbnail']['url'] = $thumbnail_url;
			}
	
		} else {
			$message['status'] = 'error';
			$message['message'] = 'Gallery tidak ditemukan';
		}
		
        $this->data['title'] = 'Edit Gallery';
        $this->data['kategori'] = $kategori;
        $this->data['id_kategori'] = $id_kategori;
        $this->data['gallery'] = $gallery;
        $this->data['message'] = $message;
		
        $this->view('gallery-form.php', $this->data);
	}
	
	public function ajaxKategoriDelete() {
		
		$result = $this->model->deleteKategori();
		
		echo json_encode($result);
		exit();
	}
	
	// Gallery
	public function ajaxGalleryChangeImageOrder() {
		
		$result = $this->model->galleryChangeImageOrder();
		
		echo json_encode($result);
		exit();
	}
	
	public function ajaxGalleryChangeImageCategory() {
		
		$result = $this->model->galleryChangeImageCategory();
		
		echo json_encode($result);
		exit();
	}
	
	public function ajaxGalleryDeleteImage() {
		
		$result = $this->model->galleryDeleteImage();
		
		echo json_encode($result);
		exit();	
	}
	
	public function ajaxGalleryAddImage() 
	{
		$result = $this->model->galleryAddImage();
		
		echo json_encode($result);
		exit();	
	}
}
