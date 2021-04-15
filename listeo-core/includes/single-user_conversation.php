<?php
	if( !defined( 'ABSPATH' ) ) exit;
?>
<div class="wrap">
<?php echo '<h2>' . __( 'Users Conversation' , 'listeo_core' ) . '</h2>' . "\n"; ?>
<?php 
	global $wpdb;
	$ids = '';
		
	$ids = $wpdb->get_results ( "SELECT * FROM `" .$wpdb->prefix."listeo_core_conversations` ORDER BY last_update DESC ");
	
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
		
		// mark this message as read
		$messages->mark_as_read($conversation_id);	
		
		// set who is adversary on that converstation
		//$adversary = ($this_conv[0]->user_1 == $current_user_id) ? $this_conv[0]->user_2 : $this_conv[0]->user_1 ;
		
		$adversary1 = $this_conv[0]->user_1;

		$adversary2 = $this_conv[0]->user_2;

		//$recipient = get_userdata( $adversary ); 
		$recipient1 = get_userdata( $adversary1 ); 
		$recipient2 = get_userdata( $adversary2 );
		
		if(empty($recipient1->first_name) && empty($recipient1->last_name)) {
			$name = $recipient1->user_nicename;
		} else {
			$name = $recipient1->first_name .' '.$recipient1->last_name;
		}

		if(empty($recipient2->first_name) && empty($recipient2->last_name)) {
			$name1 = $recipient2->user_nicename;
		} else {
			$name1 = $recipient2->first_name .' '.$recipient2->last_name;
		} 
		
		$referral = $messages->get_conversation_referral($this_conv[0]->referral);
		
		?>
		<div class="listeo_all_user_msg_container single_all_user_con_margin_top">
			<div class="listeo_msg_headline">
				<h4><?php echo esc_html($name ." & ".$name1); ?></h4>
			</div>

			<div class="listeo_msg_container_inner">
				<!-- Messages -->
				<div class="listeo_msg_inbox">
					<?php if($ids) { ?>
					<ul>
						<?php 

							foreach ($ids as $key => $conversation) {
									
									/*$message_url = add_query_arg( array( 'action' => 'view',  'conv_id' => $conversation->id ), get_permalink( get_option( 'listeo_messages_page' )) );*/

									$message_url = add_query_arg( array( 'action' => 'view',  'conv_id' => $conversation->id ),admin_url('admin.php')."?page=single-user-conversation");
		
									$last_msg = $messages->get_last_message($conversation->id);
									$conversation_data = $messages->get_conversation($conversation->id);	

									$if_read = $messages->check_if_read($conversation_data);

									/*$_conv_list_adversary = ($conversation_data[0]->user_1 == $current_user_id) ? $conversation_data[0]->user_2 : $conversation_data[0]->user_1 ;	
									$user_data = get_userdata( $_conv_list_adversary );*/

									$_conv_list_adversary1 = $conversation_data[0]->user_1;	
									$_conv_list_adversary2 = $conversation_data[0]->user_2;	
									
									$user_data1 = get_userdata( $_conv_list_adversary1 );
									$user_data2 = get_userdata( $_conv_list_adversary2 );

									$referral = $messages->get_conversation_referral($conversation->referral);
								?>
								<li <?php if(!$if_read) : ?>class="unread"<?php endif; ?>>
									<a href="<?php echo esc_url($message_url) ?>">
										<div class="listeo_single_conv_between">
											<?php
												if(empty($user_data1->first_name) && empty($user_data1->last_name)) {
													$name = $user_data1->user_nicename;
												} else {
													$name = $user_data1->first_name .' '.$user_data1->last_name;
												}

												if(empty($user_data2->first_name) && empty($user_data2->last_name)) {
													$name2 = $user_data2->user_nicename;
												} else {
													$name2 = $user_data2->first_name .' '.$user_data2->last_name;
												} 
											?>
											<h5>
												<?php echo esc_html($name." & ".$name2); ?>
											</h5>
										</div>
										<div class="listeo_msg_avatar"><?php echo get_avatar($_conv_list_adversary1, '70') ?></div>
					
										<div class="listeo_msg_by">
											<div class="listeo_msg_by_headline">
												<?php
												if(empty($user_data1->first_name) && empty($user_data1->last_name)) {
													$name = $user_data1->user_nicename;
												} else {
													$name = $user_data1->first_name .' '.$user_data1->last_name;
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
				<div class="listeo_msg_content">
					<div class="message-bubbles">
					<?php
						$current_user = wp_get_current_user();
						$roles = $current_user->roles;
						$role = array_shift( $roles );
						
						$conversation = $messages->get_single_conversation($current_user_id,$conversation_id);
						/*echo "<pre>";
						print_r($conversation);
						die;*/
						$first_sender_user_id = $conversation[0]->sender_id;
						foreach ($conversation as $key => $message) { 
						?>
							<?php 
								if($message->is_offer_message == 0) {
									?>
									<div class="listeo_msg_bubble <?php if($first_sender_user_id == (int) $message->sender_id ) echo esc_attr('me'); ?>">
										<div class="listeo_msg_avatar">
											<a href="<?php echo esc_url(get_author_posts_url($message->sender_id)); ?>">
												<?php echo get_avatar($message->sender_id, '70') ?>
											</a>
										</div>
										<div class="listeo_msg_text listeo_view_attach_msg">
											<?php echo $message->message; ?>
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
										<div class="listeo_msg_bubble <?php if($first_sender_user_id == (int) $message->sender_id ) echo esc_attr('me'); ?>">
											<div class="listeo_msg_avatar">
												<a href="<?php echo esc_url(get_author_posts_url($message->sender_id)); ?>">
													<?php echo get_avatar($message->sender_id, '70') ?>
												</a>
											</div>
											<div class="listeo_msg_text listeo_is_offer_message_text">
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
					<img style="display: none; " src="<?php echo get_stylesheet_directory_uri(); ?>/images/loader.gif" alt="" class="loading">
					<!-- Reply Area -->
					<div class="clearfix"></div>
					<input type="hidden" id="conversation_id" name="conversation_id" value="<?php echo esc_attr($_GET["conv_id"]) ?>">
				</div>
				<!-- Message Content -->

			</div>

		</div>
		
	<?php } else {
		die();
	} ?>
</div>