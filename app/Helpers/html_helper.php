<?php
/**
Functions
https://webdev.id
*/

if (!function_exists('options')) {
	function options($attr, $data, $selected = '', $print = false) 
	{
		if (empty($attr['class'])) {
			$attr['class'] = 'form-select';
		} else {
			$attr['class'] = $attr['class'] . ' form-select';
		}
		
		$select_name = $attr['name'];
		$attribute = [];
		foreach ($attr as $key => $val) {
			$attribute[] = $key . '="' . $val . '"'; 
		}
		$attribute = join(' ', $attribute);
		
		if ($selected != '') {
			if (!is_array($selected)) {
				$selected = [$selected];
			}
		}

		$result = '
		<select '. $attribute .'>';
			foreach($data as $key => $value) 
			{
				
				$option_selected = false;
				if ($selected != '') {
					if (@empty($_REQUEST[$select_name])) {
						if (in_array($key, $selected)) {
							$option_selected = true;
						}
					} else {
						if (is_array($_REQUEST[$select_name])) {
							if (in_array($key, $_REQUEST[$select_name])) {
								$option_selected = true;
							}
						} else {
							if ($key == $_REQUEST[$select_name]) {
								$option_selected = true;
							}
						}
					}
				}

				if ($option_selected) {
					$option_selected = ' selected';
				}
				$result .= '<option value="'.$key.'"'.$option_selected.'>'.$value.'</option>';
			}
			
		$result .= '</select>';
		
		if ($print) {
			echo $result;
		} else {
			return $result;
		}
		
	}
}

if (!function_exists('checkbox')) {
	function checkbox($data, $checked = []) 
	{
		if (!is_array($data)) {
			$data[] = ['attr' => ['name' => $data, 'id' => $data]];
		} else {
			if (!key_exists(0, $data)) {
				$clone = $data;
				$data = [];
				$data[] = $clone;
			}
		}
		
		$checkbox = '';
		foreach ($data as $key => $val) 
		{
			// Container
			$container_class = 'checkbox form-check mb-1';
			$attr_container = '';
			
			if (key_exists('attr_container', $val)) 
			{
				if (key_exists('class', $val['attr_container'])) {
					$container_class .= ' ' . $val['attr_container']['class'];
					unset ( $val['attr_container']['class'] );
				}
				
				foreach ($val['attr_container'] as $attr_name => $attr_value) {
					$attr_container[] = $attr_name . '=' . $attr_value;
				}
				
				if ($attr_container) {
					$attr_container = ' ' . join(' ', $attr_container);
				}
			}
			
			// Checkbox
			$attr_checked = '';
			if ($checked === true) {
				$attr_checked = 'checked';
			} else {
				if (is_array($checked)) {
					if (in_array($val['attr']['name'], $checked)) {
						$attr_checked = 'checked';
					}
				} else {
					if ($val['attr']['name'] == $checked) {
						$attr_checked = 'checked';
					}
				}
			}
			
			if (key_exists('class', $val['attr'])) {
				$val['attr']['class'] = $val['attr']['class'] . ' form-check-input';
			} else {
				$val['attr']['class'] = 'form-check-input';
			}
			
			$attr_checkbox = [];
			foreach ($val['attr'] as $attr_name => $attr_value) {
				$attr_checkbox[] = $attr_name . '="' . $attr_value . '"';
			}
			$attr_checkbox = ' ' . join(' ', $attr_checkbox) . ' ';
			
			$checkbox .= '<div class="'. $container_class .'"' . $attr_container . '>
				<input type="checkbox"'. $attr_checkbox . $attr_checked.' >
				<label class="form-check-label" for="'. $val['attr']['id'].'">' . $val['label'] . '</label>
			</div>';
		}
		
		return $checkbox;
	}
}

if (!function_exists('btn_submit')) {
	function btn_submit($data = []) {
		$html = $attr = '';
		foreach ($data as $key => $val) {
			if (key_exists('attr', $val)) {
				foreach($val['attr'] as $key_attr => $val_attr) {
					$attr .= $key_attr . '="' . $val_attr . '"';
				}
			}
				
			$html .= '<button type="submit" class="btn '.$val['btn_class'].' btn-xs"'.$attr.'>
								<span class="btn-label-icon"><i class="'.$val['icon'].'"></i></span> '.$val['text'].'
				</button>';
		}
		
		return $html;
	}
}

