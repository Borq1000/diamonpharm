<?php
class ModelToolGalleryimage extends Model { 
	public function resize($filename, $width, $height, $watermark = false, $crop = '') {

    $gd = gd_info();
    
		if (!is_file(DIR_IMAGE . $filename)) {
			if (is_file(DIR_IMAGE . 'no_image.jpg')) {
				$filename = 'no_image.jpg';
			} elseif (is_file(DIR_IMAGE . 'no_image.png')) {
				$filename = 'no_image.png';
			} else {
				return;
			}
		}

		$extension = pathinfo($filename, PATHINFO_EXTENSION);
    $image_old = $filename;
    $image_new_webp = 'cachewebp/gallery_rb/' . utf8_substr($filename, 0, utf8_strrpos($filename, '.')) . '-' . (int)$width . 'x' . (int)$height . $crop . '.webp';
		$image_new = 'cache/' . utf8_substr($filename, 0, utf8_strrpos($filename, '.')) . '-' . $width . 'x' . $height . $crop . '.' . $extension;
    $need_crop = false;
    
    if ($gd['WebP Support']) {
      if (!is_file(DIR_IMAGE . $image_new_webp) || (filectime(DIR_IMAGE . $image_old) > filectime(DIR_IMAGE . $image_new_webp))) {
        $need_crop = true;
      }
    } else {
      if (!is_file(DIR_IMAGE . $image_new) || (filectime(DIR_IMAGE . $image_old) > filectime(DIR_IMAGE . $image_new))) {
        $need_crop = true;
      }
    }
    
    if($need_crop){
      list($width_orig, $height_orig) = getimagesize(DIR_IMAGE . $image_old);
        
        $prop_orig = $width_orig / $height_orig;
        $prop_new = $width / $height; 
        
        if($crop == 'HA'){
          $height = $height_orig / $width_orig * $width;
        } else {
          if ($prop_orig > $prop_new) {
            $bottom_x = $height_orig * $prop_new;
            $bottom_y = $height_orig;
            $top_x = ($width_orig - $bottom_x) / 2;
            $top_y = 0;
          } else {
            $bottom_x = $width_orig;
            $bottom_y = $width_orig / $prop_new;
            $top_x = 0;
            $top_y = ($height_orig - $bottom_y) / 2;
          }
          //$image->crop($top_x, $top_y, $bottom_x + $top_x, $bottom_y + $top_y);
        }
    }
    if ($gd['WebP Support']) {
      // WebP
      
      if (!is_file(DIR_IMAGE . $image_new_webp) || (filectime(DIR_IMAGE . $image_old) > filectime(DIR_IMAGE . $image_new_webp))) {
                      
            $path = '';

            $directories = explode('/', dirname($image_new_webp));

            foreach ($directories as $directory) {
              $path = $path . '/' . $directory;

              if (!is_dir(DIR_IMAGE . $path)) {
                @mkdir(DIR_IMAGE . $path, 0777);
              }
            }
            $image_webp = new Image(DIR_IMAGE . $image_old);
        
        if($crop != 'HA'){
          $image_webp->crop($top_x, $top_y, $bottom_x + $top_x, $bottom_y + $top_y);
        }
        
        
        
        $image_webp->resize($width, $height);
        $image_webp->save(DIR_IMAGE . $image_new_webp);
      }
      
      $imagepath_parts = explode('/', $image_new_webp);
      $image_new_webp = str_replace(' ', '%20', $image_new_webp);  // fix bug when attach image on email (gmail.com). it is automatic changing space " " to +
      $image_new_webp = implode('/', array_map('rawurlencode', $imagepath_parts));
      
      $gd = gd_info();
      if ($this->request->server['HTTPS']) {
        return $this->config->get('config_ssl') . 'image/' . $image_new_webp;
      } else {
        return $this->config->get('config_url') . 'image/' . $image_new_webp;
      }
    } else {


		


		if (!is_file(DIR_IMAGE . $image_new) || (filectime(DIR_IMAGE . $image_old) > filectime(DIR_IMAGE . $image_new))) {
			$path = '';

			$directories = explode('/', dirname(str_replace('../', '', $image_new)));

			foreach ($directories as $directory) {
				$path = $path . '/' . $directory;

				if (!is_dir(DIR_IMAGE . $path)) {
					@mkdir(DIR_IMAGE . $path, 0777);
				}
			}

        $image = new Image(DIR_IMAGE . $image_old);
        
        if($crop != 'HA'){
          $image->crop($top_x, $top_y, $bottom_x + $top_x, $bottom_y + $top_y);
        }

        $image->resize($width, $height);
        if ($watermark) {
          $image->watermark(DIR_IMAGE . 'watermark.png', 'custom');        
        }
				$image->save(DIR_IMAGE . $image_new);
        
        
      }
      $imagepath_parts = explode('/', $image_new);
      $image_new = str_replace(' ', '%20', $image_new);  // fix bug when attach image on email (gmail.com). it is automatic changing space " " to +
      $image_new = implode('/', array_map('rawurlencode', $imagepath_parts));
      
      $gd = gd_info();
      if ($this->request->server['HTTPS']) {
        return $this->config->get('config_ssl') . 'image/' . $image_new;
      } else {
        return $this->config->get('config_url') . 'image/' . $image_new;
      }
    }
  }
}
