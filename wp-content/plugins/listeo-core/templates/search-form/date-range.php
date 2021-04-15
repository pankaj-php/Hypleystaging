<?php
if(isset($_GET[$data->name])) {
	$value = sanitize_text_field($_GET[$data->name]);
} else {
	$value = '';
} ?>


<div class="search-input-icon <?php if(isset($data->class)) { echo esc_attr($data->class); } ?> <?php if(isset($data->css_class)) { echo esc_attr($data->css_class); }?>">
	<input readonly="readonly" autocomplete="off" name="<?php echo esc_attr($data->name);?>" id="<?php echo esc_attr($data->name);?>" class="<?php echo esc_attr($data->name);?>" type="text" placeholder="<?php echo esc_attr($data->placeholder);?>" value="<?php if(isset($value)){ echo $value;  } ?>"/>
	<i class="fa fa-calendar"></i>
</div>
<input type="hidden" disabled="disabled" name="_listing_type" value="rental">