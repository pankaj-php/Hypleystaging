<?php
/**
 * Template Name: Registration-confirmation
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * @package WPVoyager
 */

get_header();

?>


<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header class="entry-header">
	</header>
	<div class="entry-content">
		<?php
		if(isset($_GET['key']) && isset($_GET['user_id']) ){
		   
		    $code = get_user_meta($_GET['user_id'],'activation_code', true);
		    
		    if($code == $_GET['key']){
		        global $wpdb;
		        $user_table_name = $wpdb->prefix.'users';
		        $wpdb->update( 
			    $user_table_name,
			     	array( 'user_status' => 1),
			     	array( 'ID' =>    $_GET['user_id'],
			       	'user_activation_key'=>$_GET['key']
			     	), 
			     	array( '%d')
				);
				
		        // update the user meta
		        update_user_meta($_GET['user_id'], 'account_activated', 1);
		    	//echo '<h5>Your email address has been activated!</h5>';
		    	echo '<div style="margin-top:5%;margin-bottom:5%">
					<h5 style="text-align: center;font-size: 18px;">Your email address has been activated!</h5>
				</div>';
		    }
		}
		?>
	</div>
	<footer class="entry-footer">
		<?php //listeo_entry_footer(); ?>
	</footer><!-- .entry-footer -->
</article>
<?php 
get_footer();
 
?>