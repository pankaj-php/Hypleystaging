<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$template_loader = new Listeo_Core_Template_Loader;

$listing_cat_url="#";

get_header(get_option('header_bar_style','standard') );

$gallery_style = get_post_meta( $post->ID, '_gallery_style', true );

if(empty($gallery_style)) { $gallery_style = get_option('listeo_gallery_type','top'); }

$count_gallery = listeo_count_gallery_items($post->ID);

if($count_gallery < 4 ){
	$gallery_style = 'content';	
}
if( get_post_meta( $post->ID, '_gallery_style', true ) == 'top' && $count_gallery == 1 ) {
	$gallery_style = 'none';	
}

if ( have_posts() ) :
if( $gallery_style == 'top' ) :
	$template_loader->get_template_part( 'single-partials/single-listing','gallery' );  
else: ?>


<!-- Gradient-->
<div class="single-listing-page-titlebar"></div>
<?php endif; ?>
<?php 
	function isMobile() {
		return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
	}
?>
<?php 



// 		$d = DateTime::createFromFormat('d-m-Y', $expires);
// 		echo $d->getTimestamp(); 
		?>
<!-- Content
================================================== -->
<style>
	.mobile-listing-review {
		display: none;
	}
	@media (max-width: 991px) {
		.mobile-listing-review {
			display: block;
			padding: 0 15px;
		}
		.desktop-listing-review {
			display: none;
		}
	}
</style>
<div class="container">
	<div class="row sticky-wrapper">
