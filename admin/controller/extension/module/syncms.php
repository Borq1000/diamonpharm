<?php

class ControllerExtensionModuleSyncMS extends Controller {

  private $version;

  public function index() {

    $this->SetVersion();
    $token = $this->GetTokenName();

    if ($this->version == "2.1")
      $path = "module";
    else
      $path = "extension/module";

    if ($this->version == "2.1" || $this->version == "2.3") {
      $this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE `key` = 'syncms_user_token'");
      $this->db->query("INSERT INTO " . DB_PREFIX . "setting (`code`, `key`, `value`) VALUES ('module_syncms', 'syncms_user_token', '{$this->session->data["$token"]}')");
    } else {
      $this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE `key` = 'module_syncms_user_token'");
      $this->db->query("INSERT INTO " . DB_PREFIX . "setting (`code`, `key`, `value`) VALUES ('module_syncms', 'module_syncms_user_token', '{$this->session->data["$token"]}')");
    }

    $this->load->model('setting/setting');

    if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
      if ($this->version == "2.1" || $this->version == "2.3") {
       $this->model_setting_setting->editSetting('syncms', $this->request->post); 
      } else {
        $this->model_setting_setting->editSetting('module_syncms', $this->request->post); 
      }

      $this->session->data['success'] = 'Настройки сохранены';
      
      $this->response->redirect($this->url->link("{$path}/syncms", $token . '=' . $this->session->data[$token], true));
    }

    $data = array();

    $data['path'] = $path;
    $data['admin_token'] = $this->session->data[$token];

    if (!isset($this->session->data['success'])) {
      $this->session->data['success'] = $this->ConfigGet("success");
    }

    if (isset($this->session->data['success'])) {
      $data['success'] = $this->session->data['success'];
      unset($this->session->data['success']);
      if ($this->version == "2.1" || $this->version == "2.3") {
        $this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE `key` = 'syncms_success'");
      } else {
        $this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE `key` = 'module_syncms_success'");
      }
    } else {
      $data['success'] = "";
    }
    
    if (isset($this->error['warning'])) {
      $data['error_warning'] = $this->error['warning'];
    } else {
      $data['error_warning'] = '';
    }

    if (!isset($this->session->data['error_warning'])) {
      $this->session->data['error_warning'] = $this->ConfigGet("error_warning");
    }

    if (isset($this->session->data['error_warning'])) {
      $data['error_warning'] = $this->session->data['error_warning'];
      unset($this->session->data['error_warning']);
      if ($this->version == "2.1" || $this->version == "2.3") {
        $this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE `key` = 'syncms_error_warning'");
      } else {
        $this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE `key` = 'module_syncms_error_warning'");
      }
    } else {
      $data['error_warning'] = "";
    }

