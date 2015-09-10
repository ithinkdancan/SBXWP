<?php

namespace SBX;

abstract class MetaBox
{
	public $cpt;
	public $args;
	protected $field;
	
	static protected $metaboxes = array();
	

	static public function register($name, $metabox)
	{
		if(isset(static::$metaboxes[$name]))
			throw new \Exception("Metabox already exists with name [$name]");

		static::$metaboxes[$name] = $metabox;
	}

	static public function get($name)
	{
		if(!isset(static::$metaboxes[$name]))
			throw new \Exception("No metabox exists with name [$name]");

		return static::$metaboxes[$name];
	}

	static public function value($name, $post_id, $key = null)
	{
		return static::get($name)->get_value($post_id, $key);
	}
	
	public function __construct($cpt, $args = array())
	{
		$this->cpt = $cpt;
		
		$defaults = array(
			'title' => '',
			'context' => 'side',
			'priority' => 'default',
			'template' => null, // only display for a specific template
			'page' => null,
			'namespace' => preg_replace('/[^a-zA-Z0-9]/', '_', strtolower(isset($args['title']) ? $args['title'] : $this->get_title()))
		);
		
		$this->args = wp_parse_args($args, $defaults);
		$this->field = "mbx_{$this->cpt}_" . $this->get_arg('namespace');

		if($this->should_init())
			$this->init();

		add_action('admin_enqueue_scripts', array($this, '_admin_enqueue_scripts'));
	}
	
	abstract protected function init();
	abstract public function render($post, $metabox);
	abstract public function get_value($post_id, $key);
	abstract public function set_value($post_id, $key, $value);

	public function get_title()
	{
		return $this->get_arg('title');
	}

	public function get_field($key)
	{
		return "{$this->field}_$key";
	}
	
	public function get_arg($name, $default_value = null)
	{
		return !empty($this->args[$name]) ? $this->args[$name] : $default_value;
	}

	protected function should_init()
	{
		if(defined('DOING_AJAX') && DOING_AJAX)
			return true;

		$template = $this->get_arg('template');

		if(!$template)
			return true;

		$post_id = $this->get_post_id();

		if($post_id === null)
			return false;

		$slug = get_page_template_slug($post_id);

		if(is_array($template))
			return in_array($slug, $template);

		return $template == $slug;
	}

	protected function get_post_id()
	{
		$keys = array('post', 'post_ID');

		foreach($_REQUEST as $key => $value)
		{
			if(in_array($key, $keys))
				return $value;
		}

		return null;
	}

	public function _admin_enqueue_scripts()
	{
		$metaurl = apply_filters( 'cmb_meta_box_url', trailingslashit( str_replace( WP_CONTENT_DIR, WP_CONTENT_URL, dirname( __FILE__ ) ) ) );
		wp_register_script( 'metabox-scripts', $metaurl . 'metabox/js/metabox.js', array('jquery', 'jquery-ui-core', 'jquery-ui-datepicker'), '0.9.1' );
		wp_enqueue_script( 'metabox-scripts' );

		wp_register_style( 'metabox-styles', $metaurl . 'metabox/styles/metabox.css', array(), '0.0.1' );
		wp_enqueue_style( 'metabox-styles' );
	}
}
?>