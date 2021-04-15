<?php

// get user email
$current_user = wp_get_current_user();

$email = $current_user->user_email;
$first_name =  $current_user->first_name;
$last_name =  $current_user->last_name;


// get meta of listing
$listing_data = get_post_meta( $data->listing_id );

// get first images
$gallery = get_post_meta( $data->listing_id, '_gallery', true );
$instant_booking = get_post_meta( $data->listing_id, '_instant_booking', true );
	$listing_type = get_post_meta( $data->listing_id, '_listing_type', true );  

foreach ( (array) $gallery as $attachment_id => $attachment_url ) 
{
	$image = wp_get_attachment_image_src( $attachment_id, 'listeo-gallery' );	
	break;
}

?>
<div class="row">
	

		<!-- Content
		================================================== -->
		<div class="col-lg-8 col-md-8 padding-right-30">

			<h3 class="margin-top-0 margin-bottom-30"><?php esc_html_e('Personal Details', 'listeo_core'); ?></h3>

			<form id="booking-confirmation" action="" method="POST">
			<input type="hidden" name="confirmed" value="yessir" />
			<input type="hidden" name="value" value="<?php echo $data->submitteddata; ?>" />

			<div class="row">

				<div class="col-md-6">
					<label><?php esc_html_e('First Name', 'listeo_core'); ?></label>
					<input type="text" required="true" name="firstname" value="<?php esc_html_e($first_name); ?>" >
				</div>

				<div class="col-md-6">
					<label><?php esc_html_e('Last Name', 'listeo_core'); ?></label>
					<input type="text" required="true" name="lastname" value="<?php esc_html_e($last_name); ?>" >
				</div>

				<div class="col-md-6">
					<div class="input-with-icon medium-icons">
						<label><?php esc_html_e('E-Mail Address', 'listeo_core'); ?></label>
						<input type="email" required="true" name="email" value="<?php esc_html_e($email); ?>" >
						<i class="im im-icon-Mail"></i>
					</div>
				</div>

				<div class="col-md-6">
					<div class="input-with-icon medium-icons">
						<label><?php esc_html_e('Phone', 'listeo_core'); ?></label>
						<input type="text" required="true" name="phone" value="<?php esc_html_e( get_user_meta( $current_user->ID, 'billing_phone', true) ); ?>" >
						<i class="im im-icon-Phone-2"></i>
					</div>
				</div>
				<!-- /// -->
				<?php if(get_option('listeo_add_address_fields_booking_form')) : ?>
				<div class="col-md-6">
					
						<label><?php esc_html_e('Street Address', 'listeo_core'); ?></label>
						<input type="text" required="true" name="billing_address_1" value="<?php esc_html_e( get_user_meta( $current_user->ID, 'billing_address_1', true) ); ?>" >
					
				</div>

				<div class="col-md-6">
					
						<label><?php esc_html_e('Postcode/ZIP', 'listeo_core'); ?></label>
						<input type="text" required="true" name="billing_postcode" value="<?php esc_html_e( get_user_meta( $current_user->ID, 'billing_postcode', true) ); ?>" >
					
				</div>
				<div class="col-md-6">
					
						<label><?php esc_html_e('Town', 'listeo_core'); ?></label>
						<input type="text" required="true" name="billing_city" value="<?php esc_html_e( get_user_meta( $current_user->ID, 'billing_country', true) ); ?>" >
					
				</div>
				<div class="col-md-6">
					
						<label><?php esc_html_e('Country', 'listeo_core'); ?></label>
						<input type="text" required="true" name="billing_country" value="<?php esc_html_e( get_user_meta( $current_user->ID, 'billing_country', true) ); ?>" >
					
				</div>
				<?php endif; ?>
				<!-- /// -->
				<div class="col-md-12 margin-top-15">
					<label><?php esc_html_e('Message', 'listeo_core'); ?></label>
					<textarea maxlength="200" name="message" placeholder="<?php esc_html_e('Your short message to the listing owner (optional)','listeo_core'); ?>" id="booking_message" cols="20" rows="3"></textarea>
				</div>
				
				
				<input type="submit" value="<?php 
				if(get_option('listeo_disable_payments')) {
			 		($instant_booking == 'on') ? esc_html_e('Confirm', 'listeo_core') : esc_html_e('Confirm and Book', 'listeo_core') ;  
				} else {
					($instant_booking == 'on') ? esc_html_e('Confirm', 'listeo_core') : esc_html_e('Confirm and Book', 'listeo_core') ;  
				}
			?>" class="button margin-top-20 margin-bottom-20" />
					
				
				
				</form>
			</div>


			<!--<a href="#" class="button booking-confirmation-btn margin-top-20"><div class="loadingspinner"></div><span class="book-now-text">
				<?php 
				if(get_option('listeo_disable_payments')) {
			 		($instant_booking == 'on') ? esc_html_e('Confirm', 'listeo_core') : esc_html_e('Confirm and Book', 'listeo_core') ;  
				} else {
					($instant_booking == 'on') ? esc_html_e('Confirm and Pay', 'listeo_core') : esc_html_e('Confirm and Book', 'listeo_core') ;  
				}
			?></span></a>-->
		</div>


		<!-- Sidebar
		================================================== -->
		<div class="col-lg-4 col-md-4 margin-top-0 margin-bottom-60">

			<!-- Booking Summary -->
			<div class="listing-item-container compact order-summary-widget">
				<div class="listing-item">
					<img src="<?php echo $image[0]; ?>" alt="">

					<div class="listing-item-content">
						<?php $rating = get_post_meta($data->listing_id, 'listeo-avg-rating', true); 
						if(isset($rating) && $rating > 0 ) : ?>
							<div class="numerical-rating" data-rating="<?php $rating_value = esc_attr(round($rating,1)); printf("%0.1f",$rating_value); ?>"></div>
						<?php endif; ?>
						<h3><?php echo get_the_title($data->listing_id); ?></h3>
						<?php if(get_the_listing_address($data->listing_id)) { ?><span><?php the_listing_address($data->listing_id); ?></span><?php } ?>
					</div>
				</div>
			</div>
			<div class="boxed-widget opening-hours summary margin-top-0">
				<h3><i class="fa fa-calendar-check-o"></i> <?php esc_html_e('Booking Summary', 'listeo_core'); ?></h3>
				<?php 
					$currency_abbr = get_option( 'listeo_currency' );
					$currency_postion = get_option( 'listeo_currency_postion' );
					$currency_symbol = Listeo_Core_Listing::get_currency_symbol($currency_abbr);
	 
				?>
				<ul id="booking-confirmation-summary">
																		
					<li><?php esc_html_e('Date', 'listeo_core'); ?> <span><?php echo $data->date_start; ?> <?php if ( isset( $data->date_end ) && $data->date_start != $data->date_end ) echo '<b> - </b>' . $data->date_end; ?></span></li>
		  
										  
																		
					<?php if ( isset($data->_hour) ) { ?><li><?php esc_html_e('Hour', 'listeo_core'); ?> <span><?php echo $data->_hour; ?></span></li><?php } ?>
					<?php if ( isset( $data->adults ) || isset( $data->childrens ) ) { ?>
						<li><?php esc_html_e('Guests', 'listeo_core'); ?> <span><?php if ( isset( $data->adults ) ) echo $data->adults;
						if ( isset( $data->childrens ) ) echo $data->childrens . ' Childrens ';
						?></span></li>
					<?php } ?>
									<?php $max_guests = get_post_meta($data->listing_id,"_max_guests",true);  
					if(get_option('listeo_remove_guests')){
						$max_guests = 1;
					}
					if(!get_option('listeo_remove_guests')) : ?>

					<?php if ( isset( $data->adults ) || isset( $data->childrens ) ) { ?>
						<li id='booking-confirmation-summary-guests'>
							<?php esc_html_e('Guests', 'listeo_core'); ?> <span><?php if ( isset( $data->adults ) ) echo $data->adults;
							if ( isset( $data->childrens ) ) echo $data->childrens . ' Childrens ';
							?></span>
						</li>
					<?php } 
					
					endif;
					
					 if ( isset( $data->tickets )) { ?>
																																						
						<li><?php esc_html_e('Tickets', 'listeo_core'); ?> <span><?php if ( isset( $data->tickets ) ) echo $data->tickets;?></span></li>
					<?php } ?>
	 
					<?php if( isset($data->services) && !empty($data->services)) { ?>
						<li id='booking-confirmation-summary-services'>
							<h5 id="summary-services"><?php esc_html_e('Additional Services','listeo_core'); ?></h5>
							<ul>
							<?php 
							$bookable_services = listeo_get_bookable_services($data->listing_id);
							$i = 0;
						if($listing_type == 'rental') {
								if(isset($data->date_start) && !empty($data->date_start) && isset($data->date_end) && !empty($data->date_end)){

		        					$firstDay = new DateTime( $data->date_start );
	    	    					$lastDay = new DateTime( $data->date_end . '23:59:59') ;

	        						$days = $lastDay->diff($firstDay)->format("%a");
								} else {
									$days = 1;
								} 
							} else {
								$days = 1;
							}
							if(isset($data->adults)){
								$guests = $data->adults;	
							} else{
								$guests = $data->tickets; 
							}
							

							foreach ($bookable_services as $key => $service) {
							
							 	// $data->date_start
							 	// $data->date_end;
							 	// days
							 
							 	$countable = array_column($data->services,'value');
							 	
							 	if(in_array(sanitize_title($service['name']),array_column($data->services,'service'))) { 
							 		?>
							 		<li>
							 			<span><?php 
										if(empty($service['price']) || $service['price'] == 0) {
											esc_html_e('Free','listeo_core');
										} else {
											if($currency_postion == 'before') { echo $currency_symbol.' '; } 
											echo listeo_calculate_service_price($service, $guests, $days, $countable[$i] );
										
											if($currency_postion == 'after') { echo ' '.$currency_symbol; }
										}
										?></span>
										<?php echo esc_html(  $service['name'] ); 
											if( isset($countable[$i]) && $countable[$i] > 1 ) { ?>
												<em>(*<?php echo $countable[$i];?>)</em>
											<?php } ?> 
									</li>
							 	<?php  $i++;
							 	}
							 	
							 }  ?>
						 	</ul>
						</li>
					<?php } ?>
					<?php if($data->price>0): ?>

					<li class="total-costs"><?php esc_html_e('Total Cost', 'listeo_core'); ?><span> 
						<?php if($currency_postion == 'before') { echo $currency_symbol.' '; } echo $data->price; if($currency_postion == 'after') { echo ' '.$currency_symbol; } ?></span></li>
					<?php endif; ?>
				</ul>

			</div>
			<!-- Booking Summary / End -->

		</div>
</div>