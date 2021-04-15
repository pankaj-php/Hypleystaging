<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
$field = $data->field;

$key = $data->key;

$multi = false;

if(isset($field['multi']) && $field['multi']) {
	$multi = true;
}

if(isset( $field['options_cb'] ) && !empty($field['options_cb'])){
	switch ($field['options_cb']) {
		case 'listeo_core_get_offer_types_flat':
			$field['options'] = listeo_core_get_offer_types_flat(false);
			break;

		case 'listeo_core_get_listing_types':
			$field['options'] = listeo_core_get_listing_types();
			break;

		case 'listeo_core_get_rental_period':
			$field['options'] = listeo_core_get_rental_period();
			break;
		
		default:
			# code...
			break;
	}	
}

?>

<select data-iamhere="yes" <?php if($multi) echo "multiple"; ?> class="<?php if($multi) echo "chosen-select-no-single"; ?> <?php echo esc_attr( isset( $field['class'] ) ? $field['class'] : $key ); ?>" name="<?php echo esc_attr( isset( $field['name'] ) ? $field['name'] : $key );  if($multi) echo "[]"; ?>" id="<?php echo esc_attr( $key ); ?>" <?php if ( ! empty( $field['required'] ) ) echo 'required'; ?>>
	
	<?php if(isset($field['placeholder']) && !empty($field['placeholder'])) : ?>
		<option value=""><?php echo esc_attr($field['placeholder']);?></option>
	<?php endif ?>

	<?php foreach ( $field['options'] as $key => $value ) : ?>

		<option value="<?php echo esc_attr( $key ); ?>" <?php

			if( isset( $field['value']) && is_array( $field['value'] ) ) {
				
				if(isset($field['value'][0]) && !empty($field['value'][0])){
				
					if(in_array($key, $field['value'][0])){
						echo 'selected="selected"';
				
					}	
				}

			} else {

				if ( isset( $field['value'] ) || isset( $field['default'] ) ) selected( isset( $field['value'] ) ? 
					$field['value'] : $field['default'], $key ); 
			}
			
			?>><?php echo esc_html( $value ); ?></option>

	<?php endforeach; ?>

</select>