<?php
class ControllerExtensionModuleSticker extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/sticker');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('module_sticker', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');
			
			if ((int)$this->request->post['module_sticker_apply']) {
				$this->response->redirect($this->url->link('extension/module/sticker', 'user_token=' . $this->session->data['user_token'], true));
			}

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		
		if (isset($this->error['sticker_price_name'])) {
			$data['error_sticker_price_name'] = $this->error['sticker_price_name'];
		} else {
			$data['error_sticker_price_name'] = array();
		}
		
		if (isset($this->error['sticker_custom_name'])) {
			$data['error_sticker_custom_name'] = $this->error['sticker_custom_name'];
		} else {
			$data['error_sticker_custom_name'] = array();
		}
		
		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/sticker', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/module/sticker', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
		
		if (isset($this->request->post['module_sticker_status'])) {
			$data['module_sticker_status'] = $this->request->post['module_sticker_status'];
		} else {
			$data['module_sticker_status'] = $this->config->get('module_sticker_status');
		}
		
		if (isset($this->request->post['module_sticker_position'])) {
			$data['module_sticker_position'] = $this->request->post['module_sticker_position'];
		} else {
			$data['module_sticker_position'] = $this->config->get('module_sticker_position');
		}
		
		if (isset($this->request->post['module_sticker_type'])) {
			$data['module_sticker_type'] = $this->request->post['module_sticker_type'];
		} else {
			$data['module_sticker_type'] = $this->config->get('module_sticker_type');
		}
		
		if (isset($this->request->post['module_sticker_new'])) {
			$data['module_sticker_new'] = $this->request->post['module_sticker_new'];
		} else {
			$data['module_sticker_new'] = $this->config->get('module_sticker_new');
		}
		
		if (isset($this->request->post['module_sticker_special'])) {
			$data['module_sticker_special'] = $this->request->post['module_sticker_special'];
		} else {
			$data['module_sticker_special'] = $this->config->get('module_sticker_special');
		}
		
		if (isset($this->request->post['module_sticker_bestseller'])) {
			$data['module_sticker_bestseller'] = $this->request->post['module_sticker_bestseller'];
		} else {
			$data['module_sticker_bestseller'] = $this->config->get('module_sticker_bestseller');
		}
		
		if (isset($this->request->post['module_sticker_stock'])) {
			$data['module_sticker_stock'] = $this->request->post['module_sticker_stock'];
		} else {
			$data['module_sticker_stock'] = $this->config->get('module_sticker_stock');
		}
		
		$this->load->model('tool/image');
		
		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 70, 70);
		
		if (isset($this->request->post['module_sticker_price'])) {
			$data['module_sticker_price'] = $this->request->post['module_sticker_price'];
		} elseif ($this->config->get('module_sticker_price')) {
			$data['module_sticker_price'] = $this->config->get('module_sticker_price');
		} else {
			$data['module_sticker_price'] = array();
		}
		
		if (isset($this->request->post['module_sticker_custom'])) {
			$data['module_sticker_custom'] = $this->request->post['module_sticker_custom'];
		} elseif ($this->config->get('module_sticker_custom')) {
			$data['module_sticker_custom'] = $this->config->get('module_sticker_custom');
		} else {
			$data['module_sticker_custom'] = array();
		}
		
		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/sticker', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/sticker')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		$this->load->model('localisation/language');

		$languages = $this->model_localisation_language->getLanguages();
		
		if (isset($this->request->post['module_sticker_price'])) {
			foreach ($this->request->post['module_sticker_price'] as $key => $sticker) {
				
				foreach ($languages as $language) {
					if ((utf8_strlen($sticker[$language['language_id']]['name']) < 1)) {
						$this->error['sticker_price_name'][$key][$language['language_id']] = $this->language->get('error_name');
						
						$this->error['warning'] = $this->language->get('error_name');
					}
				}
			}
		}
		
		if (isset($this->request->post['module_sticker_custom'])) {
			foreach ($this->request->post['module_sticker_custom'] as $key => $sticker) {
				
				foreach ($languages as $language) {
					if ((utf8_strlen($sticker[$language['language_id']]['name']) < 1)) {
						$this->error['sticker_custom_name'][$key][$language['language_id']] = $this->language->get('error_name');
						
						$this->error['warning'] = $this->language->get('error_name');
					}
				}
			}
		}

		return !$this->error;
	}
	
	public function install() {
		$this->load->model('extension/module/sticker');
		$this->model_extension_module_sticker->createColumns();
	}
}