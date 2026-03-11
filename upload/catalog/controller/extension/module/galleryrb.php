<?php
class ControllerExtensionModuleGalleryrb extends Controller {
	public function index($setting) {
		static $module = 0;

		$this->load->language('extension/module/galleryrb');

		$this->document->addScript('catalog/view/javascript/jquery/magnific/jquery.magnific-popup.min.js');
	  $this->document->addStyle('catalog/view/javascript/jquery/magnific/magnific-popup.css');
    $this->document->addStyle('catalog/view/javascript/jquery/magnific/animation-magnific-popup.css');
    $this->document->addStyle('catalog/view/javascript/jquery/rb-gallery/rb-gallery.css');
    
    if($setting['style'] == 'carousel'){
      $this->document->addScript('catalog/view/javascript/jquery/rb-gallery/rb.owl.carousel.min.js');
    }
    if($setting['style'] == 'masonry'){
      $this->document->addScript('catalog/view/javascript/jquery/rb-gallery/masonry.min.js');
    }

    $data['title'] = (isset($setting['title'])) ? $setting['title'] : $setting['title_gallery'][$this->config->get('config_language_id')];
    $data['description'] = (isset($setting['description'])) ? htmlspecialchars_decode($setting['description'][$this->config->get('config_language_id')],ENT_QUOTES) : '';
    $data['col_lg'] = $setting['col_lg'];
    $data['col_md'] = $setting['col_md'];
    $data['col_sm'] = $setting['col_sm'];
    $data['col_xs'] = $setting['col_xs'];
    $data['style'] = $setting['style'];
    $data['animation'] = $setting['animation'];
    $data['text'] = $setting['text'];
    $data['texthover'] = $setting['texthover'];
    $data['textbg'] = $setting['textbg'];
    if(isset($setting['borderimage'])) $data['borderimage'] = $setting['borderimage']; 
    
    if(isset($setting['main_language'])){
      if($setting['main_language']){
        $language_id = $setting['main_language'];
      }else{
        $language_id = $this->config->get('config_language_id');
      }
    }
    
    // Sort Order for gallery
    if(isset($setting['gallery_image'][$language_id])){
      $results = $setting['gallery_image'][$language_id];
      usort($results, function($a, $b){
        if($a['sort_order'] === $b['sort_order'])
          return 0;  
        return $a['sort_order'] > $b['sort_order'] ? 1 : -1;
      });
    } else {
      $results = array();
    }
    
    /*** For Load More ********/
    $data['gallery_length'] = count($results);
    $data['load_more_status'] = $setting['load_more_status'];
    $data['count_per_page'] = $setting['count_per_page'];
    
    if(isset($setting['module_id'])) {
      $data['module_id'] = $setting['module_id'];
    } elseif(isset($setting['code'])) {
      $data['module_id'] = $setting['code'];
    } else {
      $data['module_id'] = 0;
    }
    $data['text_load_more'] = $this->language->get('text_load_more'); 
    /*** END For Load More ********/
    /*if (isset($this->request->get['path']) && isset($setting['categories'])) {
      $parts = explode('_', (string)$this->request->get['path']);
      $category_id = (int)array_pop($parts);    
      if(!(in_array($category_id, $setting['categories']))) {
        $results = array();
      }
    }*/
    
    $count_per_page = $setting['count_per_page'];
    
    if($setting['load_more_status']){
      if($setting['count_per_page'] < 1){
        $count_per_page = 1;
      }
      $results = array_slice($results, 0, $count_per_page);
    }
    
    if(count($results) < $count_per_page){
      $data['load_more_status'] = false;
    }

    $data['galleries'] = $this->loadImages($results, $setting['style'], $setting['thumb_width'], $setting['thumb_height'], $setting['popup_width']); 
    
    $data['module'] = $module++;
    return $this->load->view('extension/module/galleryrb', $data);
	}
  
  public function loadMore(){

    $this->load->model('setting/module');
    
    $module_id = $this->request->post['module_id'];
    $offset = $this->request->post['offset'];
    $count_per_page = $this->request->post['count_per_page'];
    
    $setting = $this->model_setting_module->getModule($module_id);
    
    if(isset($setting['main_language'])){
      if($setting['main_language']){
        $language_id = $setting['main_language'];
      }else{
        $language_id = $this->config->get('config_language_id');
      }
    }

    $results = $setting['gallery_image'][$language_id];
    
    usort($results, function($a, $b){
      if($a['sort_order'] === $b['sort_order'])
        return 0;  
      return $a['sort_order'] > $b['sort_order'] ? 1 : -1;
    });
      
    $results = array_slice($results, $offset, $count_per_page);

    $data['galleries'] = $this->loadImages($results, $setting['style'], $setting['thumb_width'], $setting['thumb_height'], $setting['popup_width']); 
    
    if($data['galleries']){
      $json['success'] = $data['galleries'];
    }else{
      $json['success'] = array();
    }
    
    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));
  }
  
  protected function loadImages($results, $style, $thumb_width, $thumb_height, $setting_popup_width){
    $this->load->model('tool/image');
    $this->load->model('tool/galleryimage');
    
    $galleries = array();
    
    foreach ($results as $result) {
      if (is_file(DIR_IMAGE . $result['image'])) {
        $file_image = getimagesize(DIR_IMAGE . $result['image']);       
        // Popup image resize
        
        $popup_width = $file_image[0];
        $popup_height = $file_image[1];
                      
        $scale = $setting_popup_width / $popup_width;
        $new_popup_height = $popup_height * $scale;
        
        if($style == 'masonry'){
          $thumb = $this->model_tool_galleryimage->resize($result['image'], $thumb_width, $thumb_height, false, 'HA');
        } else {
          $thumb = $this->model_tool_galleryimage->resize($result['image'], $thumb_width, $thumb_height);
        }
        
        if($result['link']){
          $link = $result['link'];
          
          $link_parse = parse_url($link);
          
          $allowed_video_host = array(
            'youtube.com',
            'youtu.be',
            'vimeo.com',
            'rutube.ru',
            'vk.com'
          );

          if(isset($link_parse['host']) && in_array($link_parse['host'], $allowed_video_host)){
            
            $class = 'rb-gallery-popup video-link';
            
            if ($link_parse['host'] == 'rutube.ru') {
              $link = str_replace($link_parse['host'] . '/video', $link_parse['host'] . '/play/embed', $link);
            }
            
            if ($link_parse['host'] == 'vk.com') {
              $link = preg_replace('/\/video-([0-9]{1,})_([0-9]{1,})/', '/video_ext.php?oid=-$1&id=$2&hd=2&autoplay=1', $link);
            }
          }else{
            $class = '';
          }
        } else {
          $link = $this->model_tool_galleryimage->resize($result['image'], $setting_popup_width, $new_popup_height);
          $class = 'rb-gallery-popup';
        }
        
        $galleries[] = array(
          'description' => htmlspecialchars_decode($result['gallery_image_description'],ENT_QUOTES),
          'link'        => $link,
          'alt'         => $result['image_alt'],
          'title'       => $result['image_title'],
          'class'       => $class,
          'thumb'       => $thumb
        );
      }
    }
    return $galleries;
  }
  
}