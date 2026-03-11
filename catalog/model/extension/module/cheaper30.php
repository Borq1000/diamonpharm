<?php
class ModelExtensionModuleCheaper30 extends Model {
	
	public function getCheaperingFields($module_id){
		$this->load->language('extension/module/code');
		
		$res0 = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "cheaper_module_fields'");
		
		$results = array();
		if($res0->num_rows){
			if (isset($this->request->get['module_id'])){
				$module_id = $this->request->get['module_id'];
			}
			
			$stock_position = false;
			$query_position = $this->db->query("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_NAME = '" . DB_PREFIX . "cheaper_module_position'");
			if ($query_position->num_rows){
				$query_stock_position = $this->db->query("SELECT * FROM " . DB_PREFIX . "cheaper_module_position WHERE module_id='" . (int)$module_id . "'");
				if ($query_stock_position->num_rows){
					$stock_position = true;
				}
			}
			$stock_position_sort = false;
			$query_position_sort = $this->db->query("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_NAME = '" . DB_PREFIX . "cheaper_module_position_sort'");
			if ($query_position_sort->num_rows){
				$query_stock_position_sort = $this->db->query("SELECT * FROM " . DB_PREFIX . "cheaper_module_position_sort WHERE module_id='" . (int)$module_id . "'");
				if ($query_stock_position_sort->num_rows){
					$stock_position_sort = true;
				}
			}

			// Test for update
			if ($stock_position && $stock_position_sort){
				$query = $this->db->query("SELECT *, cmp.sort FROM " . DB_PREFIX . "cheaper_module_fields cmf LEFT JOIN " . DB_PREFIX . "cheaper_module_position cmp ON (cmf.id = cmp.id AND cmf.module_id = cmp.module_id) LEFT JOIN " . DB_PREFIX . "cheaper_module_position_sort cmps ON (cmp.position = cmps.position AND cmp.module_id = cmps.module_id) WHERE cmf.module_id='" . (int)$module_id . "' ORDER BY cmps.sort_order ASC, cmp.column ASC, cmp.sort ASC");
				
			} else {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "cheaper_module_fields WHERE `module_id`='" . (int)$module_id . "'");
			}
			
