<?php
/**
Functions
https://webdev.id
*/

/* Create breadcrumb
$data: title as key, and url as value */ 

if (!function_exists('breadcrumb')) {
	function breadcrumb($data = null, $breadcrumb_uri = null) 
	{
		// Method 2: Handle string format (e.g., "Bla >> Blah >> blah")
		if (is_string($data) && !empty($data)) {
			// Parse string by ">>" separator
			$titles = array_map('trim', explode('>>', $data));
			$titles = array_filter($titles); // Remove empty items
			
			if (!empty($titles)) {
				// Convert to array format
				$data = [];
				$lastIndex = count($titles) - 1;
				
				foreach ($titles as $index => $title) {
					// Last item gets the URI if provided, otherwise empty string
					// All previous items get empty string (no URL)
					if ($index === $lastIndex && $breadcrumb_uri !== null) {
						$data[$title] = $breadcrumb_uri;
					} else {
						$data[$title] = '';
					}
				}
			} else {
				$data = null; // If parsing failed, treat as empty
			}
		}
		
		// Method 1: Auto-generate breadcrumb if data is null or empty
		if (empty($data)) {
			$data = generateBreadcrumb();
		}
		
		// If still empty after auto-generation, return empty string
		if (empty($data)) {
			return '';
		}
		
		// Method 3: Array format (existing behavior) - render breadcrumb
		$separator = '&raquo;';
		echo '<nav aria-label="breadcrumb">
	  <ol class="breadcrumb">';
		foreach ($data as $title => $url) {
			if ($url) {
				echo '<li class="breadcrumb-item"><a href="'.$url.'">'.$title.'</a></li>';
			} else {
				echo '<li class="breadcrumb-item active" aria-current="page">'.$title.'</li>';
			}
		}
		echo '
	  </ol>
	</nav>';
	}
}

/**
 * Auto-generate breadcrumb from current route/controller/method
 * 
 * @return array Breadcrumb data (title => url)
 */
if (!function_exists('generateBreadcrumb')) {
	function generateBreadcrumb() 
	{
		$breadcrumb = [];
		
		try {
			// Get base URL
			$config = config('App');
			$baseURL = $config->baseURL;
			
			// Get current module from session if available
			$session = \Config\Services::session();
			$web = $session->get('web');
			
			// Start with Home
			$breadcrumb['Home'] = $baseURL;
			
			// Add module if available
			if (!empty($web['nama_module']) && !empty($web['module_url'])) {
				$moduleTitle = !empty($web['judul_module']) ? $web['judul_module'] : ucfirst($web['nama_module']);
				$breadcrumb[$moduleTitle] = $web['module_url'];
			}
			
			// Try to get controller and method from router
			$router = \Config\Services::router();
			$request = \Config\Services::request();
			
			// Get route information
			$controller = null;
			$method = null;
			
			// Try to get from router
			if (method_exists($router, 'controllerName')) {
				$controller = $router->controllerName();
			}
			if (method_exists($router, 'methodName')) {
				$method = $router->methodName();
			}
			
			// Fallback: try to get from URI segments
			if (empty($controller) || empty($method)) {
				$uri = $request->getUri();
				$segments = $uri->getSegments();
				
				if (empty($controller) && !empty($segments)) {
					// First segment is usually controller
					$controller = ucfirst($segments[0] ?? '');
				}
				
				if (empty($method) && !empty($segments[1])) {
					// Second segment is usually method
					$method = $segments[1];
				} elseif (empty($method)) {
					$method = 'index';
				}
			}
			
			// Add controller name if not 'index' and different from module
			if (!empty($controller) && strtolower($controller) !== 'index') {
				$controllerName = str_replace('Controller', '', $controller);
				$controllerName = preg_replace('/(?<!^)[A-Z]/', ' $0', $controllerName);
				$controllerName = trim($controllerName);
				
				// Only add if it's meaningful and different from module
				if (!empty($controllerName) && strtolower($controllerName) !== strtolower($web['nama_module'] ?? '')) {
					// Build controller URL from current URI
					$uri = $request->getUri();
					$segments = $uri->getSegments();
					
					// Build URL up to controller
					$controllerURL = $baseURL;
					if (!empty($segments)) {
						// Take first segment as controller URL
						$controllerURL = $baseURL . $segments[0];
					}
					
					$breadcrumb[$controllerName] = $controllerURL;
				}
			}
			
			// Add method name if not 'index'
			if (!empty($method) && strtolower($method) !== 'index') {
				$methodName = ucfirst(str_replace('_', ' ', $method));
				$breadcrumb[$methodName] = ''; // Current page, no URL
			}
			
		} catch (\Exception $e) {
			// If auto-generation fails, return empty array (will fall back to manual)
			log_message('debug', 'Breadcrumb auto-generation failed: ' . $e->getMessage());
			return [];
		}
		
		return $breadcrumb;
	}
}

