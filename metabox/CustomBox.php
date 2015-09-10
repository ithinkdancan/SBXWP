<?php

namespace SBX\MetaBox;

use SBX\MetaBox;

abstract class CustomBox extends MetaBox
{
	public function __construct($cpt, $args = array())
	{
		parent::__construct($cpt, $args);
	}

	protected function init()
	{
		add_action('add_meta_boxes_' . $this->cpt, array($this, 'add_meta_box'));
		add_action('save_post', array($this, 'save'));
	}
	
	public function get_nonce_field()
	{
		return $this->get_field('nonce');
	}
	
	public function get_value($post_id, $key)
	{
		return get_post_meta($post_id, $this->get_field($key), true);
	}
	
	public function set_value($post_id, $key, $value)
	{
		update_post_meta($post_id, $this->get_field($key), $value);
	}
	
	public function add_meta_box()
	{
		add_meta_box($this->field, $this->get_title(), array($this, 'create_meta_box'), $this->cpt, $this->args['context'], $this->args['priority']);
	}
	
	public function create_meta_box($post, $metabox)
	{	
		$this->render($post, $metabox);
		$this->add_nonce();
	}
	
	/* When the post is saved, saves our custom data */
	public function save($post_id)
	{
		// verify if this is an auto save routine. 
		// If it is our form has not been submitted, so we dont want to do anything
		if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) 
			return;
	
		// verify this came from the our screen and with proper authorization,
		// because save_post can be triggered at other times
		$nonce = $this->get_nonce_field();
		if(!isset($_POST[$nonce]) || !wp_verify_nonce($_POST[$nonce], $this->cpt))
			return;
	
		// OK, we're authenticated: we need to find and save the data
		foreach($_POST as $key => $value)
		{	
			if(strpos($key, $this->field) !== false && $key != $this->get_nonce_field())
			{

				//Get the field type
				$field_name = str_replace($this->field.'_', '', $key);
				$field_type = '';

				foreach ($this->args['fields'] as $field) {
					if($field_name == $field['name']){
						$field_type = $field['type'];
					}
				}
				
				//apply filters based on field type
				if($field_type == 'date'){
					$value = strtotime($value);
				}

				update_post_meta($post_id, $key, $value);
			}
		}
		
		// now we need to delete any values that aren't in the POST
		$keys = get_post_custom_keys($post_id);
		
		if(!count($keys)) return;
		
		foreach($keys as $key)
		{
			if(strpos($key, $this->field) !== false && !isset($_POST[$key]))
			{
				delete_post_meta($post_id, $key);
			}
		}
	}
	
	public function add_nonce()
	{
		// add nonce
		$nonce = $this->get_nonce_field();
		wp_nonce_field($this->cpt, $nonce);
	}
}