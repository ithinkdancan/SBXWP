<?php

namespace SBX;

abstract class Shortcode
{

	public function __construct($shortcode)
	{
		add_shortcode($shortcode, array($this, 'do_render'));
	}

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

	public static function contains_shortcode($shortcode)
	{
		return preg_match('/\[' . $shortcode . '[\s\]\/]?/', $shortcode) !== 0;
	}

	public static function strip_shortcode($shortcode, $content)
	{
		return preg_replace('/\[\/?' . $shortcode . '\]/', '', $content);
	}
	
	public function do_render($atts, $content = null)
	{
		return Utils::stdout(array($this, 'render'), $atts, $content);
	}

	protected function to_array($comma_separated)
	{
		return preg_split('/\s*,\s*/', $comma_separated);
	}

	protected function to_boolean($att)
	{
		return $att === true || in_array(trim(strtolower($att)), array('yes', 'y', 'true'));
	}

	protected function extract_tag($tag, $content, $single = true)
	{
		preg_match('/<'. $tag .'>([^\[]+)<\/' . $tag . '>/', $content, $matches);
		
		if($single)
			return $matches[1];

		array_shift($matches);
		return $matches;
	}

	protected function extract_shortcode($content)
	{
		$regex = get_shortcode_regex();

		preg_match_all('/' . $regex . '/', $content, $matches);
		return isset($matches[0][0]) ? $matches : null;	
	}

	protected function is_shortcode($name, $shortcode)
	{
		$regex = get_shortcode_regex();

		preg_match_all('/' . $regex . '/', $shortcode, $match);
		
		return ($match[2][0] == $name);
	}

	protected function remove_tags($tag, $content)
	{
		return preg_replace('/<\/?' . $tag . '>/', '', $content);
	}

	protected function remove_empty_paragraphs($content)
	{
		return preg_replace('/<p>[\s\n\t]*<\/p>/', '', $content);
	}

	protected function get_shortcodes($text, $single = false)
	{
		$regex = get_shortcode_regex();

		preg_match_all('/' . $regex . '/s', $text, $matches);

		if(isset($matches[0]))
			return $single ? $matches[0][0] : $matches[0];

		return null;
	}

	abstract public function render($atts, $content = null);
}