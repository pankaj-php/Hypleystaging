<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;
/**
 * Listeo_Core_Messages class
 */
class Listeo_Core_Messages {

	public function __construct() {

		add_shortcode( 'listeo_messages', array( $this, 'listeo_messages' ) );
        add_action('wp_ajax_listeo_send_message', array($this, 'send_message_ajax'));
        add_action('wp_ajax_listeo_send_message_chat', array($this, 'send_message_ajax_chat'));
		add_action('wp_ajax_listeo_get_conversation', array($this, 'get_conversation_ajax'));
        add_action('wp_ajax_listeo_admin_get_conversation', array($this, 'admin_get_conversation_ajax'));

        add_action( 'listeo_core_check_for_new_messages', array( $this, 'check_for_new_messages' ) );
        add_action( 'listeo_core_check_messages_from_email', array( $this, 'check_messages_from_email' ) );
        add_action( 'wp_ajax_listeo_create_offer', array( $this, 'listeo_create_offer' ) );
        add_action( 'wp_ajax_send_unverify_listing_msg', array( $this, 'send_unverify_listing_msg' ) );

        
	}

    /**
     * Maintenance task to expire listings.
     */
    public function check_messages_from_email() {
        //run remind work every 5 mins
        do_action('listeo_mail_unread_6hour');

        global $wpdb;

      // set user to checkchanged
      $strUser     = "cristian@hypley.com";
      $strPassword = "Australia10";

      // open
              $hMail = imap_open ("{imap.secure.emailsrvr.com:993/imap/ssl}INBOX", "$strUser", "$strPassword");

        // get headers
        $aHeaders = imap_headers( $hMail );

        // get message count
        $objMail = imap_mailboxmsginfo( $hMail );
        // echo "-----------------------<br/>";

        // process messages
        for( $idxMsg = 1; $idxMsg <= $objMail->Nmsgs; $idxMsg++  )
        {
            // get header, body info
            $objHeader = imap_headerinfo( $hMail, $idxMsg );
            $stringBody = quoted_printable_decode(imap_fetchbody( $hMail, $idxMsg,1));

            // get from object array
            $aFrom = $objHeader->from;

            // process headers
            for( $idx = 0; $idx < count($aFrom); $idx++ )
            {
                // get object
                $objData = $aFrom[ $idx ];

                // get email from
                $strEmailFrom = $objData->mailbox . "@" . $objData->host;
                // echo $strEmailFrom;
            }

            $temp_conversation_id=37;
            $temp_sender_id=145;

            $info_from_email=$objHeader->toaddress;
            // $info_from_email=$objHeader->to[0]->personal;
            if(strpos($info_from_email,"__")!==false){
                $temp_conversation_id=substr($info_from_email,0,strpos($info_from_email,"__"));
                $temp_sender_id=(int)substr($info_from_email,strpos($info_from_email,"__")+2);

                $users_results = $wpdb -> get_results( "SELECT user_1, user_2 FROM `" . $wpdb->prefix . "listeo_core_conversations` 
                    WHERE  id = $temp_conversation_id");

                foreach( $users_results as $result_row ) {
                    if($temp_sender_id==$result_row->user_1)$temp_sender_id=$result_row->user_2;
                    else if($temp_sender_id==$result_row->user_2)$temp_sender_id=$result_row->user_1;
                }

                //get original message from replied message
                $message=strpos($stringBody, '________________')>0?
                            substr($stringBody,0,strpos($stringBody, '________________')):
                            $stringBody;
                if(strpos($message, '> wrote:')>0){
                    $message = substr($message, 0, strpos($message, '> wrote:'));
                    $message = substr($message, 0, strrpos($message, '<'));
                    if(strrpos($message, 'On ')>0) $message = substr($message, 0, strrpos($message, 'On '));
                }
                            
                $result =  $wpdb -> insert( $wpdb->prefix . 'listeo_core_messages', array(
                        'conversation_id' 	=> $temp_conversation_id,
                        'sender_id' 		=> $temp_sender_id,
                        'message' 			=> $message,
                        'created_at' 		=> current_time( 'timestamp' )
                ));	

                if(isset($wpdb->insert_id)) {
                    $id = $wpdb->insert_id;
                    $conversation = $this->get_conversation($temp_conversation_id);
                    if($conversation[0]->user_1 == $temp_sender_id) {
                        $user = 'user_2';
                    } else {
                        $user = 'user_1';
                    }
                $this->mark_as_unread($user,$temp_conversation_id);
                $this->converstation_update_date($temp_conversation_id);
                } else {
                    $id = false;
                }
            }

            // delete message
            imap_delete( $hMail, $idxMsg );
        }

        // expunge deleted messages
        imap_expunge( $hMail );

        // // close
        imap_close( $hMail );
  
    }

    /**
     * Maintenance task to expire listings.
     */
    public function check_for_new_messages() {
        global $wpdb;
        $date_format = get_option('date_format');

        //  global $wpdb;
        // if ( $limit != '' ) $limit = " LIMIT " . esc_sql($limit);
        
        // if ( is_numeric($offset)) $offset = " OFFSET " . esc_sql($offset);

        // $result  = $wpdb -> get_results( "
        // SELECT * FROM `" . $wpdb->prefix . "listeo_core_conversations` 
        // WHERE  user_1 = '$user_id' OR user_2 = '$user_id'
        // ORDER BY last_update DESC $limit $offset
        // ");
        
        // return $result;

        // Notifie expiring in 5 days
       $conversation_ids = $wpdb->get_col( $wpdb->prepare( "
                SELECT id FROM {$wpdb->prefix}listeo_core_conversations
                WHERE (read_user_1 = '0'
                OR read_user_2 = '0' )
                AND notification != 'sent'
                AND last_update < %s
            ", strtotime( "-15 minutes" ) 
        )
        );

        if ( $conversation_ids ) {
            foreach ( $conversation_ids as $conversation_id ) {
                
                do_action('listeo_mail_to_user_new_message',$conversation_id);
            }
        }
  
    }

	public  function start_conversation( $args = 0 )  {

        global $wpdb;

        // TODO: filter by parameters
        $read_user_1 = '1';
        $read_user_2 = '0';

        $result =  $wpdb->insert( 
            $wpdb->prefix . 'listeo_core_conversations', 
            array(
                'user_1' => get_current_user_id(), //sender
                'user_2' => $args['recipient'], // recipeint
                'referral' => $args['referral'],
                'timestamp' => current_time( 'timestamp' ),
                'read_user_1' => $read_user_1, //sender already read
                'read_user_2' => $read_user_2,
                'notification' => '',
            ),
            array( 
                '%d',
                '%d', 
                '%s', 
                '%d',
                '%d',
                '%d',
            ) 
        );
        
        if(isset($wpdb->insert_id)) {
            $id = $wpdb->insert_id;
            $mail_args = array(
                'conversation_id' => $id,
                'message' => $args['message']
            );
            do_action('listeo_mail_to_user_new_conversation',$mail_args);
        } else {
            $id = false;
        }

        return $id;
    }

    public  function send_new_message( $args = 0 )  {
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        global $wpdb;

        // TODO: filter by parameters
       
        $sql = "
                CREATE TABLE {$wpdb->prefix}listeo_core_messages_attachements (
                  id bigint(20) NOT NULL auto_increment,
                  conversation_id bigint(20) NOT NULL,
                  sender_id bigint(20) NOT NULL,
                  attachement_id text NOT NULL,
                  created_at bigint(20) NOT NULL,
                  PRIMARY KEY  (id)
                ) $collate;
                ";
        dbDelta( $sql );

        //print_r($args);

        $result =  $wpdb -> insert( $wpdb->prefix . 'listeo_core_messages_attachements', array(
            'conversation_id'   => $args['conversation_id'],
            'sender_id'         => $args['sender_id'],
            'attachement_id'    => $args['attachement_id'],
            'created_at'        => current_time( 'timestamp' ),
        ));
    
    	if(isset($args['is_offer_message']) && $args['is_offer_message'] == 1) {
			$result =  $wpdb -> insert( $wpdb->prefix . 'listeo_core_messages', array(
            	'conversation_id' 	=> $args['conversation_id'],
            	'sender_id' 		=> $args['sender_id'],
            	'message' 			=> stripslashes_deep($args['message']),
            	'is_offer_message'  => 1, 
            	'created_at' 		=> current_time( 'timestamp' ),
        	));
		}
		else {
			$result =  $wpdb -> insert( $wpdb->prefix . 'listeo_core_messages', array(
            	'conversation_id' 	=> $args['conversation_id'],
            	'sender_id' 		=> $args['sender_id'],
            	'message' 			=> stripslashes_deep($args['message']),
            	'created_at' 		=> current_time( 'timestamp' ),
        	));	
		}

        if(isset($wpdb->insert_id)) {
            $id = $wpdb->insert_id;
            $conversation = $this->get_conversation($args['conversation_id']);
            if($conversation[0]->user_1 == $args['sender_id']) {
                $user = 'user_2';
            } else {
                $user = 'user_1';
            }
           $this->mark_as_unread($user,$args['conversation_id']);
           $this->converstation_update_date($args['conversation_id']);

            $this->check_messages_from_email();
        } else {
            $id = false;
        }
        return $id;
    }

    public function send_message_ajax() {

    	//echo "here";

        $recipient = $_REQUEST['recipient'];
        $referral = $_REQUEST['referral'];
		$message = $_REQUEST['message'];
        $att_id = $this->add_images();

        $conv_arr = array();
        
        $conv_arr['recipient'] = $recipient;
        $conv_arr['referral'] = $referral;
        $conv_arr['message'] = $message;

        //$conv_arr['attachement_id'] = $att_id;
        //check if conv exists
        $con_exists = $this->conversation_exists($recipient,$referral);
        $new_converstation  = ($con_exists) ? $con_exists : $this->start_conversation($conv_arr) ;
        

        if($new_converstation){
            $message = $_REQUEST['message'];
            $mess_arr = array();
            $mess_arr['conversation_id'] = $new_converstation;
            $mess_arr['sender_id'] = get_current_user_id();
            $mess_arr['message'] = $message;
            $mess_arr['attachement_id'] = $att_id;
            $id = $this->send_new_message($mess_arr);
        }
        
        if($id) {
            $result['type'] = 'success';
            $result['message'] = __( 'Your message was successfully sent' , 'listeo_core' );
        } else {
            $result['type'] = 'error';
            $result['message'] = __( 'Message couldn\'t be send' , 'listeo_core' );
        }

        $result = json_encode($result);
        echo $result;      
        die();
    }

    public function add_images() {
        $arr_img_ext = array('image/png', 'image/jpeg', 'image/jpg', 'image/gif');
        if(!empty($_FILES['file'])){
            for($i = 0; $i < count($_FILES['file']['name']); $i++) { 

                if (in_array($_FILES['file']['type'][$i], $arr_img_ext)) {

                    $wp_upload_dir = wp_upload_dir();
                    $wp_upload_dir       = wp_upload_dir(); 
                    $unique_file_name = wp_unique_filename( $wp_upload_dir['basedir'].'/messages', $_FILES['file']['name'][$i] ) ;
                    //print_r($unique_file_name);

                    //print_r($wp_upload_dir);
                    
                    $filename = basename( $unique_file_name );
                    //$file1 = $wp_upload_dir['url'] . '/' . $filename;
                    if( wp_mkdir_p( $wp_upload_dir['path'] ) ) {
                        $file = $wp_upload_dir['path'] . '/' . $filename;
                    } else {
                            $file = $wp_upload_dir['basedir'] . '/' . $filename;
                    }
                    $file = $wp_upload_dir['basedir'] . '/messages/' . $filename; 
                    //$wp_filetype = wp_check_filetype( $filename, null );
                    /*$data = file_get_contents($image["tmp_name"]);
                    $url = $wp_upload_dir[basedir]."/messages/".$_FILES['file']['name'][$i];
                    print_r($url);

                    move_uploaded_file($_FILES['file']['tmp_name'][$i],$wp_upload_dir[basedir]."/messages/".$url);*/


                    $data = file_get_contents($_FILES['file']['tmp_name'][$i]);
                    file_put_contents( $file, $data );

                    
                    $images[$i] = $file;
                    //$images[$i] = wp_upload_bits($_FILES['file']['name'][$i], null, file_get_contents($_FILES['file']['tmp_name'][$i]));

                    $wp_upload_dir = wp_upload_dir();
                    $url = $wp_upload_dir['url'] . '/' . basename( $_FILES['file']['name'][$i] );
                    $url = $wp_upload_dir['basedir'] . '/messages/' . $filename;
                    // Prepare an array of post data for the attachment.
                    $attachment = array(

                        'guid'           => $url, 
                        'post_mime_type' => $_FILES['file']['type'][$i],
                        'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $_FILES['file']['name'][$i] ) ),
                        'post_content'   => '',
                        'post_status'    => 'inherit'
                    );

                    $attachement_id[$i] = wp_insert_attachment( $attachment, $url);

                    $attach_data = wp_generate_attachment_metadata( $attachment_id[$i], $file );
                    wp_update_attachment_metadata( $attachment_id[$i], $attach_data );
                } else {
                    $result['type'] = 'error';
                    $result['message'] = __( 'Invalid Image' , 'listeo_core' );
                    $result = json_encode($result);
                    echo $result; 
                    die();
                }
            }
        } 
        $flag = 0;
        foreach ($attachement_id as $key => $value) {
            if($flag == 1):
                $att_id .= ',';
            endif;
            $att_id .= $value;
            $flag = 1;
        }
        return $att_id;
    }

    public function send_message_ajax_chat() { 
        
		
        $conversation_id = $_REQUEST['conversation_id'];
    	$message = $_REQUEST['message'];

        $att_id = $this->add_images();
      
        if(empty($message)){
            $result['type'] = 'error';
            $result['message'] = __( 'Empty message' , 'listeo_core' );
            $result = json_encode($result);
            echo $result;  
           
            die();
        }
        if(empty($conversation_id)){
            $result['type'] = 'error';
            $result['message'] = __( 'Whoops, we have a problem' , 'listeo_core' );
            $result = json_encode($result);
            echo $result;  
           
            die();
        }
    	    	
    	$mess_arr['conversation_id'] = $conversation_id;
    	$mess_arr['sender_id'] = get_current_user_id();
    	$mess_arr['message'] = $message;
        $mess_arr['attachement_id'] = $att_id;

    	$id = $this->send_new_message($mess_arr);
        
        if($id) {
            $result['type'] = 'success';
            $result['message'] = __( 'Your message was successfully sent' , 'listeo_core' );
        } else {
            $result['type'] = 'error';
            $result['message'] = __( 'Message couldn\'t be send' , 'listeo_core' );
        }

        $result = json_encode($result);
        echo $result;  
	   
	    die();
    }

   /**
	* Get user conversations
	*
    *
	*/
	public function get_conversations( $user_id, $limit = '', $offset = '')  {

        global $wpdb;
        if ( $limit != '' ) $limit = " LIMIT " . esc_sql($limit);
        
        if ( is_numeric($offset)) $offset = " OFFSET " . esc_sql($offset);

        $result  = $wpdb -> get_results( "
        SELECT * FROM `" . $wpdb->prefix . "listeo_core_conversations` 
        WHERE  user_1 = '$user_id' OR user_2 = '$user_id'
        ORDER BY last_update DESC $limit $offset
        ");
        
        return $result;
    }

    public function delete_conversations( $conv_id ) {
        global $wpdb;
        $user_id = get_current_user_id();
        $conversation = $this->get_conversation($conv_id);
        
        if($conversation){
            
            if($conversation[0]->user_1 == $user_id || $conversation[0]->user_2 == $user_id ){

                $result = $wpdb -> delete( $wpdb->prefix . 'listeo_core_conversations', array( 'id' => $conv_id) );
                $wpdb -> delete( $wpdb->prefix . 'listeo_core_messages', array( 'conversation_id' => $conv_id) );
                return $result;
            
            } else {
            
                return false;
            
            }
        } else {
            return false;
        }
       
        return false;
    }

//     public function get_conversations_by_latest(){
//         global $wpdb
// SELECT conv.id FROM wp_listeo_core_conversations AS conv LEFT JOIN wp_listeo_core_messages AS mes ON conv.id=mes.conversation_id  order by mes.created_at DESC 
//     }

    public function get_conversation_ajax() {
        $conversation_id = $_REQUEST['conversation_id'];
        if(!$conversation_id) {
            return;
            die();
        }
            $user_id = get_current_user_id();
            $conversation = $this->get_single_conversation($user_id,$conversation_id);
            ob_start();
            // var_dump(date_default_timezone_get());
            $time = $conversation[count($conversation)-1]->created_at;
            $diff = current_time( 'timestamp' ) - (int)$time;
            // if($time< current_time( 'timestamp' )){
            // } else {
            //     $diff = (int)$time - time();
            //     $diff = $diff/60;
            //     var_dump(578/60);
            // }
            // var_dump($diff);
            if( $diff < 7 ){
                foreach ($conversation as $key => $message) { ?>
                    <?php

    					if($message->is_offer_message == 0) {
    						?>
    						<div class="message-bubble <?php if($user_id == $message->sender_id ) echo esc_attr('me'); ?>">
    							<div class="message-avatar">
    		                    	<a href="<?php echo esc_url(get_author_posts_url($message->sender_id)); ?>">
    		                    		<?php //echo get_avatar($message->sender_id, '70') ?>
                                        <?php                                                   
                                        $custom_avatar_id   = get_the_author_meta( 'listeo_core_avatar_id', $message->sender_id ) ;
                                        $custom_avatar      = wp_get_attachment_image_src( $custom_avatar_id, 'listeo-avatar' );

                                        if ( $custom_avatar )  {
                                            echo "<img src='".$custom_avatar[0]."' style='border-radius: 50% !important;'> <br/>";
                                        } else {
                                            echo get_avatar( $message->sender_id, 70 );  
                                        }
                                        ?>
    		                    	</a>
    	                    	</div>
    							<div class="message-text">
    	                    	<?php echo wpautop($message->message) ?>
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
    					                            <?php
    					                            }
    					                    ?>                                  
    					                    </div>
    		                    		</div>
    		                    <?php       
    		                        }
    		                    ?> </div>
    		                    </div>
    						<?php
    					}
    					else if($message->is_offer_message == 1) {
    						$message_arr = explode('~@@@~',$message->message);
    						?>
                        	<div class="message-bubble <?php if($user_id == $message->sender_id ) echo esc_attr('me'); ?>">
    							<div class="message-avatar">
    								<a href="<?php echo esc_url(get_author_posts_url($message->sender_id)); ?>">
    									<?php //echo get_avatar($message->sender_id, '70') ?>
                                        <?php                                                   
                                        $custom_avatar_id   = get_the_author_meta( 'listeo_core_avatar_id', $message->sender_id ) ;
                                        $custom_avatar      = wp_get_attachment_image_src( $custom_avatar_id, 'listeo-avatar' );

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
                <?php }
            }
            $output = ob_get_clean();
            
            if( $diff < 7 ){
               $result['type'] = 'success';
            } else {
                $result['type'] = 'nosuccess';
            }
            $result['message'] = $output;
            $result = json_encode($result);
            echo $result;  
        die();
    }

    // display conversation in admin
    public function admin_get_conversation_ajax() {
        $conversation_id = $_REQUEST['conversation_id'];
        if(!$conversation_id) {
            return;
            die();
        }
            $user_id = get_current_user_id();
            $conversation = $this->get_single_conversation($user_id,$conversation_id);
            $first_sender_user_id = $conversation[0]->sender_id;
            ob_start();
            foreach ($conversation as $key => $message) { ?>      
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
                                <?php echo wpautop($message->message) ?>
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
                    else if($message->is_offer_message == 1) {
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
                      <?php } ?>
                
            <?php }
            $output = ob_get_clean();
            
            $result['type'] = 'success';
            $result['message'] = $output;
            $result = json_encode($result);
            echo $result;  
        die();
    }

    public function get_conversation( $conversation_id)  {

        global $wpdb;

        $result  = $wpdb -> get_results( "
        SELECT * FROM `" . $wpdb->prefix . "listeo_core_conversations` 
        WHERE  id = '$conversation_id'

        ");

        return $result;

    }

    public  function get_single_conversation( $user_id, $conversation_id)  {

        global $wpdb;

        $result1  = $wpdb -> get_results( "
        SELECT * FROM `" . $wpdb->prefix . "listeo_core_messages` 
        WHERE  conversation_id = '$conversation_id' 
        ORDER BY created_at ASC
        ");
        
        $result2 = $wpdb -> get_results( "
        SELECT * FROM `" . $wpdb->prefix . "listeo_core_messages_attachements` 
        WHERE  conversation_id = '$conversation_id' 
        ORDER BY created_at ASC
        ");
        $i=0;
        foreach ($result1 as $key1 => $value1) {
            $result[$i] = $value1;            
            foreach ($result2 as $key2 => $value2) {
                if ($value1->created_at == $value2->created_at) {
                   $result[$i]->attachement_id = $value2->attachement_id;
                }
            }
            $i++;
        }
        //print_r($result);
        return $result;
    }

    public function get_conversation_referral( $referral ) {
        if($referral){
            //$referral = $conversation[0]->referral;

            if (strpos($referral, 'listing_') !== false) {

                   $listing_id = str_replace('listing_','',$referral);
                   return get_the_title($listing_id);
            } 
            if (strpos($referral, 'booking_') !== false) {

                   $booking_id = str_replace('booking_','',$referral);
                   $bookings = new Listeo_Core_Bookings_Calendar;
                   $booking_data = $bookings->get_booking($booking_id);
                   $title = get_the_title($booking_data['listing_id']);
                   $status = $booking_data['status'];
                   return __('Reservation for ','listeo_core').$title;
            }
        }
        
    }

    /**
    * Get user conversations
    *
    *
    */
    public  function get_last_message( $conversation)  {

        global $wpdb;

        $result  = $wpdb -> get_results( "
        SELECT * FROM `" . $wpdb->prefix . "listeo_core_messages` 
        WHERE  conversation_id = '$conversation'
        ORDER BY created_at DESC LIMIT 1
        ");

        return $result;

    }

    /**
    * Mark as read
    *
    *
    */
    public  function mark_as_read( $conversation)  {

        global $wpdb;
        
        $conv = $this->get_conversation($conversation);
        
        if($conv[0]->user_1 == get_current_user_id()) {
            $user = 'user_1';
            $other_user=$conv[0]->user_2;
        } else {
            $user = 'user_2';
            $other_user=$conv[0]->user_1;
        }

        $result  = $wpdb->update( 
            $wpdb->prefix . 'listeo_core_conversations', 
            array( 'read_'.$user  => 1 ), 
            array( 'id' => $conversation ) 
        );

        $result  = $wpdb->update( 
            $wpdb->prefix . 'listeo_core_messages', 
            array( 'reminded'  => 1 ), 
            array( 
                'conversation_id' => $conversation,
                'sender_id' => $other_user
            ) 
        );
        
        return $result;
    }
    /**
    * Mark as unread
    *
    *
    */
    public  function converstation_update_date( $conversation )  {

        global $wpdb;

        $result  = $wpdb->update( 
            $wpdb->prefix . 'listeo_core_conversations', 
            array( 'last_update' => current_time( 'timestamp' ) ), 
            array( 'id' => $conversation ) 
        );
        
        return $result;
    } 

    /**
    * Mark as unread
    *
    *
    */
    public  function mark_as_unread( $user, $conversation)  {

        global $wpdb;

        $result  = $wpdb->update( 
            $wpdb->prefix . 'listeo_core_conversations', 
            array( 'read_'.$user => 0 ), 
            array( 
                'id' => $conversation ,
                'notification' => '' 
            ) 
        );
        
        return $result;
    }  

    /**
	* Check if read
	*
    *
	*/
	public  function check_if_read( $conversation_data)  {
        $user_id = get_current_user_id();
       
        if(isset($conversation_data)){
            if( (string) $conversation_data[0]->user_1 == $user_id){
                return $conversation_data[0]->read_user_1;
            } else {
                return $conversation_data[0]->read_user_2;
            }
        }
        
    }

    public function conversation_exists($recipient,$referral){
         $user_id = get_current_user_id();
         $conversations = $this->get_conversations($user_id);
         foreach ($conversations as $key => $conv) {
             if($user_id == (string)$conv->user_1 && $recipient == (string)$conv->user_2 && $referral == $conv->referral){
                return $conv->id;
             } elseif ($user_id == $conv->user_2 && $recipient == $conv->user_1  && $referral == $conv->referral) {
                return $conv->id;
            }
         }
         return false;
    }
	/**
	 * User messages shortcode
	 */
	public function listeo_messages( $atts ) {
		
		if ( ! is_user_logged_in() ) {
			return __( 'You need to be signed in to manage your messages.', 'listeo_core' );
		}

		$user_id = get_current_user_id();
	
		extract( shortcode_atts( array(
			'posts_per_page' => '25',
		), $atts ) );
        $limit = 7;
        $page = (isset($_GET['messages-page'])) ? $_GET['messages-page'] : 1;
        
        $offset = ( absint( $page ) - 1 ) * absint( $limit );
        
		ob_start();
		$template_loader = new Listeo_Core_Template_Loader;
        if( isset( $_GET["action"]) && $_GET["action"] == 'view' )  {
            $template_loader->set_template_data( 
                array( 
                    'ids' => $this->get_conversations($user_id) 
                )
            ) -> get_template_part( 'account/single_message' ); 
        } else {
            if( isset( $_GET["action"]) && $_GET["action"] == 'delete' )  {
                if(isset( $_GET["conv_id"]) && !empty($_GET["conv_id"])) {
                    $conv_id = $_GET["conv_id"];
                    $delete = $this->delete_conversations($conv_id);   
                    if($delete) { ?>
                        <div class="notification success"><p><?php esc_html_e('Conversation was removed','listeo_core'); ?></p></div>
                    <?php } else { ?>
                        <div class="notification error"><p><?php esc_html_e('Conversation couldn\'t be removed.','listeo_core'); ?></p></div>
                    <?php }
                } 
            }
            $total = count($this->get_conversations($user_id));
            $max_number_pages = ceil($total/$limit);
            $template_loader->set_template_data( 
                array( 
                    'ids' => $this->get_conversations($user_id,$limit,$offset),
                    'total_pages' => $max_number_pages
                ) 
            ) -> get_template_part( 'account/messages' ); 
        }

		return ob_get_clean();
	}
	
	// Create Offer ajax function
   	public function listeo_create_offer(){
   		$user_id = $_REQUEST['user_id'];
        $listeo_offer_title = $_REQUEST['listeo_offer_title'];
		$listeo_offer_description = $_REQUEST['listeo_offer_description']; 
		$listeo_offer_price = $_REQUEST['listeo_offer_price']; 
		$conversation_id = $_REQUEST['conversation_id']; 
        
        // Custom Add WC Product
		$objProduct = new WC_Product();
		$objProduct->set_name($listeo_offer_title);
		$objProduct->set_status("publish");  // can be publish,draft or any wordpress post status
		$objProduct->set_description($listeo_offer_description);
		$objProduct->set_price($listeo_offer_price); // set product price
		$objProduct->set_regular_price($listeo_offer_price); // set product regular price
		$objProduct->set_manage_stock(true); // true or false
		$objProduct->set_stock_quantity(1);
		
		$product_id = $objProduct->save();
		
		//call add custom offer woocommerce order action
		
		$order_details = wc_custom_offer_order_fun($product_id,$user_id);
		
		$payment_url = $order_details['payment_url'];
		$order_id = $order_details['order_id'];
		$currency_symbol = $order_details['currency_symbol'];
		
		
		if(isset($order_id) && $order_id != "") {		
			$result['type'] = 'success';       
			$result['product_id'] = $product_id; 
			$result['order_id'] = $order_id;
			$result['payment_url'] = $payment_url;
			
			$att_id = $this->add_images();
			$msg_arr['conversation_id'] = $conversation_id;
    		$msg_arr['sender_id'] = get_current_user_id();	 
    		$message = $listeo_offer_title."~@@@~".$listeo_offer_description."~@@@~".$currency_symbol." ".$listeo_offer_price."~@@@~".$payment_url;
    		$msg_arr['message'] = $message;
        	$msg_arr['attachement_id'] = $att_id;
        	$msg_arr['is_offer_message'] = 1;

    		$id = $this->send_new_message($msg_arr);
    		if($id) {
            	$result['offer_type'] = 'success';
            	$result['offer_message'] = __( 'Your Offer was successfully sent' , 'listeo_core' );
	        } else {
	            $result['offer_type'] = 'error';
	            $result['offer_message'] = __( 'Offer couldn\'t be send' , 'listeo_core' );
	        }	
		}
		else {
			$result['type'] = 'error';        
			$result['product_id'] = $product_id;
			$result['order_id'] = $order_id;
			$result['payment_url'] = $payment_url;
		}
       	 
        $result = json_encode($result);
        echo $result;  
	   
	    die();		
	}
	
    public function send_unverify_listing_msg(){
        
        $message = $_POST['message'];
        $listing_id = $_POST['listing_id'];
        $listing_url = get_the_permalink($listing_id);
        $admin_mail = get_option('admin_email');
        // $admin_mail = "hypleyteam@hypley.com";

        add_filter('wp_mail_content_type', function( $content_type ) {
            return 'text/html';
        });

        $user = get_userdata(get_current_user_id());
        $result['user'] = $user->display_name.'('.$user->user_email.')';
        
        $mail_content = '<div><p><a href="'.$listing_url.'"> '.$listing_url.' </a> a message has been submitted via unverified form </p> <p> '.$message.' </p>By:<br>'.$result['user'].' </div>';
        $result['content'] = ($mail_content);

        if(wp_mail($admin_mail.',michael@hypley.com,26rinky@gmail.com','Unverified Listing Contact',$mail_content) )
        {
            $result['success'] = "1";
        }
        else {
            $result['success'] = "0";
        }
  
        $result = json_encode($result);
        echo $result;
       
        die();      
    }

}