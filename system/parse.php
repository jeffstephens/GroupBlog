<?php
ob_start();
@session_start();
setcookie("LastAccess", time()) or send_notification(1, -2, "Error Report", "The LastAccess cookie could not be set.");

if(!@include("config.inc.php")) {
	$allowed = Array("install.php", "install_library.php");
	$filename = explode("/", $_SERVER['PHP_SELF']);
	
	if(!in_array($filename[(sizeof($filename) - 1)], $allowed)) {
		die(error("Site Not Configured", "This site hasn't been set up yet. Check back soon!", true));
	}
}

else {
	require "catcher.php";
}

function error($title, $message, $independent = false)
{
if($independent) {
	$sitetitle = "GroupBlog";
	$style = "<style type=\"text/css\">
body, textarea, input {
	font-family: 'Calibri', 'Arial', sans-serif }

h1 {
	border-bottom: 2px solid #003366;
	color: #003366;
	margin: 0;
	padding: 0;
	text-align: center;
	text-shadow: 1px 1px 2px #444;
	font-size: 250%; }

div p {
	margin-top: 0;
	padding-top: 0 }
	
.red {
	background-color: #FFE5E5;
	font-size: 110%;
	border-radius: 0 0 2px 2px; }
</style>";
}

else {
	$sitetitle = get_config("SiteName");
	$style = "<link type=\"text/css\" href=\"http://". get_config('publicurl') ."/system/style.css\" rel=\"stylesheet\">";
}

print "<html>
<head>
<title>". $sitetitle .": Error</title>
". $style ."
</head>
<body>
	<h1>{$title}</h1>
	<div class=\"red\" style=\"text-align: center\"><p>{$message}</p></div>
</body>
</html>";
}

function genBlowfishSalt() {
	return '$2a$07$5%TZkl3pEE^)(dFFf*&70$'; // TODO: Write random salt generator
}

function dbconnect()
{
$connection = @mysql_connect(get_config('dbhost'), get_config('dbusername'), get_config('dbpassword')) or die(error('Database Error', "A connection to the database could not be established. Please try again later."));
@mysql_select_db(get_config('dbname')) or die(error('Database Error', "A connection to the database could not be established. Please try again later."));
return $connection;
}

// Deprecated
function get_table($tablename)
{
	return get_config($tablename);
}

function get_config($option) {
	if(@include("config.inc.php")) {
		return $config[$option];
	}
	else {
		return "[Configuration File Missing]";
	}
}

function sanitize($input)
{
//To make user-submitted data database-safe
$input = @mysql_real_escape_string($input);
$input = strip_tags($input);
$input = htmlentities($input);
$input = trim($input);
return $input;
}

