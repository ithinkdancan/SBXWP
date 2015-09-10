<?php

namespace SBX;

class Utils
{
	public static function to_array($data)
	{
 		if (is_object($data)) $data = get_object_vars($data);
    	return is_array($data) ? array_map(array('SBX\\Utils', 'to_array'), $data) : $data;
	}
	
	public static function insert_after($array, $key, $newarray)
	{
		$new = array();
		
		foreach($array as $arrkey => $arrvalue)
		{
			$new[$arrkey] = $arrvalue;
			
			if($arrkey == $key)
			{
				foreach($newarray as $newkey => $newvalue)
				{
					$new[$newkey] = $newvalue;
				}
			}
		}

		return $new;
	}
	
	public static function insert_before($array, $key, $newarray)
	{
		$new = array();
		
		foreach($array as $arrkey => $arrvalue)
		{
			if($arrkey == $key)
			{
				foreach($newarray as $newkey => $newvalue)
				{
					$new[$newkey] = $newvalue;
				}
			}
			
			$new[$arrkey] = $arrvalue;
		}

		return $new;
	}
	
	public static function stdout($func_ref)
	{
		//Used to capture the std output of a function call that simply echo's content.
		//(like wordpresses language_attributes)
		ob_start();
		$args = func_get_args(); //get all params passed in so we can pass them along
		array_shift($args); //remove func_ref param
		call_user_func_array($func_ref, $args);
		$res = ob_get_contents(); //get result of function call that went to stdout instead of being returned
		ob_end_clean();

		return $res;
	}
}
?>