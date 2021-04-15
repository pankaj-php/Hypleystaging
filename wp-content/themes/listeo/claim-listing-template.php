<?php
/**
 * Template Name: claim-listing-template
 *
 * This is the template that displays claim listing page.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * @package WPVoyager
 */

if ( !is_user_logged_in() ) { 
		
$errors = array();

if ( isset( $_REQUEST['login'] ) ) {
    $error_codes = explode( ',', $_REQUEST['login'] );
 
    foreach ( $error_codes as $code ) {
       switch ( $code ) {
	        case 'empty_username':
	            $errors[] = esc_html__( 'You do have an email address, right?', 'listeo' );
	   		break;
	        case 'empty_password':
	            $errors[] =  esc_html__( 'You need to enter a password to login.', 'listeo' );
	   		break;
	        case 'invalid_username':
	            $errors[] =  esc_html__(
	                "We don't have any users with that email address. Maybe you used a different one when signing up?",
	                'listeo'
	            );
	   		break;
	        case 'incorrect_password':
	            $err = __(
	                "The password you entered wasn't quite right. <a href='%s'>Did you forget your password</a>?",
	                'listeo'
	            );
	            $errors[] =  sprintf( $err, wp_lostpassword_url() );
	 		break;
	        default:
	            break;
	    }
    }
} 
 // Retrieve possible errors from request parameters
if ( isset( $_REQUEST['register-errors'] ) ) {
    $error_codes = explode( ',', $_REQUEST['register-errors'] );
 
    foreach ( $error_codes as $error_code ) {
 		
         switch ( $error_code ) {
	        case 'email':
			     $errors[] = esc_html__( 'The email address you entered is not valid.', 'listeo' );
			   break;
			case 'email_exists':
			     $errors[] = esc_html__( 'An account exists with this email address.', 'listeo' );
			 	  break;
			case 'closed':
			     $errors[] = esc_html__( 'Registering new users is currently not allowed.', 'listeo' );
			     break;
	 		case 'captcha-no':
			     $errors[] = esc_html__( 'Please check reCAPTCHA checbox to register.', 'listeo' );
			     break;
			case 'captcha-fail':
			     $errors[] = esc_html__( "You're a bot, aren't you?.", 'listeo' );
			     break;
			case 'policy-fail':
			     $errors[] = esc_html__( "Please accept the Privacy Policy to register account.", 'listeo' );
			     break;
			case 'first_name':
			     $errors[] = esc_html__( "Please provide your first name", 'listeo' );
			     break;
			case 'last_name':
			     $errors[] = esc_html__( "Please provide your last name", 'listeo' );
			     break;
			case 'empty_user_login':
			     $errors[] = esc_html__( "Please provide your user login", 'listeo' );
			     break;
	 		case 'password-no':
			     $errors[] = esc_html__( "You have forgot about password.", 'listeo_core', 'listeo' );
			     break;
	        case 'incorrect_password':
	            $err = __(
	                "The password you entered wasn't quite right. <a href='%s'>Did you forget your password</a>?",
	                'listeo'
	            );
	            $errors[] =  sprintf( $err, wp_lostpassword_url() );
	   			break;
	        default:
	            break;
	    }
    }
} 
	get_header();

	$page_top = get_post_meta($post->ID, 'listeo_page_top', TRUE); 

	switch ($page_top) {
		case 'titlebar':
			get_template_part( 'template-parts/header','titlebar');
			break;		

		case 'parallax':
			get_template_part( 'template-parts/header','parallax');
			break;	

		case 'off':

			break;
		
		default:
			get_template_part( 'template-parts/header','titlebar');
			break;
	}

	$layout = get_post_meta($post->ID, 'listeo_page_layout', true); if(empty($layout)) { $layout = 'right-sidebar'; }
	$class  = ($layout !="full-width") ? "col-lg-9 col-md-8 padding-right-30" : "col-md-12"; ?>
	<div class="container <?php echo esc_attr($layout); ?>">

		<div class="row">

			<article id="post-<?php the_ID(); ?>" <?php post_class($class); ?>>
				<div class="col-lg-5 col-md-4 col-md-offset-3 sign-in-form style-1 margin-bottom-45">
					<?php if ( count( $errors ) > 0 ) : ?>
					    <?php foreach ( $errors  as $error ) : ?>
					        <div class="notification error closeable">
								<p><?php echo ($error); ?></p>
								<a class="close"></a>
							</div>
					    <?php endforeach; ?>
					<?php endif; ?>
					<?php if ( isset( $_REQUEST['registered'] ) ) : ?>
				    <div class="notification success closeable">
				    <p>
				        <?php
				        $password_field = get_option('listeo_display_password_field');
				        if($password_field) {
							printf(
				                esc_html__( 'You have successfully registered to %s.', 'listeo' ),
				                '<strong>'.get_bloginfo( 'name' ).'</strong>'
				            );
				        } else {
				        	printf(
				                esc_html__( 'You have successfully registered to <strong>%s</strong>. We have emailed your password to the email address you entered.', 'listeo' ),
				                '<strong>'.get_bloginfo( 'name' ).'</strong>'
				            );
				        }
				            
				        ?>
				    </p></div>
				<?php endif; ?>
					<?php  do_action('listeo_login_form');	 ?>
				</div>
				</article>
			
			<?php if($layout !="full-width") { ?>
				<div class="col-lg-3 col-md-4">
					<div class="sidebar right">
						<?php get_sidebar(); ?>
					</div>
				</div>
			<?php } ?>

		</div>

	</div>
	<div class="clearfix"></div> 
<?php
	get_footer(); 

} else { //is logged 

get_header();

while ( have_posts() ) : the_post();

	get_template_part( 'template-parts/content', 'page' );

endwhile; // End of the loop.

get_footer();

} 
?>