function blogitize($input)
{
//To add BBCode, line breaks, stripslashes, etc.
$input = stripslashes($input);
$input = str_replace("(list:bullets)", "<ul style=\"padding-top: 0; margin-top: 0; padding-bottom: 0; margin-bottom: 0\">", $input);
$input = str_replace("(ul)", "<ul style=\"padding-top: 0; margin-top: 0; padding-bottom: 0; margin-bottom: 0\">", $input);
$input = str_replace("(list:numbers)", "<ol style=\"padding-top: 0; margin-top: 0; padding-bottom: 0; margin-bottom: 0\">", $input);
$input = str_replace("(ol)", "<ol style=\"padding-top: 0; margin-top: 0; padding-bottom: 0; margin-bottom: 0\">", $input);
$input = str_replace("(item)", "<li>", $input);
$input = str_replace("(li)", "<li>", $input);
$input = str_replace("(enditem)", "</li>", $input);
$input = str_replace("(/li)", "</li>", $input);
$input = str_replace("(endlist:bullets)", "</ul>", $input);
$input = str_replace("(/ul)", "</ul>", $input);
$input = str_replace("(endlist:numbers)", "</ol>", $input);
$input = str_replace("(/ol)", "</ol>", $input);
$input = str_replace("(bold)", "<strong>", $input);
$input = str_replace("(b)", "<strong>", $input);
$input = str_replace("(endbold)", "</strong>", $input);
$input = str_replace("(/b)", "</strong>", $input);
$input = str_replace("(italic)", "<em>", $input);
$input = str_replace("(i)", "<em>", $input);
$input = str_replace("(enditalic)", "</em>", $input);
$input = str_replace("(/i)", "</em>", $input);
$input = str_replace("(underline)", "<u>", $input);
$input = str_replace("(u)", "<u>", $input);
$input = str_replace("(endunderline)", "</u>", $input);
$input = str_replace("(/u)", "</u>", $input);
$input = str_replace("--", "&#9473;", $input);
$input = preg_replace('/\(link:(.+?)\)(.+?)\(endlink\)/', '<a href="$1" target="_blank">$2</a>', $input);
$input = nl2br($input);

//Convert text emoticons to real emoticons

$input = str_replace(":)", "<img src=\"http://". get_table('publicurl') ."/system/emoticons/smile.gif\" alt=\":)\">", $input);
$input = str_replace(":-)", "<img src=\"http://". get_table('publicurl') ."/system/emoticons/smile.gif\" alt=\":-)\">", $input);
$input = str_replace(":D", "<img src=\"http://". get_table('publicurl') ."/system/emoticons/biggrin.gif\" alt=\":D\">", $input);
$input = str_replace(":-D", "<img src=\"http://". get_table('publicurl') ."/system/emoticons/biggrin.gif\" alt=\":-D\">", $input);
$input = str_replace(":P", "<img src=\"http://". get_table('publicurl') ."/system/emoticons/tongueout.gif\" alt=\":P\">", $input);
$input = str_replace(":p", "<img src=\"http://". get_table('publicurl') ."/system/emoticons/tongueout.gif\" alt=\":p\">", $input);
$input = str_replace(":-P", "<img src=\"http://". get_table('publicurl') ."/system/emoticons/tongueout.gif\" alt=\":-P\">", $input);
$input = str_replace(":-p", "<img src=\"http://". get_table('publicurl') ."/system/emoticons/tongueout.gif\" alt=\":-p\">", $input);
$input = str_replace(":(", "<img src=\"http://". get_table('publicurl') ."/system/emoticons/sad.gif\" alt=\":(\">", $input);
$input = str_replace(":-(", "<img src=\"http://". get_table('publicurl') ."/system/emoticons/sad.gif\" alt=\":-(\">", $input);
$input = str_replace(";)", "<img src=\"http://". get_table('publicurl') ."/system/emoticons/wink.gif\" alt=\";)\">", $input);
$input = str_replace(";-)", "<img src=\"http://". get_table('publicurl') ."/system/emoticons/wink.gif\" alt=\";-)\">", $input);
$input = str_replace(":'(", "<img src=\"http://". get_table('publicurl') ."/system/emoticons/crying.gif\" alt=\":'(\">", $input);
$input = str_replace("XD", "<img src=\"http://". get_table('publicurl') ."/system/emoticons/xd.gif\" alt=\"XD\">", $input);
$input = str_replace("xD", "<img src=\"http://". get_table('publicurl') ."/system/emoticons/xd.gif\" alt=\"xD\">", $input);
$input = str_replace("Xd", "<img src=\"http://". get_table('publicurl') ."/system/emoticons/xd.gif\" alt=\"Xd\">", $input);
$input = str_replace("X-D", "<img src=\"http://". get_table('publicurl') ."/system/emoticons/xd.gif\" alt=\"X-D\">", $input);
$input = str_replace("x-D", "<img src=\"http://". get_table('publicurl') ."/system/emoticons/xd.gif\" alt=\"x-D\">", $input);
$input = str_replace("X-d", "<img src=\"http://". get_table('publicurl') ."/system/emoticons/xd.gif\" alt=\"X-d\">", $input);
$input = str_replace(":O", "<img src=\"http://". get_table('publicurl') ."/system/emoticons/shocked.png\" alt=\":O\">", $input);
$input = str_replace(":-0", "<img src=\"http://". get_table('publicurl') ."/system/emoticons/shocked.png\" alt=\":-0\">", $input);
$input = str_replace(":-O", "<img src=\"http://". get_table('publicurl') ."/system/emoticons/shocked.png\" alt=\":-O\">", $input);
$input = str_replace("o_O", "<img src=\"http://". get_table('publicurl') ."/system/emoticons/shocked.png\" alt=\"o_O\">", $input);
$input = str_replace("o.O", "<img src=\"http://". get_table('publicurl') ."/system/emoticons/shocked.png\" alt=\"o.O\">", $input);

return $input;
}

