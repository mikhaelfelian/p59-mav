<?php namespace App\Controllers;

use App\Libraries\GroceryCrud;
use App\Models\GroceryCrudDatatablesAjaxModel;

class Grocery_crud_datatables_ajax extends BaseController
{
	
	private $crud = '';
	
	public function __construct() {
		
		parent::__construct();
		$this->data['title'] = 'Grocery CRUD Datatables Ajax';
		$this->ajaxUrl = base_url() . '/grocery-crud-datatables-ajax/getDataDT';
		$this->model = new GroceryCrudDatatablesAjaxModel;
		
	}
	
	public function index() 
	{
		$this->data['list_examples'] = [
			module_url() . '/customers-management' => 'Customers Management',
			module_url() . '/orders-management' => 'Orders Management (Date Picker dan Select2)',
			module_url() . '/offices-management' => 'Officers Management (View)',
			module_url() . '/products-management' => 'Products Management',
			module_url() . '/employees-management' => 'Employees Management',
			module_url() . '/film-management' => 'Film Management (Multi select)',
		];
       $this->view('grocery-crud-index', $this->data);
    }
	
	public function customers_management()
	{
	    $crud = new GroceryCrud();
		$this->crud = $crud;
		
	    $crud->setTable('customers');
		$crud->setDataTablesAjaxUrl($this->ajaxUrl);
		
		$setting['order'] = [2,"asc"];
		$setting['columnDefs'] = [
					["targets" => 0,"orderable"=>false]
				   ,["targets"=> 1,"orderable" => false]
				   ,["targets" => 8]
				   ,["orderable" => false]
		];
		
		$crud->setDataTablesAjaxSetting($setting);
		$crud->unsetJquery();
		$crud->setTheme('datatables-ajax');
		// $crud->setLanguage("indonesian");

	    $render = $crud->render();
		$this->setAssets($render);

		$this->view('grocery-crud', $this->data);
	}

	public function orders_management() {
        $crud = new GroceryCrud();

        $crud->setRelation('customerNumber','customers','{contactLastName} {contactFirstName}');
        $crud->displayAs('customerNumber','Customer');
        $crud->setTable('orders');
        $crud->setSubject('Order');
        $crud->unsetAdd();
        $crud->unsetDelete();
		// $crud->setLanguage("indonesian");
		$crud->unsetJquery();
		$crud->setTheme('datatables-ajax');
		$crud->setDataTablesAjaxUrl($this->ajaxUrl);

        $output = $crud->render();
		$this->setAssets($output);
      
		$this->view('grocery-crud', $this->data);
    }
	
    public function offices_management () {
        $crud = new GroceryCrud();

        $crud->setTheme('datatables-ajax');
        $crud->setTable('offices');
        $crud->setSubject('Office');
        $crud->requiredFields(['city']);
        $crud->columns(['city','country','phone','addressLine1','postalCode']);
        $crud->setRead();
		$crud->unsetJquery();
		$crud->setDataTablesAjaxUrl($this->ajaxUrl);

        $output = $crud->render();
		$this->setAssets($output);

        $this->view('grocery-crud', $this->data);
    }

    public function products_management() {
        $crud = new GroceryCrud();

        $crud->setTable('products');
        $crud->setSubject('Product');
        $crud->unsetColumns(['productDescription']);
        $crud->callbackColumn('buyPrice', function ($value) {
            return $value.' &euro;';
        });
		$crud->unsetJquery();
		$crud->setTheme('datatables-ajax');
		$crud->setDataTablesAjaxUrl($this->ajaxUrl);

		$output = $crud->render();
		$this->setAssets($output);

        $this->view('grocery-crud', $this->data);
    }

