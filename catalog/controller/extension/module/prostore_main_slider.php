<?php
class ControllerExtensionModuleProstoreMainSlider extends Controller {
	public function index($setting) {
		static $module = 0;

		$this->load->model('design/banner');
		$this->load->model('tool/image');

		$data['autoplay'] = $setting['autoplay'];
		$data['speed'] = $setting['speed'];
		$data['lazyload'] = $this->config->get('theme_prostore_lazyload');

		if (isset($setting['img_link'])) {
			$data['img_link'] = $setting['img_link'];
		} else {
			$data['img_link'] = '';
		}
		if (isset($setting['resizable'])) {
			$data['resizable'] = $setting['resizable'];
		} else {
			$data['resizable'] = '';
		}

		$data['slide_width'] = $setting['slide_width'];
		$data['slide_fade'] = $setting['slide_fade'];
		$data['banners'] = array();

		if(isset($setting['slider_image'])){
			$isRightImage = 0;
			$i = 0;
			foreach ($setting['slider_image'] as  $result) {

				if(!isset($result['language'][$this->config->get('config_language_id')])){ continue; }
				$result = $result['language'][$this->config->get('config_language_id')];
				if (is_file(DIR_IMAGE . $result['image'])) {

					if ($result['position'] == 2) {
						$isRightImage = 1;
					} else {
						$i++;
					}
					$fillWholeSlide = 0;
					if (isset($result['full'])) {
						$fillWholeSlide = $result['full'];
					}

					// Мобильная картинка: resize с сохранением пропорций оригинала
					$image2_resized = '';
					if (!empty($result['image2']) && is_file(DIR_IMAGE . $result['image2'])) {
						$img2_info = @getimagesize(DIR_IMAGE . $result['image2']);
						if ($img2_info && $img2_info[0] > 0) {
							$img2_w = 768;
							$img2_h = (int)round($img2_info[1] * ($img2_w / $img2_info[0]));
						} else {
							$img2_w = 768;
							$img2_h = 500;
						}
						$image2_resized = $this->model_tool_image->resize($result['image2'], $img2_w, $img2_h);
					}

					$data['banners'][$result['sort_order']][] = array(
						'title' => $result['title'],
						'link'  => $result['link'],
						'full'  => $fillWholeSlide,
						'position'  => $result['position'],
						'slider_text'  => html_entity_decode($result['slider_text'], ENT_QUOTES, 'UTF-8'),
						'btn_text'  => html_entity_decode($result['btn_text'], ENT_QUOTES, 'UTF-8'),
						'text_color'  => $result['text_color'],
						'width_pc'  => $result['width_pc'],
						'height_pc'  => $result['height_pc'],
						'image' => $this->model_tool_image->resize($result['image'], 1920, 800),
						'image2' => $image2_resized,
					);
				}
			}
		}

		ksort($data['banners']);

		$data['left_image_count'] = $i;
		$data['isrightimage'] = $isRightImage;
		if (!$isRightImage && !empty($data['banners']) ) {
			$firstBanner = current($data['banners']);
			$this->document->addLink($firstBanner[0]['image'], 'preload_as_image');
		}

		$data['module'] = $module++;

		return $this->load->view('extension/module/prostore_main_slider', $data);

	}
}
?>