function textemailblogitize($input)
{
//To add BBCode, line breaks, stripslashes, etc.
$input = stripslashes($input);
$input = str_replace("(list:bullets)", "", $input);
$input = str_replace("(list:numbers)", "", $input);
$input = str_replace("(item)", "
-", $input);
$input = str_replace("(enditem)", "", $input);
$input = str_replace("(endlist:bullets)", "", $input);
$input = str_replace("(endlist:numbers)", "", $input);
$input = str_replace("(bold)", "", $input);
$input = str_replace("(endbold)", "", $input);
$input = str_replace("(italic)", "", $input);
$input = str_replace("(enditalic)", "", $input);
$input = str_replace("(underline)", "", $input);
$input = str_replace("(endunderline)", "", $input);
$input = str_replace("(b)", "", $input);
$input = str_replace("(/b)", "", $input);
$input = str_replace("(i)", "", $input);
$input = str_replace("(/i)", "", $input);

return $input;
}

function htmlemailblogitize($input)
{
//To add BBCode, line breaks, stripslashes, etc.
$input = stripslashes($input);
$input = str_replace("(list:bullets)", "<ul>", $input);
$input = str_replace("(list:numbers)", "<li>", $input);
$input = str_replace("(item)", "<li>", $input);
$input = str_replace("(enditem)", "</li>", $input);
$input = str_replace("(endlist:bullets)", "</ul>", $input);
$input = str_replace("(endlist:numbers)", "</li>", $input);
$input = str_replace("(bold)", "<strong>", $input);
$input = str_replace("(endbold)", "</strong>", $input);
$input = str_replace("(italic)", "<em>", $input);
$input = str_replace("(enditalic)", "</em>", $input);
$input = str_replace("(underline)", "<u>", $input);
$input = str_replace("(endunderline)", "</u>", $input);

return $input;
}

function authorlookup($id)
{
if($id <= 0)
  {
  $authors = Array("0" => "someone using the old family site", "-1" => "Help System", "-2" => "Family Website System", "-3" => "Notification System");
  $author = $authors[$id];
  }

else
  {
  dbconnect();
  
  $query = mysql_fetch_assoc(mysql_query("SELECT * FROM `". get_table('users') ."` WHERE `ID` = ". sanitize($id) .";"));
  $author = $query['Name'];
  }

if($id == $_SESSION['familysite'])
  $author = "you";

return $author;
}

function authornamelookup($id)
{
if($id <= 0)
  {
  $authors = Array("0" => "someone using the old family site", "-1" => "Help System", "-2" => get_table('SiteName') ." System", "-3" => "Notification System");
  $author = $authors[$id];
  }

else
  {
  dbconnect();
  
  $query = mysql_fetch_assoc(mysql_query("SELECT * FROM `". get_table('users') ."` WHERE `ID` = ". sanitize($id) .";"));
  $author = $query['Name'];
  }

return $author;
}

function idlookup($name)
{
dbconnect();

$query = mysql_fetch_assoc(mysql_query("SELECT * FROM `". get_table('users') ."` WHERE `Name` = '". sanitize($name) ."';"));
$id = $query['ID'];

return $id;
}

function send_mail($toname, $toemail, $subject, $body)
{
dbconnect();
$id = mysql_fetch_assoc(mysql_query("SELECT * FROM `". get_table('users') ."` WHERE `Email` = '". sanitize($toemail) ."' LIMIT 1;"));
$id = $id['ID'];
$subject = sanitize($subject);
$body = sanitize($body);

if($toname == "Helper")
  $sender = -1;
else
  $sender = -2;

mysql_query("INSERT INTO `". get_table('notifications') ."` (`InboxID`, `SenderID`, `Sent`, `Read`, `Subject`, `Body`) VALUES ('{$id}', '{$sender}', '". time() ."', '0', '{$subject}', '{$body}');") or die(error('Error', "Notification system: <strong class=\"error\">Catastrophic failure</strong><br /><br />". mysql_error()));

send_notification(1, -2, "send_mail() used", "send_mail() was used.");
}

function html_mail($toname, $toemail, $subject, $body1)
{
dbconnect();
$id = mysql_fetch_assoc(mysql_query("SELECT * FROM `". get_table('users') ."` WHERE `Email` = '". sanitize($toemail) ."' LIMIT 1;"));
$id = $id['ID'];
$subject = sanitize($subject);
$body = sanitize($body);

if($toname == "Helper")
  $sender = -1;
else
  $sender = -2;

mysql_query("INSERT INTO `". get_table('notifications') ."` (`InboxID`, `SenderID`, `Sent`, `Read`, `Subject`, `Body`) VALUES ('{$id}', '{$sender}', '". time() ."', '0', '{$subject}', '{$body1}');") or send_mail("Jeff Stephens", "jefftheman45@gmail.com", "Error Report", "An HTML notification couldn't be sent. ". mysql_error());

send_notification(1, -2, "html_mail() used", "html_mail() was used.");
}

function notify($type, $entryID, $otherID)
{
if($type == "comment")
  {
  //Publish comment
  $entryinfo = mysql_fetch_assoc(mysql_query("SELECT * FROM `". get_table('blog') ."` WHERE `ID` = {$entryID} LIMIT 1;")) or send_notification(1, -2, "Error Report", "Couldn't look up entry info. ". mysql_error());
  
  $commentinfo = mysql_fetch_assoc(mysql_query("SELECT * FROM `". get_table('comments') ."` WHERE `ID` = {$otherID} LIMIT 1;")) or send_notification(1, -2, "Error Report", "Couldn't look up comment info. ". mysql_error());
  
  $recipientlist = mysql_query("SELECT * FROM `". get_table('users') ."` WHERE `ID` != {$commentinfo['Author']} AND `Active` = 1;");
  
  while($mailrow = mysql_fetch_assoc($recipientlist))
    {
    $subject = "New Blog Comment";
    $body = "<strong>A new blog comment has been added.</strong> ". authornamelookup($commentinfo['Author']) ." added a comment to ". authornamelookup($entryinfo['Author']) ."'s blog entry, &quot;". blogitize($entryinfo['Title']) ."&quot; at ". date("g:i a, n/j/y", ($commentinfo['Posted'] + get_table('dateoffset'))) .". You can see the comment in full below, or <a href=\"entry.php?entry={$entryinfo['ID']}\">see it in context &raquo;</a>.<br />
<br />
<blockquote>". blogitize($commentinfo['Comment']) ."</blockquote>";
    
    send_notification($mailrow['ID'], -2, $subject, $body);
    }
  }

elseif($type == "entry")
  {
  //Publish entry
  $entryinfo = mysql_fetch_assoc(mysql_query("SELECT * FROM `". get_table('blog') ."` WHERE `ID` = {$entryID} LIMIT 1;")) or send_notification(1, -2, "Error Report", "Couldn't look up entry info. ". mysql_error());
  
  $recipientlist = mysql_query("SELECT * FROM `". get_table('users') ."` WHERE `ID` != {$entryinfo['Author']} AND `Active` = 1;");
  
  while($mailrow = mysql_fetch_assoc($recipientlist))
    {
    //Generate preview - first 50 words
    $entrypreviewarray = explode(" ", textemailblogitize($entryinfo['Entry']));
    $entrypreview = "";
    
    for($i = 0; $i <= 50; $i++)
      $entrypreview .= $entrypreviewarray[$i] . " ";
    
    $entrypreview = textemailblogitize(trim($entrypreview));
    
    $subject = "New Blog Entry";
    $body = "<strong>A new blog entry has been added.</strong> ". authornamelookup($entryinfo['Author']) ." added the entry, &quot;". blogitize($entryinfo['Title']) ."&quot; at ". date("g:i a, n/j/y", ($entryinfo['Posted'] + get_table('dateoffset'))) .". Below is an excerpt; <a href=\"entry.php?entry={$entryinfo['ID']}\">click here</a> to see the full entry plus comments and attachments.<br />
<br />
<blockquote>{$entrypreview}... [<a href=\"entry.php?entry={$entryinfo['ID']}\">Read More &raquo;</a>]</blockquote>";
    
    send_notification($mailrow['ID'], -2, $subject, $body);
    }
  }
}

function send_notification($recipient, $sender, $subject, $body)
{
//New function for sending notifications. Deprecates send_mail() and html_mail() and HTML/Text preference. Released with Version 1.0.
dbconnect();

if($recipient <= 0)
  send_notification($sender, -3, "Notification Failed", "Your notification couldn't be sent because the recipient ". authornamelookup($recipient) ." cannot receive notifications at this time.");

mysql_query("INSERT INTO `". get_table('notifications') ."` (`InboxID`, `SenderID`, `Sent`, `Read`, `Subject`, `Body`) VALUES ('{$recipient}', '{$sender}', ". time() .", 0, '". addslashes($subject) ."', '". addslashes($body) ."');") or send_notification(1, -1, "Mailer-Daemon: Notification Failed", "A notification couldn't be sent.<br />
<br />
<strong>mysql_error():</strong> ". mysql_error() ."<br />
<strong>mysql_query:</strong><br />
<blockquote>INSERT INTO `notifications` (`InboxID`, `SenderID`, `Sent`, `Read`, `Subject`, `Body`) VALUES ('{$recipient}', '{$sender}', ". time() .", {$subject}', '{$body}');</blockquote>

<strong>From:</strong> ". authorlookup($sender) ." (#{$sender})<br />
<strong>To:</strong> ". authorlookup($recipient) ." (#{$recipient})<br />
<strong>Subject:</strong> {$subject}<br />
<strong>Body:</strong><br />
<blockquote>{$body}</blockquote>");
}

function errorlog($message)
{
//Streamlines sending error reports and allows for easy future expansion
send_notification(1, -2, "Error Report", $message);
}
?>