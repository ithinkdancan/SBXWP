<?php namespace SBX;

abstract class AJAX
{
	protected $action_args = array();


	public function __construct($function, $args = array(), $private = false)
	{

		add_action("wp_ajax_$function", array($this, 'parse_action'));
		
		if(!$private){
			add_action("wp_ajax_nopriv_$function", array($this, 'parse_action'));
		}

		$this->action_args = $args;
		
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

	public function parse_action($value='')
	{

		$func_args = array();

		foreach($this->action_args as $arg)
		{
			$func_args[$arg] = isset($_REQUEST[$arg]) ? $_REQUEST[$arg] : null;
		}

		echo call_user_func(array($this, 'do_action'), $func_args);

		die();

	}

	abstract public function do_action($args);
}