			if ($query->num_rows){
				foreach ($query->rows as $result){
					
					$query_value = $this->db->query("SELECT * FROM " . DB_PREFIX . "cheaper_module_value WHERE `id`='" . (int)$result['id'] . "' AND `module_id`='" . (int)$module_id . "' ORDER BY sort ASC");
					
					$select_value = array();
					if ($query_value->num_rows){
						foreach ($query_value->rows as $value){
							$calc = array();
							
							$query_value_calc = $this->db->query("SELECT * FROM " . DB_PREFIX . "cheaper_module_value_calculator cm  WHERE `module_id` = '" . (int)$module_id . "' AND cm.`id`='" . (int)$value['id'] . "' AND cm.`value_id`='" . (int)$value['value_id'] . "'");
							
							if ($query_value_calc->num_rows){
								foreach ($query_value_calc->rows as $cal){
									$calc[$cal['formula_id']] = $cal['calc'];
								}
							}
							
							$select_value[] = array(
								'name' => json_decode($value['text'], true)[$this->config->get('config_language_id')],
								'value' => $calc
							);
						}
					}
					
					$query_value_formula = $this->db->query("SELECT * FROM " . DB_PREFIX . "cheaper_module_formula WHERE `value`='" . (int)$result['id'] . "' AND `module_id`='" . (int)$module_id . "' AND `type` = 'field' LIMIT 1");
					
					$number = false;
					if ($query_value_formula->num_rows) {
						$number = true;
					}
					
					$results[] = array(
						'position' => (isset($result['position']) ? $result['position'] : 1),
						'column' => (isset($result['column']) ? $result['column'] : 1),
						/*'sort' => $result['sort'],*/
						'id' => $result['id'],
						'icon' => json_decode($result['icon'],true),
						'name' => json_decode($result['name'],true),
						'type' => $result['type'],
						'number' => $number,
						'placeholder' => $this->language->get('text_' . $result['regex']),
						'regex' => $result['regex'],
						'valid' => json_decode($result['valid'],true),
						'required' => $result['required'],
						'query_value' 	=> $select_value,
					);
				}
			}
		}
		
		return $results;
	}
	
	public function getCheaperingStyle($module_id) {
		
		$res0 = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "cheaper_module_style'");
		if($res0->num_rows){
			$query = $this->db->query("SELECT style FROM " . DB_PREFIX . "cheaper_module_style WHERE `module_id`='" . (int)$module_id . "'"); 
			
			if (isset($query->row['style'])){
				return json_decode($query->row['style']);
			} else {
				return false;
			}
			
		}
	}
	
	public function getCheaperingEmail($module_id) {
		
		$emails = array();
		
		$res0 = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "cheaper_module_email'");
		if($res0->num_rows){
			$query = $this->db->query("SELECT email FROM " . DB_PREFIX . "cheaper_module_email WHERE `module_id`='" . (int)$module_id . "'"); 
			
			if ($query->rows){
				foreach ($query->rows as $email){
					$emails[] = $email['email'];
				}
			}
		}
		
		return $emails;
	}
	
	public function getCheaperingProtection($module_id) {
		
		$results = array();
		$res0 = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "cheaper_module_protection'");
		
		if($res0->num_rows){
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "cheaper_module_protection WHERE `module_id`='" . (int)$module_id . "'"); 

			if ($query->num_rows){
				foreach ($query->rows as $result){
					$text = json_decode($result['text'],true);
					$results = array(
						'module_id' => $result['module_id'],
						'format' => $result['format'],
						'text' => html_entity_decode($text[$this->config->get('config_language')], ENT_QUOTES, 'UTF-8'),
					);
				}
			}
		}
		return $results;
	}
	
	public function writesendquick($data) {
		
		
		
		$this->load->language('extension/module/cheaper30');
		
		if (isset($data['option'])) {$options = " `option` = '" . $this->db->escape(json_encode($data['option'])) . "',";} else {$options = " `option` = '',";}
		
		$config_language_id = $this->config->get('config_language_id');
		
		$input_fields = array();
		$regex_fields = array();
		if (isset($data['input_field']) and $data['input_field']){
			foreach ($data['input_field'] as $id => $field){
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "cheaper_module_fields WHERE `id` = '" . (int)$id . "' AND `module_id` = '" . (int)$data['module_id'] . "'");
				if ($query->num_rows){
					foreach ($query->rows as $row){
						$input_fields[$row['id']] = json_decode($row['name'],true)[$config_language_id];
						$regex_fields[$row['id']] = $row['regex'];
					}
				}
			}
		}
		
		$text_email = '';
		if ($data['input_field']){
			foreach ($input_fields as $id => $field){
				if (isset($data['input_field'][$id])){
					if (is_array($data['input_field'][$id])) {
						$text_email .= '<strong>' . $field . '</strong>: ';
						foreach ($data['input_field'][$id] as $key => $value) {
							if (isset($key) && $key !== 'file') {
								if ($value and $value != '') {
									$text_email .= '<span class="normal">' . $value . '</span>' . (($key + 1) != count($data['input_field'][$id]) ? ', ' : '') ;
								}
							} else {
								if (isset($data['input_field'][$id]['file'])){
									$this->load->model('tool/upload');
									$upload_info = $this->model_tool_upload->getUploadByCode($data['input_field'][$id]['file']);
									if (isset($upload_info['code']) && $upload_info['code']){
									$href = $this->url->link('tool/upload/download', '&code=' . $upload_info['code'], true);
									
									if (strpos($href, HTTP_SERVER . 'admin/') === false) {
										$href = str_replace(HTTP_SERVER, HTTP_SERVER . 'admin/', $href);
									}
									if (strpos($href, HTTPS_SERVER . 'admin/') === false) {
										$href = str_replace(HTTPS_SERVER, HTTPS_SERVER . 'admin/', $href);
									}
									
									} else {$href = '';}
									if (isset($upload_info['name']) && $upload_info['name']){
										$name = ' (' . $upload_info['name'] . ')';
									}
									$text_email .= ($href ? '<a href="' . $href . '">' . $this->language->get('text_cheaper30_file') . $name . '</a>' : '');
								}
							}
						}
					} else {
						$text_email .= '<strong>' . $field . '</strong>: <span class="normal">' . $data['input_field'][$id] . '</span>';
					}
					$text_email .= '<br>';
				}
			}
		}
		if ($data['itog']){
			$itog = str_replace('/n','<br>',$data['itog']);
			$itog = str_replace('/ss','<span class="normal">',$itog);
			$itog = str_replace('/se','</span>',$itog);
			$text_email .= '<br>' . '<span class="normal">' . $this->language->get('text_calculator_rachet') . '</span>' . '<br>' . $itog;
		}
		$text_email .= '<br>' . $this->language->get('href_zapros') . '<a href="' . $this->request->post['href'] . '" target="_blank">' . $this->request->post['href'] . '</a><br>';
		
		$send_module = array();
		$emails = array();
		
		$config = $this->config_version();
		$module_info = array();
		
		if ($this->db->query("INSERT INTO " . DB_PREFIX . "cheaper_module SET `module_id` = '" . (int)$data['module_id'] . "', `date` = '" . $this->db->escape($data['date']) . "', `product_id` = '" . $this->db->escape($data['prod_id']) . "', `price` = '" . (int)$data['price'] . "'," . $options . " `text` = '" . $this->db->escape(json_encode($text_email, true)) . "', `status` = '0'")){
			
			$last_id = $this->db->getLastId();
			
			$text = '<br><strong>' . $this->language->get('text_more_zapros') . $last_id . '</strong><br><br>' . $text_email;
			
			$send_module['success_send'] = $this->language->get('success_send_module');
			$cheaper30_h1 = '';
			
			if ($data['module_id']) {
				$module_info = $this->{$config['model_']}->getModule($data['module_id']);
				
				if (isset($module_info['cheaper30_succes'][$config_language_id])){
					$send_module['success_send'] = $module_info['cheaper30_succes'][$config_language_id];
				}
				if (isset($module_info['cheaper30_h1'][$config_language_id])){
					$cheaper30_h1 = $module_info['cheaper30_h1'][$config_language_id];
				}
				
				$emails = $this->getCheaperingEmail($data['module_id']);
			}
			
			$email = $this->config->get('config_email');
			
			if (isset($data['prod_id'])){
				$product_id = (int)$data['prod_id'];
			} else {
				$product_id = 0;
			}
			
			$text_product = '';
			$this->load->model('catalog/product');
			$product_info = $this->model_catalog_product->getProduct($product_id);
			if ($product_info) {
				if (isset($product_info['name'])) {
					$name_product = $product_info['name'];
				} else {
					$name_product = $this->language->get('text_no_product');
				}
				$href_product = $this->url->link('product/product', '&product_id=' . $product_info['product_id'], 'SSL');
				
				$text_product = '<strong>' . $this->language->get('text_product') . '</strong><a href="' . $href_product . '">' . $name_product . '</a>';

			}
			
			$text = $text_product . $text;
			
			$text_cheaper30_title = sprintf($this->language->get('text_cheaper30_title'), $cheaper30_h1 . ' №' . $last_id, $this->request->server['HTTP_HOST']);
			
			$message  = '<html dir="ltr" lang="en">' . "\n";
			$message .= '  <head>' . "\n";
			$message .= '    <title>' . $text_cheaper30_title . '</title>' . "\n";
			$message .= '    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">' . "\n";
			$message .= '  </head>' . "\n";
			$message .= '  <body>' . html_entity_decode($text, ENT_QUOTES, 'UTF-8') . '</body>' . "\n";
			$message .= '</html>' . "\n";
			
			$store_name = $this->config->get('config_name');
			
			$telegram = '';
			
			$uploads_info = array();
			if ($data['input_field']){
				$telegram .= '<strong>' . $this->language->get('text_more_zapros') . $last_id . (isset($cheaper30_h1) ? ' "' . $cheaper30_h1 . '"' : '') . '</strong>' . "\n";
				
				$test_file = true;
				foreach ($input_fields as $id => $field){
					$regex = $regex_fields[$id];
					if (isset($data['input_field'][$id])){
						if (is_array($data['input_field'][$id])){
							
							$telegram .= $field . ': ';
							
							foreach ($data['input_field'][$id] as $key => $value){
								if (isset($key) && $key !== 'file'){
									if ($value and $value != ''){
										$telegram .= $value . (($key + 1) != count($data['input_field'][$id]) ? ', ' : '');
									}
								} else {
									if (isset($data['input_field'][$id]['file'])){
										$this->load->model('tool/upload');
										$upload_info = $this->model_tool_upload->getUploadByCode($data['input_field'][$id]['file']);
										
										if ($upload_info){
											$upload_info['name_field'] = $field . $this->language->get('text_file_file');
											$uploads_info[] = $upload_info;
											
											$telegram .= $this->language->get('text_down_file');
										}
									}
								}
							}
						} else {
							$telegram .= $field . ': ';
							
							if ($regex == 'phoneUS'){
								$phone_replace = $data['input_field'][$id];
								foreach ([' ','(',')','-'] as $replace){
									$phone_replace = str_replace($replace,'',$phone_replace);
								}
								$telegram .= '<a href="tel:' . $phone_replace . '">' . $phone_replace . '</a>';
								
							} else {
								$telegram .= $data['input_field'][$id];
							}
						}
						
						$telegram .=  "\n";
					}
				}
				$telegram .= "\n" . $this->language->get('href_zapros') . ' ' . urldecode( $this->request->post['href'] ) . "\n";
			
			}
			
			if ($data['itog']){
				$itog = $data['itog'];
				$itog = str_replace('/ss','<strong>',$itog);
				$itog = str_replace('/se','</strong>',$itog);
				$itog = str_replace("/n","\n",$itog);

				$telegram .= "\n" . '<strong>' . $this->language->get('text_calculator_rachet') . '</strong>' . "\n" . $itog;
				
			}
			if ($module_info && isset($module_info['status_email']) && $module_info['status_email']){
				$mail = new Mail();
				
				if ($config['version'] != '2.0'){
					$mail->protocol = $this->config->get('config_mail_protocol');
					$mail->parameter = $this->config->get('config_mail_parameter');
					$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
					$mail->smtp_username = $this->config->get('config_mail_smtp_username');
					$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
					$mail->smtp_port = $this->config->get('config_mail_smtp_port');
					$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
				}
				
				
				if ($emails){
					foreach ($emails as $email1){
						$mail->setTo($email1);
						$mail->setFrom($email);
						$mail->setSender(html_entity_decode($store_name, ENT_QUOTES, 'UTF-8'));
						$mail->setSubject(html_entity_decode($text_cheaper30_title, ENT_QUOTES, 'UTF-8'));
						$mail->setHtml($message);
						
						if ($uploads_info){
							foreach ($uploads_info as $key => $upload_info){
								if (copy(DIR_UPLOAD . $upload_info['filename'], DIR_UPLOAD . $upload_info['name'])){
									$mail->addAttachment(DIR_UPLOAD . $upload_info['name']);
								}
							}
						}
						
						$mail->send();
					}
				} else {
					$mail->setTo($this->config->get('config_email'));
					$mail->setFrom($email);
					$mail->setSender(html_entity_decode($store_name, ENT_QUOTES, 'UTF-8'));
					$mail->setSubject(html_entity_decode($text_cheaper30_title, ENT_QUOTES, 'UTF-8'));
					$mail->setHtml($message);
					
					if ($uploads_info){
						foreach ($uploads_info as $key => $upload_info){
							if (copy(DIR_UPLOAD . $upload_info['filename'], DIR_UPLOAD . $upload_info['name'])){
								$mail->addAttachment(DIR_UPLOAD . $upload_info['name']);
							}
						}
					}
					
					$mail->send();
				}
				
				
				if ($uploads_info){
					foreach ($uploads_info as $key => $upload_info){
						if(file_exists(DIR_UPLOAD . $upload_info['name'])){
							unlink(DIR_UPLOAD . $upload_info['name']);
						}
					}
				}
				
			}
			if ($module_info && isset($module_info['status_telegram']) && $module_info['status_telegram']){

				$downloads = [];
				$types = [];
				foreach (['photo_video', 'audio', 'document'] as $type){
					$types[$type] = 0;
				}
			
				$type_text = '';
				if ($uploads_info){
					foreach ($uploads_info as $key => $upload_info){
						$type = $this->typeFile($upload_info['name']);
						
						$type_test = $type;
						if ($type_test == 'photo' || $type_test == 'video'){
							$type_text .= $upload_info['name_field'] . "\n";
						}
					}
					
					foreach ($uploads_info as $key => $upload_info){
						
						
						
						$type = $this->typeFile($upload_info['name']);
						
						$type_test = $type;
						if ($type_test == 'photo' || $type_test == 'video'){
							$type_test = 'photo_video';
						}
						
						if (!$key){
							$downloads[] = [
								'type' => $type,
								'media' => 'attach://' . $types[$type_test],
								'caption' => $telegram,
								'parse_mode' => 'html',
								'path' => new CURLFile(DIR_UPLOAD . $upload_info['filename'], mime_content_type(DIR_UPLOAD . $upload_info['filename']), $upload_info['name'])
								
							];
						} else {
							$downloads[] = [
								'type' => $type,
								'media' => 'attach://' . $types[$type_test],
								'parse_mode' => 'html',
								'caption' => ($type_test != 'photo_video' ? $upload_info['name_field'] : ($type_test == 'photo_video' && !$types[$type_test] ? $type_text : '')),
								'path' => new CURLFile(DIR_UPLOAD . $upload_info['filename'], mime_content_type(DIR_UPLOAD . $upload_info['filename']), $upload_info['name'])
								
							];
						}
						
						$types[$type_test]++;
					}
				}
			
				if (!$downloads){
					$this->sendTelegram($telegram, array(), $module_info);
				} else {
					$this->sendTelegram($telegram, $downloads, $module_info);
				}
			}

		} else {
			
			$send_module['error_send'] = $this->language->get('error_send_module');
			
			if ($data['module_id']) {
				$module_info = $this->{$config['model_']}->getModule($data['module_id']);
				
				if (isset($module_info['cheaper30_errort'][$config_language_id])){
					$send_module['error_send'] = $module_info['cheaper30_errort'][$config_language_id];
				}
			}
		}
		
		return $send_module;
	}
	
	public function typeFile($filename){
		$path_parts = pathinfo($filename);
		$extension = $path_parts['extension'];
		
		$type = 'document';
		if (in_array($extension, ['jpg','jpeg','gif','png','webp'])){
			$type = 'photo';
		}
		if (in_array($extension, ['mp4','webm','mov','mkv','avi','wmv'])){
			$type = 'video';
		}
		if (in_array($extension, ['mp3','ogg','aac','flac','wma'])){
			$type = 'audio';
		}
		return $type;
	}
	
	public function sendTelegram($strong = '', $downloads = array(), $module_info){
		
		if ($module_info) {

			$token = $module_info['token_telegram'];
			$chat_id = $module_info['chat_id_telegram'];
		
		/*$proxy = "67.154.111.452:3128";*/

			if ($downloads){
				
				$result = [];
				foreach ($downloads as $download){
					if ($download['type'] == 'photo' || $download['type'] == 'video'){
						$result['photo_video'][] = $download;
						$result['photo_video_type'] = 'photo_video';
						$result['photo_video_key'][] = $download['path'];
					} else {
						$result[$download['type']][] = $download;
						$result[$download['type'] . '_type'] = $download['type'];
						$result[$download['type'] . '_key'][] = $download['path'];
					}
				}
				
				$results = [];
				$post_data = [];
				
				$sorts = [];
				if ($result){
					foreach($result as $sort => $value){
						if ($sort == 'document'){$sorts[] = 'document';}
						if ($sort == 'photo_video'){$sorts[] = 'photo_video';}
						if ($sort == 'audio'){$sorts[] = 'audio';}
					}
				}
				
				foreach ($sorts as $type){
					if (isset($result[$type])){
						$post_data = [
							'chat_id' => $chat_id,
							'type' => $result[$type . '_type'],
							'media' => json_encode($result[$type]),
						];
					}
					if (isset($result[$type . '_key'])){
						$post_data = array_merge($post_data, $result[$type . '_key']);
					}
					if ($post_data){
						$results[] = $post_data;
						$post_data = [];
					}
				}
				
				if ($results){
					foreach ($results as $resul){
						if ($resul['type'] == 'document1'){

						} else {
							$ch = curl_init('https://api.telegram.org/bot'. $token .'/sendMediaGroup');
							curl_setopt($ch, CURLOPT_POST, 1);
							curl_setopt($ch, CURLOPT_POSTFIELDS, $resul);
							curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
							curl_setopt($ch, CURLOPT_HEADER, false);
							curl_exec($ch);
							curl_close($ch);
						}
					}
				}
				
			} else {
				
				$ch = curl_init();
				
				$strong = urlencode($strong);
				
				curl_setopt($ch, CURLOPT_URL, 'https://api.telegram.org/bot' . $token . '/sendMessage?chat_id=' . $chat_id . '&parse_mode=html&text=' . $strong);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

				// Подключение к прокси серверу
				/*curl_setopt($ch, CURLOPT_PROXY, $proxy);*/
				// если требуется авторизация
				// curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyauth);

				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_HEADER, 1);
				curl_exec($ch);
				curl_close($ch);

			}
		}
	}

	function getCheaperingCalculators($module_id){
		
		$language_id = $this->config->get('config_language_id');
		
		$result = array();
		
		$query_formula = $this->db->query("SELECT * FROM " . DB_PREFIX . "cheaper_module_formula WHERE `module_id`='" . (int)$module_id . "' ORDER BY formula_id,sort_order ASC");
		
		if ($query_formula->num_rows){
			foreach ($query_formula->rows as $formula){
				
				$conditions = $this->getCondition($module_id, $formula['formula_id'], $formula['sort_order'], $language_id);
				
				$result[$formula['formula_id']][] = array(
					'type' => $formula['type'],
					'value' => $formula['value'],
					'show' => $formula['show'],
					'conditions' => $conditions
				);
			}
		}
		
		return $result;
	}
	
	function getCondition($module_id, $formula_id, $sort_field, $language_id){
		$result = array();
		
		$query_condition = $this->db->query("SELECT * FROM " . DB_PREFIX . "cheaper_module_condition WHERE `module_id`='" . (int)$module_id . "' AND `formula_id`='" . (int)$formula_id . "' AND `sort_field`='" . (int)$sort_field . "' ORDER BY formula_id ASC,sort_field ASC,`row` ASC,FIELD(`condition`,'if','to','else'),sort_order ASC");
		
		if ($query_condition->num_rows){
			foreach ($query_condition->rows as $condition){
				if ($condition['type'] == 'field'){
					$query_field = $this->db->query("SELECT DISTINCT name FROM " . DB_PREFIX . "cheaper_module_fields WHERE `module_id`='" . (int)$module_id . "' AND `id`='" . (int)$condition['value'] . "'");
					
					if (isset($query_field->row['name'])){
						$values = json_decode($query_field->row['name'],true);
						$value = $values[$language_id];
					} else {
						$value = '';
					}
					$field_id = $condition['value'];
				} elseif ($condition['type'] == 'number') {
					
					$values = [];
					foreach (json_decode($condition['value'],true) as $valu){
						foreach ($valu as $lang_id => $val){
							$values[$lang_id] = $val;
						}
					}
					
					$value = '';
					if (isset($values[$language_id])){
						$value = $values[$language_id];
					}

				} else {
					$value = htmlspecialchars_decode($condition['value']);
					$field_id = 0;
				}
				
				$result[$condition['sort_field']][$condition['row']][$condition['condition']][] = array(
					'type' => $condition['type'],
					'value' => $value,
					'field_id' => $field_id,
					'sort_order' => $condition['sort_order']
				);
			}
		}
		
		return $result;
		
	}
	
	
	public function config_version(){
		$data = array();
		
		$config_version = substr(VERSION, 0, 3);
		$data['version'] = $config_version;
		$this->load->model('extension/module/cheaper30');
		if ($config_version == '3.0' or $config_version == '3.1'){
			$this->load->model('setting/module');
			$data['model_'] = 'model_setting_module';
			
			
			$this->load->model('setting/setting');
			$setting_info = $this->model_setting_setting->getSetting('theme_' . $this->config->get('config_theme'), $this->config->get('config_store_id'));
			if (isset($setting_info['theme_' . $this->config->get('config_theme') . '_directory'])) {
				$data['theme'] = $setting_info['theme_' . $this->config->get('config_theme') . '_directory'];
			} elseif (isset($setting_info['theme_default_directory'])) {
				$data['theme'] = $setting_info['theme_default_directory'];
			} else {
				$data['theme'] = $this->config->get('config_theme');
			}
			
			
		} else {
			$this->load->model('extension/module');
			$data['model_'] = 'model_extension_module';
			$data['theme'] = $this->config->get('config_theme');
		}
		return $data;
	}
	
}