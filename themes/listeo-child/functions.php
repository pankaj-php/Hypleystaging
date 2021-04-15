<?php 
add_action( 'wp_enqueue_scripts', 'listeo_enqueue_styles');
function listeo_enqueue_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css',array('bootstrap','listeo-icons','listeo-woocommerce') ); 
    wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/css/cristian_style.css');
    //wp_enqueue_style( 'child-style-2', get_stylesheet_directory_uri() . '/css/sahil_style.css');
    
} 
// add_action( 'wp_enqueue_scripts', 'listeo_cristian_behind_scripts', 9999);
add_action( 'wp_head', 'listeo_cristian_behind_scripts', 9999);
function listeo_cristian_behind_scripts() {
	//dequeue frontend js because send message with widget has error
	//wp_dequeue_script('listeo_core-frontend');
    //wp_deregister_script('listeo_core-frontend');
    //wp_register_script( 'listeo_core-frontend', get_stylesheet_directory_uri() . '/js/frontend.js', array( 'jquery' ));
	//wp_enqueue_script('listeo_core-frontend');
    
	// wp_dequeue_script('daterangerpicker');
 //    wp_deregister_script('daterangerpicker');
    // wp_register_script( 'daterangerpicker', 'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js', array( 'jquery','moment' ) );
	// wp_enqueue_script('daterangerpicker');  
    
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

function mz_footer(){
	
	if(isset($_GET['page_id']) && $_GET['page_id'] == 71){
		
	?>

	<script>
		
		jQuery(document).ready(function(){

			setTimeout(function(){

				jQuery('p#_gallery-description').html('Photo are the first thing that guests will see. We recommend adding 10 or more high quality photos.<br>Photo requirments:<br><ul><li>High resolution - Atleast 1,000 pixels wide</li><li>Horizontal orientation - No vertical photos</li><li>Color photos - No block & white</li><li>Mics. - No collages, screenshots, No watermarks</li></ul>');

			},200);

		});

	</script>

<?php
		
	}

	if(is_page(66)){
	    ?>
	    <script>
	        jQuery(document).ready(function() {
	        	if(jQuery(".message-content").length){
				    jQuery(".message-content").animate({ 
				        scrollTop: jQuery('.message-content').get(0).scrollHeight 
				    }, 2000);
				}
			});
	    </script>
	    <?php
	}
}

add_action('wp_footer','mz_footer');

function whero_limit_image_size($file) {

	// Calculate the image size in KB
	$image_size = $file['size']/1024;

	// File size limit in KB
	$limit = 200;

	// Check if it's an image
	$is_image = strpos($file['type'], 'image');

	if ( ( $image_size > $limit ) && ($is_image !== false) )
        	$file['error'] = 'Your picture is too large. It has to be smaller than '. $limit .'KB';

	return $file;

}
//add_filter('wp_handle_upload_prefilter', 'whero_limit_image_size');


add_action("widgets_init","register_unveryfie_siderbar");
    function register_unveryfie_siderbar()
    {
      register_sidebar(array(
      'name' => 'Single Unveryfie Listing Sidebar',
      'id' => 'single_unveryfie_siderbar',
      'before_widget' => '<div id="%1$s" class="widget %2$s">',
      'after_widget' => '</div>',
      'before_title' => '<h3>',
      'after_title' => '</h3>'
       ));
    }
// // keep users logged in for longer in wordpress
// function wcs_users_logged_in_longer( $expirein ) {
//     // 1 month in seconds
//     return 2628000;
// }
// add_filter( 'auth_cookie_expiration', 'wcs_users_logged_in_longer' );