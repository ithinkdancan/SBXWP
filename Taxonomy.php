<?php

namespace SBX;

abstract class Taxonomy
{
	static protected $taxonomies = array();

	/**
	 * Dynamically load a directory of templates
	 * 
	 * @param  string $dir
	 * @param  string $namespace
	 * @return void
	 */
	public static function load($dir, $namespace)
	{
		$files = scandir($dir);
		
		foreach($files as $file)
		{
			if(strpos($file, '.php') !== false)
			{				
				$classname = $namespace . '\\' . substr($file, 0, strpos($file, '.'));
				new $classname;
			}
		}
	}


	public $id;

	protected $taxonomy_args;

	public function __construct($id, $post_types)
	{
		self::$taxonomies[$id] = $this;
	
		$this->id = $id;
		
		$this->taxonomy_args = $this->create();

		register_taxonomy($id, $post_types, $this->taxonomy_args);
	}
	
	static public function get_taxonomy($type)
	{
		return isset(self::$taxonomies[$type]) ? self::$taxonomies[$type] : null;
	}
	
	static public function get_current_taxonomy()
	{
		$tax_slug = get_query_var('taxonomy');
		return self::get_taxonomy($tax_slug);
	}
	
	static public function get_current_term()
	{
		$tax_slug = get_query_var('taxonomy');
		$term_slug = get_query_var('term');
		return self::get_taxonomy($tax_slug)->get_term_by_slug($term_slug);
	}
	
	public function get_slug()
	{
		return $this->id;
	}

	public function get_rewrite()
	{
		if(isset($this->taxonomy_args['rewrite']) && isset($this->taxonomy_args['rewrite']['slug']))
			return $this->taxonomy_args['rewrite']['slug'];

		return null;
	}
	
	public function get_terms($args = array())
	{
		$terms = get_terms($this->id, $args);
		return $this->wrap_terms($terms);
	}
	
	public function get_post_terms($post_id, $args = array())
	{
		$args = wp_parse_args($args, array('orderby' => 'term_order'));
		$terms = wp_get_object_terms($post_id, $this->id, $args);
		return $this->wrap_terms($terms);
	}
	
	public function get_term($term_id)
	{
		return $term_id > 0 ? $this->wrap(get_term($term_id, $this->id)) : null;
	}
	
	public function get_term_by_slug($value)
	{
		return $this->wrap(get_term_by('slug', $value, $this->id));
	}

	public function get_term_children($term_id, $args = array())
	{
		$defaults = array('parent' => $term_id);
		$args = wp_parse_args($args, $defaults);
		$terms = get_terms($this->id, $args);
		return $this->wrap_terms($terms);
	}
	
	public function wrap_terms($terms)
	{
		$wrapped = array();
	
		foreach($terms as $term)
			$wrapped[] = $this->wrap($term);
		
		return $wrapped;
	}
	
	protected function wrap($term)
	{
		if(!($term instanceof Term))
			return new Term($this, $term);
		
		return $term;
	}
	
	abstract protected function create();
}

?>