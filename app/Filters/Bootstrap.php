<?php namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class Bootstrap implements FilterInterface
{
   public function before(RequestInterface $request, $arguments = null)
   {
	   $config = config('App');
	   
	   helper('csrf');
	   
		// Custom CSRF
		if ($config->csrf['enable']) 
		{
			if ($config->csrf['auto_check']) {
				$message = csrf_validation();
				if ($message) {
					echo view('app_error.php', ['content' => $message['message']]);
					exit;
				}
			}
			
			if ($config->csrf['auto_settoken']) {
				csrf_settoken();
			}
		}
		
		$router = service('router');
		$controller  = $router->controllerName();

		$exp  = explode('\\', $controller);

		$nama_module =  'welcome';		
		foreach ($exp as $key => $val) {
			if (!$val || strtolower($val) == 'app' || strtolower($val) == 'controllers')
				unset($exp[$key]);
		}
		
		// Dash tidak valid untuk nama class, sehingga jika ada dash di url maka otomatis akan diubah menjadi underscore, hal tersebut berpengaruh ke nama controller
		$nama_module = str_replace('_', '-', strtolower(join('/', $exp)));
		
		// Normalize module name: convert slashes to dashes for subdirectories (e.g., agent/sales -> agent-sales)
		// This ensures both /agent/sales and /agent/sales/cart use the same module name
		if (strpos($nama_module, '/') !== false) {
			// Check if there's a module with dashes instead of slashes
			$module_with_dash = str_replace('/', '-', $nama_module);
			$db = \Config\Database::connect();
			$module_check = $db->table('module')->where('nama_module', $module_with_dash)->countAllResults();
			if ($module_check > 0) {
				$nama_module = $module_with_dash;
			}
		}
		
		$module_url = $config->baseURL . str_replace('-', '/', $nama_module);
		
		session()->set('web', ['module_url' => $module_url, 'nama_module' => $nama_module, 'method_name' => $router->methodName()]);
   }
   
   public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
   {
       
   }  
   
}