if (!function_exists('btn_action')) {
	function btn_action($data = []) {

		$html = '<div class="form-inline btn-action-group">';
		$attr = '';
		foreach ($data as $key => $val) 
		{
			if ($key == 'edit') 
			{
				$btn_class = 'btn btn-success btn-xs me-1';
				if (!key_exists('attr', $val)) {
					 $val['attr'] = ['class' => $btn_class];
				}
				
				foreach ($val['attr'] as $attr_name => $attr_value) {
					if ($attr_name == 'class') {
						$attr_value = $btn_class . ' ' . $attr_value;
					}
					
					$attr .= $attr_name . '="' . $attr_value . '"';
				}
				
				$html .= '<a href="'.$data[$key]['url'].'" ' . $attr . '>
							<span class="btn-label-icon"><i class="fa fa-edit pe-1"></i></span> Edit
						</a>';
			}
			
			else if ($key == 'delete') {
				$html .= '<form method="post" action="'. $data[$key]['url'] .'">
						<button type="submit" data-action="delete-data" data-delete-title="'.$data[$key]['delete-title'].'" class="btn btn-danger btn-xs">
							<span class="btn-label-icon"><i class="fa fa-times pe-1"></i></span> Delete
						</button>
						<input type="hidden" name="delete" value="delete"/>
						<input type="hidden" name="id" value="'.$data[$key]['id'].'"/>
					</form>';
			}
			else {
				
				if (key_exists('attr', $data[$key])) {
					foreach($data[$key]['attr'] as $key_attr => $val_attr) {
						$attr .= $key_attr . '="' . $val_attr . '"';
					}
				}
				$html .= '<a href="'.$data[$key]['url'].'" class="btn '.$data[$key]['btn_class'].' btn-xs me-1" ' . $attr . '>
							<span class="btn-label-icon"><i class="'.$data[$key]['icon'].'"></i></span>&nbsp;'.$data[$key]['text'].'
						</a>';
				
			}
		}
		
		$html .= '</div>';
		return $html;
	}
}

if (!function_exists('btn_label')) {
	function btn_label($data) 
	{
		$icon = '';
		if (key_exists('icon', $data)) {
			$icon = '<span class="btn-label-icon"><i class="' . $data['icon'] . ' pe-1"></i></span> ';
		}

		$attr = [];
		if (key_exists('attr', $data)) {
			foreach($data['attr'] as $name => $value) {
				if ($name == 'class') {
					// $value = 'btn-inline ' . $value;
				}
				$attr[] = $name . '="' . $value . '"';
			}
		}
		
		$label = '';
		if (key_exists('label', $data)) {
			$label = $data['label'];
		}
		$html = '
			<button  type="button" ' . join(' ', $attr) . '>'.$icon. $label . '</button>';
		return $html;
	}
}

/*
	btn_link(
		['icon' => 'fas fa-pencil-alt', 
			'attr' => ['class' => 'btn btn-success btn-xs'], 
			'url' => BASE_URL . $current_module['nama_module'] . '/edit?id='. $val['id_siswa'],
			'label' => 'Edit'
		]
	);
*/
if (!function_exists('btn_link')) {
	function btn_link($data) 
	{
		$icon = '';
		if (key_exists('icon', $data)) {
			$icon = '<span class="btn-label-icon"><i class="' . $data['icon'] . ' pe-1"></i></span> ';
		}

		$attr = [];
		if (key_exists('attr', $data)) {
			foreach($data['attr'] as $name => $value) {
				if ($name == 'class') {
					// $value = 'btn-inline ' . $value;
				}
				$attr[] = $name . '="' . $value . '"';
			}
		}
		
		$label = '';
		if (key_exists('label', $data)) {
			$label = $data['label'];
		}
		$html = '
			<a href="'.$data['url'].'" ' . join(' ', $attr) . '>'.$icon. $label . '</a>';
		return $html;
	}
}

// --- Slug helper ---
if (!function_exists('slugify')) {
	/**
	 * Membuat slug dari string, mengganti spasi, karakter asing, dsb.
	 *
	 * @param string $text Input string
	 * @param string $divider Pemisah (default '-')
	 * @return string Slug yang sudah dibersihkan
	 */
	function slugify($text, $divider = '-') {
		$text = trim($text);
		// transliterate
		if (function_exists('iconv')) {
			$text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
		}
		// mengganti non letter/digit menjadi divider
		$text = preg_replace('~[^\pL\d]+~u', $divider, $text);
		// hapus karakter yang tidak diinginkan
		$text = preg_replace('~[^-\w]+~', '', $text);
		// trim
		$text = trim($text, $divider);
		// hapus duplikat divider
		$text = preg_replace('~-+~', $divider, $text);
		// lowercase
		$text = strtolower($text);

		if (empty($text)) {
			return 'n-a';
		}
		return $text;
	}
}