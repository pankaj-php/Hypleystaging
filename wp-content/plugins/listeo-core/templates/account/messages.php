<?php 
$ids = '';
if(isset($data)) :
	$ids	 	= (isset($data->ids)) ? $data->ids : '' ;
endif;

/*if($_SERVER['REMOTE_ADDR'] == "123.201.19.159")
{
	echo "<pre>";
		print_r($data);
	echo "<pre>"; 
	exit;
}*/
    
$messages = new Listeo_Core_Messages();
?>
<div class="messages-container margin-top-0">
	<div class="messages-headline">
		<h4><?php esc_html_e('Inbox','listeo_core') ?></h4>
	</div>
	
	<div class="messages-inbox">
		
		<ul>
			<?php 
			if($ids) { 
			foreach ($ids as $key => $conversation) {
				$message_url = add_query_arg( array( 'action' => 'view',  'conv_id' => $conversation->id ), get_permalink( get_option( 'listeo_messages_page' )) );

				$last_msg = $messages->get_last_message($conversation->id);
				$conversation_data = $messages->get_conversation($conversation->id);
				$referral = $messages->get_conversation_referral($conversation->referral);
				$if_read  = $messages->check_if_read($conversation_data);	
				
				// echo "<pre>";
				// 	print_r($last_msg);
				// echo '</pre>';
				// die();

				if($last_msg[0]->is_offer_message == 0)
				{
					?>
						<li <?php if(!$if_read) : ?> class="unread" <?php endif; ?>>
							<a href="<?php echo esc_url($message_url) ?>">
								<?php
									if($last_msg) {
										//set adversary
										$adversary = ($conversation_data[0]->user_1 == get_current_user_id()) ? $conversation_data[0]->user_2 : $conversation_data[0]->user_1 ;
										
										$user_data = get_userdata( $adversary ); ?>
										<div class="message-avatar">
											<?php //echo get_avatar($adversary, '70') ?>	
											<?php
											$custom_avatar_id 	= get_the_author_meta( 'listeo_core_avatar_id', $owner_id ) ;
											$custom_avatar 		= wp_get_attachment_image_src( $custom_avatar_id, 'listeo-avatar' );

											if ( $custom_avatar )  {
												echo "<img src='".$custom_avatar[0]."' style='width: 100px; height : auto;'> <br/>";
											} else {
												echo get_avatar( $adversary, 70 );  
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
												<h5>
													<?php echo esc_html($name); ?> <?php if($referral) : ?> 
													<span class="mes_referral" style="float:none;"> <?php echo esc_html($referral);  ?></span><?php endif; ?>
													<?php if(!$if_read) : ?><i><?php esc_html_e('Unread','listeo_core') ?></i><?php endif; ?>
												</h5>
												<span><?php echo human_time_diff( $last_msg[0]->created_at, current_time('timestamp')  );  ?></span>
											</div>
											<p>
												<?php 
													echo ( $last_msg[0]->sender_id == get_current_user_id() ) ? '<i class="fa fa-mail-forward" ></i>' : '<i class="fa fa-mail-reply"></i>';
													?> <?php echo $last_msg[0]->message; ?>
													
											</p>
										</div>
								<?php } ?>
							</a>
						</li>		
					<?php
				}
				else if($last_msg[0]->is_offer_message == 1) {
					?>
						<li <?php if(!$if_read) : ?> class="unread" <?php endif; ?>>
							<a href="<?php echo esc_url($message_url) ?>">
								<?php
									if($last_msg) {
										//set adversary
										$adversary = ($conversation_data[0]->user_1 == get_current_user_id()) ? $conversation_data[0]->user_2 : $conversation_data[0]->user_1 ;
										
										$user_data = get_userdata( $adversary ); ?>
										<div class="message-avatar"><?php echo get_avatar($adversary, '70') ?></div>
					
										<div class="message-by">
											<div class="message-by-headline">
												<?php
												if(empty($user_data->first_name) && empty($user_data->last_name)) {
													$name = $user_data->user_nicename;
												} else {
													$name = $user_data->first_name .' '.$user_data->last_name;
												} ?>
												<h5>
													<?php echo esc_html($name); ?> <?php if($referral) : ?> 
													<span class="mes_referral" style="float:none;"> <?php echo esc_html($referral);  ?></span><?php endif; ?>
													<?php if(!$if_read) : ?><i><?php esc_html_e('Unread','listeo_core') ?></i><?php endif; ?>
												</h5>
												<span><?php echo human_time_diff( $last_msg[0]->created_at, current_time('timestamp')  );  ?></span>
											</div>
											<p>
												<?php 
													echo ( $last_msg[0]->sender_id == get_current_user_id() ) ? '<i class="fa fa-mail-forward" ></i>' : '<i class="fa fa-mail-reply"></i>';
													?> <?php esc_html_e('Custom Offer','listeo_core') ?>
													
											</p>
										</div>
								<?php } ?>
							</a>
						</li>			
					<?php
				}
				?>
			<?php }
			} else { ?>
				<li><p style="padding:30px;"><?php esc_html_e("You don't have any messages yet",'listeo_core'); ?></p></li>
			<?php } ?>
		</ul>
	</div>
</div>

<?php
$current_page = (isset($_GET['messages-page'])) ? $_GET['messages-page'] : 1;

if($data->total_pages > 1) { ?>
	<div class="clearfix"></div>
	<div class="pagination-container margin-top-30 margin-bottom-0">
		<nav class="pagination">
			<?php 
				echo paginate_links( array(
					'base'         	=> @add_query_arg('messages-page','%#%'),
					'format'       	=> '?messages-page=%#%',
					'current' 		=> $current_page,
					'total' 		=> $data->total_pages,
					'type' 			=> 'list',
					'prev_next'    	=> true,
			        'prev_text'    	=> '<i class="sl sl-icon-arrow-left"></i>',
			        'next_text'    	=> '<i class="sl sl-icon-arrow-right"></i>',
			         'add_args'     => false,
   					 'add_fragment' => ''
				    
				) );?>
		</nav>
	</div>
	<?php } ?>