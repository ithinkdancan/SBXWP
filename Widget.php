<?php

namespace SBX;

class Widget
{
	/**
	 * Dynamically load a directory of widgets
	 * 
	 * @param  [type] $dir
	 * @param  [type] $namespace
	 * @return [type]
	 */
	public static function load($dir, $namespace)
	{
		$files = scandir($dir);
		
		foreach($files as $file)
		{
			if(strpos($file, '.php') !== false)
			{				
				$classname = $namespace . '\\' . substr($file, 0, strpos($file, '.'));
				register_widget($classname);
			}
		}
	}

	public static function register($sidebar, $args = false)
	{
		$defaults = array(
			'template' => null,
			'global' => false
		);

		$args = wp_parse_args($args, $defaults);

		if($args['global'] || $args['template'])
		{
			$pages = get_pages(array(
				'sort_order' => 'ASC',
				'sort_column' => 'post_title',
				'hierarchical' => 0,
			));
			
			foreach($pages as $page)
			{
				if($args['template'])
				{
					$template = get_page_template_slug($page->ID);

					if(is_array($args['template']) && !in_array($template, $args['template']))
						continue;

					if(!is_array($args['template']) && $template != $args['template'])
						continue;
				}

				$id = $page->post_name;
				$name = $page->post_title . ' (' . $sidebar['name'] . ')';

				register_sidebar(array(
					'name' => $name,
					'id' => "$id-" . $sidebar['id'],
					'description' => $sidebar['name'] . " for {$page->post_title}",
					'before_widget' => $sidebar['before_widget'],
					'after_widget' => $sidebar['after_widget'],
					'before_title' => $sidebar['before_title'],
					'after_title' => $sidebar['after_title'],
				));
			}
		}
		else
		{
			register_sidebar($sidebar);
		}
	}
}