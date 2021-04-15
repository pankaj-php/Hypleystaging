<?php 
add_action( 'wp_enqueue_scripts', 'listeo_enqueue_styles' );
function listeo_enqueue_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css',array('bootstrap','listeo-icons','listeo-woocommerce') );
    wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/css/cristian_style.css');
    //dequeue frontend js because send message with widget has error
    wp_dequeue_script('listeo_core-frontend');
    //wp_deregister_script('listeo_core-frontend');
    wp_register_script( 'listeo_core-frontend', get_stylesheet_directory_uri() . '/js/frontend-new.js', array( 'jquery' ));
    wp_enqueue_script('listeo_core-frontend');
    wp_register_script( 'cristian_script', get_stylesheet_directory_uri() . '/js/cristian_script.js', array( 'jquery' ));
    wp_enqueue_script('cristian_script');
    
} 

 
function remove_parent_theme_features() {
   	
}
add_action( 'after_setup_theme', 'remove_parent_theme_features', 10 );

function listing_category_slider(){
	$termArray = get_terms( array(
	    'taxonomy' => 'listing_category',
	    'hide_empty' => false,
	) );
	$html  = '<link rel="stylesheet" href="'.site_url() . '/wp-content/themes/listeo-child/css/flexslider.css"/>
				<script type="text/javascript" src="'.site_url(). '/wp-content/themes/listeo-child/js/jquery.flexslider-min.js"></script>	
	<div class="flexslider">
  			<ul class="slides">';
	foreach ($termArray as $singleTerm) {
		$metaData = get_term_meta($singleTerm->term_id);
		$coverImageID = $metaData['_cover'][0];
		$coverImage = wp_get_attachment_image_src($coverImageID, array('784','500'));
		if ($coverImage) : 
			$html .='<li><img src="'.$coverImage[0].'" /></li>';		
		endif; 
	}
	
	$html .='</ul></div>';
	$html .= '<script>
jQuery(window).load(function() {
  jQuery(".flexslider").flexslider({
    animation: "slide",
    controlNav: false
  });
});</script>';
	return $html;
}
add_shortcode( 'listing-category', 'listing_category_slider' );
if (!is_admin()) {
    add_filter( 'script_loader_tag', function ( $tag, $handle ) {    
        if ( strpos( $tag, "jquery-migrate.min.js" ) || strpos( $tag, "jquery.js") ) {
            return $tag;
        }
        return str_replace( ' src', ' defer src', $tag );
    }, 10, 2 );
}