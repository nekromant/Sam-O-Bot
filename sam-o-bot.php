<?php

// activate full error reporting
//error_reporting(E_ALL & E_STRICT);

include 'XMPPHP/XMPP.php';
include 'config.php';
include "process.php";
$s = new samolink($config_dbhost,$config_dbuser,$config_dbpass,$config_dbname,$config_update_interval,$config_owner);

$s->load_plugins($config_plugins);
#Use XMPPHP_Log::LEVEL_VERBOSE to get more logging for error reports
#If this doesn't work, are you running 64-bit PHP with < 5.2.6?
$conn = new XMPPHP_XMPP($config_jhost, $config_jport, $config_juser, $config_jpass, $config_jresource, $config_jhost, $printlog=true, $loglevel=XMPPHP_Log::LEVEL_INFO);
//$conn = new XMPPHP_XMPP('jabber.ru', 5222, 'sam-o-bot', 'koikke', 'sylwer', 'jabber.ru', $printlog=true, $loglevel=XMPPHP_Log::LEVEL_INFO);
$conn->useSSL(false);
$conn->useEncryption(false);
$conn->autoSubscribe();

$vcard_request = array();

try {
    $conn->connect();
    echo "done\n";
    $s->jconn=$conn;
    while(!$conn->isDisconnected()) {
        //if ($conn->isSessionStarted())
	//{
	  $msg = $s->update();
        	if ($msg!==false)
		{
	  	$conn->message($msg['to'], $body=$msg['body']);
		}
	//}
    	$payloads = $conn->processUntil(array('message', 'presence', 'end_stream', 'session_start', 'vcard'),5);
    	foreach($payloads as $event) {
    		$pl = $event[1];
    		switch($event[0]) {
    			case 'message': 
    				print "---------------------------------------------------------------------------------\n";
    				print "Message from: {$pl['from']}\n";
    				if(isset($pl['subject'])) print "Subject: {$pl['subject']}\n";
//     				print $pl['body'] . "\n";
				//print_r($pl);
    				print "---------------------------------------------------------------------------------\n";
    				//$conn->message($pl['from'], $body="Thanks for sending me \"{$pl['body']}\".", $type=$pl['type']);
				//	$cmd = explode(' ', $pl['body']);
				echo "memory stats: ".memory_get_usage()."\n";
				if ($pl['body']!="") 
				{
				  $body = explode("\n",$pl['body']);
				  //This is a hack to allow multiple commands to be executed in one message
				  //TODO: I guess a limit would be nice as well as proper respose filter. 
				  //While now bot responds to EVERY command
				  foreach ($body as $cmd)
				  {
				    $pl['body']=$cmd;
				    $s->process_message($pl);
				    //echo "$r \n";
				    //$conn->message($pl['from'], $body=$r, $type=$pl['type']);
				  }
				}
				    echo "memory stats: ".memory_get_usage()."\n";


    				//if($cmd[0] == 'quit') $conn->disconnect();
    				/*if($cmd[0] == 'break') $conn->send("</end>");
    				if($cmd[0] == 'vcard') {
						if(!($cmd[1])) $cmd[1] = $conn->user . '@' . $conn->server;
						// take a note which user requested which vcard
						$vcard_request[$pl['from']] = $cmd[1];
						// request the vcard
						$conn->getVCard($cmd[1]);
					}*/
    			break;
    			case 'presence':
    				print "Presence: {$pl['from']} [{$pl['show']}] {$pl['status']}\n";
    			break;
    			case 'session_start':
    			    print "Session Start\n";
			    	$conn->getRoster();
				$s->session_started=true;
    				$conn->presence($status="Как истина - где-то рядом...");
    			break;
				case 'vcard':
					// check to see who requested this vcard
					$deliver = array_keys($vcard_request, $pl['from']);
					// work through the array to generate a message
					print_r($pl);
					$msg = '';
					foreach($pl as $key => $item) {
						$msg .= "$key: ";
						if(is_array($item)) {
							$msg .= "\n";
							foreach($item as $subkey => $subitem) {
								$msg .= "  $subkey: $subitem\n";
							}
						} else {
							$msg .= "$item\n";
						}
					}
					// deliver the vcard msg to everyone that requested that vcard
					foreach($deliver as $sendjid) {
						// remove the note on requests as we send out the message
						unset($vcard_request[$sendjid]);
    					$conn->message($sendjid, $msg, 'chat');
					}
				break;
    		}
    	}
    }
} catch(XMPPHP_Exception $e) {
    die($e->getMessage());
}
