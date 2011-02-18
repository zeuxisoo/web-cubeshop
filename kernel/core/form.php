<?php
if (defined("IN_APPS") === false) exit("Access Dead");

abstract class Form_Type {
	protected static $entity;
	
	public function run() {
		foreach(self::$entity as $kind => $data) {
			call_user_func(array("self", "create_".$kind), $data);
		}
	}
	
	public function create_init($data) {
		Form_Html::form_open($data);
	}
	
	public function create_hidden($data) {
		if (is_array($data)) {
			foreach($data as $row) {
				$row['type'] = 'hidden';
				
				Form_Html::input($row);
			}
		}
	}
}

class Form_List extends Form_Type {

	public function __construct($entity) {
		parent::$entity = $entity;
	}
	
	public function create_content($data) {
		foreach($data as $function_name => $function_parameters) {
			call_user_func(array("self", "create_content_".$function_name), $function_parameters);
		}
	}
		
	public function create_content_title($title) {
		Form_Html::row(Form_Html::title($title, true));
	}
	
	public function create_content_control($controls) {
		if (isset($controls['pagebar']) === true) {
			Form_Util::out(Form_Html::row($controls['pagebar'], array('table-pagebar')));
		}
	
		if (isset($controls['delete']) === true) {	
			Form_Util::out(Form_Html::row(Form_Html::input($controls['delete'], true), array('table-delete')));
		}
	}
	
	public function create_content_table($table) {
		Form_Util::out(Form_Html::row(Form_Util::new_line(array(
			Form_Html::table_open(Form_Util::get_hash_value($table, 'attributes', array()), true),
			Form_Html::table_header($table['header'], true),
			Form_Html::table_row($table['rows'], true),
			Form_Html::table_close(true)
		)), array('data-container')));
	}

}

class Form_Detail extends Form_Type {
	
	public function __construct($entity) {
		parent::$entity = $entity;
	}
	
	public function create_content($data) {
		foreach($data as $function_name => $function_parameters) {
			call_user_func(array("self", "create_content_".$function_name), $function_parameters);
		}
	}
	
	public function create_content_title($title) {
		Form_Html::row(Form_Html::title($title, true));
	}
	
	public function create_content_rows($rows) {		
		foreach($rows as $row) {
			$html = array();
			foreach($row as $type => $value) {
				$html[] = Form_Html::$type($value, true);
			}
			Form_Util::out((Form_Html::row(Form_Util::new_line($html))));
		}
	}
	
	public function create_content_control($controls) {
		$html = array();
		foreach($controls['input'] as $value) {
			$html[] = Form_Html::input($value, true);
		}
		
		Form_Util::out(Form_Html::row(Form_Util::new_line($html), array('control')));
	}
	
}

class Form_Util {
	public static function get_hash_value($hash_table, $key, $default_value = '') {
		return isset($hash_table[$key]) ? $hash_table[$key] : $default_value;
	}
	
	public static function get_attribute_string($attributes) {
		$attribute = array();
		
		foreach($attributes as $key => $value) {
			$attribute[] = sprintf('%s="%s"', $key, $value);	
		}
		
		return implode(" ", $attribute);
	}
		
	public static function out($html = "") {
		echo $html,"\n";
	}
	
	public static function new_line($htmls) {
		return implode("\n", $htmls);
	}
}

class Form_Page {
	
	public static function head() {
		$disable_over_flow_hidden = true;
		include_once View::display("admin/header.html");
	}
	
	public static function foot() {
		include_once View::display("admin/footer.html");
	}
	
	public static function container_open() {
		Form_Util::out();
		Form_Util::out("<div class='container'>");
	}
	
	public static function container_close() {
		Form_Util::out("</div>");
	}
	
	public static function form_content_open() {
		Form_Util::out("<div class='form detail-form round-corner'>");
	}
	
	public static function form_content_close() {
		Form_Util::out("</div>");
	}
	
	public static function block_flow_message() {
		global $flow_message;
		include_once View::display("admin/block-flow-message.html");
	}
	
	public static function content($html) {
		echo $html;
	}
	
}

class Form_Html {

	public static function form_open($attributes) {
		if (isset($attributes['id']) === false) {
			$attributes['id'] = $attributes['name'];
		}
	
		Form_Util::out(sprintf("<form enctype='multipart/form-data' %s>", Form_Util::get_attribute_string($attributes)));
	}

	public static function form_close() {
		Form_Util::out("</form>");
	}

	public static function input($attributes, $is_return = false) {
	
		if (isset($attributes['id']) === false) {
			$attributes['id'] = $attributes['name'];
		}
		
		$label = "";
		if (isset($attributes['label']) === true) {
			$label = "&nbsp;".$attributes['label'];
			unset($attributes['label']);
		}
		
		$html = sprintf("<input %s />%s", Form_Util::get_attribute_string($attributes), $label);
		
		if ($is_return === true) {
			return $html;
		}else{
			Form_Util::out($html);
		}
	}
	
