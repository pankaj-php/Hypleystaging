<?php 
$current_user = wp_get_current_user();	
$user_post_count = count_user_posts( $current_user->ID , 'listing' ); 
$roles = $current_user->roles;
$role = array_shift( $roles ); 

if(!in_array($role,array('administrator','admin','owner'))) :
	$template_loader = new Listeo_Core_Template_Loader; 
	$template_loader->get_template_part( 'account/owner_only'); 
	return;
endif; 

?>

<!-- Notice -->
<!--  -->

<!-- Content -->
<div class="row">

	 <?php 
	$listings_page = get_option('listeo_listings_page');   
	if($listings_page) : ?>
	<a href="<?php echo esc_url(get_permalink($listings_page)); ?>?status=active">
	<?php endif; ?>
	<!-- Item -->
	<div class="col-lg-3 col-md-6">
		<div class="dashboard-stat color-1">
			<div class="dashboard-stat-content"><h4><?php $user_post_count = count_user_posts( $current_user->ID , 'listing' ); echo $user_post_count; ?></h4> <span><?php esc_html_e('Active Listings','listeo_core'); ?></span></div>
			<div class="dashboard-stat-icon"><i class="im im-icon-Map2"></i></div>
		</div>
	</div>
<?php if($listings_page) : ?>
	</a>
	<?php endif; ?>
	<?php $total_views = get_user_meta( $current_user->ID, 'listeo_total_listing_views', true ); ?>
	<!-- Item -->
	<div class="col-lg-3 col-md-6">
		<div class="dashboard-stat color-2">
			<div class="dashboard-stat-content"><h4><?php echo esc_html($total_views); ?></h4> <span><?php esc_html_e('Total Views','listeo_core'); ?></span></div>
			<div class="dashboard-stat-icon"><i class="im im-icon-Line-Chart"></i></div>
		</div>
	</div>


	<?php 

	$author_posts_comments_count = listeo_count_user_comments(
	    array(
	        'author_id' => $current_user->ID , // Author ID
	        'author_email' => $current_user->user_email, // Author ID
	        'approved' => 1, // Approved or not Approved
	    )
	);
	 
	?>
		<?php $reviews_page = get_option('listeo_reviews_page');
	if($reviews_page):  ?>
	<!-- Item -->
	<a href="<?php echo esc_url(get_permalink($reviews_page)); ?>">
	<?php endif; ?>
	<div class="col-lg-3 col-md-6">
		<div class="dashboard-stat color-3">
			<div class="dashboard-stat-content"><h4><?php echo esc_html($author_posts_comments_count); ?></h4> <span><?php esc_html_e('Total Reviews','listeo_core'); ?></span></div>
			<div class="dashboard-stat-icon"><i class="im im-icon-Add-UserStar"></i></div>
		</div>
	</div>
	<?php if($reviews_page):  ?>
	</a>
<?php endif; ?>


	<!-- Item -->
	<?php $total_bookmarks = get_user_meta( $current_user->ID, 'listeo_total_listing_bookmarks', true ); ?>
	<div class="col-lg-3 col-md-6">
		<div class="dashboard-stat color-4">
			<div class="dashboard-stat-content"><h4><?php echo esc_html($total_bookmarks); ?></h4> <span><?php esc_html_e('Times Bookmarked','listeo_core') ?></span></div>
			<div class="dashboard-stat-icon"><i class="im im-icon-Heart"></i></div>
		</div>
	</div>

</div>


<div class="row">

	<!-- Recent Activity -->
	<div class="col-lg-6 col-md-12">
		<div class="dashboard-list-box with-icons margin-top-20" style="position: relative;">
			<h4><?php esc_html_e('Recent Activities','listeo_core'); ?></h4>
			<a href="#" id="listeo-clear-activities" class="clear-all-activities" data-nonce="<?php echo wp_create_nonce( 'delete_activities' ); ?>"><?php esc_html_e('Clear All','listeo_core') ?></a>
			<?php echo do_shortcode( '[listeo_activities]' ); ?>
		
	</div>

	<!-- Invoices -->
	<div class="col-lg-6 col-md-12">
		<div class="dashboard-list-box invoices with-icons margin-top-20">
			<h4><?php esc_html_e('Your Listing Packages','listeo_core') ?></h4>
			<ul class="products user-packages">
					<?php 
					$user_packages = listeo_core_user_packages( get_current_user_id() );
					if($user_packages) :
					foreach ( $user_packages as $key => $package ) :
						$package = listeo_core_get_package( $package );
						?>
						<li class="user-job-package">
						<i class="list-box-icon sl sl-icon-diamond"></i>
						<strong><?php echo $package->get_title(); ?></strong>
						<p>
							<?php
							if ( $package->get_limit() ) {
								printf( _n( 'You have %1$s listings posted out of %2$d', 'You have %1$s listings posted out of %2$d', $package->get_count(), 'listeo_core' ), $package->get_count(), $package->get_limit() );
							} else {
								printf( _n( 'You have %s listings posted', 'You have %s listings posted', $package->get_count(), 'listeo_core' ), $package->get_count() );
							}

							if ( $package->get_duration() ) {
								printf( ', ' . _n( 'listed for %s day', 'listed for %s days', $package->get_duration(), 'listeo_core' ), $package->get_duration() );
							}

							$checked = 0;
						?>
						</p>

					</li>
					<?php endforeach;
					else : ?>
						<li class="no-icon"><?php esc_html_e("You don't have any listing packages yet.",'listeo_core'); ?></li>
					<?php endif; ?>
				</ul>
		</div>
	</div>
</div>