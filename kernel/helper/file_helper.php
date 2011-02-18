<?php
if (defined("IN_APPS") === false) exit("Access Dead");

class File_Helper {

	public static function update_exists_file($settings) {
		$old_file_path = $settings['old_file_path'];
		$upload_file = $settings['upload_file'];

		// If upload image is empty (not upload)
		if (empty($upload_file['input_field']['name']) === true) {
			$cube_cover_image = basename($old_file_path);
		}else{
			self::delete_file($old_file_path); // remove exists image
			$resized_image = Upload_Helper::save_image_with_resize($upload_file); // Upload Cover
			$cube_cover_image = $resized_image['file_name_resized'];		
		}
		
		return $cube_cover_image;
	}
	
	public static function delete_file($file_path) {
		if (is_array($file_path) === true) {
			array_map(array("File_Helper", "delete_file"), $file_path);
		}else{
			if (file_exists($file_path) === true && is_file($file_path)) {
				unlink($file_path);
			}
		}
	}
}
?>