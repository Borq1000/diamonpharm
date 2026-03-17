<?php
class ModelToolImage extends Model {
	public function resize($filename, $width, $height) {
		if (!is_file(DIR_IMAGE . $filename) || substr(str_replace('\\', '/', realpath(DIR_IMAGE . $filename)), 0, strlen(DIR_IMAGE)) != str_replace('\\', '/', DIR_IMAGE)) {
			return;
		}

		$extension = pathinfo($filename, PATHINFO_EXTENSION);

		// PNG→JPEG: конвертировать фото товаров (>200px) для экономии трафика
		$convert_to_jpeg = false;
		if (strtolower($extension) === 'png' && (int)$width > 200) {
			$convert_to_jpeg = true;
			$out_extension = 'jpg';
		} else {
			$out_extension = $extension;
		}

		$image_old = $filename;
		$image_new = 'cache/' . utf8_substr($filename, 0, utf8_strrpos($filename, '.')) . '-' . (int)$width . 'x' . (int)$height . '.' . $out_extension;

		if (!is_file(DIR_IMAGE . $image_new) || (filemtime(DIR_IMAGE . $image_old) > filemtime(DIR_IMAGE . $image_new))) {
			list($width_orig, $height_orig, $image_type) = getimagesize(DIR_IMAGE . $image_old);

			if (!in_array($image_type, array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF, IMAGETYPE_WEBP))) {
				if ($this->request->server['HTTPS']) {
					return $this->config->get('config_ssl') . 'image/' . $image_old;
 				} else {
					return $this->config->get('config_url') . 'image/' . $image_old;
				}
			}

			$path = '';

			$directories = explode('/', dirname($image_new));

			foreach ($directories as $directory) {
				$path = $path . '/' . $directory;

				if (!is_dir(DIR_IMAGE . $path)) {
					@mkdir(DIR_IMAGE . $path, 0777);
				}
			}

			if ($width_orig != $width || $height_orig != $height) {
				$image = new Image(DIR_IMAGE . $image_old);
				$image->resize($width, $height);
				if ($convert_to_jpeg) {
					// Сохраняем как PNG во временный файл, затем конвертируем в JPEG
					$tmp_png = DIR_IMAGE . $image_new . '.tmp.png';
					$image->save($tmp_png);
					$img = @imagecreatefrompng($tmp_png);
					if ($img) {
						$bg = imagecreatetruecolor(imagesx($img), imagesy($img));
						imagefill($bg, 0, 0, imagecolorallocate($bg, 255, 255, 255));
						imagecopy($bg, $img, 0, 0, 0, 0, imagesx($img), imagesy($img));
						imagejpeg($bg, DIR_IMAGE . $image_new, 85);
						imagedestroy($img);
						imagedestroy($bg);
					}
					@unlink($tmp_png);
				} else {
					$image->save(DIR_IMAGE . $image_new);
				}
			} else {
				if ($convert_to_jpeg) {
					$img = @imagecreatefrompng(DIR_IMAGE . $image_old);
					if ($img) {
						$bg = imagecreatetruecolor(imagesx($img), imagesy($img));
						imagefill($bg, 0, 0, imagecolorallocate($bg, 255, 255, 255));
						imagecopy($bg, $img, 0, 0, 0, 0, imagesx($img), imagesy($img));
						imagejpeg($bg, DIR_IMAGE . $image_new, 85);
						imagedestroy($img);
						imagedestroy($bg);
					}
				} else {
					copy(DIR_IMAGE . $image_old, DIR_IMAGE . $image_new);
				}
			}
		}

		$image_new = str_replace(' ', '%20', $image_new);  // fix bug when attach image on email (gmail.com). it is automatic changing space " " to +

		if ($this->request->server['HTTPS']) {
			return $this->config->get('config_ssl') . 'image/' . $image_new;
		} else {
			return $this->config->get('config_url') . 'image/' . $image_new;
		}
	}
}
