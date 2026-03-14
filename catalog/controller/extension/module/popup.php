<?php
/**
 * Popup Module - Catalog Controller
 * @version 1.0.0
 */
class ControllerExtensionModulePopup extends Controller {
	public function index() {
		// Debug: uncomment to check settings
		// error_log('Popup status: ' . var_export($this->config->get('module_popup_status'), true));

		// Check if module is enabled
		$status = $this->config->get('module_popup_status');
		if (!$status) {
			$output = '<!-- Popup disabled: status=' . var_export($status, true) . ' -->';
			if (isset($this->request->get['route']) && $this->request->get['route'] == 'extension/module/popup') {
				$this->response->setOutput($output);
			}
			return $output;
		}

		$this->load->language('extension/module/popup');

		$data['image'] = '';
		if ($this->config->get('module_popup_image') && is_file(DIR_IMAGE . $this->config->get('module_popup_image'))) {
			$this->load->model('tool/image');
			$data['image'] = $this->model_tool_image->resize($this->config->get('module_popup_image'), 500, 333);
		}

		$data['image_link'] = $this->config->get('module_popup_image_link');
		$data['form_title'] = $this->config->get('module_popup_form_title') ? $this->config->get('module_popup_form_title') : $this->language->get('text_form_title');
		$data['delay'] = (int)($this->config->get('module_popup_delay') ? $this->config->get('module_popup_delay') : 3) * 1000; // Convert to milliseconds
		$data['hide_days'] = (int)($this->config->get('module_popup_hide_days') ? $this->config->get('module_popup_hide_days') : 1);
		$data['button_text'] = $this->config->get('module_popup_button_text') ? $this->config->get('module_popup_button_text') : $this->language->get('button_send');

		$data['action'] = $this->url->link('extension/module/popup/send', '', true);

		$data['text_name'] = $this->language->get('text_name');
		$data['text_phone'] = $this->language->get('text_phone');
		$data['text_success'] = $this->language->get('text_success');
		$data['text_error'] = $this->language->get('text_error');
		$data['dev_mode'] = $this->config->get('module_popup_dev_mode') ? 1 : 0;

		// Support both direct call and AJAX
		$output = $this->load->view('extension/module/popup', $data);

		if (isset($this->request->get['route']) && $this->request->get['route'] == 'extension/module/popup') {
			$this->response->setOutput($output);
		}

		return $output;
	}

	public function send() {
		$this->load->language('extension/module/popup');

		$json = array();

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			$name = isset($this->request->post['name']) ? trim($this->request->post['name']) : '';
			$phone = isset($this->request->post['phone']) ? trim($this->request->post['phone']) : '';

			// Validation
			if (utf8_strlen($name) < 2 || utf8_strlen($name) > 64) {
				$json['error'] = $this->language->get('error_name');
			}

			if (utf8_strlen($phone) < 5 || utf8_strlen($phone) > 32) {
				$json['error'] = $this->language->get('error_phone');
			}

			if (!isset($json['error'])) {
				// Send email
				$mail = new Mail($this->config->get('config_mail_engine'));
				$mail->parameter = $this->config->get('config_mail_parameter');
				$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
				$mail->smtp_username = $this->config->get('config_mail_smtp_username');
				$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
				$mail->smtp_port = $this->config->get('config_mail_smtp_port');
				$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

				$mail->setTo($this->config->get('config_email'));
				$mail->setFrom($this->config->get('config_email'));
				$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
				$mail->setSubject(html_entity_decode($this->language->get('email_subject'), ENT_QUOTES, 'UTF-8'));

				$message = $this->language->get('email_greeting') . "\n\n";
				$message .= $this->language->get('text_name') . ': ' . $name . "\n";
				$message .= $this->language->get('text_phone') . ': ' . $phone . "\n\n";
				$message .= $this->language->get('email_footer');

				$mail->setText($message);

				try {
					$mail->send();
					$json['success'] = $this->language->get('text_success');
				} catch (Exception $e) {
					$json['error'] = $this->language->get('text_error');
				}

				try {
					$additional = [ 'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'yclid', '_ym_uid', '_ym_counter', 'roistat_first_visit', 'roistat_visit' ];

					$params = [
						'name'	=> $name,
						'phone'	=> $phone
					];

					foreach ( $additional as $param ) {
						if ( isset( $this->request->post[ $param ] ) ) { $params[ $param ] = $this->request->post[ $param ]; }
					}

					// $url = 'http://requestbin.cn:80/z2gxj6z2';
					$url = 'https://ep.morekit.io/94912d32a257db2f243caf1ca5c8632e';

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
		} else {
			$json['error'] = $this->language->get('text_error');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