	public static function select($attributes, $is_return = false) {
		$options = array();
		foreach($attributes['options'] as $key => $value) {
			if (empty($attributes['default_options']) === true || $attributes['default_options'] != $key) {
				$options[] = sprintf('<option value="%s">%s</option>', $key, $value);
			}else{
				$options[] = sprintf('<option value="%s" selected="selected">%s</option>', $key, $value);
			}
		}
		unset($attributes['options'], $attributes['default_options']);
		
		$html[] = sprintf("<select %s>", Form_Util::get_attribute_string($attributes));
		$html[] = "</select>";
		
		array_splice($html, 1, 0, $options);	// insert options into select

		$html = &Form_Util::new_line($html);
		
		if ($is_return === true) {
			return $html;
		}else{
			Form_Util::out($html);
		}
	}
	
	public static function checkbox($attributes, $is_return = false) {	
		$options = array();
		foreach($attributes['options'] as $attribute) {
			$attribute['type'] = $attributes['type'];
			
			if (in_array($attribute['value'], $attributes['default_options']) === true) {
				$attribute['checked'] = "checked";
			}
			
			$options[] = Form_Html::input($attribute, true);
		}
	
		$html = Form_Util::new_line($options);
	
		if ($is_return === true) {
			return $html;
		}else{
			Form_Util::out($html);
		}
	}
	
	public static function textarea($attributes, $is_return = false) {
		$value = Form_Util::get_hash_value($attributes, 'value');
		unset($attributes['value']);
	
		$html = sprintf("<textarea %s>%s</textarea>", Form_Util::get_attribute_string($attributes), $value);
	
		if ($is_return === true) {
			return $html;
		}else{
			Form_Util::out($html);
		}		
	}
	
	public static function table_open($attributes = array(), $is_return = false) {
		$attributes['width'] = Form_Util::get_hash_value($attributes, 'width', "100%");
		$attributes['class'] = Form_Util::get_hash_value($attributes, 'class', "data-list");
		
		$html = sprintf("<table %s>", Form_Util::get_attribute_string($attributes));
		
		if ($is_return === true) {
			return $html;
		}else{
			Form_Util::out($html);
		}
	}
	
	public static function table_close($is_return = false) {
		$html = "</table>";
		
		if ($is_return === true) {
			return $html;
		}else{
			Form_Util::out($html);
		}
	}
	
	public static function table_header($headers, $is_return = false) {
		$html = array("<tr>");
		foreach($headers as $width => $title) {
			if (empty($title) === true) {
				$width = "5%";
				$title = Form_Html::input(array('type' => 'checkbox', 'name' => 'del[]', 'value' => "", 'id' => 'header_control_delete'), true);
			}

			$width = is_numeric($width) == false ? sprintf(' width="%s"', $width) : "";
			
			$html[] = sprintf("<th%s>%s</th>", $width, $title);
		}
		$html[] = "</tr>";
		
		$html = &Form_Util::new_line($html);
		
		if ($is_return === true) {
			return $html;
		}else{
			Form_Util::out($html);
		}		
	}
	
	public static function table_row($rows, $is_return = false) {
		$html = array();
		foreach($rows as $row_id => $row) {
			$html[] = "<tr>";
			foreach($row as $text) {
				
				$attributes = array('align' => 'left', 'class' => "text");
				
				if (is_null($text) === true) {
					
					// Check box
					$attributes['align'] = "center";
					$text = Form_Html::input(array('type' => 'checkbox', 'name' => 'del[]', 'value' => $row_id, 'id' => 'delete_'.$row_id), true);
					
				}elseif (is_array($text) === true) {
					
					$text['type'] = Form_Util::get_hash_value($text, 'type', 'link');
					$type = $text['type'];
					unset($text['type']);
						
					if ($type == "image" || $type == "file") {
					
						// Preview or File
						$text = Form_Html::preview(array('type' => $type, 'info' => $text), true);
						
					}elseif ($type === "link") {

						// Control Link [edit]
						$temp_text = array();
						foreach($text as $name => $link) {
							$temp_text[] = Form_Html::link(array(
								'name' => $name,
								'link' => $link,
							), true);
						}
						$text = &implode(" | ", $temp_text);
						
						$attributes['class'] = "link";
						
					}

				}

				// * Normal text will not handle
				// Apply attribute to each html item
				$attributes = Form_Util::get_attribute_string($attributes);
				if (empty($attributes) === false) {
					$attributes = " ".$attributes;
				}
				
				$html[] = sprintf("<td%s>%s</td>", $attributes, $text);
			}
			$html[] = "</tr>";
		}
	
		$html = &Form_Util::new_line($html);
	
		if ($is_return === true) {
			return $html;
		}else{
			Form_Util::out($html);
		}		
	}
	
