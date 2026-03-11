<?php
class ControllerExtensionModuleCheaper30 extends Controller {
	public function index($setting = array()) {
			$this->load->language('product/product');
			$this->load->language('extension/module/cheaper30');
			
			$this->load->model('catalog/product');
			$this->load->model('catalog/review');
			$this->load->model('tool/image');
			
			if (isset($this->request->get['module_id'])){
				$module_id = $this->request->get['module_id'];
			}
			
			if (isset($setting['module_id']) && $setting['module_id']){
				$module_id = $setting['module_id'];
			}
			
			$this->load->model('extension/module/cheaper30');
			$config = $this->model_extension_module_cheaper30->config_version();

			$setting = $this->{$config['model_']}->getModule($module_id);
			
			if (isset($this->request->get['prod_id'])) {
				$product_id = (int)$this->request->get['prod_id'];
			} else {
				$product_id = 0;
			}
			
			if ($config['version'] == '2.3' or $config['version'] == '2.2' or $config['version'] == '2.1' or $config['version'] == '2.0'){
				$data = $this->languageGet();
			}
			
			if (isset($setting['position'])){
				$data['position'] = $setting['position'];
			} else {
				$data['position'] = '';
			}
			
			$data['extension'] = 'extension/';
			if ($config['version'] == '2.2' or $config['version'] == '2.1' or $config['version'] == '2.0'){
				$data['extension'] = '';
			}
			
			$data['theme'] = $config['theme'];
			
			$data['localization_scripts'] = false;
			$config_language = $this->config->get('config_language');
			if (isset($this->session->data['language']) and $this->session->data['language']){
				$config_language = $this->session->data['language'];
				$data['config_language'] = $config_language;
			}
			if ($config_language) {
				if (explode('-', $config_language)) {
					foreach (explode('-', $config_language) as $lan) {
						if (file_exists(DIR_APPLICATION . 'view/javascript/cheaper30/jsdelivr/localization/messages_' . $lan . '.js')) {
							$data['localization_scripts'] = 'catalog/view/javascript/cheaper30/jsdelivr/localization/messages_' . $lan . '.js';
							break;
						}
					}
				}
			}
			if (isset($lan)){$data['lan'] = $lan;}
			$data['text_manufacturer'] = $this->language->get('text_manufacturer');
			$data['text_model'] = $this->language->get('text_model');
			$data['text_stock'] = $this->language->get('text_stock');
			$data['td_more'] = $this->language->get('td_more');
			$data['text_select'] = $this->language->get('text_select');
			$data['text_related'] = $this->language->get('text_related');
			$data['text_tax'] = $this->language->get('text_tax');
			$data['send'] = $this->language->get('send');
			$data['button_wishlist'] = $this->language->get('button_wishlist');
			$data['button_compare'] = $this->language->get('button_compare');
			$data['text_loading'] = $this->language->get('text_loading');
			$data['button_upload'] = $this->language->get('button_upload');
			
			$data['text_fio'] = $this->language->get('text_fio');
			$data['text_phone'] = $this->language->get('text_phone');
			$data['text_email'] = $this->language->get('text_email');
			$data['text_href'] = $this->language->get('text_href');
			$data['text_comment'] = $this->language->get('text_comment');
			$data['cheapering'] = $this->language->get('cheapering');
			$data['text_options'] = $this->language->get('text_options');
			$data['text_qyantity'] = $this->language->get('text_qyantity');
			$data['text_cheapering'] = $this->language->get('text_cheapering');
			
			$data['text_success_zakon'] = $this->language->get('text_success_zakon');
			
			$data['close'] = $this->language->get('close');
			
			$data['text_tax'] = $this->language->get('text_tax');
			$data['text_price'] = $this->language->get('text_price');
			
			if (!$setting['product']){$product_id = 0;}
			if (isset($setting['bootstrap'])){$data['bootstrap'] = $setting['bootstrap'];} else {$data['bootstrap'] = 0;}
			if (isset($setting['font'])){$data['font'] = $setting['font'];} else {$data['font'] = 0;}
			
			if ($setting['type_view']){
				$data['type_view'] = $setting['type_view'];
			} else {
				$data['type_view'] = 0;
			}
			
			if (isset($setting['status_email'])){
				$status_email = $setting['status_email'];
			} else {
				$status_email = 0;
			}
			
			if (isset($setting['status_telegram'])){
				$status_telegram = $setting['status_telegram'];
			} else {
				$status_telegram = 0;
			}
			
			if (isset($setting['popup_width']) && $setting['popup_width']){
				$popup_width = $setting['popup_width'];
			} else {
				$popup_width = array(
					'lg' => 'col-lg-4',
					'md' => 'col-md-6',
					'sm' => 'col-sm-8',
					'xs' => 'col-xs-10',
				);
			}
			
			foreach (['lg', 'md', 'sm', 'xs'] as $class){
				$cols[$class] = array(
					'col-' . $class . '-12' => 'col-' . $class . '-offset-0',
					'col-' . $class . '-10' => 'col-' . $class . '-offset-1',
					'col-' . $class . '-8' => 'col-' . $class . '-offset-2',
					'col-' . $class . '-6' => 'col-' . $class . '-offset-3',
					'col-' . $class . '-4' => 'col-' . $class . '-offset-4',
					'col-' . $class . '-2' => 'col-' . $class . '-offset-5'
				);
			}
			
			$data['class_bootstrap'] = "col-lg-offset-4 col-lg-4 col-md-offset-3 col-md-6 col-sm-offset-2 col-sm-8 col-xs-offset-1 col-xs-10";
			
			$class_bootstrap = ''; 
			foreach ($popup_width as $class => $pop_width){
				$class_bootstrap .= $cols[$class][$pop_width] . ' ' . $pop_width . ' ';
			}
			if ($class_bootstrap){
				$data['class_bootstrap'] = $class_bootstrap;
			}
			
			$data['product_id'] = $product_id;
			
			if ($product_id){
				$product_info = $this->model_catalog_product->getProduct($product_id);
				
				if ($product_info['image']) {
					$data['image'] = $this->model_tool_image->resize($product_info['image'], $setting['width'], $setting['height']);
				} else {
					$data['image'] = $this->model_tool_image->resize('no_image.png', $setting['width'], $setting['height']);
				}
				
				if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
					$data['price'] = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$data['price'] = false;
				}

				if ((float)$product_info['special']) {
					$data['special'] = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$data['special'] = false;
				}
				
				if ($this->config->get('config_tax')) {
					$data['tax'] = $this->currency->format((float)$product_info['special'] ? $product_info['special'] : $product_info['price'], $this->session->data['currency']);
				} else {
					$data['tax'] = false;
				}
				
				if (!$data['special']) {
					$data['pr'] = str_replace(" ","", $data['price']);
				} else {
					$data['pr'] = str_replace(" ","", $data['special']);
				}
				
				$data['description'] = utf8_substr(strip_tags(html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8')), 0, 300) . '..';
				
				$data['link_manufacturers'] = $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $product_info['manufacturer_id']);
				
				$data['name'] = $product_info['name'];
				$data['manufacturer'] = $product_info['manufacturer'];
				$data['model'] = $product_info['model'];
				$data['minimum'] = $product_info['minimum'];
				if ($product_info['quantity'] <= 0) {
				$data['stock'] = $product_info['stock_status'];
				} else {
					$data['stock'] = $this->language->get('text_instock');
				}
			}
			
			$version = 'module_';

			$data['text_cheaper_h1'] = '';
			if ($setting['cheaper30_h1']) {
				if (isset($setting['cheaper30_h1'][$this->config->get('config_language_id')])){
					$data['text_cheaper_h1'] = $setting['cheaper30_h1'][$this->config->get('config_language_id')];
				}
			}
			$data['text_cheaper_h4'] = '';
			if ($setting['cheaper30_h4']) {
				if (isset($setting['cheaper30_h4'][$this->config->get('config_language_id')])){
					$data['text_cheaper_h4'] = $setting['cheaper30_h4'][$this->config->get('config_language_id')];
				}
			}
			
			$fields = $this->model_extension_module_cheaper30->getCheaperingFields($module_id);
			
			$data['results'] = array();
			$name_id = 'x';
			$phone_id = 'x';
			if ($fields) {
				foreach ($fields as $field) {
					if (isset($field['name'][$this->config->get('config_language_id')])){
						$data['results'][$field['position']][$field['column']][] = array(
							'id' 			=> $field['id'],
							'icon' 			=> $field['icon'],
							'name' 			=> $field['name'][$this->config->get('config_language_id')],
							'type' 			=> $field['type'],
							'number' 		=> $field['number'],
							'regex' 		=> $field['regex'],
							'valid' 		=> $field['valid'],
							'required' 		=> $field['required'],
							'query_value' 	=> $field['query_value'],
						);
						if ($field['name'][$this->config->get('config_language_id')] == 'Имя')
							$name_id = $field['id'];
						if ($field['name'][$this->config->get('config_language_id')] == 'Телефон')
							$phone_id = $field['id'];
					}
				}
			}
			
			$data['name_id'] = $name_id;
			$data['phone_id'] = $phone_id;

			$data['formulas'] = $this->model_extension_module_cheaper30->getCheaperingCalculators($module_id);
			
			
			if ($this->config->get($version . 'cheaper30_format')) {$data['cheaper30_format'] = $this->config->get($version . 'cheaper30_format');} else {$data['cheaper30_format'] = false;}
			
			if ($this->config->get($version . 'cheaper30_text')) {
				$text = $this->config->get($version . 'cheaper30_text');
				if (isset($text[$this->session->data['language']])) {
					$data['cheaper30_text'] = html_entity_decode($text[$this->session->data['language']], ENT_QUOTES, 'UTF-8');
				} else {
					$data['cheaper30_text'] = false;
				}
			} else {
				$data['cheaper30_text'] = false;
			}
			
			$data['href'] = $this->url->link('product/product', '&product_id=' . $product_id);
		
			$data['date'] = date("d.m.Y");
			
			$data['style'] = '';$data['protection'] = '';
			if ($module_id){
				$data['style'] = $this->model_extension_module_cheaper30->getCheaperingStyle($module_id);
				$data['protection'] = $this->model_extension_module_cheaper30->getCheaperingProtection($module_id);
			}
			$data['module_id'] = $module_id;
			
			if ($setting['captcha'] && $setting['captcha'] != 'default'){
				if ($config['version'] == '2.2' or $config['version'] == '2.1' or $config['version'] == '2.0'){
					$data['captcha'] = str_replace(array("\r\n", "\r", "\n"), ' ', $this->load->controller('captcha/' . $setting['captcha']));
				} else {
					$data['captcha'] = str_replace(array("\r\n", "\r", "\n"), ' ', $this->load->controller('extension/captcha/' . $setting['captcha']));
				}
				
				$data['captch'] = $setting['captcha'];
			} else {
				$data['captcha'] = '';
				if ($setting['captcha'] && $setting['captcha'] == 'default'){
					$data['captch'] = 'default';
				} else {
					$data['captch'] = '';
				}
				
			}
			
			if ($config['version'] != '2.2' && $config['version'] != '2.0'){
				$data['session_language'] = $this->session->data['language'];
			}
			$data['time'] = time();
			
			if ($config['version'] == '2.1' or $config['version'] == '2.0'){
				if ($setting['type_view']){
					if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/extension/module/cheaper30.tpl')) {
						return $this->load->view($this->config->get('config_template') . '/template/extension/module/cheaper30.tpl', $data);
					} else {
						return $this->load->view('default/template/extension/module/cheaper30.tpl', $data);
					}
				} else {
					if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/extension/module/cheaper30.tpl')) {
						$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/extension/module/cheaper30.tpl', $data));
					} else {
						$this->response->setOutput($this->load->view('default/template/extension/module/cheaper30.tpl', $data));
					}
				}
			} else {
				if ($setting['type_view']){
					return $this->load->view('extension/module/cheaper30', $data);
				} else {
					return $this->response->setOutput($this->load->view('extension/module/cheaper30', $data));
				}
			}
	}
	public function loadcaptcha($setting = array()){
		$captcha = false;
		if (isset($this->request->get['module_id'])){
			$module_id = $this->request->get['module_id'];
			
			$this->load->model('extension/module/cheaper30');
			$config = $this->model_extension_module_cheaper30->config_version();

			$setting = $this->{$config['model_']}->getModule($module_id);
			
			if ($setting['captcha'] && $setting['captcha'] != 'default'){
				if ($config['version'] == '2.2' or $config['version'] == '2.1' or $config['version'] == '2.0'){
					$captcha = str_replace(array("\r\n", "\r", "\n"), ' ', $this->load->controller('captcha/' . $setting['captcha']));
				} else {
					$captcha = str_replace(array("\r\n", "\r", "\n"), ' ', $this->load->controller('extension/captcha/' . $setting['captcha']));
				}
			}
		}
		return $this->response->setOutput($captcha, array());
	}
	public function quick(){
		$this->load->model('extension/module/cheaper30');
		$config = $this->model_extension_module_cheaper30->config_version();
		
		$json = array();
		
		$version = 'module_';

		if ($this->config->get($version . 'cheaper30_text')) {
				$text = $this->config->get($version . 'cheaper30_text');
				if (isset($text[$this->session->data['language']])) {
					$data['cheaper30_text'] = html_entity_decode($text[$this->session->data['language']], ENT_QUOTES, 'UTF-8');
				} else {
					$data['cheaper30_text'] = false;
				}
			} else {
				$data['cheaper30_text'] = false;
			}
			
		if (!isset($this->request->post['zachita']) && ($this->config->get($version . 'cheaper30_format') == "checkbox" && $data['cheaper30_text'])) {
			$json['error']['zachita'] = $this->language->get('error_zachita');
		}
		
		$captcha = '';
		if (isset($this->request->post['module_id'])){
			$setting = $this->{$config['model_']}->getModule($this->request->post['module_id']);
			
			if (isset($setting['captcha']) and $setting['captcha'] and $setting['captcha'] != 'default'){
				$captcha = $setting['captcha'];
			}
			if ($captcha){
				if ($captcha == 'google'){
					unset($this->session->data['gcapcha']);
				}
				$captchas = $this->load->controller('extension/captcha/' . $captcha . '/validate');
				if ($captchas) {
					$json['error'] = $captchas;
				}
			}
		}
		if (!isset($json['error'])) {
			$json['message'] = $this->model_extension_module_cheaper30->writesendquick($this->request->post);
			try {
				$params = [
					'name'		=> (string)@$this->request->post[ 'input_field' ][ $this->request->post[ 'name_id' ] ],
					'phone'		=> (string)@$this->request->post[ 'input_field' ][ $this->request->post[ 'phone_id' ] ],
					'website'	=> urldecode( (string)@$this->request->post['href'] ),
				];

				if ( $this->request->post[ 'input_product_id' ] ) {
					$params[ 'form_name' ] = 'Узнать стоимость';
					$params[ 'product' ] = (string)@$this->request->post[ 'product' ];
				} else {
					$params[ 'form_name' ] = 'Получить прайс';
				}

				$additional = [ 'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'yclid', '_ym_uid', '_ym_counter', 'roistat_first_visit', 'roistat_visit' ];

				foreach ( $additional as $param ) {
					if ( isset( $this->request->post[ $param ] ) ) { $params[ $param ] = $this->request->post[ $param ]; }
				}

				// $url = 'https://webhook.site/ce56fcea-3a6e-42e7-bc6c-c73fa6a0b1ea';
				$url = 'https://ep.morekit.io/a359cca6e99605a5accc4028d8681c98';

				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_POST, TRUE);
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode( $params ) );
				curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
				$data = curl_exec($ch);
				$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				curl_close($ch);
			} catch(\Exception $e) {
			}
			
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	public function settings(){
		$json = array();
		
		$this->load->model('extension/module/cheaper30');
		$config = $this->model_extension_module_cheaper30->config_version();
		if (isset($this->request->get['module_id'])){
			$module_info = $this->{$config['model_']}->getModule($this->request->get['module_id']);
			$config_language_id = $this->config->get('config_language_id');
			
			if (isset($module_info['cheaper30_h1'][$config_language_id]) && !$module_info['type_view'] && $module_info['status']){
				$json['cheaper30_h1'] = $module_info['cheaper30_h1'][$config_language_id];
			}
		}
		if (isset($this->request->get['route'])){
			$json['route'] = $this->request->get['route'];
		}
		if (isset($module_info['product'])){
			$json['product'] = $module_info['product'];
		}
		
		return $json;
	}
	public function jsonsettings(){
		
		$json = $this->settings();
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	public function route($data = array()){
		$this->load->model('extension/module/cheaper30');
		$config = $this->model_extension_module_cheaper30->config_version();
		
		$data['route'] = '';
		if (isset($this->request->get['route'])){
			$data['route'] = $this->request->get['route'];
		}
		
		$data['extension'] = 'extension/';
		if ($config['version'] == '2.2' or $config['version'] == '2.1' or $config['version'] == '2.0'){
			$data['extension'] = '';
		}
		
		if ($config['version'] == '2.1' or $config['version'] == '2.0'){
			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/extension/module/cheaper30scripts.tpl')) {
				return $this->load->view($this->config->get('config_template') . '/template/extension/module/cheaper30scripts.tpl', $data);
			} else {
				return $this->load->view('default/template/extension/module/cheaper30scripts.tpl', $data);
			}
		} else {
			return $this->load->view('extension/module/cheaper30scripts', $data);
		}
	}
	public function newsession($json = array()){
		
		$get_module_id = '';
		if (isset($this->request->get['module_id'])){
			$get_module_id = '_' . $this->request->get['module_id'];
		}
		
		$char = strtoupper(substr(str_shuffle('abcdefghjkmnpqrstuvwxyz'), 0, 4));

		$str = rand(1, 7) . rand(1, 7) . $char;

		$this->session->data['captcha_id' . $get_module_id] = $str;
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function process(){
		$get_module_id = '';
		if (isset($this->request->get['module_id'])){
			$get_module_id = '_' . $this->request->get['module_id'];
		}
		
		if(strtoupper($this->request->get['captcha']) == $this->session->data['captcha_id' . $get_module_id]){
			echo 'true';
		} else {
			echo 'false';
		}
	}
	
	public function image(){
		
		$get_module_id = '';
		if (isset($this->request->get['module_id'])){
			$get_module_id = '_' . $this->request->get['module_id'];
		}
		
		if(!isset($this->session->data['captcha_id' . $get_module_id])) {
			$str = 'ERROR!';
		} else {
			$str = $this->session->data['captcha_id' . $get_module_id];
		}
		
		$image = imagecreatefrompng(DIR_APPLICATION . 'view/javascript/fonts/button.png');
		
		$colour = imagecolorallocate($image, 183, 178, 152);
		
		$font = DIR_APPLICATION . 'view/javascript/fonts/Anorexia.ttf';
		
		$rotate = rand(-15, 15);
		
		imagettftext($image, 14, $rotate, 18, 30, $colour, $font, $str);
		
		$this->response->addHeader('Content-Type: image/png');
		$this->response->addHeader('Cache-Control: no-cache');
		
		imagepng($image);
	}
	
	public function refreshimage($data = array()){
		$extension = 'extension/';
		$this->load->model('extension/module/cheaper30');
		$config = $this->model_extension_module_cheaper30->config_version();
		if ($config['version'] == '2.2' or $config['version'] == '2.1' or $config['version'] == '2.0'){
			$extension = '';
		}
		$get_module_id = '';
		if (isset($this->request->get['module_id'])){
			$get_module_id = '&module_id=' . $this->request->get['module_id'];
		}
		return $this->response->setOutput('<a id="refreshimg" title="Click to refresh image"><img src="index.php?route=' . $extension . 'module/cheaper30/image/' . time() . $get_module_id . '" width="132" height="46" alt="Captcha image"></a>');
	}
	
	public function languageGet(){
		$language_get = array('text_cheaper30_module','text_send_cheaper30_module','text_close_cheaper30_module','text_cheaper30_required','text_cheaper30_file','text_error_zachita','entry_valid_default_legend','success_send_module','error_send_module','error_require_from_group','error_required_from_group','text_cheaper30_title','text_product','text_more_zapros');
		foreach ($language_get as $get){
			$data[$get] = $this->language->get($get);
		}
		return $data;
	}
	
	
	
	
	
	
	
	
	
}