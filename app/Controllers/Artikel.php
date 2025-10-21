<?php
namespace App\Controllers;
use App\Models\ArtikelModel;

class Artikel extends BaseController
{
	private $configFilepicker;
	
	public function __construct() 
	{
		parent::__construct();
		$this->model = new ArtikelModel;
		$this->configFilepicker = new \Config\Filepicker();
		
		$this->addJs('
			var filepicker_server_url = "' . $this->configFilepicker->serverURL . '";
			var filepicker_icon_url = "' . $this->configFilepicker->iconURL . '";', true
		);
			
		$this->addJs($this->config->baseURL . 'public/vendors/jquery.select2/js/select2.full.min.js');
		$this->addJs($this->config->baseURL . 'public/vendors/tinymce/tinymce.js');
		$this->addJs($this->config->baseURL . 'public/vendors/flatpickr/dist/flatpickr.js');
		$this->addJs($this->config->baseURL . 'public/themes/modern/js/artikel.js');

		$this->addStyle($this->config->baseURL . 'public/themes/modern/css/artikel.css');	
		$this->addStyle($this->config->baseURL . 'public/vendors/flatpickr/dist/flatpickr.min.css');
		$this->addStyle(['attr' => ['id' => 'style-head-flatpickr'], 'url' => $this->config->baseURL . 'public/vendors/flatpickr/dist/themes/' . $this->themeMode .'.css?r='.time()]);
		$this->addStyle($this->config->baseURL . 'public/vendors/jquery.select2/css/select2.min.css');
		$this->addStyle($this->config->baseURL . 'public/vendors/dropzone/dropzone.min.css');

		$this->addJs($this->config->baseURL . 'public/vendors/jwdfilepicker/jwdfilepicker.js');
		$this->addJs($this->config->baseURL . 'public/themes/modern/js/jwdfilepicker-defaults.js');
		$this->addJs($this->config->baseURL . 'public/vendors/dropzone/dropzone.min.js');
		$this->addStyle($this->config->baseURL . 'public/vendors/jwdfilepicker/jwdfilepicker.css');
		$this->addStyle($this->config->baseURL . 'public/vendors/jwdfilepicker/jwdfilepicker-loader.css');
		$this->addStyle($this->config->baseURL . 'public/vendors/jwdfilepicker/jwdfilepicker-modal.css');
	}
	
	public function index()
	{
		$message = [];
		
		if (!empty($_POST['delete'])) 
		{
			$message= $this->model->deleteArtikel();
		}
		
       $artikel = $this->model->getAllArtikel($this->whereOwn());
		
		if (!$artikel) {
            $this->errorDataNotfound();
			return;
		}
		
		foreach ($artikel as $val) {
			$in[] = $val['id_artikel'];
			$in_mask[] = '?';
		}
		
		// Artikel Author
		$artikel_author = $this->model->getArtikelAuthor($in_mask, $in);
		
		// Kategori
		$artikel_kategori = $this->model->getArtikelKategori($in_mask, $in);
		
        $this->data['title'] = 'Edit Artikel';
        $this->data['artikel'] = $artikel;
        $this->data['artikel_kategori'] = $artikel_kategori;
        $this->data['artikel_author'] = $artikel_author;
        $this->data['message'] = $message;

        $this->view('artikel-result.php', $this->data);
	}
	
	public function add() 
	{
		$this->data['title'] = 'Add Artikel';
		
		$message = [];
		$id_artikel = '';
		$artikel = [];
		if (!empty($_POST['submit'])) {
			$save = $this->model->saveData();
			$message = $save['message'];
			if ($message['status'] == 'ok') {
				$id_artikel = $save['id_artikel'];
				$data['title'] = 'Edit Artikel';
			}
		}
		
		$set_data = $this->model->setData($id_artikel, $this->whereOwn());
		
		if ($set_data['artikel']) {		
			foreach ($artikel as $key => $val) {
				$data[$key] = $val;
			}
		}
		
		$this->data = array_merge($this->data, $set_data);
        $this->data['message'] = $message;
		
        $this->view('artikel-form.php', $this->data);
	}
	
	public function edit() 
	{
		$this->data['title'] = 'Edit Artikel';
		if (empty($_GET['id'])) {
			$this->errorDataNotFound();
			return;
		}
		 
		$message = [];
		if (!empty($_POST['submit'])) {
			$save = $this->model->saveData();
			$message = $save['message'];
		}
		
		$set_data = $this->model->setData($_GET['id'], $this->whereOwn());
		if (!$set_data['artikel']) {
			$this->errorDataNotFound();
			return;
        }
		
		foreach ($set_data['artikel'] as $key => $val) {
			$this->data[$key] = $val;
		}
				
		$this->data = array_merge($this->data, $set_data);
        $this->data['message'] = $message;
		
        $this->view('artikel-form.php', $this->data);
	}
}