	public static function title($text, $is_return = false) {
		$html = sprintf("<h2>%s</h2>", $text);
		
		if ($is_return === true) {
			return $html;
		}else{
			Form_Util::out($html);
		}
	}

	public static function row($html, $css = array(), $is_return = false) {
		$html = sprintf("<div class='%s'>\n%s\n</div>", implode(" ", array_merge(array("row"), $css)), $html);
		
		if ($is_return === true) {
			return $html;
		}else{
			Form_Util::out($html);
		}
	}
	
	public static function label($text, $is_return = false) {
		if (is_string($text) === true) {
			$html = sprintf("<label>%s:</label>", $text);
		}elseif (is_array($text) === true && isset($text['class']) == true) {
			$html = sprintf("<label class='%s'>%s:</label>", $text['class'], $text['text']);
		}
		
		if ($is_return === true) {
			return $html;
		}else{
			Form_Util::out($html);
		}
	}
	
	public static function remark($text, $is_return = false) {
		if (empty($text) === false) {
			$html = sprintf("<span class='remark'>%s</span>", $text);
			
			if ($is_return === true) {
				return $html;
			}else{
				Form_Util::out($html);
			}
		}
	}

	public static function preview($data, $is_return = false) {
		$html = "";
		
		if ($data['type'] === 'image' && empty($data['info']['src']) === false) {
			$html = sprintf('<img %s>', Form_Util::get_attribute_string($data['info']));
		}elseif ($data['type'] === 'file' && empty($data['info']['src']) === false) {
			$html = sprintf('<a href="%s" target="_blank" class="download">Click to Download/Open</a>', $data['info']['src']);
		}
		
		$html = empty($html) === true ? "" : sprintf('<span class="preview">%s</span>', $html);
	
		if ($is_return === true) {
			return $html;
		}else{
			Form_Util::out($html);
		}		
	}
	
	public static function link($data, $is_return = false) {
		$html = sprintf(
			'<a href="%s" target="%s">%s</a>', 
			Form_Util::get_hash_value($data, 'link'), 
			Form_Util::get_hash_value($data, 'target'), 
			Form_Util::get_hash_value($data, 'name')
		);
	
		if ($is_return === true) {
			return $html;
		}else{
			Form_Util::out($html);
		}	
	}

}

class Form extends Form_Html {

	const IS_DETAIL = "detail";
	const IS_LIST = "list";
	const IS_POST = "post";
	const IS_GET = "get";
	
	private static $default_value_source = array();
	
	public static function init() {}
	
	public static function bulid($type, $entity) {
		$handler = "Form_".ucfirst($type);
		self::out(new $handler($entity));
	}
	
	public static function out($html) {
		Form_Page::head();
		Form_Page::container_open();
		Form_Page::block_flow_message();
		Form_Page::form_content_open();
		
		if (is_object($html) === true) {
			$html->run();
		}else{
			Form_Page::content($html);
		}
		
		Form_Page::form_content_close();
		Form_Page::container_close();
		Form_Page::foot();
	}
	
}

class Form_View {

	const TEXT_FIELD = "text";
	const PASSWORD_FIELD = "password";
	const CHECKBOX_FIELD = "checkbox";
	const RADIO_FIELD = "radio";

	public static function input($label, $name, $value = "", $label_css_class = "", $remark = "", $type = self::TEXT_FIELD, $other_attributes = array()) {	
		return array(
			'label' => $label,
			'input' => array_merge(array('type' => $type, 'name' => $name, 'class' => $label_css_class, 'value' => $value), $other_attributes),
			"remark" => $remark
		);
	}

	public static function password($label, $name, $value = "", $label_css_class = "", $remark = "") {
		return self::input($label, $name, $value, $label_css_class, $remark , self::PASSWORD_FIELD);	
	}
	
	public static function select($label, $name, $options, $default_options = "", $label_css_class = "select") {
		return array(
			'label' => array('text' => $label, 'class' => $label_css_class),
			'select'=> array('name' => $name, 'options' => $options, 'default_options' => $default_options),
		);
	}
	
	public static function checkbox($label, $options, $default_options = "", $label_css_class = "checkbox", $type = self::CHECKBOX_FIELD) {
		$temp_options = array();
		foreach($options as $option) {
			$temp_options[] = array(
				'label' => $option[0],
				'name' => $option[1],
				'value' => $option[2],
				'id' => $option[3],
			);
		}
	
		return array(
			'label' => array('text' => $label, 'class' => $label_css_class),
			'checkbox' => array(
				'type' => $type,
				'options' => $temp_options,
				'default_options' => $default_options
			),
		);
	}
	
