<?php
	if( !defined( 'ABSPATH' ) ) exit;
	global $wpdb;
	$messages = new Listeo_Core_Messages();
	
	if (isset($_GET['page_no'])) {
		$pageno = $_GET['page_no'];
	} else {
		$pageno = 1;
	}
	$no_of_records_per_page = 15;
	$offset = ($pageno-1) * $no_of_records_per_page; 
	
	
	$rowcount = $wpdb->get_var( "SELECT COUNT(*) FROM `" .$wpdb->prefix."listeo_core_conversations`");
	//$total_rows = mysqli_fetch_array($result)[0];
	$total_pages = ceil($rowcount / $no_of_records_per_page);

	//echo $rowcount.'=='.$total_pages;

	$ids = $wpdb->get_results ( "SELECT * FROM `" .$wpdb->prefix."listeo_core_conversations` ORDER BY last_update DESC  LIMIT " .$offset." , " .$no_of_records_per_page);
	//echo "SELECT * FROM `" .$wpdb->prefix."listeo_core_conversations` ORDER BY last_update DESC  LIMIT " .$offset." , " .$no_of_records_per_page

	/*if($_SERVER['REMOTE_ADDR'] == "123.201.19.159")
	{
		echo "<pre>";
			print_r($ids);
		echo "<pre>"; 
		exit;
	}*/

?>
<div class="wrap">
	<?php echo '<h2>' . __( 'Users Conversation' , 'listeo_core' ) . '</h2>' . "\n"; ?>
	<div class="listeo_all_user_conv_main">
		<div id="titlebar">
			<div class="row">
				<div class="col-md-12">
					<h1><?php echo __( 'Messages' , 'listeo_core' ); ?></h1>	
				</div>
			</div>
		</div>
		<div class="listeo_all_user_msg_container">
			<div class="listeo_msg_headline">
				<h4><?php esc_html_e('Inbox','listeo_core') ?></h4>
			</div>
			<div class="listeo_msg_inbox">
				<ul>
					<?php 
						if($ids) { 
						foreach ($ids as $key => $conversation) {
							
							$message_url = add_query_arg( array( 'action' => 'view',  'conv_id' => $conversation->id ),admin_url('admin.php')."?page=single-user-conversation");

							$last_msg = $messages->get_last_message($conversation->id);
							
							$conversation_data = $messages->get_conversation($conversation->id);
							$referral = $messages->get_conversation_referral($conversation->referral);
							$if_read  = $messages->check_if_read($conversation_data);	
							/*echo "<pre>";
								print_r($last_msg);
							echo "</pre>";*/
							if($last_msg[0]->is_offer_message == 0)
							{
								?>
									<li <?php if(!$if_read) : ?> class="unread" <?php endif; ?>>
										<a href="<?php echo esc_url($message_url) ?>">
											<?php
												if($last_msg) {
													//set adversary
													$adversary1 = $conversation_data[0]->user_1;

													$adversary2 = $conversation_data[0]->user_2;
													
													$user_data1 = get_userdata( $adversary1 ); 
													$user_data2 = get_userdata( $adversary2 ); ?>
													<div class="listeo_conv_between">
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
													<div class="listeo_msg_avatar"><?php echo get_avatar($adversary1, '40') ?></div>
													
													<div class="listeo_msg_by">
														<div class="listeo_msg_by_headline">
															<?php
															if(empty($user_data1->first_name) && empty($user_data1->last_name)) {
																$name = $user_data1->user_nicename;
															} else {
																$name = $user_data1->first_name .' '.$user_data1->last_name;
															} ?>
															<h5>
																<?php echo esc_html($name); ?> <?php if($referral) : ?> 
																<span class="listeo_mes_referral" style="float:none;"> <?php echo esc_html($referral);  ?></span><?php endif; ?>
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
													//$adversary = ($conversation_data[0]->user_1 == get_current_user_id()) ? $conversation_data[0]->user_2 : $conversation_data[0]->user_1 ;

													$adversary1 = $conversation_data[0]->user_1;

													$adversary2 = $conversation_data[0]->user_2;
													
													$user_data1 = get_userdata( $adversary1 ); 
													$user_data2 = get_userdata( $adversary2 ); ?>
													
													<div class="listeo_conv_between">
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
													
													<div class="listeo_msg_avatar"><?php echo get_avatar($adversary1, '40') ?></div>
													
													<div class="listeo_msg_by">
														<div class="listeo_msg_by_headline">
															<?php
															if(empty($user_data1->first_name) && empty($user_data1->last_name)) {
																$name = $user_data1->user_nicename;
															} else {
																$name = $user_data1->first_name .' '.$user_data1->last_name;
															} ?>
															<h5>
																<?php echo esc_html($name); ?> <?php if($referral) : ?> 
																<span class="listeo_mes_referral" style="float:none;"> <?php echo esc_html($referral);  ?></span><?php endif; ?>
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
	<ul class="pagination">	
<?php 
$second_last = $total_pages - 1; 
$page_no = $pageno;
$adjacents = 2;
if($page_no <= 4) { 
 for ($counter = 1; $counter < 8; $counter++){ 
 if ($counter == $page_no) {
    echo "<li class='active'><a>$counter</a></li>"; 
 }else{
           echo "<li><a href='?page=user-conversation&page_no=$counter'>$counter</a></li>";
                }
}
echo "<li><a>...</a></li>";
echo "<li><a href='?page=user-conversation&page_no=$second_last'>$second_last</a></li>";
echo "<li><a href='?page=user-conversation&page_no=$total_pages'>$total_pages</a></li>";
}elseif($page_no > 4 && $page_no < $total_pages - 4) { 
echo "<li><a href='?page=user-conversation&page_no=1'>1</a></li>";
echo "<li><a href='?page=user-conversation&page_no=2'>2</a></li>";
echo "<li><a>...</a></li>";
for (
     $counter = $page_no - $adjacents;
     $counter <= $page_no + $adjacents;
     $counter++
     ) { 
     if ($counter == $page_no) {
 echo "<li class='active'><a>$counter</a></li>"; 
 }else{
        echo "<li><a href='?page=user-conversation&page_no=$counter'>$counter</a></li>";
          }                  
       }
echo "<li><a>...</a></li>";
echo "<li><a href='?page=user-conversation&page_no=$second_last'>$second_last</a></li>";
echo "<li><a href='?page=user-conversation&page_no=$total_pages'>$total_pages</a></li>";
}?>
</ul>
	</div>
</div>