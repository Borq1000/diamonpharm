<?php
/**
 * Popup Module - Admin Controller
 * @version 1.0.0
 */
class ControllerExtensionModulePopup extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/popup');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('module_popup', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
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
			'href' => $this->url->link('extension/module/popup', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/module/popup', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		// Status
		if (isset($this->request->post['module_popup_status'])) {
			$data['module_popup_status'] = $this->request->post['module_popup_status'];
		} else {
			$data['module_popup_status'] = $this->config->get('module_popup_status');
		}

		// Image
		if (isset($this->request->post['module_popup_image'])) {
			$data['module_popup_image'] = $this->request->post['module_popup_image'];
		} else {
			$data['module_popup_image'] = $this->config->get('module_popup_image');
		}

		// Image preview
		$this->load->model('tool/image');
		if (isset($this->request->post['module_popup_image']) && is_file(DIR_IMAGE . $this->request->post['module_popup_image'])) {
			$data['thumb'] = $this->model_tool_image->resize($this->request->post['module_popup_image'], 300, 200);
		} elseif ($this->config->get('module_popup_image') && is_file(DIR_IMAGE . $this->config->get('module_popup_image'))) {
			$data['thumb'] = $this->model_tool_image->resize($this->config->get('module_popup_image'), 300, 200);
		} else {
			$data['thumb'] = $this->model_tool_image->resize('no_image.png', 300, 200);
		}

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 300, 200);

		// Image link
		if (isset($this->request->post['module_popup_image_link'])) {
			$data['module_popup_image_link'] = $this->request->post['module_popup_image_link'];
		} else {
			$data['module_popup_image_link'] = $this->config->get('module_popup_image_link');
		}

		// Form title
		if (isset($this->request->post['module_popup_form_title'])) {
			$data['module_popup_form_title'] = $this->request->post['module_popup_form_title'];
		} else {
			$data['module_popup_form_title'] = $this->config->get('module_popup_form_title');
		}

		// Delay (seconds)
		if (isset($this->request->post['module_popup_delay'])) {
			$data['module_popup_delay'] = $this->request->post['module_popup_delay'];
		} else {
			$data['module_popup_delay'] = $this->config->get('module_popup_delay') ? $this->config->get('module_popup_delay') : 3;
		}

		// Hide days
		if (isset($this->request->post['module_popup_hide_days'])) {
			$data['module_popup_hide_days'] = $this->request->post['module_popup_hide_days'];
		} else {
			$data['module_popup_hide_days'] = $this->config->get('module_popup_hide_days') ? $this->config->get('module_popup_hide_days') : 1;
		}

		// Button text
		if (isset($this->request->post['module_popup_button_text'])) {
			$data['module_popup_button_text'] = $this->request->post['module_popup_button_text'];
		} else {
			$data['module_popup_button_text'] = $this->config->get('module_popup_button_text') ? $this->config->get('module_popup_button_text') : 'Отправить';
		}

		// Dev mode
		if (isset($this->request->post['module_popup_dev_mode'])) {
			$data['module_popup_dev_mode'] = $this->request->post['module_popup_dev_mode'];
		} else {
			$data['module_popup_dev_mode'] = $this->config->get('module_popup_dev_mode');
		}

		$data['user_token'] = $this->session->data['user_token'];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/popup', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/popup')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}
