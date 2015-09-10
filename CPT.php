<?php

namespace SBX;

use \WP_Query;

abstract class CPT
{
	// static table of CPTs by type
	protected static $cpts = array();
	
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


	public $id;
	public $args;
	public $labels;
	
	// WP_Query object from the last query made
	public $query;
	
	public function __construct($id)
	{
		$this->id = $id;
		static::$cpts[$id] = $this;
		
		$this->register($this->create());

		$this->init_image_support();
		
		// for adding custom columns to list view
		add_filter("manage_{$this->id}_posts_columns", array($this, 'add_columns'));
		add_action("manage_{$this->id}_posts_custom_column", array($this, 'render_column'), 10, 2);

		add_action('admin_enqueue_scripts', array($this, '_admin_enqueue_scripts'));
	}
	
	/**
	 * Returns the CPT for a given post type
	 */
	static public function get_cpt($type)
	{
		return isset(self::$cpts[$type]) ? self::$cpts[$type] : null;
	}

	static public function get_current_post_type()
	{
		global $post, $typenow, $current_screen;

		//we have a post so we can just get the post type from that
		if ($post && $post->post_type)
			return $post->post_type;

		//check the global $typenow - set in admin.php
		elseif($typenow)
			return $typenow;

		//check the global $current_screen object - set in sceen.php
		elseif($current_screen && $current_screen->post_type)
			return $current_screen->post_type;

		//lastly check the post_type querystring
		elseif(isset($_REQUEST['post_type']))
			return sanitize_key($_REQUEST['post_type']);

		//we do not know the post type!
		return null;
	}
	
	public function get_label($plural = false)
	{
		return $plural ? $this->labels['name'] : $this->labels['singular_name'];
	}
	
	public function get_title($post)
	{
			return $post->post_title;
	}

	public function get_slug()
	{
		$slug = $this->args['rewrite']['slug'];
		return $slug ? $slug : $this->id;
	}

	public function get_all($args = array())
	{
		$defaults = array('post_type' => $this->id, 'orderby' => 'post_date', 'order' => 'DESC', 'depth' => 1, 'posts_per_page' => -1);
		$args = wp_parse_args($args, $defaults);
		$this->query = new WP_Query($args);
		
		return $this->query->posts;
	}

	public function get_all_posts_by_term($term, $args = array())
	{
		$defaults = array('taxonomy' => $term->taxonomy->get_slug(), 'term' => $term->get_slug());
		$args = wp_parse_args($defaults, $args);
		return $this->get_all($args);
	}
	
	public function register($args)
	{
		$this->args = $args;
		$this->labels = $args['labels'];
		
		if(!post_type_exists($this->id))
			register_post_type($this->id, $args);
	}
	
	public function add_columns($columns)
	{
		return $columns;
	}
	
	public function render_column($name, $post_id)
	{
		
	}
	
	public function init_admin()
	{
		
	}

	protected function init_image_support()
	{

	}

	public function get_image_url($type, $post_id, $size = null)
	{
		if (class_exists('MultiPostThumbnails')) {
			return \MultiPostThumbnails::get_post_thumbnail_url($this->id, $type, $post_id, $size);
		}

		return null;
	}

	protected function add_image_support($id, $label)
	{
		if (class_exists('MultiPostThumbnails')) {
			new \MultiPostThumbnails(array(
				'label' => $label,
				'id' => $id,
				'post_type' => $this->id
				)
	    	);
	    }
	}

	public function _admin_enqueue_scripts()
	{
		if(self::get_current_post_type() == $this->id && is_admin())
			$this->admin_enqueue_scripts();
	}

	protected function admin_enqueue_scripts()
	{

	}

	abstract protected function create();
}

?>