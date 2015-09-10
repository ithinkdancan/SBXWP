<?php

namespace SBX;

use \WP_Query;
use SBX\MetaBox;

abstract class Template
{
	protected static $templates = array();

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
				static::register(new $classname);
			}
		}
	}

	static public function register(Template $template)
	{
		if(isset(static::$templates[$template->path]))
			throw new \Exception("Template already exists for path [{$template->path}]");

		static::$templates[$template->path] = $template;
	}

	static public function get($path)
	{
		if(!isset(static::$templates[$path]))
			throw new \Exception("No template exists for path [$path]");

		return static::$templates[$path];
	}

	static public function has($path)
	{
		return isset(static::$templates[$path]);
	}

	static public function current()
	{
		global $post;

		if($post)
		{
			$slug = get_page_template_slug($post->ID);

			if(static::has($slug))
				return static::get($slug);

			if(is_single() && static::has('single.php'))
				return static::get('single.php');
		}

		return null;
	}


	public $path;

	public function __construct($path)
	{
		$this->path = $path;

		add_action('init', array($this, 'metaboxes_init'));
		add_action('widgets_init', array($this, 'widgets_init'), 11);
	}

	public function pages()
	{
		$query = new WP_Query(array(
			'meta_key' => '_wp_page_template',
			'meta_value' => $this->path,
			'posts_per_page' => -1,
			'post_status' => 'publish',
			'post_type' => 'page'
		));

		return $query->posts;
	}

	final public function metaboxes_init()
	{
		$this->metaboxes();
	}

	final public function widgets_init()
	{
		$this->sidebars();
	}

	public function sidebar($id, $page = null)
	{
		if(!$page)
		{
			global $post;
			$page = $post;
		}

		dynamic_sidebar("{$page->post_name}-$id");
	}

	public function metabox($id, $page = null)
	{
		if(!$page)
		{
			global $post;
			$page = $post;
		}

		return MetaBox::value($id, $page->ID);
	}

	abstract protected function metaboxes();
	abstract protected function sidebars();
}