<?php

namespace SBX\MetaBox;

use SBX\MetaBox;
use \MultiPostThumbnails;

class ImageBox extends MetaBox
{
	public function __construct($cpt, $args = array())
	{
		if(!class_exists('MultiPostThumbnails'))
			throw new Exception('This class requires the MultiPostThumbnails plugin');

		parent::__construct($cpt, $args);
	}

	protected function init()
	{
		new MultiPostThumbnails(array(
			'label' => $this->get_title(),
			'id' => $this->get_field('image'),
			'post_type' => $this->cpt,
			'context' => $this->get_arg('context'),
			'priority' => $this->get_arg('priority')
		));
	}

	public function render($post, $metabox) {}
	public function set_value($post_id, $key, $value) {}

	public function get_value($post_id, $key)
	{
		return MultiPostThumbnails::get_post_thumbnail_url($this->cpt, $this->get_field('image'), $post_id); 
	}
}