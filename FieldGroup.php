<?php namespace SBX;

abstract class FieldGroup
{
  protected static $fieldgroups = array();

  public $id;
  public $title;
  public $args;

  public static function load($dir, $namespace)
  {
    $files = scandir($dir);

    foreach($files as $file)
    {
      if(strpos($file, '.php') !== false)
      {

        $classname = $namespace . '\\' . substr($file, 0, strpos($file, '.'));
        new $classname();
      }
    }
  }

  public function __construct($id, $title, $args = array())
  {
    $this->id = $id;
    $this->title = $title;
    $this->args = $args;

    static::$fieldgroups[$id] = $this;

    $this->register($this->create());
  }

  /**
   * Returns the CPT for a given post type
   */
  static public function get_fieldgroup($type)
  {
    return isset(self::$fieldgroups[$type]) ? self::$fieldgroups[$type] : null;
  }

  public function get_id()
  {
    return 'acf_' . $this->id;
  }

  public function clean_key($key)
  {
    return preg_replace('/^'.$this->id.'_/', '', $key);
  }

  private function get_subfields($fields)
  {

  	return array_map(function($tag) {
            $subfields = array();

            if(is_array($tag)){
	            foreach ($tag as $key => $value) {

	            	// Check for Subfields
	            	if(is_array($value)){
	            		$value = $this->get_subfields($value);
	            	}

	            	$subfields[$this->clean_key($key)] = $value;
	            }

	            return $subfields;
	        }

            return $tag;

          }, $fields);
  }

  public function get_fields()
  {

    $fields = array();

    foreach ( $this->fields as $field) {
      $field_object = get_field_object($field['name']);

      $field_object['key'] = $this->clean_key($field_object['key']);

      //Clean up the subfields
      if(is_array($field_object['value'])){
          $field_object['value'] = $this->get_subfields($field_object['value']);
      }

      $fields[$field_object['key']] = $field_object['value'];
    }

    return $fields;
  }

  public function unique_keys($fields = array())
  {

    foreach ($fields as &$field) {
      $field['key'] = $this->id . '_' . $field['name'];
      $field['name'] = $this->id . '_' . $field['name'];

      if(isset($field['sub_fields'])){
        $field['sub_fields'] = $this->unique_keys($field['sub_fields']);
      }
    }

    return $fields;
  }


  public function register($fields)
  {

    $this->fields = $this->unique_keys($fields);

    $defaults = array(
      'key' => 'acf_' . $this->id,
      'title' => $this->title,
      'fields' => $this->fields,
      'location' => array (
        array (
          array (
            'param' => 'post_type',
            'operator' => '==',
            'value' => 'post',
          ),
        ),
      ),
      'menu_order' => 0,
      'position' => 'normal',
      'style' => 'default',
      'label_placement' => 'top',
      'instruction_placement' => 'label',
      'hide_on_screen' => '',
      'active' => 1,
      'description' => '',
    );

    $args = wp_parse_args($this->args, $defaults);

    if( function_exists('acf_add_local_field_group') ){
      acf_add_local_field_group($args);
    }
  }


  abstract protected function create();

}
