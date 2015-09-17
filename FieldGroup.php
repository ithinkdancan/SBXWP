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


  public function add_keys($fields = array())
  {

    foreach ($fields as &$field) {
      $field['key'] = $this->id . '_' . $field['name'];
    }

    return $fields;
  }


  public function register($fields)
  {

    $this->fields = $this->add_keys($fields);

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