<?php while ( have_posts() ) : the_post();  ?>
		<div class="col-lg-8 col-md-8 padding-right-30">
			
			<!-- Titlebar -->
			<div id="titlebar" class="listing-titlebar">
				<div class="listing-titlebar-title">
					<h2><?php the_title(); ?>
					<?php
					$terms = get_the_terms( get_the_ID(), 'listing_category' );
					if ( $terms && ! is_wp_error( $terms ) ) : 
					    $categories = array();
					    foreach ( $terms as $term ) {
					        
					        $categories[] = sprintf( '<a href="%1$s">%2$s</a>',
                    			esc_url( get_term_link( $term->slug, 'listing_category' ) ),
                    			esc_html( $term->name )
                			);

                			$listing_cat_url = $term->slug;
					    }

					    $categories_list = join( ", ", $categories );
					    ?>
					    <span class="listing-tag">
					        <?php  echo ( $categories_list ) ?>
					    </span>
					<?php endif; ?>
					<?php $listing_type = get_post_meta( get_the_ID(), '_listing_type', true);
					switch ($listing_type) {
					 	case 'service':
					 		$type_terms = get_the_terms( get_the_ID(), 'service_category' );
					 		$taxonomy_name = 'service_category';
					 		break;
					 	case 'rental':
					 		$type_terms = get_the_terms( get_the_ID(), 'rental_category' );
					 		$taxonomy_name = 'rental_category';
					 		break;
					 	case 'event':
					 		$type_terms = get_the_terms( get_the_ID(), 'event_category' );
					 		$taxonomy_name = 'event_category';
					 		break;
					 	
					 	default:
					 		# code...
					 		break;
					 } 
					 if( isset($type_terms) ) {
					 	if ( $type_terms && ! is_wp_error( $type_terms ) ) : 
					    $categories = array();
					    foreach ( $type_terms as $term ) {
					        $categories[] = sprintf( '<a href="%1$s">%2$s</a>',
                    			esc_url( get_term_link( $term->slug, $taxonomy_name ) ),
                    			esc_html( $term->name )
                			);
					    }

					    $categories_list = join( ", ", $categories );
					    ?>
					    <span class="listing-tag">
					        <?php  echo ( $categories_list ) ?>
					    </span>
						<?php endif;
					 }
					 ?>
					</h2>
					<?php if(get_the_listing_address()): ?>
						<span>
							<a href="#listing-location" class="listing-address">
								<i class="fa fa-map-marker"></i>
								<?php the_listing_address(); ?>
							</a>
						</span>
					<?php endif; ?>
					<?php
						if(!get_option('listeo_disable_reviews')){
							 $rating = get_post_meta($post->ID, 'listeo-avg-rating', true); 
								if(isset($rating) && $rating > 0 ) : 
									$rating_type = get_option('listeo_rating_type','star');
									if($rating_type == 'numerical') { ?>
										<div class="numerical-rating" data-rating="<?php $rating_value = esc_attr(round($rating,1)); printf("%0.1f",$rating_value); ?>">
									<?php } else { ?>
										<div class="star-rating" data-rating="<?php echo $rating; ?>">
									<?php } ?>
									<?php $number = listeo_get_reviews_number($post->ID);  ?>
									<div class="rating-counter"><a href="#listing-reviews">(<?php printf( _n( '%s review', '%s reviews', $number,'listeo_core' ), number_format_i18n( $number ) );  ?>)</a></div>
								</div>
							<?php endif; 
						}
					?>
				</div>

			</div>
			<!-- Content
			================================================== -->
			<?php 
			if($gallery_style == 'none' ) :
				$gallery = get_post_meta( $post->ID, '_gallery', true );
				if(!empty($gallery)) : 

					foreach ( (array) $gallery as $attachment_id => $attachment_url ) {
						$image = wp_get_attachment_image_src( $attachment_id, 'listeo-gallery' );
						echo '<img src="'.esc_url($image[0]).'" class="single-gallery margin-bottom-40" style="margin-top:-30px;"></a>';
					}
					
				 endif;
			 endif; ?>

			<!-- Listing Nav -->
			<div id="listing-nav" class="listing-nav-container">
				<ul class="listing-nav">
					<li><a href="#listing-overview" class="active"><?php esc_html_e('Overview','listeo_core'); ?></a></li>
					<?php if($count_gallery > 0 && $gallery_style == 'content') : ?><li><a href="#listing-gallery"><?php esc_html_e('Gallery','listeo_core'); ?></a></li>
					<?php endif; 
					$_menu = get_post_meta( get_the_ID(), '_menu_status', 1 ); 

					if(!empty($_menu)) {
						$_bookable_show_menu =  get_post_meta(get_the_ID(), '_hide_pricing_if_bookable',true);
						if(!$_bookable_show_menu){ ?>
							<li><a href="#listing-pricing-list"><?php esc_html_e('Pricing','listeo_core'); ?></a></li>
						<?php } ?>
						
					<?php } 

					$video = get_post_meta( $post->ID, '_video', true ); 
					if(!empty($video)) :  ?>
						<li><a href="#listing-video"><?php esc_html_e('Video','listeo_core'); ?></a></li>
					<?php endif;
					$latitude = get_post_meta( $post->ID, '_geolocation_lat', true ); 
					if(!empty($latitude)) :  ?>
					<li><a href="#listing-location"><?php esc_html_e('Location','listeo_core'); ?></a></li>
					<?php 
					endif;
                    echo 'tetxt asd here';
					if(get_post_meta( $post->ID, '_cancellation_policy', true) != '') {
					?>
						<li><a href="#listing-cancellation_policy"><?php esc_html_e('Cancellation policy','listeo_core'); ?></a></li>	
					<?php
                    
					}
                    
					if(!get_option('listeo_disable_reviews')){
						$reviews = get_comments(array(
						    'post_id' => $post->ID,
						    'status' => 'approve' //Change this to the type of comments to be displayed
						)); 
						if ( $reviews ) : ?>
						<li><a href="#listing-reviews"><?php esc_html_e('Reviews','listeo_core'); ?></a></li>
						<?php endif; ?>
						<li><a href="#add-review"><?php esc_html_e('Add Review','listeo_core'); ?></a></li>
					<?php } ?>
				</ul>
			</div>

			<!-- Overview -->
			<div id="listing-overview" class="listing-section">
				<?php $template_loader->get_template_part( 'single-partials/single-listing','main-details' );  ?>
				
				<!-- Description -->
				
				<?php the_content(); ?>
				<?php $template_loader->get_template_part( 'single-partials/single-listing','socials' );  ?>
				<?php $template_loader->get_template_part( 'single-partials/single-listing','features' );  ?>
			</div>

			<?php
			
			if( $count_gallery > 0 && $gallery_style == 'content') : $template_loader->get_template_part( 'single-partials/single-listing','gallery-content' ); endif; ?>
			<?php $template_loader->get_template_part( 'single-partials/single-listing','pricing' );  ?>
			<?php $template_loader->get_template_part( 'single-partials/single-listing','opening' );  ?>
			<?php $template_loader->get_template_part( 'single-partials/single-listing','video' );  ?>
			<?php 
				if(!isMobile()) {
			?>
				<div class="desktop-listing-review">
					<?php $template_loader->get_template_part( 'single-partials/single-listing','location' );  ?>
				</div>
			<?php } ?>
			<?php
				if(get_post_meta( $post->ID, '_cancellation_policy', true) != '') {
					$template_loader->get_template_part( 'single-partials/single-listing','cancellation_policy' );  
				}
			?>
			<div class="desktop-listing-review">
				<?php if(!get_option('listeo_disable_reviews')){ 
					$template_loader->get_template_part( 'single-partials/single-listing','reviews' ); } ?>
			</div>
		</div>
		<?php endwhile; // End of the loop. ?>
		<!-- Sidebar
		================================================== -->
		<div class="col-lg-4 col-md-4 margin-top-75 sticky">

				<?php if( get_post_meta($post->ID,'_verified',true ) == 'on') : ?>
					<!-- Verified Badge -->
					<div class="verified-badge with-tip" data-tip-content="<?php esc_html_e('Listing has been verified and belongs to the business owner or manager.','listeo_core'); ?>">
						<i class="sl sl-icon-check"></i> <?php esc_html_e('Verified Listing','listeo_core') ?>
					</div>
				<?php else:
					if(get_option('listeo_claim_page_button')){
					$claim_page = get_option('listeo_claim_page');?>
					<!-- <div class="claim-badge with-tip" data-tip-content="<?php esc_html_e('Click to claim this listing.','listeo_core'); ?>">
						<a href="<?php echo get_permalink($claim_page); ?>"><i class="sl sl-icon-question"></i> <?php esc_html_e('Not verified. Claim this listing!','listeo_core') ?></a>
					</div> -->
					<?php }

					endif; ?>

				<?php if( get_post_meta($post->ID,'_verified',true ) == 'on') {
					get_sidebar('listing');
				}
				else{ 
					?>
					<div id="un_verified_listing_widget" class="listing-widget widget listeo_core widget_listing_owner boxed-widget my_widget1">
						<div>
								<h4><b>Contact Business:</b></h4>
								<?php 
									if( is_user_logged_in() ) { 
										?>
											<div id="small-dialog" class="zoom-anim-dialog mfp-hide">
												<div class="small-dialog-header">
													<h3><?php esc_html_e('Send Message', 'listeo_core'); ?></h3>
												</div>
												<div id="unverify_listing_msg_form" class="message-reply margin-top-0">
													<form>
													
														<textarea 
														required
														cols="40" id="contact-message" class="custommessage" name="message" rows="3" placeholder="<?php esc_attr_e('Your message','listeo_core'); ?>"></textarea>
														<button data-listing_id="<?php echo esc_attr(get_the_ID()); ?>" id="send_unverify_listing_msg_btn" class="button">
														<i class="fa fa-circle-o-notch fa-spin" aria-hidden="true"></i><?php esc_html_e('Send Message', 'listeo_core'); ?></button>	
														<div style="display: none;" class="notification closeable success margin-top-20"></div>
										 			
										 			</form>
								  
												</div>
											</div>
											<div style="margin-top:5px;" class="new-list-btn unverify_listing_btn">
												<a id="unverify_listing_msg_btn" href="#small-dialog" class="send-message-to-owner button popup-with-zoom-anim listeo_list_provider_meg_btn">
													<?php esc_html_e('Message Business', 'listeo'); ?>
												</a>
											</div>
										<?php
									}
									else{
										?>
										<div style="margin-top:5px;" class="new-list-btn unverify_listing_btn">
											<a id="unverify_listing_msg_btn" href="#sign-in-dialog" class="send-message-to-owner button popup-with-zoom-anim listeo_list_provider_meg_btn" style="display:inline-block;">
												Message Vendor				</a>
										</div>
										<?php
									}	
								?>
								<p>Unverified businesses take a few hours to respond</p>
								<!-- <p> If this is your business <a href="<?php echo site_url(); ?>/claim-listing/">click here</a> to verify today.</p> -->
								<?php
									if(get_option('listeo_claim_page_button')){

										$claim_page = get_option('listeo_claim_page');?>
										<div style="margin-top:80px;" class="claim_link"><p> Is this your Business ? <a href="<?php echo get_permalink($claim_page); ?>"><?php esc_html_e('Claim this listing!','listeo_core') ?></a> </p></div>

										<?php
									}
								?>
						</div>
					</div>
					<div>
						<?php if ( is_active_sidebar( 'single_unveryfie_siderbar' ) ) : ?>
								<?php dynamic_sidebar( 'single_unveryfie_siderbar' ); ?>
						<?php endif; ?>
					</div>
					<?php
				} ?>

		</div>
		<!-- Sidebar / End -->
		<?php 
			if(isMobile()) {
		?>
			<div class="mobile-listing-review">
				<?php $template_loader->get_template_part( 'single-partials/single-listing','location' );  ?>
			</div>
		<?php } ?>
		<div class="mobile-listing-review">
			<?php if(!get_option('listeo_disable_reviews')){ 
				$template_loader->get_template_part( 'single-partials/single-listing','reviews' ); } ?>
		</div>
	</div>
