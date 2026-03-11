<?php
class ControllerExtensionModuleGalleryrb extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/galleryrb');

		$this->document->setTitle($this->language->get('heading_title'));
    $this->document->addScript('view/javascript/jquery/colorpicker/js/bootstrap-colorpicker.min.js');
    $this->document->addStyle('view/javascript/jquery/colorpicker/css/bootstrap-colorpicker.min.css');
    
    //CKEditor
    $this->document->addScript('view/javascript/ckeditor/ckeditor.js');
    $this->document->addScript('view/javascript/ckeditor/ckeditor_init.js');
    //Jquery UI
    $this->document->addScript('view/javascript/jquery/jquery-ui/jquery-ui.min.js'); 

		$this->load->model('setting/module');
    $this->load->model('extension/module/galleryrb');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if (!isset($this->request->get['module_id'])) {
				$this->model_extension_module_galleryrb->addModule('galleryrb', $this->request->post);
			} else {
				$this->model_extension_module_galleryrb->editModule($this->request->get['module_id'], $this->request->post);
			}
      
			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		$data['heading_title'] = $this->language->get('heading_title');
    
    $data['ckeditor'] = $this->config->get('config_editor_default');
    
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}

		if (isset($this->error['width_thumb'])) {
			$data['error_width_thumb'] = $this->error['width_thumb'];
		} else {
			$data['error_width_thumb'] = '';
		}

		if (isset($this->error['height_thumb'])) {
			$data['error_height_thumb'] = $this->error['height_thumb'];
		} else {
			$data['error_height_thumb'] = '';
		}
    		
    if (isset($this->error['width_popup'])) {
			$data['error_width_popup'] = $this->error['width_popup'];
		} else {
			$data['error_width_popup'] = '';
		}

		if (isset($this->error['height_popup'])) {
			$data['error_height_popup'] = $this->error['height_popup'];
		} else {
			$data['error_height_popup'] = '';
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

		if (!isset($this->request->get['module_id'])) {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/galleryrb', 'user_token=' . $this->session->data['user_token'], true)
			);
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/galleryrb', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true)
			);
		}
    
		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/galleryrb', 'user_token=' . $this->session->data['user_token'], true);
		} else {
			$data['action'] = $this->url->link('extension/module/galleryrb', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
		}

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
		}

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($module_info)) {
			$data['name'] = $module_info['name'];
		} else {
			$data['name'] = '';
		}
    
    if (isset($this->request->get['module_id'])) {
			$data['shortcode'] = '[gallery_rb id=' . $this->request->get['module_id'] . ']';
		} else {
			$data['shortcode'] = '';
		}

		if (isset($this->request->post['thumb_width'])) {
			$data['thumb_width'] = $this->request->post['thumb_width'];
		} elseif (!empty($module_info)) {
			$data['thumb_width'] = $module_info['thumb_width'];
		} else {
			$data['thumb_width'] = '';
		}

		if (isset($this->request->post['thumb_height'])) {
			$data['thumb_height'] = $this->request->post['thumb_height'];
		} elseif (!empty($module_info)) {
			$data['thumb_height'] = $module_info['thumb_height'];
		} else {
			$data['thumb_height'] = '';
		}
    
    if (isset($this->request->post['popup_width'])) {
			$data['popup_width'] = $this->request->post['popup_width'];
		} elseif (!empty($module_info)) {
			$data['popup_width'] = $module_info['popup_width'];
		} else {
			$data['popup_width'] = '';
		}

		if (isset($this->request->post['popup_height'])) {
			$data['popup_height'] = $this->request->post['popup_height'];
		} elseif (!empty($module_info)) {
			$data['popup_height'] = $module_info['popup_height'];
		} else {
			$data['popup_height'] = '';
		}
    
    if (isset($this->request->post['col_lg'])) {
			$data['col_lg'] = $this->request->post['col_lg'];
		} elseif (!empty($module_info['col_lg'])) {
			$data['col_lg'] = $module_info['col_lg'];
		} else {
			$data['col_lg'] = '4';
		}
    
    if (isset($this->request->post['col_md'])) {
			$data['col_md'] = $this->request->post['col_md'];
		} elseif (!empty($module_info['col_md'])) {
			$data['col_md'] = $module_info['col_md'];
		} else {
			$data['col_md'] = '4';
		}
    
    if (isset($this->request->post['col_sm'])) {
			$data['col_sm'] = $this->request->post['col_sm'];
		} elseif (!empty($module_info['col_sm'])) {
			$data['col_sm'] = $module_info['col_sm'];
		} else {
			$data['col_sm'] = '6';
		}
    
    if (isset($this->request->post['col_xs'])) {
			$data['col_xs'] = $this->request->post['col_xs'];
		} elseif (!empty($module_info['col_xs'])) {
			$data['col_xs'] = $module_info['col_xs'];
		} else {
			$data['col_xs'] = '12';
		}
    
    if (isset($this->request->post['style'])) {
			$data['style'] = $this->request->post['style'];
		} elseif (!empty($module_info['style'])) {
			$data['style'] = $module_info['style'];
		} else {
			$data['style'] = 'grid';
		}
    
    // Select category
    
    $data['user_token'] = $this->session->data['user_token'];
    
    /*$this->load->model('catalog/category');
    
    if (isset($this->request->post['categories'])) {
			$data['categories'] = $this->request->post['categories'];
		} elseif (!empty($module_info['categories'])) {
			$data['categories'] = $module_info['categories'];
		} else {
			$data['categories'] = array();
		}
    $data['gallery_categories'] = array();
  
    foreach ($data['categories'] as $category_id) {
			$category_info = $this->model_catalog_category->getCategory($category_id);

			if ($category_info) {
				$data['gallery_categories'][] = array(
					'category_id' => $category_info['category_id'],
					'name'        => ($category_info['path']) ? $category_info['path'] . ' &gt; ' . $category_info['name'] : $category_info['name']
				);
			}
		}*/
    
    if (isset($this->request->post['animation'])) {
			$data['animation'] = $this->request->post['animation'];
		} elseif (!empty($module_info)) {
			$data['animation'] = $module_info['animation'];
		} else {
			$data['animation'] = 'scale';
		} 
    
    if (isset($this->request->post['borderimage'])) {
			$data['borderimage'] = $this->request->post['borderimage'];
		} elseif (!empty($module_info['borderimage'])) {
			$data['borderimage'] = $module_info['borderimage'];
		} else {
			$data['borderimage'] = '';
		} 

    if (isset($this->request->post['text'])) {
			$data['text'] = $this->request->post['text'];
		} elseif (!empty($module_info)) {
			$data['text'] = $module_info['text'];
		} else {
			$data['text'] = '0';
		}    
    
    if (isset($this->request->post['textbg'])) {
			$data['textbg'] = $this->request->post['textbg'];
		} elseif (!empty($module_info['textbg'])) {
			$data['textbg'] = $module_info['textbg'];
		} else {
			$data['textbg'] = 'rgba(255,255,255,0.51)';
		}
    
    if (isset($this->request->post['texthover'])) {
			$data['texthover'] = $this->request->post['texthover'];
		} elseif (!empty($module_info)) {
			$data['texthover'] = $module_info['texthover'];
		} else {
			$data['texthover'] = '1';
		}
    
    if (isset($this->request->post['title_gallery'])) {
			$data['title_gallery'] = $this->request->post['title_gallery'];
		} elseif (!empty($module_info['title_gallery'])) {
			$data['title_gallery'] = $module_info['title_gallery'];
		} else {
			$data['title_gallery'] = array();
		}
    
    if (isset($this->request->post['description'])) {
			$data['description'] = $this->request->post['description'];
		} elseif (!empty($module_info['description'])) {
			$data['description'] = $module_info['description'];
		} else {
			$data['description'] = array();
		}
    
    // Gallery image start
    
    $this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();
    $data['lang'] = $this->language->get('lang');
    
    $this->load->model('tool/image');

		if (isset($this->request->post['gallery_image'])) {
			$gallery_images = $this->request->post['gallery_image'];
		} elseif (!empty($module_info['gallery_image'])) {
			$gallery_images = $module_info['gallery_image'];
		} else {
			$gallery_images = array();
		}
    
    $data['gallery_images'] = array();

		foreach ($gallery_images as $key => $value) {
      foreach ($value as $gallery_image) {
        if (is_file(DIR_IMAGE . $gallery_image['image'])) {
          $image = $gallery_image['image'];
          $thumb = $gallery_image['image'];
        } else {
          $image = '';
          $thumb = 'no_image.png';
        }
        
        //Update 1.4 fix
        if (isset($gallery_image['link'])){
          $link = $gallery_image['link'];
        } else {
          $link = '';
        }
        
        //Update 2.1 fix
        if (isset($gallery_image['image_alt'])){
          $image_alt = $gallery_image['image_alt'];
        } else {
          $image_alt = '';
        }
  
        //Update 2.1 fix
        if (isset($gallery_image['image_title'])){
          $image_title = $gallery_image['image_title'];
        } else {
          $image_title = '';
        }

        $data['gallery_images'][$key][] = array(
          'gallery_image_description' => $gallery_image['gallery_image_description'],
          'image'                    => $image,
          'thumb'                    => $this->model_tool_image->resize($thumb, 100, 100),
          'link'                     => $link,
          'image_alt'                => $image_alt,
          'image_title'              => $image_title,
          'sort_order'               => $gallery_image['sort_order']
        );
      }
		}

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);
    
    // Gallery Image END
    
    if (isset($this->request->post['load_more_status'])) {
			$data['load_more_status'] = $this->request->post['load_more_status'];
		} elseif (!empty($module_info)) {
			$data['load_more_status'] = $module_info['load_more_status'];
		} else {
			$data['load_more_status'] = '';
		}
    
		if (isset($this->request->post['count_per_page'])) {
			$data['count_per_page'] = $this->request->post['count_per_page'];
		} elseif (!empty($module_info)) {
			$data['count_per_page'] = $module_info['count_per_page'];
		} else {
			$data['count_per_page'] = '10';
		}

    if (isset($this->request->post['main_language'])) {                                         
			$data['main_language'] = $this->request->post['main_language'];
		} elseif (!empty($module_info['main_language'])) {
			$data['main_language'] = $module_info['main_language'];
		} else {
			$data['main_language'] = '';
		}
    
		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($module_info)) {
			$data['status'] = $module_info['status'];
		} else {
			$data['status'] = '';
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/galleryrb', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/galleryrb')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		if (!$this->request->post['thumb_width']) {
			$this->error['width_thumb'] = $this->language->get('error_width_thumb');
		}

		if (!$this->request->post['thumb_height']) {
			$this->error['height_thumb'] = $this->language->get('error_height_thumb');
		}
    if (!$this->request->post['popup_width']) {
			$this->error['width_popup'] = $this->language->get('error_width_popup');
		}

		if (!$this->request->post['popup_height']) {
			$this->error['height_popup'] = $this->language->get('error_height_popup');
		}

		return !$this->error;
	}
}