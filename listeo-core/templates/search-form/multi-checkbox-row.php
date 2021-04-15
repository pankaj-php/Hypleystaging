<div class="row <?php if(isset($data->css_class)) { echo esc_attr($data->css_class); }?> ">
<?php if(isset($data->dynamic) && $data->dynamic=='yes'){ ?>
	<div class="notification warning"><p><?php esc_html_e('Please choose category to display filters','listeo_core') ?></p> </div>
<?php } else {


if(isset($_GET[$data->name])) {
	$selected = $_GET[$data->name];
} else {
	$selected = array();
} 

if(isset($data->taxonomy) && !empty($data->taxonomy)) {
	$data->options = listeo_core_get_options_array('taxonomy',$data->taxonomy);
	$groups = array_chunk($data->options, 4, true);
	if(is_tax($data->taxonomy)){
		$selected[get_query_var($data->taxonomy)] = 'on';
	}	
	?>
	<div class="panel-checkboxes-container">
	<?php
	
	if(!is_array($selected)){

		$selected_arr = array();
		$selected_arr[$selected] = 'on';
		$selected = $selected_arr;
	}

	foreach ($groups as $group) { ?>
		
	
	<?php foreach ($group as $key => $value) {  	
		
		$second_level_terms = get_terms( array(
            'taxonomy' => $value['taxonomy'], // you could also use $taxonomy as defined in the first lines
            'child_of' => $value['id'],
            //'parent' => $value['id'], // disable this line to see more child elements (child-child-child-terms)
            'hide_empty' => false,
        ) );
	?>

		<div class="panel-checkbox-wrap" style="width:100%;">
			<input data-tex_id="<?php echo $value['id']; ?>" <?php if ($second_level_terms) { echo 'class="has_listeo_child_texononomy"'; }  ?>  <?php if ( array_key_exists ($value['slug'], $selected) ) { echo 'checked="checked"'; } ?> id="<?php echo esc_html($value['slug']) ?>" value="<?php echo esc_html($value['slug']) ?>" type="checkbox" name="<?php echo $data->name.'['.esc_html($value['slug']).']'; ?>">
			<label for="<?php echo esc_html($value['slug']) ?>"><?php echo esc_html($value['name']) ?></label>	
			<?php
						
		        if ($second_level_terms) {
		        	foreach ($second_level_terms as $second_level_term) {
		                ?>
		                	<div class="listeo_child_texononomy_wrap has_listeo_child_texononomy_<?php echo $value['id']; ?>">
		                		<input <?php if ( array_key_exists ($second_level_term->slug, $selected) ) { echo 'checked="checked"'; } ?> id="<?php echo esc_html($second_level_term->slug) ?>" value="<?php echo esc_html($second_level_term->slug) ?>" type="checkbox" name="<?php echo $data->name.'['.esc_html($second_level_term->slug).']'; ?>">
								<label for="<?php echo esc_html($second_level_term->slug) ?>"><?php echo esc_html($second_level_term->name) ?></label>
							</div>	
		                <?php
		                //echo $second_level_term->name;
		            }	
		        }	
			?>
		</div>
	<?php } ?>
		
<?php } ?>
	</div>
<?php }

if(isset($data->options_source) && empty($data->taxonomy) ) {
	if(isset($data->options_cb) && !empty($data->options_cb) ){
		switch ($data->options_cb) {
			case 'listeo_core_get_offer_types':
				$data->options = listeo_core_get_offer_types_flat(false);
				break;

			case 'listeo_core_get_listing_types':
				$data->options = listeo_core_get_listing_types();
				break;

			case 'listeo_core_get_rental_period':
				$data->options = listeo_core_get_rental_period();
				break;
		
			default:
				# code...
				break;
		}	
	}

	if($data->options_source == 'custom') {
		$data->options = array_flip($data->options);
	}
	foreach ($data->options as $key => $value) { ?>

		<input <?php if ( array_key_exists ($key, $selected) ) { echo 'checked="checked"'; } ?> id="<?php echo esc_html($key) ?>" type="checkbox" name="<?php echo $data->name.'['.esc_html($key).']'; ?>">
		<label for="<?php echo esc_html($key) ?>"><?php echo esc_html($value) ?></label>
	
<?php } 
}
}
?>


</div>