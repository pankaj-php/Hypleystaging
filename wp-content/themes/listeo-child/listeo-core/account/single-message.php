<?php die("testing");?>
<?php $ids = '';
//user_1 ten co wysyla
//user_2 ten co dostaje
if(isset($data)) :
	$ids	 	= (isset($data->ids)) ? $data->ids : '' ;
	/*if($_SERVER['REMOTE_ADDR'] == "123.201.19.159")
	{
		echo "<pre>ttttt";
			print_r($ids);
		echo "<pre>"; 
		exit;
	}*/
endif;

if( isset( $_GET["action"]) && $_GET["action"] == 'view' )  {

	$messages = new Listeo_Core_Messages();

	//check if user can

	$conversation_id = $_GET["conv_id"]; 
	
	$current_user_id = get_current_user_id();
	
	// get this conversation data
	$this_conv = $messages->get_conversation($conversation_id);	
	if(!$this_conv) { ?>
		<h4><?php esc_html_e('This message does not exists.','listeo_core'); ?></h4>
		<?php return;
	}
	if($current_user_id == (int)$this_conv[0]->user_1 || $current_user_id == (int)$this_conv[0]->user_2 ) :

		// mark this message as read
		$messages->mark_as_read($conversation_id);	
		
		// set who is adversary on that converstation
		$adversary = ($this_conv[0]->user_1 == $current_user_id) ? $this_conv[0]->user_2 : $this_conv[0]->user_1 ;
		$recipient = get_userdata( $adversary ); 
		
		if(empty($recipient->first_name) && empty($recipient->last_name)) {
			$name = $recipient->user_nicename;
		} else {
			$name = $recipient->first_name .' '.$recipient->last_name;
		} 
		
		$referral = $messages->get_conversation_referral($this_conv[0]->referral);
		
		?>
		<div class="messages-container margin-top-0">
			<div class="messages-headline">
				<h4><?php echo esc_html($name); ?><?php if($referral) : ?> <span><?php echo esc_html($referral);  ?></span><?php endif; ?></h4>
				<a href="?action=delete&conv_id=<?php echo esc_attr($conversation_id); ?>" class="message-action" id="message-delete"><i class="sl sl-icon-trash"></i> <?php esc_html_e('Delete Conversation', 'listeo_core' ); ?></a>
			</div>

			<div class="messages-container-inner">
				<div id="small-dialog" class="zoom-anim-dialog mfp-hide">
					<div class="small-dialog-header">
						<h3><?php esc_html_e('Create An Offer', 'listeo_core'); ?></h3>
					</div>
					<div class="message-reply margin-top-0">
						<div>
							<input type="hidden" id="user_id" name="user_id" value="<?php echo (int)$this_conv[0]->user_1; ?>" />
							<input required type="text" name="listeo_offer_title" id="listeo_offer_title" placeholder="<?php esc_attr_e('Enter Offer Title','listeo_core');?>" />
							<label class="listeo_title_label listeo_offer_validation_hide" for="listeo_offer_title">
								<?php esc_attr_e('Please Enter Title','listeo_core');?>
							</label>	
						</div>						
						<div>
							<textarea required cols="40" id="listeo_offer_description" name="listeo_offer_description" rows="3" placeholder="<?php esc_attr_e('Enter Offer Description','listeo_core');?>"></textarea>
							<label class="listeo_description_label listeo_offer_validation_hide" for="listeo_offer_title">
								<?php esc_attr_e('Please Enter Description','listeo_core');?>
							</label>
						</div>
						<div>
							<input required type="number" name="listeo_offer_price" id="listeo_offer_price" placeholder="<?php esc_attr_e('Enter Offer Price','listeo_core');?>" />
							<label class="listeo_price_label listeo_offer_validation_hide" for="listeo_offer_title">
								<?php esc_attr_e('Please Enter Price','listeo_core');?>
							</label>
							<label class="listeo_invalid_price_label listeo_offer_validation_hide" for="listeo_offer_title">
								<?php esc_attr_e('Please Enter Valid Price','listeo_core');?>
							</label>
						</div>
						<button class="button" id="listeo_add_offer_btn">
							<i class="fa fa-circle-o-notch fa-spin" aria-hidden="true"></i>
							<?php esc_html_e('Create An Offer', 'listeo_core'); ?>
						</button>		
					</div>
				</div>
				<!-- Messages -->
				<div class="messages-inbox">
					<?php if($ids) { ?>
					<ul>
						<?php 

							foreach ($ids as $key => $conversation) {
									
									$message_url = add_query_arg( array( 'action' => 'view',  'conv_id' => $conversation->id ), get_permalink( get_option( 'listeo_messages_page' )) );
		
									$last_msg = $messages->get_last_message($conversation->id);
									$conversation_data = $messages->get_conversation($conversation->id);	

									$if_read = $messages->check_if_read($conversation_data);

									$_conv_list_adversary = ($conversation_data[0]->user_1 == $current_user_id) ? $conversation_data[0]->user_2 : $conversation_data[0]->user_1 ;	
									$user_data = get_userdata( $_conv_list_adversary );

									$referral = $messages->get_conversation_referral($conversation->referral);
								?>
								<li <?php if(!$if_read) : ?>class="unread"<?php endif; ?>>
									<a href="<?php echo esc_url($message_url) ?>">
										<div class="message-avatar">
											<?php //echo get_avatar($_conv_list_adversary, '70') ?>
											<?php
											$custom_avatar_id 	= get_the_author_meta( 'listeo_core_avatar_id', $conversation_data[0]->user_2 ) ;
											$custom_avatar 		= wp_get_attachment_image_src( $custom_avatar_id, 'listeo-avatar' );

											if ( $custom_avatar )  {
												echo "<img src='".$custom_avatar[0]."' style='border-radius: 50% !important;'> <br/>";
											} else {
												echo get_avatar( $owner_id, 70 );  
											}
											?>		
										</div>
					
										<div class="message-by">
											<div class="message-by-headline">
												<?php
												if(empty($user_data->first_name) && empty($user_data->last_name)) {
													$name = $user_data->user_nicename;
												} else {
													$name = $user_data->first_name .' '.$user_data->last_name;
												} ?>
												<h5><?php echo esc_html($name); ?>
												<?php if(!$if_read) : ?><i><?php esc_html_e('Unread','listeo_core') ?></i><?php endif; ?>
											</h5>
											<span><?php echo human_time_diff( $last_msg[0]->created_at, current_time('timestamp')  );  ?></span>
											</div>
											<p>
												<?php if($referral) : echo esc_html($referral); endif; ?>
												<?php 
											//echo $last_msg[0]->message;
											 ?></p>
										</div>
									</a>
								</li>
							<?php } ?>
						</ul>
					<?php } ?>
					
				
				</div>
				<!-- Messages / End -->

				<!-- Message Content -->
				<div class="message-content">
					<p style="color:gray;">
						To protect your payment, Always communicate and pay through the hypley website or App.
					</p>
					<div class="message-bubbles">
					<?php
						$current_user = wp_get_current_user();
						$roles = $current_user->roles;
						$role = array_shift( $roles );
						
						$conversation = $messages->get_single_conversation($current_user_id,$conversation_id);

						
						foreach ($conversation as $key => $message) { 

						// echo '<pre>';
						// print_r( $message );
						// echo '</pre>';
						?>
							<?php 
								if($message->is_offer_message == 0) {
									?>
									<div class="shibbir message-bubble <?php if($current_user_id == (int) $message->sender_id ) echo esc_attr('me'); ?>">
										<div class="message-avatar">
											<a href="<?php echo esc_url(get_author_posts_url($message->sender_id)); ?>">
												<?php 
												//echo get_avatar($message->sender_id, '70') 
												$custom_avatar_id 	= get_the_author_meta( 'listeo_core_avatar_id', $message->sender_id ) ;
												$custom_avatar 		= wp_get_attachment_image_src( $custom_avatar_id, 'listeo-avatar' );

												if ( $custom_avatar )  {
													echo "<img src='".$custom_avatar[0]."' style='border-radius: 50% !important;'> <br/>";
												} else {
													echo get_avatar( $owner_id, 70 );  
												}
												?>
											</a>
										</div>
										<div class="message-text">
											<?php echo wpautop($message->message) ?>
											<?php //echo $message->message; ?>
		                					<?php 
		                                   		if($message->attachement_id){
		                                       	?>
		                                    	<div class="view-attachment">
		                                        	<div class="btn-attachment">
		                                            	<?php echo "View Attachments"; ?>
		                                        	</div>
		                                			<div class="message-attachment" style="display: none;">
				                                        <?php
				                                            $att_id = explode(',', $message->attachement_id);
				                                            foreach ($att_id as $key => $value) {
				                                                $url = wp_get_attachment_url($value);
				                                                ?>
				                                                <a class="download-image" href="<?php echo $url; ?>" title="<?php echo basename($url); ?>"  download>
				                                                    <img src="<?php echo $url; ?>" width=50 height=50 />
				                                                    <span class="image-title">
				                                                        <!-- <i class="sl sl-icon-cloud-download"></i> -->
				                                                        <i class="fa fa-download" aria-hidden="true"></i>
				                                                    </span>
				                                                </a>
				                                         <?php } ?>                                  
		                            				</div>
		                            			</div>
		                                <?php } ?> 
		                                </div>
		                               </div>
									<?php
								}
								else if($message->is_offer_message == 1){
										$message_arr = explode('~@@@~',$message->message);

										
									?>
										<div class="message-bubble <?php if($current_user_id == (int) $message->sender_id ) echo esc_attr('me'); ?>">
											<div class="message-avatar">
												<a href="<?php echo esc_url(get_author_posts_url($message->sender_id)); ?>">
													<?php //echo get_avatar($message->sender_id, '70') ?>
													<?php 													
													$custom_avatar_id 	= get_the_author_meta( 'listeo_core_avatar_id', $message->sender_id ) ;
													$custom_avatar 		= wp_get_attachment_image_src( $custom_avatar_id, 'listeo-avatar' );

													if ( $custom_avatar )  {
														echo "<img src='".$custom_avatar[0]."' style='border-radius: 50% !important;'> <br/>";
													} else {
														echo get_avatar( $message->sender_id, 70 );  
													}
													?>
												</a>
											</div>
											<div class="message-text listeo_is_offer_message_text">
												<div class="listeo_is_offer_price_title">	
													<h6><?php echo $message_arr[0]; ?></h6>
													<span><?php echo $message_arr[2]; ?></span>
												</div>
												<div class="listeo_is_offer_desc_dtl">
													<p><?php echo $message_arr[1]; ?></p>
												</div>
												<div class="listeo_offer_payment_url_btn"> 
													<a target="_blank" href="<?php echo $message_arr[3]; ?>">pay</a> 
												</div>
												
											</div>
										</div>	
									<?php
								}
							?>
					<?php } ?>
				</div>
					<!-- <img data-test="<?php //echo site_url(); ?>" style="display: none; " src="<?php //echo get_stylesheet_directory_uri(); ?>/images/loader.gif" alt="" class="loading"> -->
					
					<img style="display: none; " src="<?php echo site_url(); ?>/wp-content/themes/listeo/images/loader.gif" alt="" class="loading">
					<!-- Reply Area -->
					<div class="clearfix"></div>
					<div class="message-reply">
					
						<form action="" id="send-message-from-chat" enctype="multipart/form-data" >
							<!-- enctype="multipart/form-data" -->
							<textarea cols="40" id="contact-message" name="message" required rows="3" class="shobhit" placeholder="<?php esc_html_e('Message', 'listeo_core'); ?>"></textarea>
							<input type="hidden" id="conversation_id" name="conversation_id" value="<?php echo esc_attr($_GET["conv_id"]) ?>">
							<input type="hidden" id="recipient" name="recipient" value="<?php echo esc_attr($adversary) ?>">

							<div class="choose-file-wrapper">
								<div class="choose-file">Choose</div>
								<span class="selected-files"></span>
							</div>
							<h1>Hello</h1>
							<input type="file" name="images[]" id="fileInput" multiple style="display: none;"> 

							<button class="button listeo_send_msg_btn"><?php esc_html_e('Send Message', 'listeo_core'); ?></button>
							
							<?php if(in_array($role,array('administrator','admin','owner'))) : ?>
								<a class="button listeo_create_offer_btn popup-with-zoom-anim" href="#small-dialog"><?php esc_html_e('Create An Offer', 'listeo_core'); ?></a>
							<?php endif; ?>
						</form>
					</div>
					
				</div>
				<!-- Message Content -->

			</div>

		</div>
	<?php else: ?>
		<?php esc_html_e("It's not your converstation!",'listeo_core'); ?>
	<?php endif; ?>
<?php } else {
	die();
} ?>