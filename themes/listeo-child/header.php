<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package listeo
 */

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="profile" href="http://gmpg.org/xfn/11">

<script type="text/javascript" src="<?php echo site_url();?>/wp-content/themes/listeo-child/js/counterup.js"></script>
<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php if ( function_exists( 'gtm4wp_the_gtm_tag' ) ) { gtm4wp_the_gtm_tag(); } ?>

<?php wp_body_open(); ?>	
<!-- Wrapper -->
<div id="wrapper">
	
<?php

 do_action('listeo_after_wrapper'); ?>
<?php 
$header_layout = get_option('listeo_header_layout') ;

$sticky = get_option('listeo_sticky_header') ;

if(is_singular()){

	$header_layout_single = get_post_meta($post->ID, 'listeo_header_layout', TRUE); 

	switch ($header_layout_single) {
		case 'on':
		case 'enable':
			$header_layout = 'fullwidth';
			break;

		case 'disable':
			$header_layout = false;
			break;	

		case 'use_global':
			$header_layout = get_option('listeo_header_layout'); 
			break;
		
		default:
			$header_layout = get_option('listeo_header_layout'); 
			break;
	}


	$sticky_single = get_post_meta($post->ID, 'listeo_sticky_header', TRUE); 
	switch ($sticky_single) {
		case 'on':
		case 'enable':
			$sticky = true;
			break;

		case 'disable':
			$sticky = false;
			break;	

		case 'use_global':
			$sticky = get_option('listeo_sticky_header'); 
			break;
		
		default:
			$sticky = get_option('listeo_sticky_header'); 
			break;
	}
	if(is_singular('listing')){
		$sticky = false;
	}
	
}


$header_layout = apply_filters('listeo_header_layout_filter',$header_layout);
$sticky = apply_filters('listeo_sticky_header_filter',$sticky); 
?>
<!-- Header Container
================================================== -->
<header id="header-container" class="<?php echo esc_attr(($sticky == true || $sticky == 1) ? "sticky-header" : ''); ?> <?php echo esc_attr($header_layout); ?>">

	<!-- Header -->
	<div id="header">
		<div class="container">
		<?php 
				$logo = get_option( 'pp_logo_upload', '' ); 
				$logo_transparent = get_option( 'pp_dashboard_logo_upload', '' ); 
			 ?>
			<!-- Left Side Content -->
			<div class="left-side" >
				<div id="logo" data-logo-transparent="<?php echo esc_attr($logo_transparent); ?>" data-logo="<?php echo esc_attr($logo); ?>" >
					<?php 
		                $logo = get_option( 'pp_logo_upload', '' ); 
		                if(( is_page_template('template-home-search.php') || is_page_template('template-home-search-splash.php') )  && (get_option('listeo_home_transparent_header') == 'enable')){
		                	$logo = get_option( 'pp_dashboard_logo_upload', '' ); 
		                }
		                $logo_retina = get_option( 'pp_retina_logo_upload', '' ); 
		             	if($logo) {
		                    if(is_front_page()){ ?>
		                    <a href="<?php echo esc_url( 'www.hypley.com.au' ); ?>" title="<?php echo esc_attr(get_bloginfo('name', 'display')); ?>" rel="home"><img src="<?php echo esc_url($logo); ?>" data-rjs="<?php echo esc_url($logo_retina); ?>" alt="<?php esc_attr(bloginfo('name')); ?>" width="125" height="40" /></a>
		                    <?php } else { ?>
		                    <a href="<?php echo esc_url( 'www.hypley.com.au' ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><img src="<?php echo esc_url($logo); ?>" data-rjs="<?php echo esc_url($logo_retina); ?>" alt="<?php esc_attr(bloginfo('name')); ?>" width="125" height="40"/></a>
		                    <?php }
		                } else {
		                    if(is_front_page()) { ?>
		                    <h1><a href="<?php echo esc_url( 'www.hypley.com.au' ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
		                    <?php } else { ?>
		                    <h2><a href="<?php echo esc_url( 'www.hypley.com.au' ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h2>
		                    <?php }
		                }
	                ?>
                </div>
              
				
				<!-- Mobile Navigation -->
				<div class="mmenu-trigger <?php if (wp_nav_menu( array( 'theme_location' => 'primary', 'echo' => false )) == false) { ?> hidden-burger <?php } ?>">
					<button class="hamburger hamburger--collapse" type="button">
						<span class="hamburger-box">
							<span class="hamburger-inner"></span>
						</span>
					</button>
				</div>
				


				<!-- Main Navigation -->
				<nav id="navigation" class="style-1">
					<?php wp_nav_menu( array( 
							'theme_location' => 'primary', 
							'menu_id' => 'responsive', 
							'container' => false,
							'fallback_cb' => 'listeo_fallback_menu',
							'walker' => new listeo_megamenu_walker
					) );  ?>
				</nav>


				<div class="clearfix"></div>
				<!-- Main Navigation / End -->
				
			</div>
			
			<!-- Left Side Content / End -->
			<?php 
			
			$my_account_display = get_option('listeo_my_account_display', true );
			$submit_display = get_option('listeo_submit_display', true );
			
			if($my_account_display != false || $submit_display != false ) :	?> 
			<!-- Right Side Content / End -->

			<div class="right-side">
				<div class="header-widget">
					<?php 
					if(class_exists('Listeo_Core_Template_Loader')):
						$template_loader = new Listeo_Core_Template_Loader;		
						$template_loader->get_template_part( 'account/logged_section' ); 
					endif;
					?>
				</div>
			</div>

			<!-- Right Side Content / End -->
			<?php endif; ?>
			
		</div>
	</div>
	<!-- Header / End -->

</header>

<?php 
if( true == $my_account_display && !is_page_template( 'template-dashboard.php' ) ) : ?>
	<!-- Sign In Popup -->
	<div id="sign-in-dialog" class="zoom-anim-dialog mfp-hide">

		<div class="small-dialog-header">
			<h3><?php esc_html_e('Sign In','listeo'); ?></h3>
		</div>
		<!--Tabs -->
		<div class="sign-in-form style-1"> 
			<?php do_action('listeo_login_form'); ?>
		</div>
	</div>
	<!-- Sign In Popup / End -->
<?php endif; ?>
<div class="clearfix"></div>
<!-- Header Container / End -->

<script type="text/javascript">
	jQuery( document ).ready(function() {
		<?php 
		if(is_page(66)){
			?>		
			setTimeout(function(){ jQuery('.sign_in_li').trigger('click'); }, 200);
			<?php
		}
		else{
			?>
			setTimeout(function(){ jQuery('.sign_up_li').trigger('click'); }, 200);
		<?php
		}
		?>
		jQuery('.btn.btn-mo.btn-block.btn-social.btn-customtheme.btn-custom-dec.login-button').attr("style","");
		jQuery('.mofa.mofa-google').css("margin-top","5px");
		jQuery('.mofa.mofa-facebook').css("margin-top","5px");
	});
</script>

<script>
jQuery(document).on('click','.sign_in_link',function(){
        jQuery('.sign_up_li').removeClass('active');
		 jQuery('.sign_in_li').addClass('active');
        //jQuery('.sign_up_li').trigger('click');
	});

jQuery(document).on('click','.sign_up_link',function(){
        jQuery('.sign_in_li').removeClass('active');
        jQuery('.sign_up_li').addClass('active');
       // jQuery('.sign_up_li').trigger('click');
});	
</script>
<script type="text/javascript">
$(function(){
  $('div #media-uploader a').click(function(){
    alert($(this).attr('href'));
   
  });
});
</script>
