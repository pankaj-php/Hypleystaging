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

$selected = '';
// Get selected value
if ( isset( $field['value'] ) ) {
	$selected = $field['value'];
} elseif ( isset( $field['default']) && is_int( $field['default'] ) ) {
	$selected = $field['default'];
} elseif ( ! empty( $field['default'] ) && ( $term = get_term_by( 'slug', $field['default'], $field['taxonomy'] ) ) ) {

	$selected = $term->term_id;
} 

// Select only supports 1 value
if ( is_array( $selected ) && $multi == false ) {
	$selected = current( $selected );
}
$taxonomy = get_taxonomy($field['taxonomy']);





$ip_address = $_SERVER['REMOTE_ADDR'];

if($ip_address == "123.201.19.159")
{
	echo "<pre>";
		print_r('test');
	echo "</pre>";	  
}
else
{
	wp_dropdown_categories( apply_filters( 'listeo_core_term_select_field_wp_dropdown_categories_args', array(
	'taxonomy'         => $field['taxonomy'],
	'hierarchical'     => 1,
	'multiple'   	   => $multi,
	'show_option_all'  => false,
	'show_option_none' => (isset($field['required']) && $field['required'] == true) ? '' : __('Choose ','listeo_core'). $taxonomy->labels->singular_name,
	'name'             => (isset( $field['name'] ) ? $field['name'] : $key),
	'orderby'          => 'name',
	'selected'         => $selected,
	'class'			   => 'chosen-select-no-single',
	'hide_empty'       => false,
	 'walker'  => new Willy_Walker_CategoryDropdown()
), $key, $field ) );
}