    public function employees_management()
    {
        $crud = new GroceryCrud();

		$crud->setTheme('datatables-ajax');
        $crud->setTable('employees');
        $crud->setRelation('officeCode','offices','city');
        $crud->displayAs('officeCode','Office City');
        $crud->setSubject('Employee');
		$crud->setDataTablesAjaxUrl($this->ajaxUrl);

        $crud->requiredFields(['lastName']);

        $output = $crud->render();
		$this->setAssets($output);
		
        $this->view('grocery-crud', $this->data);
    }

    public function film_management()
    {
        $crud = new GroceryCrud();

        $crud->setTable('film');
        $crud->setRelationNtoN('actors', 'film_actor', 'actor', 'film_id', 'actor_id', 'fullname');
        $crud->setRelationNtoN('category', 'film_category', 'category', 'film_id', 'category_id', 'name');
        $crud->unsetColumns(['special_features','description','actors']);
		$crud->unsetJquery();
		$crud->setTheme('datatables-ajax');
		$crud->setDataTablesAjaxUrl($this->ajaxUrl);
		
        $crud->fields(['title', 'description', 'actors' ,  'category' ,'release_year', 'rental_duration', 'rental_rate', 'length', 'replacement_cost', 'rating', 'special_features']);

        $output = $crud->render();
		$this->setAssets($output);
		
        $this->view('grocery-crud', $this->data);
    }
	
	public function getDataDT() {
		
		
		$this->hasPermissionPrefix('read');
		
		$num_data = $this->model->countAllData();
		$result['draw'] = $start = $this->request->getPost('draw') ?: 1;
		$result['recordsTotal'] = $num_data;
		
		$query = $this->model->getListData();
		$result['recordsFiltered'] = $query['total_filtered'];
				
		$result['data'] = $query['data'];
		
		$unset_read = $_GET['unset_read'];
		$unset_clone = $_GET['unset_clone'];
		$unset_edit = $_GET['unset_edit'];
		$unset_delete = $_GET['unset_delete'];
		if(!$unset_delete || !$unset_edit || !$unset_read) 
		{
			foreach($result['data'] as &$val) 
			{
				$button = '';
				$primary_key = $val[$_GET['primary_key']];
				if(!$unset_read){
					$button .= 
					'<a href="' . $_GET['read_url'] . '/' . $primary_key . '" class="btn btn-primary d-flex align-items-center btn-xs" role="button">
						<i class="fas fa-eye me-2"></i>' . $_GET['lang_view'] . '
					</a>';
				}
				
				if(!$unset_clone){
					$button .= 
					'<a href="' . $_GET['clone_url'] . '/' . $primary_key . '" class="btn btn-secondary d-flex align-items-center btn-xs" role="button">
						<i class="fas fa-copy me-2"></i>' . $_GET['lang_clone'] . '
					</a>';
				}
				
				if(!$unset_edit){
					$button .= 
					'<a href="' . $_GET['edit_url'] . '/' . $primary_key . '" class="btn btn-success d-flex align-items-center btn-xs" role="button">
						<i class="fas fa-edit me-2"></i>' . $_GET['lang_edit'] . '
					</a>';
				}
				
				if(!$unset_delete){
					$button .= 
					'<a href="' . $_GET['delete_url'] . '/' . $primary_key . '" class="btn btn-danger d-flex align-items-center btn-xs delete-row" role="button">
						<i class="fas fa-times me-2"></i>' . $_GET['lang_delete'] . '
					</a>';
				}
				
				$val['ignore_actions'] = '<div class="btn-group">' . $button . '</div>';
			}
		}
		
		echo json_encode($result); exit();
	}
	
	private function setAssets($data) {
		 $vars = get_object_vars($data);
		foreach($vars as $name => $value) 
		{
			switch ($name) {
				case 'js_files':
					foreach ($value as $js_file) {
						$this->addJs($js_file);
					}
					break;
				case 'css_files':
					foreach ($value as $css_file) {
						$this->addStyle($css_file);
					}
					break;
				case 'output':
					$this->data['output'] = $value;
			}
		}
		
	}
}
