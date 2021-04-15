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
<link href="https://fonts.googleapis.com/css?family=Varela+Round" rel="stylesheet">

<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<!-- Wrapper -->
<div id="wrapper">
<?php do_action('listeo_after_wrapper'); ?>

<!-- Header Container
================================================== -->
<header id="header-container" class="fixed fullwidth">


	<!-- Header -->
	<div id="header">
		<div class="container">
			
			<!-- Left Side Content -->
			<div class="left-side" >
				<div id="logo">
					<?php 
		                $logo = get_option( 'pp_logo_upload', '' ); 
		                $logo_retina = get_option( 'pp_retina_logo_upload', '' ); 
		             	if($logo) {
		                    if(is_front_page()){ ?>
		                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr(get_bloginfo('name', 'display')); ?>" rel="home"><img src="<?php echo esc_url($logo); ?>" data-rjs="<?php echo esc_url($logo_retina); ?>" alt="<?php esc_attr(bloginfo('name')); ?>"/></a>
		                    <?php } else { ?>
		                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><img src="<?php echo esc_url($logo); ?>" data-rjs="<?php echo esc_url($logo_retina); ?>" alt="<?php esc_attr(bloginfo('name')); ?>"/></a>
		                    <?php }
		                } else {
		                    if(is_front_page()) { ?>
		                    <h1><a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
		                    <?php } else { ?>
		                    <h2><a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h2>
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
					<?php if( true == $my_account_display) : ?>
					
						<?php if ( is_user_logged_in() ) { 
								$current_user = wp_get_current_user();
								$roles = $current_user->roles;
								$role = array_shift( $roles ); 
								if(!empty($current_user->user_firstname)){
									$name = $current_user->user_firstname;
								} else {
									$name =  $current_user->display_name;
								}
						?>
						<div class="user-menu">
							<div class="user-name"><span><?php echo get_avatar( $current_user->user_email, 32 );?></span><?php esc_html_e('My Account','listeo') ?></div>
							<ul>
							<?php if(!in_array($role,array('owner'))) : ?>
								<?php $user_bookings_page = get_option('listeo_user_bookings_page');  if( $user_bookings_page ) : ?>
								<li <?php if( $post->ID == $user_bookings_page ) : ?>class="active" <?php endif; ?>><a href="<?php echo esc_url(get_permalink($user_bookings_page)); ?>"><i class="fa fa-calendar-check-o"></i> <?php esc_html_e('My Bookings','listeo');?></a></li>
								<?php endif; ?>
							<?php endif; ?>
							<?php if(in_array($role,array('administrator','admin','owner'))) : ?>
								<?php $dashboard_page = get_option('listeo_dashboard_page');  if( $dashboard_page ) : ?>
								<li><a href="<?php echo esc_url(get_permalink($dashboard_page)); ?>"><i class="sl sl-icon-settings"></i> <?php esc_html_e('Dashboard','listeo');?></a></li>
								<?php endif; ?>
							<?php endif; ?>
								<?php if(!in_array($role,array('owner'))) : ?>
									<?php  $reviews_page = get_option('listeo_reviews_page');  if( $reviews_page ) : ?>
									<li <?php if( $post->ID == $reviews_page ) : ?>class="active" <?php endif; ?>><a href="<?php echo esc_url(get_permalink($reviews_page)); ?>"><i class="sl sl-icon-star"></i> <?php esc_html_e('Reviews','listeo');?></a></li>
									<?php endif; ?>
								<?php endif; ?>

								<?php if(!in_array($role,array('owner'))) : ?>
									<?php $bookmarks_page = get_option('listeo_bookmarks_page');  if( $bookmarks_page ) : ?>
									<li <?php if( $post->ID == $bookmarks_page ) : ?>class="active" <?php endif; ?>><a href="<?php echo esc_url(get_permalink($bookmarks_page)); ?>"><i class="sl sl-icon-heart"></i> <?php esc_html_e('Bookmarks','listeo');?></a></li>
									<?php endif; ?>
								<?php endif; ?>
								
								<?php $messages_page = get_option('listeo_messages_page');  if( $messages_page ) : ?>
								<li <?php if( $post->ID == $messages_page ) : ?>class="active" <?php endif; ?>><a href="<?php echo esc_url(get_permalink($messages_page)); ?>"><i class="sl sl-icon-envelope-open"></i> <?php esc_html_e('Messages','listeo');?>
								<?php 
									$counter = listeo_get_unread_counter();
									if($counter) { ?>
									<span class="nav-tag messages"><?php echo esc_html($counter); ?></span>
									<?php } ?>
								</a></li>
								<?php endif; ?>
								
							<?php if(in_array($role,array('administrator','admin','owner'))) : ?>
								<?php $bookings_page = get_option('listeo_bookings_page');  if( $bookings_page ) : ?>
								<li <?php if( $post->ID == $bookings_page ) : ?>class="active" <?php endif; ?>><a href="<?php echo esc_url(get_permalink($bookings_page)); ?>/?status=waiting"><i class="fa fa-calendar-check-o"></i> <?php esc_html_e('Bookings','listeo');?></a></li>
								<?php endif; ?>
							<?php endif; ?>

							<?php if(!in_array($role,array('owner'))) : ?>
								<?php $profile_page = get_option('listeo_profile_page');  if( $profile_page ) : ?>
								<li <?php if( $post->ID == $profile_page ) : ?>class="active" <?php endif; ?>><a href="<?php echo esc_url(get_permalink($profile_page)); ?>"><i class="sl sl-icon-user"></i> <?php esc_html_e('My Profile','listeo');?></a></li>
								<?php endif; ?>
							<?php endif; ?>

								<li><a href="<?php echo wp_logout_url(home_url()); ?>"><i class="sl sl-icon-power"></i> <?php esc_html_e('Logout','listeo');?></a></li>
							</ul>
						</div>
						<?php } else { 
							$popup_login = get_option( 'listeo_popup_login' ); 
							$submit_page = get_option('listeo_submit_page');  
							if(function_exists('Listeo_Core')):
							if( $popup_login == 'ajax' && !is_page_template('template-dashboard.php') ) { ?>
								<a href="#sign-in-dialog" class="sign-in popup-with-zoom-anim"><i class="sl sl-icon-login"></i> <?php esc_html_e('Sign In', 'listeo'); ?></a>
							<?php } else {
								$login_page = get_option('listeo_profile_page') ?>
								<a href="<?php echo esc_url(get_permalink($login_page)); ?>" class="sign-in"><i class="sl sl-icon-login"></i> <?php esc_html_e('Sign In', 'listeo'); ?></a>
							<?php }
							endif; ?>
						<?php } ?>

					<?php endif; ?>
					<?php if( true == $submit_display) : ?>
						<?php if(is_user_logged_in()){ ?>
							<?php if(in_array($role,array('administrator','admin','owner'))) : ?>
								<?php $submit_page = get_option('listeo_submit_page');  if( $submit_page ) : ?>
									<a href="<?php echo esc_url(get_permalink($submit_page)); ?>" class="button border with-icon"><?php esc_html_e('Add Listing', 'listeo'); ?> <i class="sl sl-icon-plus"></i></a>
								<?php endif; ?>
							<?php else: ?>
								<?php $browse_page = get_post_type_archive_link( 'listing' ); ;  if( $browse_page ) : ?>
									<a href="<?php echo esc_url($browse_page); ?>" class="button border"><?php esc_html_e('Browse Listings', 'listeo'); ?></i></a>
								<?php endif; ?>
							<?php endif; ?>	
						<?php } else { ?>
								<?php $submit_page = get_option('listeo_submit_page');  if( $submit_page ) : ?>
									<a href="<?php echo esc_url(get_permalink($submit_page)); ?>" class="button border with-icon"><?php esc_html_e('Add Listing', 'listeo'); ?> <i class="sl sl-icon-plus"></i></a>
								<?php endif; ?>
						<?php } ?>
						
					<?php endif; ?>
				</div>
			</div>
			<!-- Right Side Content / End -->
			<?php endif; ?>
			
		</div>
	</div>
	<!-- Header / End -->

</header>
<?php 
if( true == $my_account_display) : ?>
<!-- Sign In Popup -->
<div id="sign-in-dialog" class="zoom-anim-dialog mfp-hide">

	<div class="small-dialog-header">
		<h3><?php esc_html_e('Sign In','listeo'); ?></h3>
	</div>
	<!--Tabs -->
	<div class="sign-in-form style-1"> 
		<?php  do_action('listeo_login_form'); ?>
	</div>
</div>
			<!-- Sign In Popup / End -->
<?php endif; ?>
<div class="clearfix"></div>
<!-- Header Container / End -->