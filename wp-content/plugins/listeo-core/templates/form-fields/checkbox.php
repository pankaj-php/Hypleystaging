<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
$field = $data->field;
$key = $data->key;
?>
<!-- Rounded switch -->

<?php
	if($field['name'] == "_count_per_guest" || $field['name'] == "_instant_booking"){
		?>
		<div class="switch_box box_1">
			<div class="cd-switch">
			  <div class="switch">	
			    <input value="on" type="radio" name="<?php echo esc_attr( isset( $field['name'] ) ? $field['name'] : $key ); ?>" id="<?php echo esc_attr( isset( $field['name'] ) ? $field['name'] : $key ); ?>">
			    <label for="<?php echo esc_attr( isset( $field['name'] ) ? $field['name'] : $key ); ?>">Yes</label>
			    <input type="radio" name="<?php echo esc_attr( isset( $field['name'] ) ? $field['name'] : $key ); ?>" id="no<?php echo esc_attr( isset( $field['name'] ) ? $field['name'] : $key ); ?>" checked>
			    <label for="no<?php echo esc_attr( isset( $field['name'] ) ? $field['name'] : $key ); ?>">No</label>
			    <span class="switchFilter"></span>
			  </div>
			</div>
</div>
			<label class="listeo_core-switch"></label>		
		<?php
	}
	else {
		?>
			<div class="switch_box box_1">
				<input type="checkbox" 
				class="input-checkbox switch_1" 

				name="<?php echo esc_attr( isset( $field['name'] ) ? $field['name'] : $key ); ?>"
				
				id="<?php echo esc_attr( $key ); ?>" 
				placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>" 
				value="on"
				<?php isset( $field['value'] ) ? checked($field['value'],'on') : ''; ?> 
				maxlength="<?php echo ! empty( $field['maxlength'] ) ? $field['maxlength'] : ''; ?>" 
				<?php if ( ! empty( $field['required'] ) ) echo 'required'; ?> 
				<?php if ( isset( $field['unit'] ) ) echo 'data-unit="'.$field['unit'].'"'; ?> 

				/>
			</div>

			<label class="listeo_core-switch"></label>

<?php } ?>