<?php

// $subject = 'Remind Of Unread Messages';
//             $body = 'hello! clarence';

//             $headers  = 'MIME-Version: 1.0' . "\r\n";
//             $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
//             $headers .= 'From: Hypley <cristian@hypley.com>' . "\r\n" .
//                 // 'Reply-To: 37__145 <cristian@hypley.com>' . "\r\n" .
//                 'X-Mailer: PHP/' . phpversion();
//                 mail('clarenceli419@gmail.com', $subject, $body, $headers);
// global $wpdb;

// set user to check
$strUser     = "cristian@hypley.com";
$strPassword = "zxcvasdfqwer1234Z";

// open
$hMail = imap_open ("{imap.hostinger.com:993/imap/ssl}INBOX", "$strUser", "$strPassword");

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
    // echo $info_from_email;
    // $info_from_email=$objHeader->to[0]->personal;
    if(strpos($info_from_email,"__")!==false){
        // echo "asdf";
        $temp_conversation_id=substr($info_from_email,0,strpos($info_from_email,"__"));
        // echo $temp_conversation_id."<br>";
        $temp_sender_id=(int)substr($info_from_email,strpos($info_from_email,"__")+2);
        // echo $temp_sender_id."<br>";

        // $users_results = $wpdb -> get_results( "SELECT user_1, user_2 FROM `" . $wpdb->prefix . "listeo_core_conversations` 
        //     WHERE  id = $temp_conversation_id");

        // foreach( $users_results as $result_row ) {
        //     if($temp_sender_id==$result_row->user_1)$temp_sender_id=$result_row->user_2;
        //     else if($temp_sender_id==$result_row->user_2)$temp_sender_id=$result_row->user_1;
        // }
    

        // $result =  $wpdb -> insert( $wpdb->prefix . 'listeo_core_messages', array(
        //         'conversation_id' 	=> $temp_conversation_id,
        //         'sender_id' 		=> $temp_sender_id,
        //         'message' 			=> substr($stringBody,0,strpos($stringBody, 'Hypley <michael@hypley.com> wrote:')),
        //         'created_at' 		=> current_time( 'timestamp' ),
        // ));	
        // echo $temp_conversation_id."<br>";
        // echo $temp_sender_id."<br>";
        // echo htmlspecialchars($stringBody)."<br>-------------------------<br>";
        // echo substr($stringBody,0,strpos($stringBody, 'Hypley <michael@hypley.com> wrote:'))."<br>";
        // echo $stringBody."-------------------------<br>";
        $message=strpos($stringBody, '________________')>0?
                            substr($stringBody,0,strpos($stringBody, '________________')):
                            $stringBody;
        
        // echo $message."***************************<br>";
        if(strpos($message, '> wrote:')>0){
                    $message = substr($message, 0, strpos($message, '> wrote:'));
                    // echo $message."111111111111111111111111111111111111111111<br>";
                    $message = substr($message, 0, strrpos($message, '<'));
                    if(strrpos($message, 'On ')>0) $message = substr($message, 0, strrpos($message, 'On '));
                }
        echo $message."///////////////////////////<br>";

        // if(isset($wpdb->insert_id)) {
        //     $id = $wpdb->insert_id;
        //     $conversation = $this->get_conversation($temp_conversation_id);
        //     if($conversation[0]->user_1 == $temp_sender_id) {
        //         $user = 'user_2';
        //     } else {
        //         $user = 'user_1';
        //     }
        // $this->mark_as_unread($user,$temp_conversation_id);
        // $this->converstation_update_date($temp_conversation_id);
        // } else {
        //     // $id = false;
        // }
    }

    // delete message
    // imap_delete( $hMail, $idxMsg );
    // expunge deleted messages
        // imap_expunge( $hMail );

        // // close
        imap_close( $hMail );
}

?>