    if ($this->ConfigGet('token') != '') {
      $headers = array("Authorization:Bearer " . $this->ConfigGet('token'), "Content-Type: application/json");
    } else {
      $headers = array("Authorization:Basic " . base64_encode($this->ConfigGet('login') . ':' . $this->ConfigGet('password')), "Content-Type: application/json");
    } 

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_ENCODING , "gzip ''");

    // Типы цен
    $url = 'https://api.moysklad.ru/api/remap/1.2/context/companysettings/pricetype';
    curl_setopt($ch, CURLOPT_URL, $url);
    $response = json_decode(curl_exec($ch), true);
    $response = $this->CheckResponse($response, $ch);
    $data['sale_prices'] = array();
    $notAuth = "Авторизация не выполнена";
    if (!empty($response) && !isset($response['errors'])) {
      foreach ($response as $key => $value) {
        $data['sale_prices'][] = $value['name'];
      }
    } else {
      $data['sale_prices'][] = $notAuth;
    }
    $data['sale_price_key'] = $this->ConfigGet('sale_price');

    // Склады
    curl_setopt($ch, CURLOPT_URL, 'https://api.moysklad.ru/api/remap/1.2/entity/store');
    $response = json_decode(curl_exec($ch), true);
    $response = $this->CheckResponse($response, $ch);
    $data['store'] = array();
    $notAuth = "Авторизация не выполнена";
    if (!empty($response) && !isset($response['errors'])) {
      foreach ($response['rows'] as $key => $value) {
        $data['store'][$value['meta']['href']] = $value['name'];
        $data['stock_store'][$value['meta']['href']] = $value['name'];
      }
    } else {
      $data['store'][] = $notAuth;
      $data['stock_store'][] = $notAuth;
    }
    $data['store_key'] = $this->ConfigGet('store');
    $data['stock_store_key'] = $this->ConfigGet('stock_store');

    // Организации
    curl_setopt($ch, CURLOPT_URL, 'https://api.moysklad.ru/api/remap/1.2/entity/organization');
    $response = json_decode(curl_exec($ch), true);
    $response = $this->CheckResponse($response, $ch);
    $data['organization'] = array();
    $notAuth = "Авторизация не выполнена";
    if (!empty($response) && !isset($response['errors'])) {
      foreach ($response['rows'] as $key => $value) {
        $data['organization'][$value['meta']['href']] = $value['name'];
      }
    } else {
      $data['organization'][] = $notAuth;
    }
    $data['organization_key'] = $this->ConfigGet('organization');

    // Каналы продаж
    curl_setopt($ch, CURLOPT_URL, 'https://api.moysklad.ru/api/remap/1.2/entity/saleschannel');
    $response = json_decode(curl_exec($ch), true);
    $response = $this->CheckResponse($response, $ch);
    $data['saleschannel'] = array();
    $notAuth = "Авторизация не выполнена";
    if (!empty($response) && !isset($response['errors'])) {
      $data['saleschannel'][0] = "--- Не указывать ---";
      foreach ($response['rows'] as $key => $value) {
        $data['saleschannel'][$value['meta']['href']] = $value['name'];
      }
    } else {
      $data['saleschannel'][] = $notAuth;
    }
    $data['saleschannel_key'] = $this->ConfigGet('saleschannel');

    // Product offset
    $url = 'https://api.moysklad.ru/api/remap/1.2/entity/assortment?filter=type=product;type=bundle&limit=0';
    curl_setopt($ch, CURLOPT_URL, $url);

    $response = json_decode(curl_exec($ch), true);
    $response = $this->CheckResponse($response, $ch);

    $data['product_offset'] = array();
    $notAuth = "Авторизация не выполнена";
    $numb = 1;
    
    if (!empty($response) && !isset($response['errors'])) {
      $data['product_offset'][0] = "Все товары";
      $size = $response['meta']['size'];
      for ($i = 0; $i < floor($size / 1000); $i++) { 
        $data['product_offset'][$numb] = sprintf("%s - %s", $numb, $numb + 999);
        $numb += 1000;
      }
      $data['product_offset'][$numb] = sprintf("%s - %s", $numb, $size);
      
    } else {
      $data['product_offset'][] = $notAuth;
    }
    $data['product_offset_key'] = $this->ConfigGet('product_offset');

    $languageID = $this->config->get("config_language_id");
    
    // Product SQL offset
    $query = $this->db->query("SELECT COUNT(product_id) FROM " . DB_PREFIX . "product INNER JOIN " . DB_PREFIX . "product_description USING (product_id) WHERE language_id = '{$languageID}'");
    $data['product_sql_offset'] = array();
    $numb = 1;
    
    $data['product_sql_offset'][0] = "Все товары";
    $size = $query->row['COUNT(product_id)'];
    for ($i = 0; $i < floor($size / 100); $i++) { 
      $data['product_sql_offset'][$numb] = sprintf("%s - %s", $numb, $numb + 99);
      $numb += 100;
    }
    $data['product_sql_offset'][$numb] = sprintf("%s - %s", $numb, $size);
      
    $data['product_sql_offset_key'] = $this->ConfigGet('product_sql_offset');

    // Category offset
    $url = 'https://api.moysklad.ru/api/remap/1.2/entity/productfolder?limit=0';
    curl_setopt($ch, CURLOPT_URL, $url);

    $response = json_decode(curl_exec($ch), true);
    $response = $this->CheckResponse($response, $ch);

    $data['cat_offset'] = array();
    $notAuth = "Авторизация не выполнена";
    $numb = 1;
    
    if (!empty($response) && !isset($response['errors'])) {
      $data['cat_offset'][0] = "Все категории";
      $size = $response['meta']['size'];
      for ($i = 0; $i < floor($size / 1000); $i++) { 
        $data['cat_offset'][$numb] = sprintf("%s - %s", $numb, $numb + 999);
        $numb += 1000;
      }
      $data['cat_offset'][$numb] = sprintf("%s - %s", $numb, $size);
      
    } else {
      $data['cat_offset'][] = $notAuth;
    }
    $data['cat_offset_key'] = $this->ConfigGet('cat_offset');

    curl_close($ch);

    // Stock Status
    $query = $this->db->query("SELECT name, stock_status_id FROM " . DB_PREFIX . "stock_status WHERE language_id = $languageID");
    foreach ($query->rows as $key => $value) {
      $data['stock_status'][$value['stock_status_id']] = $value['name'];
    }
    $data['stock_status_key'] = $this->ConfigGet('stock_status');
    
    // Wight Unity
    $query = $this->db->query("SELECT title, weight_class_id FROM " . DB_PREFIX . "weight_class_description WHERE language_id = $languageID");
    foreach ($query->rows as $key => $value) {
      $data['weight_unity'][$value['weight_class_id']] = $value['title'];
    }
    $data['weight_unity_key'] = $this->ConfigGet('weight_unity');

    // Основные настройки
    $data['status'] = $this->ConfigGet('status');
    $data['login'] = $this->ConfigGet('login');
    $data['password'] = $this->ConfigGet('password');
    $data['token'] = $this->ConfigGet('token');

    // Лишние товары
    $data['absence_products'] = $this->ConfigGet('absence_products');

    // Добавление/обновление категорий
    $data['category_binding'] = $this->ConfigGet('category_binding');
    $data['cat_url_update'] = $this->ConfigGet('cat_url_update');
    $data['category_time_limit'] = $this->ConfigGet('category_time_limit');

    // Добавление/обновление товаров
    $data['binding'] = $this->ConfigGet('binding');
    $data['binding_name'] = $this->ConfigGet('binding_name');
    $data['desc_update'] = $this->ConfigGet('desc_update'); 
    $data['name_update'] = $this->ConfigGet('name_update');
    $data['url_update'] = $this->ConfigGet('url_update');
    $data['cat_update'] = $this->ConfigGet('cat_update');
    $data['sku_update'] = $this->ConfigGet('sku_update');
    $data['weight_update'] = $this->ConfigGet('weight_update');
    $data['manufacturer_update'] = $this->ConfigGet('manufacturer_update');
    $data['stock_status_update'] = $this->ConfigGet('stock_status_update');
    $data['manufacturer'] = $this->ConfigGet('manufacturer');
    $data['from_group'] = $this->ConfigGet('from_group');
    $data['not_from_group'] = $this->ConfigGet('not_from_group');
    $data['prod_parent_cat'] = $this->ConfigGet('prod_parent_cat');
    $data['subtract'] = $this->ConfigGet('subtract');
    $data['subtract_update'] = $this->ConfigGet('subtract_update');
    $data['ean_update'] = $this->ConfigGet('ean_update');
    $data['supplier'] = $this->ConfigGet('supplier');
    $data['uom'] = $this->ConfigGet('uom');
    $data['client_price'] = $this->ConfigGet('client_price');
    $data['special_price'] = $this->ConfigGet('special_price');
    $data['empty_field'] = $this->ConfigGet('empty_field');
    $data['add_out_of_stock'] = $this->ConfigGet('add_out_of_stock');
    $data['certain_products'] = $this->ConfigGet('certain_products');
    $data['product_time_limit'] = $this->ConfigGet('product_time_limit');

    // Добавление товаров в Мой Склад
    $data['desc_update_ms'] = $this->ConfigGet('desc_update_ms'); 
    $data['weight_update_ms'] = $this->ConfigGet('weight_update_ms');
    $data['unit_update_ms'] = $this->ConfigGet('unit_update_ms');
    $data['article_update_ms'] = $this->ConfigGet('article_update_ms');
    $data['cat_update_ms'] = $this->ConfigGet('cat_update_ms');
    $data['stock_update_ms'] = $this->ConfigGet('stock_update_ms');
    $data['weight_update_ms'] = $this->ConfigGet('weight_update_ms');
    $data['manufacturer_update_ms'] = $this->ConfigGet('manufacturer_update_ms');
    $data['image_update_ms'] = $this->ConfigGet('image_update_ms');
    $data['modif_update_ms'] = $this->ConfigGet('modif_update_ms');
    $data['from_group_sql'] = $this->ConfigGet('from_group_sql');

    // Синхронизация заказов
    $data['order_prefix'] = $this->ConfigGet('order_prefix');
    $data['order_binding'] = $this->ConfigGet('order_binding');
    $data['shipping_binding'] = $this->ConfigGet('shipping_binding');
    $data['product_reserve'] = $this->ConfigGet('product_reserve');
    $data['shipping_add'] = $this->ConfigGet('shipping_add');
    $data['conduct_order'] = $this->ConfigGet('conduct_order');
    $data['quick_add'] = $this->ConfigGet('quick_add');
    $data['comment_info'] = $this->ConfigGet('comment_info');
    $data['not_skip_order'] = $this->ConfigGet('not_skip_order');
    $data['order_day_limit'] = $this->ConfigGet('order_day_limit');
    $data['nds'] = $this->ConfigGet('nds');
    $data['default_email'] = $this->ConfigGet('default_email');
    $data['agent_type'] = $this->ConfigGet('agent_type');
    $data['two_side_status'] = $this->ConfigGet('two_side_status');
    $data['image_size_ms'] = $this->ConfigGet('image_size_ms');
    
    // Лог
    $filename = $_SERVER['DOCUMENT_ROOT'] . "/catalog/controller/{$path}/syncms_log.txt";
    if (file_exists($filename)) {
      $size = filesize($filename);
      $length = 1*1024*1024;
      
      $data['log'] = file_get_contents($filename, false, null, 0, $length);
    }

    // Синхронизация изображений
    $data['delete_img'] = $this->ConfigGet('delete_img');

    // Синхронизация опций
    $data['sum_option'] = $this->ConfigGet('sum_option');
    $data['diff_option_price'] = $this->ConfigGet('diff_option_price');      
    $data['attr_from_fields'] = $this->ConfigGet('attr_from_fields');
    $data['attr_group'] = $this->ConfigGet('attr_group');
    $data['subtract_option'] = $this->ConfigGet('subtract_option');
    $data['delete_option'] = $this->ConfigGet('delete_option');
    $data['delete_attribute'] = $this->ConfigGet('delete_attribute');
    
    // Мета товаров
    $data['meta_prod_update'] = $this->ConfigGet('meta_prod_update');
    $data['prod_meta_title'] = $this->ConfigGet('prod_meta_title');
    $data['prod_meta_desc'] = $this->ConfigGet('prod_meta_desc');
    $data['prod_meta_keyword'] = $this->ConfigGet('prod_meta_keyword');

    // Мета категорий
    $data['meta_cat_update'] = $this->ConfigGet('meta_cat_update');
    $data['cat_meta_title'] = $this->ConfigGet('cat_meta_title');
    $data['cat_meta_desc'] = $this->ConfigGet('cat_meta_desc');
    $data['cat_meta_keyword'] = $this->ConfigGet('cat_meta_keyword');

    $data += $this->load->language("{$path}/syncms");
    $this->document->setTitle($this->language->get('heading_title'));

    $data += $this->GetBreadCrumbs();

    $data['action'] = $this->url->link("{$path}/syncms", $token . '=' . $this->session->data[$token], true);

    if ($this->version == '2.1') {
      $data['cancel'] = $this->url->link('extension/module', $token . '=' . $this->session->data[$token] . '&type=module', true);
    } elseif ($this->version == '2.3') {
      $data['cancel'] = $this->url->link('extension/extension', $token . '=' . $this->session->data[$token] . '&type=module', true);
    } else {
      $data['cancel'] = $this->url->link('marketplace/extension', $token . '=' . $this->session->data[$token] . '&type=module', true);
    }

    // Ссылки
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== '') {
      $url = HTTPS_CATALOG . "index.php?route={$path}/syncms/";
      $data['admin_url'] = HTTPS_SERVER;
    } else {
      $url = HTTP_CATALOG . "index.php?route={$path}/syncms/";
      $data['admin_url'] = HTTP_SERVER;
    }

    $data['product_add_href'] =  $url . 'ProductAdd';
    $data['product_add_ms_href'] =  $url . 'ProductAddMS';
    $data['category_add_ms_href'] =  $url . 'CategoryAddMS';
    $data['stock_update_href'] = $url . 'StockUpdate';
    $data['price_update_href'] = $url . 'PriceUpdate';
    $data['product_update_href'] = $url . 'ProductUpdate';
    $data['category_update_href'] = $url . 'CategoryUpdate';
    $data['image_sync_href'] = $url . 'SyncImage';
    $data['modification_sync_href'] = $url . 'SyncModification';
    $data['category_add_href'] = $url . 'CategoryAdd';
    $data['absence_category_href'] = $url . 'SyncAbsenceCategory';
    $data['absence_product_href'] = $url . 'SyncAbsenceProducts';
    $data['order_add_href'] = $url . 'OrderAdd';
    $data['order_update_href'] = $url . 'OrderUpdate';
    $data['order_update_oc_href'] = $url . 'OrderUpdateOC';
    $data['log_clear_href'] = $url . 'LogClear';

    $data['cron_product_add'] = "/usr/bin/wget -O - '" . $url . 'ProductAdd&cron=true' . "'";
    $data['cron_product_add_ms'] = "/usr/bin/wget -O - '" . $url . 'ProductAddMS&cron=true' . "'";
    $data['cron_category_add_ms'] = "/usr/bin/wget -O - '" . $url . 'CategoryAddMS&cron=true' . "'";
    $data['cron_stock_update'] = "/usr/bin/wget -O - '" . $url . 'StockUpdate&cron=true' . "'";
    $data['cron_price_update'] = "/usr/bin/wget -O - '" . $url . 'PriceUpdate&cron=true' . "'";
    $data['cron_product_update'] = "/usr/bin/wget -O - '" . $url . 'ProductUpdate&cron=true' . "'";
    $data['cron_category_update'] = "/usr/bin/wget -O - '" . $url . 'CategoryUpdate&cron=true' . "'";
    $data['cron_image_sync'] = "/usr/bin/wget -O - '" . $url . 'SyncImage&cron=true' . "'";
    $data['cron_modification_sync'] = "/usr/bin/wget -O - '" . $url . 'SyncModification&cron=true' . "'";
    $data['cron_category_add'] = "/usr/bin/wget -O - '" . $url . 'CategoryAdd&cron=true' . "'";
    $data['cron_sync_absence_category'] = "/usr/bin/wget -O - '" . $url . 'SyncAbsenceCategory&cron=true' . "'";
    $data['cron_sync_absence_products'] = "/usr/bin/wget -O - '" . $url . 'SyncAbsenceProducts&cron=true' . "'";
    $data['cron_order_add'] = "/usr/bin/wget -O - '" . $url . 'OrderAdd&cron=true' . "'";
    $data['cron_order_update'] = "/usr/bin/wget -O - '" . $url . 'OrderUpdate&cron=true' . "'";
    $data['cron_order_update_oc'] = "/usr/bin/wget -O - '" . $url . 'OrderUpdateOC&cron=true' . "'";

    $data['header'] = $this->load->controller('common/header');
    $data['column_left'] = $this->load->controller('common/column_left');
    $data['footer'] = $this->load->controller('common/footer');

    if ($this->version == '2.1')
      $this->response->setOutput($this->load->view("{$path}/syncms.tpl", $data));
    else
      $this->response->setOutput($this->load->view("{$path}/syncms", $data));
  }


  private function GetBreadCrumbs() {
    if ($this->version == "2.1")
      $path = "module";
    else
      $path = "extension/module";
    
    $token = $this->GetTokenName();

    $data = array(); $data['breadcrumbs'] = array();
    $data['breadcrumbs'][] = array(
      'text' => $this->language->get('text_home'),
      'href' => $this->url->link('common/dashboard', $token . '=' . $this->session->data[$token], true));
    if ($this->version == '2.1') {
      $data['breadcrumbs'][] = array(
      'text' => $this->language->get('text_extension'),
      'href' => $this->url->link('extension/module', $token . '=' . $this->session->data[$token] . '&type=module', true));
    } elseif ($this->version == '2.3') {
      $data['breadcrumbs'][] = array(
      'text' => $this->language->get('text_extension'),
      'href' => $this->url->link('extension/extension', $token . '=' . $this->session->data[$token] . '&type=module', true));
    } else {
      $data['breadcrumbs'][] = array(
      'text' => $this->language->get('text_extension'),
      'href' => $this->url->link('marketplace/extension', $token . '=' . $this->session->data[$token] . '&type=module', true));
    }
    
    $data['breadcrumbs'][] = array(
      'text' => $this->language->get('heading_title'),
      'href' => $this->url->link("{$path}/syncms", $token . '=' . $this->session->data[$token], true));
    return $data;
  }

  private function SetVersion() {
    if (preg_match('/^2.1.*/', VERSION)) {
      $this->version = "2.1";
    } elseif (preg_match('/^2.3.*/', VERSION)) {
      $this->version = "2.3";
    } else {
      $this->version = "3";
    }
  }

  private function GetTokenName() {
    if ($this->version == '2.1' || $this->version == '2.3') {
      $token = "token";
    } else {
      $token = "user_token";
    }

    return $token;
  }

  private function ConfigGet($value) {
    if ($this->version == "2.1" || $this->version == '2.3') {
      return $this->config->get("syncms_" . $value);
    } else {
      return $this->config->get("module_syncms_" . $value);
    }
  }

  public function UpdateOffset()
  {
    if (isset($this->request->get['key']) && isset($this->request->get['value'])) {
      $this->db->query("UPDATE " . DB_PREFIX . "setting SET value = '{$this->request->get['value']}' WHERE `key` = '{$this->request->get['key']}'");
    }
  }

  private function CheckResponse($response, $ch)
  {
    //Обработка ошибок curl
    if (gettype($response) == "array") {
      // Массив
      if (isset($response['errors'])) {
        if ($response['errors'][0]['code'] == '1073' || $response['errors'][0]['code'] == '1049') {
          while (isset($response['errors'])) {
            sleep(3);
            $response = json_decode(curl_exec($ch), true);
          }
          return $response;
        }
      }
    }

    return $response;
  }
}