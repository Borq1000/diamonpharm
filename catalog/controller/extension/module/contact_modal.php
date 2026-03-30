<?php
class ControllerExtensionModuleContactModal extends Controller {

	public function index() {
		$language_id = $this->config->get('config_language_id');

		// --- Телефоны из ProStore конфига ---
		$data['phones'] = array();
		$prostore_phones = $this->config->get('theme_prostore_phones');
		if ($prostore_phones) {
			foreach ($prostore_phones as $phone) {
				if (isset($phone[$language_id]) && $phone[$language_id]) {
					$display = html_entity_decode($phone[$language_id], ENT_QUOTES, 'UTF-8');
					$data['phones'][] = array(
						'display' => $display,
						'href'    => 'tel:' . preg_replace('/[\s\-\(\)]/', '', $display),
					);
				}
			}
		}

		// --- Мессенджеры из ProStore конфига ---
		$data['messengers'] = array();
		$this->load->model('setting/setting');
		$mes_setting = $this->model_setting_setting->getSetting('theme_prostoremeslinks');
		$messenger_links = isset($mes_setting['theme_prostoremeslinks_array'])
			? $mes_setting['theme_prostoremeslinks_array']
			: array();
		$messenger_navs = $this->config->get('theme_prostore_messenger_nav');

			// Извлекаем ссылку WhatsApp из ProStore конфига
		$wa_link = 'https://wa.me/79660788880';

		if ($messenger_navs && $messenger_links) {
			foreach ($messenger_navs as $nav) {
				if (!isset($messenger_links[$nav['settype']])) {
					continue;
				}
				$parts = explode(' ', $messenger_links[$nav['settype']]);
				$icon  = strtolower($parts[0]);
				$url   = end($parts) . (isset($nav['link']) ? $nav['link'] : '');

				if ($icon === 'whatsapp') {
					$wa_link = $url;
				}
			}
		}

		// Ссылки из централизованного модуля «Мессенджеры» (с фолбэком на старые значения)
		$cfg_wa  = $this->config->get('module_messengers_whatsapp');
		$cfg_tg  = $this->config->get('module_messengers_telegram');
		$cfg_max = $this->config->get('module_messengers_max');

		$url_wa  = $cfg_wa  ? $cfg_wa  : $wa_link;
		$url_tg  = $cfg_tg  ? $cfg_tg  : 'https://t.me/+ZJWJXn9HRglkYjZi';
		$url_max = $cfg_max ? $cfg_max : 'https://max.ru/diamondpharm';

		// Порядок: Max → WhatsApp → Telegram
		$data['messengers'] = array(
			array('name' => 'Max',      'icon' => 'max',      'url' => $url_max, 'img' => 'image/catalog/000/max-messenger.jpg'),
			array('name' => 'WhatsApp', 'icon' => 'whatsapp', 'url' => $url_wa,  'img' => 'image/catalog/000/wsp.png'),
			array('name' => 'Telegram', 'icon' => 'telegram', 'url' => $url_tg,  'img' => 'image/catalog/000/Telegram.png'),
		);

		// --- Callback (обратный звонок) ---
		$data['callback_status'] = $this->config->get('theme_prostore_callback_status');

		// Согласие на обработку персональных данных (152-ФЗ)
		$data['text_pdata'] = '';
		if ($this->config->get('theme_prostore_callback_pdata')) {
			$this->load->language('extension/theme/prostore');
			$this->load->model('catalog/information');
			$info = $this->model_catalog_information->getInformation(
				$this->config->get('theme_prostore_callback_pdata')
			);
			if ($info) {
				$data['text_pdata'] = sprintf(
					$this->language->get('text_prostore_pdata'),
					'Отправить',
					$this->url->link('information/information/agree', 'information_id=' . $this->config->get('theme_prostore_callback_pdata'), true),
					$info['title'],
					$info['title']
				);
			}
		}

		// Поддержка и прямого AJAX-вызова, и вызова из другого контроллера
		$output = $this->load->view('extension/module/contact_modal', $data);

		if (isset($this->request->get['route']) && $this->request->get['route'] == 'extension/module/contact_modal') {
			$this->response->setOutput($output);
		}

		return $output;
	}
}
