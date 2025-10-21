<?php namespace App\Controllers;

use App\Libraries\GroceryCrud;

class Grocery_crud_datatables extends BaseController
{
	public function __construct() {
		
		parent::__construct();
		$this->data['title'] = 'Grocery CRUD Datatables';
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

	    $crud->setTable('grocerycrud_customers');
		$crud->unsetJquery();
		$crud->setTheme('datatables');
		// $crud->setLanguage("indonesian");

	    $render = $crud->render();
		$this->setAssets($render);

		$this->view('grocery-crud', $this->data);
	}

	public function orders_management() {
        $crud = new GroceryCrud();

        $crud->setRelation('customerNumber','grocerycrud_customers','{contactLastName} {contactFirstName}');
        $crud->displayAs('customerNumber','Customer');
        $crud->setTable('grocerycrud_orders');
        $crud->setSubject('Order');
        $crud->unsetAdd();
        $crud->unsetDelete();
		// $crud->setLanguage("indonesian");
		$crud->unsetJquery();
		$crud->setTheme('datatables');

        $output = $crud->render();
		$this->setAssets($output);
      
		$this->view('grocery-crud', $this->data);
    }
	
    public function offices_management () {
        $crud = new GroceryCrud();

        $crud->setTheme('datatables');
        $crud->setTable('grocerycrud_offices');
        $crud->setSubject('Office');
        $crud->requiredFields(['city']);
        $crud->columns(['city','country','phone','addressLine1','postalCode']);
        $crud->setRead();
		$crud->unsetJquery();

        $output = $crud->render();
		$this->setAssets($output);

        $this->view('grocery-crud', $this->data);
    }

    public function products_management() {
        $crud = new GroceryCrud();

        $crud->setTable('grocerycrud_products');
        $crud->setSubject('Product');
        $crud->unsetColumns(['productDescription']);
        $crud->callbackColumn('buyPrice', function ($value) {
            return $value.' &euro;';
        });
		$crud->unsetJquery();
		$crud->setTheme('datatables');

		$output = $crud->render();
		$this->setAssets($output);

        $this->view('grocery-crud', $this->data);
    }

    public function employees_management()
    {
        $crud = new GroceryCrud();

        $crud->setTheme('datatables');
        $crud->setTable('grocerycrud_employees');
        $crud->setRelation('officeCode','grocerycrud_offices','city');
        $crud->displayAs('officeCode','Office City');
        $crud->setSubject('Employee');

        $crud->requiredFields(['lastName']);

		$output = $crud->render();
		$this->setAssets($output);
		$this->view('grocery-crud', $this->data);
    }

    public function film_management()
    {
        $crud = new GroceryCrud();

        $crud->setTable('grocerycrud_film');
        $crud->setRelationNtoN('actors', 'grocerycrud_film_actor', 'grocerycrud_actor', 'film_id', 'actor_id', 'fullname');
        $crud->setRelationNtoN('category', 'grocerycrud_film_category', 'grocerycrud_category', 'film_id', 'category_id', 'name');
        $crud->unsetColumns(['special_features','description','actors']);
		$crud->unsetJquery();
		$crud->setTheme('datatables');
		
        $crud->fields(['title', 'description', 'actors' ,  'category' ,'release_year', 'rental_duration', 'rental_rate', 'length', 'replacement_cost', 'rating', 'special_features']);

        $output = $crud->render();
		$this->setAssets($output);
		
        $this->view('grocery-crud', $this->data);
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