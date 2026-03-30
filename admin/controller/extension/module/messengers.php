<?php
class ControllerExtensionModuleMessengers extends Controller {

	private $error = array();

	public function index() {
		$this->load->language('extension/module/messengers');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('module_messengers', $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');
			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		// Errors
		$data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';

		// Breadcrumbs
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
			'href' => $this->url->link('extension/module/messengers', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/module/messengers', 'user_token=' . $this->session->data['user_token'], true);
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		// Load saved values
		$data['module_messengers_status']   = $this->getVal('module_messengers_status', 1);
		$data['module_messengers_whatsapp'] = $this->getVal('module_messengers_whatsapp', '');
		$data['module_messengers_telegram'] = $this->getVal('module_messengers_telegram', '');
		$data['module_messengers_max']      = $this->getVal('module_messengers_max', '');

		// Language strings
		$data['heading_title']       = $this->language->get('heading_title');
		$data['text_edit']           = $this->language->get('text_edit');
		$data['text_enabled']        = $this->language->get('text_enabled');
		$data['text_disabled']       = $this->language->get('text_disabled');
		$data['entry_status']        = $this->language->get('entry_status');
		$data['entry_whatsapp']      = $this->language->get('entry_whatsapp');
		$data['entry_telegram']      = $this->language->get('entry_telegram');
		$data['entry_max']           = $this->language->get('entry_max');
		$data['help_whatsapp']       = $this->language->get('help_whatsapp');
		$data['help_telegram']       = $this->language->get('help_telegram');
		$data['help_max']            = $this->language->get('help_max');
		$data['button_save']         = $this->language->get('button_save');
		$data['button_cancel']       = $this->language->get('button_cancel');

		$data['header']      = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer']      = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/messengers', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/messengers')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		return !$this->error;
	}

	private function getVal($key, $default = '') {
		if (isset($this->request->post[$key])) {
			return $this->request->post[$key];
		}
		$val = $this->config->get($key);
		return ($val !== null) ? $val : $default;
	}
}