function format_tanggal_db($date) 
{
	if ($date == '0000-00-00' || $date == '')
		return $date;
	
	$bulan = nama_bulan();
	$exp = explode('-', $date);
	return $exp[2] . ' ' . $bulan[ ($exp[1] * 1) ] . ' ' . $exp[0]; // * untuk mengubah 02 menjadi 2
}

if (!function_exists('current_url')) {
	function current_url() {
		return $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	}
}

if (!function_exists('set_value')) {
	function set_value($field_name, $default = '') 
	{
		// echo '<pre>'; print_r($_POST);
		$search = $field_name;
		// echo $search ; die;
		// If Array
		$is_array = false;
		if (strpos($search, '[')) {
			$is_array = true;
			$exp = explode('[', $field_name);
			$field_name = $exp[0];
			
		}
		
		// print_r($field_name);
		
		if (!empty($_POST[$field_name])) {
			
			if ($is_array) {
				
				$exp_close = explode(']', $exp[1]);
				$index = $exp_close[0];
				// echo $index; die;
			
				return $_POST[$field_name][$index];
			}
			return $_POST[$field_name];
		}

		return $default;
	}
}

function current_action_url() {
	global $config;
	return $config['base_url'] . '?module=' . $_GET['module'] . '&action=' . $_GET['action']; 
}


function get_menu_db ($aktif = 'all', $show_all = false) 
{
	global $db;
	global $app_module;
	// print_r($app_module);
	$result = [];
	$nama_module = $app_module['nama_module'];
	
	$where = ' ';
	$where_aktif = '';
	if ($aktif != 'all') {
		$where_aktif = ' AND aktif = '.$aktif;
	}
	
	$role = '';
	if (!$show_all) {
		$role = ' AND id_role = ' . $_SESSION['user']['id_role'];
	}
	
	$sql = 'SELECT * FROM menu 
				LEFT JOIN menu_role USING (id_menu)
				LEFT JOIN module USING (id_module)
			WHERE 1 = 1 ' . $role
				. $where_aktif.' 
			ORDER BY urut';
	
	$db->query($sql);
	
	$current_id = '';
	while ($row = $db->fetch()) 
	{
		$result[$row['id_menu']] = $row;
		$result[$row['id_menu']]['highlight'] = 0;
		$result[$row['id_menu']]['depth'] = 0;

		if ($nama_module == $row['nama_module']) {
			
			$current_id = $row['id_menu'];
			$result[$row['id_menu']]['highlight'] = 1;
		}
		
	}
	// echo '<pre>'; print_r($result);
	
	if ($current_id) {
		menu_current($result, $current_id);
	}
	
	return $result;
}

function menu_current( &$result, $current_id) 
{
	$parent = $result[$current_id]['id_parent'];

	$result[$parent]['highlight'] = 1; // Highlight menu parent
	if (@$result[$parent]['id_parent']) {
		menu_current($result, $parent);
	}
}

function create_image_mime ($tipe_file, $newfile)
{
	switch ($tipe_file)
	{
		case "image/gif":
			return imagecreatefromgif($newfile);
			
		case "image/png":
			return imagecreatefrompng($newfile);
			
		case "image/bmp":
			return imagecreatefrombmp($newfile);
			
		default:
			return imagecreatefromjpeg($newfile);		
	}
}
	
