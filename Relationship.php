<?php namespace SBX;

use  SBX\MetaBox\CustomBox;

class Relationship extends CustomBox
{
	public $related_cpt;
	protected static $relationships = array();

	public function __construct($cpt, $related_cpt, $args = null)
	{	

		static::$relationships[$cpt][$related_cpt->id] = $this;

		$this->related_cpt = $related_cpt;		
		$args = wp_parse_args($args, array('namespace' => "{$this->related_cpt->id}"));

		parent::__construct($cpt, $args);
	}
	public static function get_relationship($cpt, $related_cpt)
	{
		return isset(self::$relationships[$cpt][$related_cpt]) ? self::$relationships[$cpt][$related_cpt] : null;
	}

	public function add_meta_box()
	{
		add_meta_box($this->field, $this->related_cpt->get_label(), array($this, 'create_meta_box'), $this->cpt, $this->args['context'], $this->args['priority']);
	}

	public function is_multiple()
	{
		return $this->get_arg('multiple', false);
	}

	public function get_relationship_field()
	{
		return $this->get_field('rel');
	}
	
	public function get_relationship_value($post_id)
	{
		return $this->get_value($post_id, 'rel');
	}

	public function get_related_posts($post_id)
	{
		$cpt = CPT::get_cpt($this->cpt);

		$args = array(
			'posts_per_page' => -1,
			'post_type' => $cpt->id,
			'meta_key' => $this->get_relationship_field(),
			'meta_value' => $post_id
		);

		return $cpt->get_all($args);
	}

	public function render($post, $metabox)
	{
		$posts = $this->related_cpt->get_all();
		if($this->is_multiple())
		{
			$this->render_checkboxes($posts);
		}
		else
		{
			$this->render_select($posts);
		}
		
	}

	protected function get_indent($post)
	{

		$indent = '';
		$parent = get_post($post->post_parent);
		
		while($parent->post_parent)
		{
			$indent .= '--';
			$parent = get_post($parent->post_parent);
		}
		
		return $indent;
	}

	protected function render_select($posts)
	{
		global $post;
		
		$field = $this->get_relationship_field();
		$value = $this->get_relationship_value($post->ID, $field);
		
		?><select name="<?php echo $field; ?>"><option value="">Select a <?php echo $this->related_cpt->get_label(); ?></option><?php
		
		foreach($posts as $cp)
		{		
			$selected = $cp->ID == $value ? ' selected="selected"' : ''
			?><option value="<?php echo $cp->ID; ?>"<?php echo $selected; ?>><?php echo $this->get_indent($cp) . $this->related_cpt->get_title($cp); ?></option><?php
		}
		
		?></select><?php
	}

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
				if($key == $this->get_relationship_field()){
					update_post_meta($post_id, $key, $value);
				}
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



}