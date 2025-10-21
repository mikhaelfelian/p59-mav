<?php namespace App\Controllers;

use App\Libraries\GroceryCrud;

class Grocery_crud extends BaseController
{
	public function customers_management()
	{
	    $crud = new GroceryCrud();

	    $crud->setTable('customers');

	    $crud->unsetJquery();
        // $output = $crud->render();

        // return $this->_exampleOutput($output);
		
		
		$crud->setTheme('datatables');

	    $render = $crud->render();
		$this->setAssets($render);
		
		$this->addJs(base_url() . '/public/vendors/datatables/dist/js/dataTables.bootstrap5.min.js');
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
$crud->unsetJquery();
        // $output = $crud->render();

        // return $this->_exampleOutput($output);
		
		
		$crud->setTheme('datatables');

	    $render = $crud->render();
		$this->setAssets($render);
		
		$this->addJs(base_url() . '/public/vendors/datatables/dist/js/dataTables.bootstrap5.min.js');
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

    public function offices_management () {
        $crud = new GroceryCrud();

        $crud->setTheme('datatables');
        $crud->setTable('offices');
        $crud->setSubject('Office');
        $crud->requiredFields(['city']);
        $crud->columns(['city','country','phone','addressLine1','postalCode']);
        $crud->setRead();

        $output = $crud->render();

        return $this->_exampleOutput($output);
    }

    public function products_management() {
        $crud = new GroceryCrud();

        $crud->setTable('products');
        $crud->setSubject('Product');
        $crud->unsetColumns(['productDescription']);
        $crud->callbackColumn('buyPrice', function ($value) {
            return $value.' &euro;';
        });

        $output = $crud->render();

        return $this->_exampleOutput($output);
    }

    public function employees_management()
    {
        $crud = new GroceryCrud();

        $crud->setTheme('datatables');
        $crud->setTable('employees');
        $crud->setRelation('officeCode','offices','city');
        $crud->displayAs('officeCode','Office City');
        $crud->setSubject('Employee');

        $crud->requiredFields(['lastName']);

        $output = $crud->render();

        return $this->_exampleOutput($output);
    }

    public function film_management()
    {
        $crud = new GroceryCrud();

        $crud->setTable('film');
        $crud->setRelationNtoN('actors', 'film_actor', 'actor', 'film_id', 'actor_id', 'fullname');
        $crud->setRelationNtoN('category', 'film_category', 'category', 'film_id', 'category_id', 'name');
        $crud->unsetColumns(['special_features','description','actors']);

        $crud->fields(['title', 'description', 'actors' ,  'category' ,'release_year', 'rental_duration', 'rental_rate', 'length', 'replacement_cost', 'rating', 'special_features']);

        $output = $crud->render();

        return $this->_exampleOutput($output);
    }


    private function _exampleOutput($output = null) {
        return view('themes/modern/grocery-crud', (array)$output);
    }


}
