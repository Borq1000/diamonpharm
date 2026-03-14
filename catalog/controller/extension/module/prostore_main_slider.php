<?php
class ControllerExtensionModuleProstoreMainSlider extends Controller {

	// Конвертирует PNG-кеш в JPEG для экономии трафика (фото не нуждаются в прозрачности)
	private function resizeToJpeg($filename, $width, $height, $quality = 85) {
		$url = $this->model_tool_image->resize($filename, $width, $height);

		if (strtolower(pathinfo($filename, PATHINFO_EXTENSION)) !== 'png') {
			return $url;
		}

		$base = utf8_substr($filename, 0, utf8_strrpos($filename, '.'));
		$png_path = DIR_IMAGE . 'cache/' . $base . '-' . (int)$width . 'x' . (int)$height . '.png';
		$jpg_path = DIR_IMAGE . 'cache/' . $base . '-' . (int)$width . 'x' . (int)$height . '.jpg';

		// Нет PNG-кеша — пробуем с заглавным расширением
		if (!is_file($png_path)) {
			$png_path = DIR_IMAGE . 'cache/' . $base . '-' . (int)$width . 'x' . (int)$height . '.PNG';
		}

		if (!is_file($jpg_path) || (is_file($png_path) && filemtime($png_path) > filemtime($jpg_path))) {
			if (is_file($png_path)) {
				$img = @imagecreatefrompng($png_path);
				if ($img) {
					$bg = imagecreatetruecolor(imagesx($img), imagesy($img));
					imagefill($bg, 0, 0, imagecolorallocate($bg, 255, 255, 255));
					imagecopy($bg, $img, 0, 0, 0, 0, imagesx($img), imagesy($img));
					imagejpeg($bg, $jpg_path, $quality);
					imagedestroy($img);
					imagedestroy($bg);
				}
			}
		}

		if (is_file($jpg_path)) {
			return preg_replace('/\.png$/i', '.jpg', $url);
		}

		return $url;
	}

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
						$image2_resized = $this->resizeToJpeg($result['image2'], $img2_w, $img2_h);
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
						'image' => $this->resizeToJpeg($result['image'], 1920, 800),
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

		$html = $this->load->view('extension/module/prostore_main_slider', $data);

		// PHP-инжекция: CSS + атрибуты изображений (обходит кеш Twig)
		$inject = '<style>
@media (max-width: 767px) {
  .intro__item-wrapper {
    position: relative;
    min-height: 60vh;
    overflow: hidden;
  }
  .intro__item-cover,
  .intro__item-image {
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    width: 100% !important;
    height: auto !important;
    min-height: 100% !important;
  }
  .intro__item-cover img,
  .intro__item-image img {
    width: 100% !important;
    height: 100% !important;
    min-height: 60vh !important;
    object-fit: cover !important;
  }
}
</style>
<script>
document.addEventListener("DOMContentLoaded",function(){
  var s=document.querySelectorAll(".intro__swiper .swiper-slide:not(.swiper-slide-duplicate) picture img");
  if(s.length){s[0].setAttribute("fetchpriority","high");s[0].removeAttribute("loading");}
  for(var i=1;i<s.length;i++){s[i].setAttribute("loading","lazy");}
});
</script>';

		return $inject . $html;

	}
}
?>
