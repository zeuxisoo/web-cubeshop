<?php
if (defined("IN_APPS") === false) exit("Access Dead");

class Upload_Helper {

	/*
	 * $settings = array(
	 *		'input_field' => $_FILES['image_file'], 		// <input type='file' name='image_file' />
	 *		'save_to_folder' => ATTACHMENT_ROOT.'/photo',	// Store file folder
	 *		'max_size' => 1024,								// Optional (Default by php.ini settings)
	 *		'file_name_body' => 'reiszed',					// Optional (like resized.jpg)
	 *		'file_name_body_pre' => 'thumb_', 				// Optional (like thumb_xxxxxxx.jpg)
	 *		'jpeg_quality' => 100,							// Optional (Default 85)
	 *		'resize' => true,								// Optional (Default false)
	 *		'resize_width' => 100,							// Optional (Default 150)
	 *		'resize_height' => 100,							// Optional (Default 150)
	 *		'resize_by_width' => true,						// Optional (Default false)
	 *		'resize_by_height' => false,					// Optional (Default false)
	 * );
	 */
	public static function save_file($settings) {
		$file_name_body = isset($settings['file_name_body']) === true ? $settings['file_name_body'] : date("YmdHis")."-".time();
		$file_name_body_pre = isset($settings['file_name_body_pre']) === true ? $settings['file_name_body_pre'] : '';
		$max_size = isset($settings['max_size']) === true ? intval($settings['max_size']) : 0;
		$jpeg_quality = isset($settings['jpeg_quality']) === true ? intval($settings['jpeg_quality']) : 0;
	
		// Original file name
		if (isset($settings['normal_file_name']) && $settings['normal_file_name'] === true) {
			$file_name_body = "";
		}
	
		$uploader = new Upload($settings['input_field']);

		if ($uploader->uploaded) {
		
			// Define file name body
			if (empty($file_name_body) === false) {
				$uploader->file_new_name_body = $file_name_body;
			}
			
			// Define file name pre
			if (empty($file_name_body_pre) === false) {
				$uploader->file_name_body_pre = $file_name_body_pre;
			}
			
			// Define max file size
			if (empty($file_max_size) === false) {
				$uploader->file_max_size = $file_max_size;
			}
			
			// Define quality for JPEG
			if (empty($jpeg_quality) === false) {
				$uploader->jpeg_quality = $jpeg_quality;
			}
			
			// Define is or not resize
			if (isset($settings['resize']) === true) {
				$uploader->image_resize = $settings['resize'] === true;
				$uploader->image_ratio_no_zoom_in = true;
				$uploader->image_ratio_no_zoom_out = true;
			}
			
			// Define resize width & height & resize ratio width & height
			if (isset($settings['resize']) === true && $settings['resize'] === true) {
				if (isset($settings['resize_width'])) {
					$uploader->image_x = intval($settings['resize_width']);
				}
				
				if (isset($settings['resize_height'])) {
					$uploader->image_y = intval($settings['resize_height']);
				}
				
				// Define resize by width or height
				if (isset($settings['resize_by_width'])) {
					$uploader->image_ratio_y = $settings['resize_by_width'];
				}
				
				if (isset($settings['resize_by_height'])) {
					$uploader->image_ratio_x = $settings['resize_by_height'];
				}
			}

			// Make it upload, if success will return processed file name
			if (isset($settings['save_to_folder']) && empty($settings['save_to_folder']) === false) {
				$uploader->process($settings['save_to_folder']);
				
				if ($uploader->processed) {
					$uploader->clean();
					return array(
						'file_name' => $uploader->file_dst_name,
						'file_path' => $uploader->file_dst_path,
						'file_path_with_name' => $uploader->file_dst_pathname,
					);
				}
			}
			
			return $uploader->error;
		}
	}
	
	/*
	 * $settings = array(
	 *		'input_field' => '',
	 *		'save_to_folder' => '',
	 * )
	 */
	public static function save_image($settings) {
		return self::save_file(array(
			'input_field' => $settings['input_field'],
			'save_to_folder' => $settings['save_to_folder'],
			'jpeg_quality' => 100,
		));
	}