	public static function radio($label, $options, $default_options = "", $label_css_class = "radio") {
		return self::checkbox($label, $options, $default_options, $label_css_class, self::RADIO_FIELD);
	}
	
	public static function textarea($label, $name, $default_text = "", $textarea_css_class = "editor", $label_css_class = "textarea") {
		return array(
			'label' => array('text' => $label, 'class' => $label_css_class),
			'textarea' => array('name' => $name, 'class' => $textarea_css_class, 'value' => $default_text),
		);
	}
	
	public static function upload_image($label, $name, $preview_source = "", $preview_width = "", $preview_height = "", $type = 'image') {
		$view = array(
			'label' => $label,
			'input' => array('type' => "file", 'name' => $name),
			'preview' => array(
				'type' => $type,
				'info' => array(
					'src' => "", 'width' => 0, 'height' => 0,
				)
			)
		);
	
		$info['src'] = $preview_source;
	
		if (empty($preview_width) === false) {
			$info['width'] = $preview_width;
		}
		
		if (empty($preview_height) === false) {
			$info['height'] = $preview_height;
		}
		
		$view['preview']['info'] = $info;
		
		return $view;
	}
	
	public static function upload_file($label, $name, $preview_source = "") {
		return self::upload_image($label, $name, $preview_source, 0, 0, 'file');
	}
	
	public static function preview($type, $source, $width = "", $height = "") {
		$view = array(
			'type' => $type,
			'src' => $source,
		);
		
		if (empty($width) === false) {
			$view['width'] = $width;
		}
		
		if (empty($height) === false) {
			$view['height'] = $height;
		}
		
		return $view;
	}
	
	public static function manage($primary_key = "", $other_controls = array()) {
		$edit_url = Module::create("script")->get('edit');
		
		if (empty($primary_key) === true) {
			return $other_controls;
		}else{
			return array_merge(
				array('Edit' => Util::make_url($edit_url, array('id' => $primary_key))), 
				$other_controls
			);
		}
	}
	
	public static function links($controls) {
		return self::manage("", $controls);
	}
	
	public static function create_detail($title, $row = array(), $hidden = array(), $action = array()) {
		// Handle hidden field on "action"
		// If defined action field will replace the orgigin field
		$default_hidden = array('name' => 'action', 'value' => 'create');

		// Found out if or not defined default action, 
		// If yes, clear default action and wait new action details
		foreach($hidden as $hide) {
			if ($default_hidden['name'] == $hide['name']) {
				$default_hidden = array();
				break;
			}
		}
		
		// If not empty will merge exists action into defined hidden table
		if (empty($default_hidden) === false) {
			$hidden = array_merge(array($default_hidden), $hidden);
		}
	
		//
		return array(
			'init' => array(
				'action' => Form_Util::get_hash_value($action, 'url', PHP_SELF),
				'method' => Form_Util::get_hash_value($action, 'method', Form::IS_POST),
				'name' => Form_Util::get_hash_value($action, 'name', "submit-form"),
			),
			'hidden' => $hidden,
			'content' => array(
				'title' => $title,
				
				'rows' => $row,
				
				'control' => array(
					'input' => array(
						array('type' => 'submit', 'name' => 'commit', 'value' => 'Submit', 'class' => 'control'),
						array('type' => 'reset', 'name' => 'clear', 'value' => 'Clear', 'class' => 'control')
					)
				),
			)
		);	
	}
	
	public static function create_list($title, $table, $pagebar = "", $can_delete = true, $hidden = array(), $action = array()) {
		$hidden[] = array_merge(
			array('name' => 'action', 'value' => 'create'),
			array('name' => 'option', 'value' => 'go'),
			$hidden
		);

		// Control
		if (empty($pagebar) === false) {
			$control['pagebar'] = $pagebar;
		}
		
		if ($can_delete === true) {
			$control['delete'] = array('type' => 'submit', 'name' => 'commit', 'value' => 'Delete', 'class' => 'control');
		}
		
		//
		return array(
			'init' => array(
				'action' => Form_Util::get_hash_value($action, 'url', PHP_SELF),
				'method' => Form_Util::get_hash_value($action, 'method', Form::IS_POST),
				'name' => Form_Util::get_hash_value($action, 'name', "submit-form"),
			),
			'hidden' => $hidden,
			'content' => array(
				'title' => $title,
			
				'table' => array(
					'header' => $table[0],
					'rows' => $table[1],
				),
							
				'control' => $control
			),
		);
	}
	
}
?>