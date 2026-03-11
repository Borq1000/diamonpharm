<?php
class ControllerExtensionModuleCertificates extends Controller {
	public function index($setting) {
		$this->load->language('extension/module/certificates');
		$this->load->model('tool/image');

		// Получаем настройки модуля
		$limit = isset($setting['limit']) ? (int)$setting['limit'] : 8;
		$per_page = isset($setting['per_page']) ? (int)$setting['per_page'] : 4;
		// Игнорируем размеры из админки для фронтенда
		$width = 500;  // Фиксированная ширина
		$height = 700; // Фиксированная высота для вертикальных сертификатов
		
		$data['certificates'] = array();
		$data['heading_title'] = !empty($setting['name']) ? $setting['name'] : $this->language->get('heading_title');
		
		// Получаем список сертификатов из настроек модуля
		if (isset($setting['certificates'])) {
			$certificates = array_slice($setting['certificates'], 0, $per_page);
			
			foreach ($certificates as $certificate) {
				if (!empty($certificate['image']) && is_file(DIR_IMAGE . $certificate['image'])) {
					$data['certificates'][] = array(
						'title' => isset($certificate['title'][$this->config->get('config_language_id')]) ? $certificate['title'][$this->config->get('config_language_id')] : '',
						'description' => isset($certificate['description'][$this->config->get('config_language_id')]) ? $certificate['description'][$this->config->get('config_language_id')] : '',
						'thumb' => $this->model_tool_image->resize($certificate['image'], $width, $height),
						'popup' => $this->model_tool_image->resize($certificate['image'], 1000, 1400),
						'alt' => isset($certificate['alt'][$this->config->get('config_language_id')]) ? $certificate['alt'][$this->config->get('config_language_id')] : '',
					);
				}
			}
		}
		
		// Настройки для "Загрузить еще"
		$data['show_more'] = false;
		if (isset($setting['certificates']) && count($setting['certificates']) > $per_page) {
			$data['show_more'] = true;
			$data['total_certificates'] = count($setting['certificates']);
			$data['loaded_certificates'] = count($data['certificates']);
			$data['per_page'] = $per_page;
			$data['module_id'] = isset($setting['module_id']) ? $setting['module_id'] : 0;
		}
		
		$data['text_load_more'] = $this->language->get('text_load_more');

		if (!empty($data['certificates'])) {
			return $this->load->view('extension/module/certificates', $data);
		}
	}
	
	public function loadMore() {
		$this->load->language('extension/module/certificates');
		$this->load->model('setting/module');
		$this->load->model('tool/image');
		
		$json = array();
		
		if (isset($this->request->post['module_id']) && isset($this->request->post['offset'])) {
			$module_id = (int)$this->request->post['module_id'];
			$offset = (int)$this->request->post['offset'];
			$per_page = isset($this->request->post['per_page']) ? (int)$this->request->post['per_page'] : 4;
			
			$module_info = $this->model_setting_module->getModule($module_id);
			
			if ($module_info && isset($module_info['certificates'])) {
				$width = isset($module_info['width']) ? (int)$module_info['width'] : 300;
				$height = isset($module_info['height']) ? (int)$module_info['height'] : 300;
				
				$certificates = array_slice($module_info['certificates'], $offset, $per_page);
				$certificates_data = array();
				
				foreach ($certificates as $certificate) {
					if (!empty($certificate['image']) && is_file(DIR_IMAGE . $certificate['image'])) {
						$certificates_data[] = array(
							'title' => isset($certificate['title'][$this->config->get('config_language_id')]) ? $certificate['title'][$this->config->get('config_language_id')] : '',
							'description' => isset($certificate['description'][$this->config->get('config_language_id')]) ? $certificate['description'][$this->config->get('config_language_id')] : '',
							'thumb' => $this->model_tool_image->resize($certificate['image'], $width, $height),
							'popup' => $this->model_tool_image->resize($certificate['image'], 1000, 1400),
							'alt' => isset($certificate['alt'][$this->config->get('config_language_id')]) ? $certificate['alt'][$this->config->get('config_language_id')] : '',
						);
					}
				}
				
				if (!empty($certificates_data)) {
					$json['success'] = $certificates_data;
					$json['hasMore'] = (count($module_info['certificates']) > ($offset + $per_page));
				} else {
					$json['error'] = 'Нет больше сертификатов';
				}
			} else {
				$json['error'] = 'Модуль не найден';
			}
		} else {
			$json['error'] = 'Неверные параметры';
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}