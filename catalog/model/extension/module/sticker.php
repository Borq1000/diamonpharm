<?php
class ModelExtensionModuleSticker extends Model {
	public function getProductSticker($product) {
		$this->load->language('extension/module/sticker');
		
		$language_id = $this->config->get('config_language_id');
		$sticker_data = array();

		// New Sticker	
		$sticker_new = $this->config->get('module_sticker_new');
		$date_added = (time() - strtotime($sticker_new['date_new'] ? $product['date_added'] : $product['date_available'])) / 86400;

		if ($sticker_new['status'] && (int)$date_added <= (int)$sticker_new['day']) {
			$sticker_data[] = array(
				'class' 	 => 'sticker-new',
				'name'  	 => $this->language->get('text_sticker_new'),
				'image' 	 => '',
				'sort_order' => $sticker_new['sort_order']
			);
		}				
		
		// Sticker Special
		$sticker_special = $this->config->get('module_sticker_special');
		$class = '';
		
		if ((float)$product['special'] && $sticker_special['status']) {
			if ($sticker_special['label'] == 2) {
				$name = $this->language->get('text_sticker_special') . ' <span class="sticker-text-percent">-' . round(($product['price'] - $product['special']) / ($product['price'] / 100)) . '%</span>';
			} elseif ($sticker_special['label'] == 1) {
				$class = ' sticker-percent';
				$name = '-' . round(($product['price'] - $product['special']) / ($product['price'] / 100)) . '%';
			} else {
				$name = $this->language->get('text_sticker_special');
			}
			
			$sticker_data[] = array(
				'class' 		=> 'sticker-special' . $class,
				'name'  		=> $name,
				'image' 		=> '',
				'sort_order'	=> $sticker_special['sort_order']
			);
		}
		
		// Sticker Bestseller
		$sticker_bestseller = $this->config->get('module_sticker_bestseller');
		
		if ($sticker_bestseller['status'] && (int)$sticker_bestseller['sale'] && $product['sticker_bestseller'] && $product['sticker_bestseller'] >= $sticker_bestseller['sale']) {
			$sticker_data[] = array(
				'class' 	 => 'sticker-bestseller',
				'name'  	 => $this->language->get('text_sticker_bestseller'),
				'image' 	 => '',
				'sort_order' => $sticker_bestseller['sort_order']
			);
		}
		
		// Sticker Stock Status
		$sticker_stock = $this->config->get('module_sticker_stock');
		
		if ($sticker_stock['status'] && ($product['quantity'] <= 0)) {
			$sticker_data[] = array(
				'class' 	 => 'sticker-stock',
				'name'  	 => $product['stock_status'],
				'image' 	 => '',
				'sort_order' => $sticker_stock['sort_order']
			);
		}
		
		// Sticker Price
		if ($this->config->get('module_sticker_price')) {
			if ((float)$product['special']) {
				$price = $this->tax->calculate($product['special'], $product['tax_class_id'], $this->config->get('config_tax'));
			} else {
				$price = $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax'));
			}
			
			foreach ($this->config->get('module_sticker_price') as $key => $value) {
				if ($value['status'] && (!$value['date_start'] || $value['date_start'] <= date('Y-m-d')) && (!$value['date_end'] || $value['date_end'] > date('Y-m-d')) && ($price >= (float)$value['min']) && ($price <= (float)$value['max'])) {
					$sticker_data[] = array(
						'class' 		=> 'sticker-price' . $key,
						'name'  		=> $value[$language_id]['name'],
						'image' 		=> $value['image'] ? 'style="background: url(\'/image/' . $value['image'] . '\');"' : '',
						'sort_order'	=> $value['sort_order']
					);
				}
			}
		}
		
		// Sticker Custom
		if ($product['sticker_custom'] && $this->config->get('module_sticker_custom')) {
			$sticker_custom = unserialize($product['sticker_custom']);
			
			foreach ($this->config->get('module_sticker_custom') as $key => $value) {
				if ($value['status'] && isset($sticker_custom[$key]['status']) && $sticker_custom[$key]['status'] &&  (!$value['date_start'] || $value['date_start'] <= date('Y-m-d')) && (!$value['date_end'] || $value['date_end'] > date('Y-m-d'))) {
					$sticker_data[] = array(
						'class' 	 => 'sticker-custom' . $key,
						'name'  	 => $value[$language_id]['name'],
						'image' 	 => $value['image'] ? 'style="background: url(\'/image/' . $value['image'] . '\');"' : '',
						'sort_order' => $value['sort_order']
					);
				}
			}
		}
		
		if ($sticker_data) {
			$sort_order = array();
			
			foreach ($sticker_data as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}

			array_multisort($sort_order, SORT_ASC, $sticker_data);
			
			return $sticker_data;
		} else {
			return false;
		}
	}
}