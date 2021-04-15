<?php 

/* Determine the type of form */
if(isset($_GET["action"])) {
	$form_type = $_GET["action"];
} else {
	$form_type = 'submit';
}
$current_user = wp_get_current_user();
$roles = $current_user->roles;
$role = array_shift( $roles ); 
if(!in_array($role,array('administrator','admin','owner'))) :
	$template_loader = new Listeo_Core_Template_Loader; 
	$template_loader->get_template_part( 'account/owner_only'); 
	return;
endif;
?>
<form action="<?php  echo esc_url( $data->action ); ?>" method="post" id="submit-listing-form" class="listing-manager-form" enctype="multipart/form-data">
	
	<div id="add-listing">

		<!-- Section -->
		<div class="add-listing-section type-selection">

			<!-- Headline -->
			<div class="add-listing-headline">
				<h3><?php esc_html_e('Choose Listing Type','listeo_core') ?></h3>
			</div>
			<?php 	$listing_types = get_option('listeo_listing_types',array( 'service', 'rental', 'event' )); 
					if(empty($listing_types)) { $listing_types = array('service'); } 
				?>
			<div class="row">
				<div class="col-lg-12">
					<div class="listing-type-container">
						<?php if(in_array('service',$listing_types)): ?>
						<a href="#" class="listing-type" data-type="service">
							<span class="listing-type-icon"><i class="im im-icon-Location-2"></i></span>
							<h3><?php esc_html_e('Service','listeo_core') ?></h3>
						</a>
						<?php endif; ?>
						<?php if(in_array('rental',$listing_types)): ?>
						<a href="#" class="listing-type" data-type="rental">
							<span class="listing-type-icon"><i class="im im-icon-Home-2"></i></span>
							<h3><?php esc_html_e('Rent','listeo_core') ?></h3>
						</a>
						<?php endif; ?>
						<?php if(in_array('event',$listing_types)): ?>
						<a href="#" class="listing-type" data-type="event">
							<span class="listing-type-icon"><i class="im im-icon-Electric-Guitar"></i></span>
							<h3><?php esc_html_e('Event','listeo_core') ?></h3>
						</a>
						<?php endif; ?>
						<input type="hidden" id="listing_type" name="_listing_type">
					</div>
				</div>
			</div>
			
		</div>
	<div class="submit-page">

	<p>
		<input type="hidden" 	name="listeo_core_form" value="<?php echo $data->form; ?>" />
		<input type="hidden" 	name="listing_id" value="<?php echo esc_attr( $data->listing_id ); ?>" />
		<input type="hidden" 	name="step" value="<?php echo esc_attr( $data->step ); ?>" />
		<button type="submit" name="continue"  style="display: none" class="button margin-top-20"><?php echo esc_attr( $data->submit_button_text ); ?> <i class="fa fa-arrow-circle-right"></i></button>

	</p>

</form>
</div>
</div>