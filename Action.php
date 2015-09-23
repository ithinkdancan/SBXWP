<?php

namespace SBX;

abstract class Action
{

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

	public function __construct($action)
	{
		add_action($action, array($this, 'do_action'));
	}

	abstract protected function do_action($args);

}