</div>



<?php else : ?>

<?php get_template_part( 'content', 'none' ); ?>

<?php endif; ?>


<?php get_footer(); ?>

<script>
	jQuery('#send_unverify_listing_msg_btn').on('click',function(e) {
        
        jQuery('#send_unverify_listing_msg_btn').addClass('loading').prop('disabled', true);

        var message = jQuery("#unverify_listing_msg_form").find("#contact-message").val();
        var listing_id = jQuery(this).data("listing_id");
        var ajax_file_url = '<?php echo admin_url('admin-ajax.php'); ?>';

        jQuery.ajax({
	        type: 'POST',
	        dataType: 'json',
	        url: ajax_file_url,
	        data: {"action": "send_unverify_listing_msg", message: message, listing_id: listing_id },
	        success: function(data){
				jQuery('#unverify_listing_msg_form .custommessage').val('');
	        	if(data.success == 1)
	        	{
	        		jQuery('#send_unverify_listing_msg_btn').removeClass('loading');
                    jQuery('#unverify_listing_msg_form .notification').removeClass('error').addClass('success').show().html('Your message is successfully sent.');
					
	        	}
	        	else {
	        		jQuery('#unverify_listing_msg_form .notification').removeClass('success').addClass('error').show().html("Your message couldn't send, please try again.");
                    jQuery('#send_unverify_listing_msg_btn').removeClass('loading').prop('disabled', false);

	        	}
	        }
	    });

        e.preventDefault();
       });
</script>