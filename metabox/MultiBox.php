<?php

namespace SBX\MetaBox;

class MultiBox extends CustomBox
{
	public function __construct($cpt, $args = array())
	{
		/*
		$fields = array(
			array(
				'type' => 'textarea',
				'label' => 'Some Label',
				'name' => 'somefieldname'
			),
			array(
				'type' => 'input',
				'label' => 'Some Label',
				'name' => 'somefieldname'
			)
		);
		*/

		parent::__construct($cpt, $args);
	}

	public function get_values($post_id)
	{
		$fields = $this->get_arg('fields');
		$values = array();

		foreach($fields as $field)
			$values[$field['name']] = $this->get_value($post_id, $field['name']);

		return $values;
	}
		
	public function render($post, $metabox)
	{
		$fields = $this->get_arg('fields', array());

		foreach($fields as $field)
		{
			$name = $this->get_field($field['name']);
			call_user_func(array($this, 'render_' . $field['type']), $post, $field);
		}		
	}

	protected function render_select($post, $field_info)
	{
		$field = $this->get_field($field_info['name']);
		$value = $this->get_value($post->ID, $field_info['name']);
		$label = isset($field_info['label']) ? $field_info['label'] : '';
		$options = $field_info['options'];

		?>
		<p>
			<?php if($label) : ?>
			<label>
				<?php echo $label; ?>
			<?php endif; ?>
			<select class="widefat" name="<?php echo $field; ?>">
				<?php foreach($options as $key => $label) : ?>
					<option value="<?php echo $key; ?>" <?php echo $key == $value ? 'selected' : ''; ?>><?php echo $label; ?></option>
				<?php endforeach; ?>

			</select>
			<?php if($label) : ?>
			</label>
			<?php endif; ?>
		</p>		
		<?php
	}

	protected function render_textarea($post, $field_info)
	{
		$field = $this->get_field($field_info['name']);
		$value = $this->get_value($post->ID, $field_info['name']);
		$label = isset($field_info['label']) ? $field_info['label'] : '';

		?>
		<p>
			<?php if($label) : ?>
			<label>
				<?php echo $label; ?>
			<?php endif; ?>
			<textarea class="widefat" rows="8" name="<?php echo $field; ?>"><?php echo $value; ?></textarea>
			<?php if($label) : ?>
			</label>
			<?php endif; ?>
		</p>		
		<?php
	}

	protected function render_input($post, $field_info)
	{
		$field = $this->get_field($field_info['name']);
		$value = $this->get_value($post->ID, $field_info['name']);
		$label = isset($field_info['label']) ? $field_info['label'] : '';

		?>
		<p>
			<?php if($label) : ?>
			<label>
				<?php echo $label; ?>
			<?php endif; ?>
			<input class="widefat" name="<?php echo $field; ?>" type="text" value="<?php echo htmlentities($value); ?>">
			<?php if($label) : ?>
			</label>
			<?php endif; ?>
		</p>		
		<?php
	}

	protected function render_date($post, $field_info)
	{
		$field = $this->get_field($field_info['name']);
		$value = $this->get_value($post->ID, $field_info['name']);
		$label = isset($field_info['label']) ? $field_info['label'] : '';

		?>
		<p>
			<?php if($label) : ?>
			<label>
				<?php echo $label; ?>
			<?php endif; ?>
			<input class="widefat cmb_datepicker" name="<?php echo $field; ?>" type="text" value="<?php echo $value ? htmlentities(date( 'm\/d\/Y', $value )) : ''; ?>">
			<?php if($label) : ?>
			</label>
			<?php endif; ?>
		</p>		
		<?php
	}

	protected function render_checkbox($post, $field_info)
	{
		$field = $this->get_field($field_info['name']);
		$value = $this->get_value($post->ID, $field_info['name']);
		$label = isset($field_info['label']) ? $field_info['label'] : '';

		?>
		<p>
			<input type="checkbox" name="<?php echo $field; ?>" value="1" <?php echo $value == '1' ? 'checked' : ''; ?>>
			<?php if($label) : ?>
				<label><?php echo $label; ?></label>
			<?php endif; ?>
		</p>		
		<?php
	}
}