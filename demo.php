<?
#################################################################################
# SprachBox Voicemail Demo by Mark Hagebaum (PC Web Version, not yet responsive)
#################################################################################


// Note: 
// you need to enable the IMAP email password of your t-online email acount in order to use the demo

// please replace username and password with your real t-online account data
$current_mailbox = array(
		'label' 	=> 'DT SprachBox Demo',
		'enable'	=> true,
		'mailbox' 	=> '{secureimap.t-online.de:993/imap/ssl}INBOX',
		'username' 	=> 'XXXXXXXX@t-online.de',
		'password' 	=> 'XXXXXXXX'
);

// a function to decode MIME message header extensions and get the text
function decode_imap_text($str){
    $result = '';
    $decode_header = imap_mime_header_decode($str);
    foreach ($decode_header AS $obj) {
        $result .= htmlspecialchars(rtrim($obj->text, "\t"));
	}
    return $result;
}
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="target-densitydpi=device-dpi" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<title>DT SprachBox Fetch Demo </title>
<link href="res/demo.css" rel="stylesheet" type="text/css" />
</head>
<body>
<div id="wrapper">
<div id="main">
<div id="mailboxes">
	
			<div class="mailbox">
			<h2><?=$current_mailbox['label']?></h2>
			<?
			if (!$current_mailbox['enable']) {
			?>
				<p>This mailbox is disabled.</p>
			<?
			} else {
				
				// Open an IMAP stream to our mailbox
				$stream = @imap_open($current_mailbox['mailbox'], $current_mailbox['username'], $current_mailbox['password']) or die("Error: Username or Password are wrong.");
				imap_reopen($stream, "{secureimap.t-online.de:993/imap/ssl}INBOX.SprachBox") or die(implode(", ", imap_errors()));

				
				if (!$stream) { 
				?>
					<p>Could not connect to: <?=$current_mailbox['label']?>. Error: <?=imap_last_error()?></p>
				<?
				} else {
					// Get our messages from the last week
					// Instead of searching for this week's message you could search for all the messages in your inbox using: $emails = imap_search($stream,'ALL');
					// $emails = imap_search($stream, 'SINCE '. date('d-M-Y',strtotime("-8 week")));
					$emails = imap_search($stream,'ALL');
					
					if (!count($emails)){
					?>
						<p>No e-mails found.</p>
					<?
					} else {
						
						// Delete existing messages in the message folder 
						if (is_dir('messages')) { array_map('unlink', glob("messages/*.mp3"));} 
						else { mkdir('messages');} 								
												
						// New- Fetch the email content & attachment
						$msgCount = imap_num_msg($stream); 
						
						for ($X = $msgCount; $X > 0; $X--) { 					
						
						    
							$info  = imap_fetchstructure($stream,$X);									
							$mcont = imap_fetch_overview($stream,$X,0);
							$numparts = count($info->parts);
							$Y=2;
							//test
							$file = imap_fetchbody($stream, $X, 1, FT_PEEK); 
							$dec=base64_decode($file); // decode the file
							file_put_contents("messages/Voicemail".$X.".mp3",$dec);  // write MP3 file in message folder
							$ndate = $rest = substr($mcont[0]->date,0, -9); // cut the GMT and seconds out of the date
							$ndate = $rest = substr($rest,4); // cut the day of the week from the string
							?><span class="attach"> <a href="<?="messages/Voicemail".$X.".mp3"?>"> <img style=" width:30px;height:30px; vertical-align:middle; margin-left:20px;" src="res/play2.png"><?=decode_imap_text($mcont[0]->subject)?> <?=$ndate?> </a></span><br><br><?
							
							
							
							/* old code version 
							
							if ($numparts >= 2) {
							foreach ($info->parts as $part) {
							
							if ($part->disposition == "ATTACHMENT") { 
								
								$name = $part->dparameters[0]->value;
								$file = imap_fetchbody($stream, $X, $Y, FT_PEEK); 
								$dec=base64_decode($file); // decode the file
								file_put_contents("messages/".$name,$dec);  // write MP3 file in message folder
								$ndate = $rest = substr($mcont[0]->date,0, -9); // cut the GMT and seconds out of the date
								$ndate = $rest = substr($rest,4); // cut the day of the week from the string
								?>
							    <div class="email_item clearfix <?=$mcont[0]->seen?'read':'unread'?>"> <? // add a different class for seperating read and unread e-mails ?>
								<span class="subject" title="<?=decode_imap_text($mcont[0]->subject)?>"><?=decode_imap_text($mcont[0]->subject)?></span>							
								<span class="date"> <?=$ndate?> </span>
								<span class="attach"> <a href="<?="messages/".$name?>"> <img src="res/play.png" ></a> </span> 
								</div>
							    <?
								$Y=$Y+1;	
							}
							}
							} */
							}
						// End of my fetch activity
						// Note: in this routine all slam down calls are hided. Only real messages are shown
						
						
					imap_close($stream); 
				}
				}
			} 
			?>
			</div>
			</div>
			</div><!-- #main -->
<div id="footer"><p> SprachBox Demo by MH</p>
</div>
</div><!-- #wrapper -->
</body>
</html>