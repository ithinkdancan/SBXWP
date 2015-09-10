<?php

namespace SBX;

class Term
{
	public $id;

	public $taxonomy;
	public $term;

	public function __construct($taxonomy, $term)
	{
		$this->taxonomy = $taxonomy;
		$this->term = $term;
		$this->id = $term->term_id;
	}
	
	public function get_title()
	{
		return $this->term->name;
	}
	
	public function get_parent()
	{
		if($this->term->parent)
			return $this->taxonomy->get_term($this->term->parent);
			
		return null;
	}
	
	public function get_slug()
	{
		return $this->term->slug;
	}
	
	public function get_url()
	{
		return get_term_link($this->term);
	}
	
	public function get_description()
	{
		return $this->term->description;
	}
	
	public function get_children()
	{
		return $this->taxonomy->get_term_children($this->id);
	}
}