function create_image ($tipe_file, $resized_img, $newfile)
{
	switch ($tipe_file)
	{
		case "image/gif":
			return imagegif ($resized_img,$newfile, 85);
			
		case "image/png":
			imagesavealpha($resized_img, true);
			$color = imagecolorallocatealpha($resized_img, 0,0,0,127);
			imagefill($resized_img, 0,0, $color);
			return imagepng ($resized_img,$newfile, 9);
			
		case "image/bmp":
			return imagecreatefrombmp($newfile);
			
		default:
			return imagejpeg ($resized_img,$newfile, 85);
			
	}
}

function get_filename($file_name, $path) {
	
	$file_name_path = $path . $file_name;
	if ($file_name != "" && file_exists($file_name_path))
	{
		$file_ext = strrchr($file_name, '.');
		$file_basename = substr($file_name, 0, strripos($file_name, '.'));
		$num = 1;
		while (file_exists($file_name_path)){
			$file_name = $file_basename."($num)".$file_ext;
			$num++;
			$file_name_path = $path . $file_name;
		}
		
		return $file_name;
	}
	return $file_name;
}

function upload_image($path, $file, $max_w = 500, $max_h = 500) 
{
	
	$file_type = $file['type'];
	$new_name =  get_filename(stripslashes($file['name']), $path); ;
	$move = move_uploaded_file($file['tmp_name'], $path . $new_name);
	
	$save_image = false;
	if ($move) {
		$dim = image_dimension($path . $new_name, $max_w, $max_h);
		$save_image = save_image($path . $new_name, $file_type, $dim[0], $dim[1]);
	}
	
	if ($save_image)
		return $new_name;
	else
		return false;
}

function image_dimension($images, $maxw=null, $maxh=null)
{
	if($images)
	{
		$img_size = @getimagesize($images);
		$w = $img_size[0];
		$h = $img_size[1];
		$dim = array('w','h');
		foreach($dim AS $val){
			$max = "max{$val}";
			if(${$val} > ${$max} && ${$max}){
				$alt = ($val == 'w') ? 'h' : 'w';
				$ratio = ${$alt} / ${$val};
				${$val} = ${$max};
				${$alt} = ${$val} * $ratio;
			}
		}
		return array($w,$h);
	}
}

function save_image($image, $file_type, $w, $h) 
{
	$img_size = @getimagesize($image);
	
	$resized_img = imagecreatetruecolor($w,$h);
	$new_img = create_image_mime($file_type, $image);
	imagecopyresized($resized_img, $new_img, 0, 0, 0, 0, $w, $h, $img_size[0], $img_size[1]);
	$do = create_image($file_type, $resized_img, $image);
	ImageDestroy ($resized_img);
	ImageDestroy ($new_img);
	return $do;
}

function upload_file($path, $file) 
{
	$new_name =  get_filename(stripslashes($file['name']), $path); ;
	$move = move_uploaded_file($file['tmp_name'], $path . $new_name);
	if ($move) 
		return $new_name;
	else
		return false;
}

function get_dimensi_kartu($ori_panjang, $ori_lebar, $dpi) {
	// print_r($ori_panjang); die;
	$px = 0.393700787; 
	$panjang = $ori_panjang * $dpi * $px;
	$lebar = $ori_lebar * $dpi * $px;
	return ['w' => $panjang, 'h' => $lebar];
}

function generateQRCode($version, $ecc, $text, $module_width) {
	
	require APPPATH . 'ThirdParty\qrcode\qrcode_extended.php';
	$qr = new QRCodeExtended();
	$ecc_code = ['L' => QR_ERROR_CORRECT_LEVEL_L
		, 'M' => QR_ERROR_CORRECT_LEVEL_M
		, 'Q' => QR_ERROR_CORRECT_LEVEL_Q
		, 'H' => QR_ERROR_CORRECT_LEVEL_H
	];
	$qr->setErrorCorrectLevel($ecc_code[$ecc]);
	$qr->setTypeNumber($version);
	$qr->addData($text);
	$qr->make();
	return $qr->saveHtml($module_width);
}