	/*
	 * $settings = array(
	 *		'input_field' => '',
	 *		'save_to_folder' => '',
	 *		'file_path_with_name' => '',
	 *		'resize_to_folder' => '',
	 *		'resize_width' => '',
	 *		'resize_height' => '',
	 *		'resize_by_width' => true,		// Optional (Default: true)
	 *		'resize_by_height' => false,	// Optional (Default: false) 
	 * )
	 */
	public static function save_image_with_resize($settings) {
		$data = self::save_image($settings);

		if (isset($data['file_path_with_name']) === true) {
			$data = self::create_thumb_image(array(
				'file_path_with_name' => $data['file_path_with_name'],
				'resize_to_folder' => $settings['resize_to_folder'],
				'resize_width' => $settings['resize_width'],
				'resize_height' => $settings['resize_height'],
				'resize_by_width' => isset($settings['resize_by_width']) ? $settings['resize_by_width'] : true,
				'resize_by_height' => isset($settings['resize_by_height']) ? $settings['resize_by_height'] : false,
			));
			
			// Return value
			$data['file_name_resized'] = $data['file_name'];
			
			$pre_name[] = "thumb-";
			if (isset($settings['file_name_body_pre']) === true) {
				$pre_name[] = $settings['file_name_body_pre'];
			}
			$data['file_name'] = str_replace($pre_name, "", $data['file_name'], $cnt = 1);
			
			ksort($data);
			
			return $data;
		}
	}
	
	/*
	 * $settings = array(
	 *		'input_field' => $_FILES['image_field'],
	 *		'save_to_folder' => ATTACHMENT_ROOT."/test/1",
	 *		'resize_to_folder' => '',
	 *		'file_path_with_name' => '',
	 *		'file_name_body_pre' => '',		// Optional (Default: thumb_)
	 *		'resize_width' => '',
	 *		'resize_height' => '',
	 *		'resize_by_width' => true,		// Optional (Default: true)
	 *		'resize_by_height' => false,	// Optional (Default: false) 
	 * )
	 */
	public static function create_thumb_image($settings) {
		$file_name = basename($settings['file_path_with_name']);
		$pre_name = isset($settings['file_name_body_pre']) ? $settings['file_name_body_pre'] : 'thumb-';
		$need_resize_file_path = $settings['resize_to_folder'].'/'.$file_name;
		
		$current_path_info = pathinfo($settings['file_path_with_name']);

		if (copy($settings['file_path_with_name'], $need_resize_file_path) || $settings['file_path_with_name'] == $need_resize_file_path) {
			return self::save_file(array(
				'input_field' => $need_resize_file_path,
				'save_to_folder' => $settings['resize_to_folder'],
				'jpeg_quality' => 100,
				'file_name_body_pre' => isset($settings['file_name_body_pre']) ? $settings['file_name_body_pre'] : 'thumb-',
				'file_name_body' => $current_path_info['filename'],
				'resize' => true,
				'resize_width' => $settings['resize_width'],
				'resize_height' => $settings['resize_height'],
				'resize_by_width' => isset($settings['resize_by_width']) ? $settings['resize_by_width'] : true,
				'resize_by_height' => isset($settings['resize_by_height']) ? $settings['resize_by_height'] : false,
			));
		}
	}
	
	
	/*
	 * $settings = array(
	 *		'normal' => array(
	 *			'input_field' => $_FILES['image_field'],
	 *			'save_to_folder' => ATTACHMENT_ROOT."/test/1",
	 *		),
	 * 		'thumb' => array(
	 *			array('resize_width' => 150, "resize_height" => 150, "resize_to_folder" => ATTACHMENT_ROOT."/test/2"),
	 *			array('resize_width' => 250, "resize_height" => 250, "resize_to_folder" => ATTACHMENT_ROOT."/test/3"),
	 *		)
	 * )
	 */
	public static function save_image_with_multi_thumb($settings) {
		$remark['normal'] = $data = self::save_image($settings['normal']);
		
		if (isset($data['file_path_with_name']) === true) {
			foreach($settings['thumb'] as $thumb) {
				$remark['thumb'][] = self::create_thumb_image(array(
					'file_path_with_name' => $data['file_path_with_name'],
					'resize_to_folder' => $thumb['resize_to_folder'],
					'resize_width' => $thumb['resize_width'],
					'resize_height' => $thumb['resize_height'],
					'resize_by_width' => isset($thumb['resize_by_width']) ? $settings['resize_by_width'] : true,
					'resize_by_height' => isset($thumb['resize_by_height']) ? $settings['resize_by_height'] : false,
				));
			}
		}
		
		$remark['filename'] = $remark['normal']['file_name'];
		
		ksort($remark);
		
		return $remark;
	}
	
}
?>