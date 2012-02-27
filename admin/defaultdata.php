<?php
session_start();
include "../system/parse.php";

if(isset($_GET['install'])) {
	if(!isset($_SESSION['familysite']))
		die("Location: ../login.php?go=admin/index.php");
	
	if(!in_array($_SESSION['familysite'], get_config("mods")))
		die("Location: ../index.php?error=permissiondenied");
	
	$mode = $_GET['install'];

	if($mode == "basichelp")
		{
		$connection = dbconnect();
		
$helpdata = array(
  array('ID' => '1','Title' => 'What if I forget my password?','Category' => 'account','Article' => '(bold)If you forget your password:(endbold)
In case it happens, be sure to set up your account with a Secret Question and Answer on the (link:../preferences.php)preferences(endlink) page. Then, if you forget your password, you can recover it by answering this question.

Just click \\&quot;Forgot your Password?\\&quot; on the login screen and you can begin the password reset process. It\\\'s a piece of cake.

In any other case, just email me at jefftheman45@gmail.com and I\\\'ll reset your password for you.','Posted' => '1199776369','Modified' => '1203056479','Version' => '4'),
  array('ID' => '2','Title' => 'The timestamp is way off!','Category' => 'blog','Article' => 'If it says a blog entry or comment was posted way off of when it actually was, this is because of a time zone difference error. Please just let me know where it\\\'s showing up with either the form on the bottom of the (link:index.php)Help Index(endlink) page or by emailing me at (link:mailto:jefftheman45@gmail.com)jefftheman45@gmail.com(endlink) and I\\\'ll fix it right away.

Also, if you ever see that something is listed as being posted \\&quot;yesterday\\&quot; when you just posted it, it\\\'s another similar error. Please let me know, and I\\\'ll fix it as soon as possible.','Posted' => '1199779422','Modified' => '1199859790','Version' => '2'),
  array('ID' => '3','Title' => 'I saw a weird error.','Category' => 'blog','Article' => 'If instead of the blog you saw something about \\&quot;mysql\\&quot;, it\\\'s just a problem with the server. Check back in a few minutes or so, and the problem should fix itself.

However, these errors should not be showing up, and you should instead see the generic error, \\&quot;(bold)Error:(endbold) A connection to the database could not be established. Please try again later or check your configuration file.\\&quot; In this case, do the same thing; it\\\'s a temporary issue that will go away by itself.','Posted' => '1199859313','Modified' => '1199859313','Version' => '1'),
  array('ID' => '4','Title' => 'What happens when I delete a notification?','Category' => 'notification','Article' => 'When you delete a notification, (bold)all that\\\'s deleted is the message.(endbold) NO blog entries or comments are affected. Notifications are simply messages letting you know about something going on on the family site for your convenience. When you delete them, nothing else is affected except that message.

In fact, it\\\'s sometimes better to delete old notifications because it makes it easier to find the ones you want.','Posted' => '1199860746','Modified' => '1203054857','Version' => '2'),
  array('ID' => '5','Title' => 'Where are my notifications?','Category' => 'notification','Article' => 'On the homepage, a maximum of five notifications are shown, and only from the past week. This is to help clean up and organize the homepage. And don\\\'t worry -- all of your older notifications are always in your (link:../inbox.php)Notification Inbox(endlink).

Also, you\\\'re only allowed to have 50 notifications at any given time. Each week, old notifications are purged so that everyone has no more than 50 in their inbox. If there are notifications you want to keep, be sure to delete other ones so the ones you want are saved.','Posted' => '1201330051','Modified' => '1205217227','Version' => '3'),
  array('ID' => '6','Title' => 'How do I write a new entry?','Category' => 'blog','Article' => 'If you\\\'d like to create a new blog entry to share with everyone on the site, it\\\'s easy! First, click on the \\&quot;(link:../addentry.php)Add Entry(endlink)\\&quot; link in the upper-left hand corner of your screen. Then, simply fill out a title for your post, write the entry itself, and after you preview it once, you can choose the (bold)Publish Entry(endbold) option and after you hit (bold)Go(endbold), everyone can see your post!

Note: If you\\\'d like, you can also attach a photo that goes along with the entry. This is completely optional, and if you\\\'re on a slower internet connection, you might want to skip this step. However, it\\\'s appreciated if we can see what you\\\'re talking about with a photo! ;)','Posted' => '1201998310','Modified' => '1201998436','Version' => '2'),
  array('ID' => '7','Title' => 'How do I add a blog comment?','Category' => 'blog','Article' => 'If you\\\'re looking at someone\\\'s entry and you\\\'d like to share a story of your own, or just make a comment on it, you can add one by following these steps:

(list:numbers)(item)From either the (link:../index.php)Homepage(endlink) or the (link:../blog.php)Blog Browser(endlink), click on the comment link. It will either say \\&quot;1 comment\\&quot; or \\&quot;No comments yet. Add one\\&quot;.(enditem)(item)You are now viewing just one entry. At the bottom of the entry, there is a listing of comments. Click on the \\&quot;Add Comment\\&quot; link, fill out your comment, and post it. Now everyone can see what you said!(enditem)(endlist:numbers)','Posted' => '1201998818','Modified' => '1201998885','Version' => '3'),
  array('ID' => '8','Title' => 'How do I edit attachments?','Category' => 'blog','Article' => '(bold)Updated as of Version 1.4(endbold)

To change an attachment on an existing blog entry, simply view that blog entry and click on (bold)[Delete](endbold) next to the title of the attachement. (Either \\&quot;Photo Attachment\\&quot;, \\&quot;Video Attachment\\&quot;, or \\&quot;File Attachment\\&quot;.) You will be prompted to confirm this action. Click (bold)OK(endbold), and the attachment is gone.
(italic)Note: You can only do this on your own blog entries.(enditalic)

To add an attachment to an existing blog entry, view that blog entry and click (bold)attach a file &raquo;(endbold) underneath the blog comments. Click (bold)Add Media &raquo;(endbold), and you will be prompted for a photo, video, or file attachment. Click (bold)Next Step(endbold), and the file has been attached!
(italic)Note: You can only do this on your own blog entries.(enditalic)','Posted' => '1204528884','Modified' => '1208409662','Version' => '4'),
  array('ID' => '9','Title' => 'What does it mean if my account is inactive?','Category' => 'account','Article' => 'If you receive a notice that your account is inactive, this is because you haven\\\'t logged in for 30 or more days. When this happens, you no longer receive notifications for:(list:bullets)
(item)New Blog Entries(enditem)(item)New Blog Comments(enditem)(item)The Poll of the Week Subscription (if you are subscribed)(enditem)(endlist:bullets)
You will, however, still receive:(list:bullets)
(item)Announcements from the site administrator (Family Website System)(enditem)(item)Responses to help questions(enditem)(item)Confirmations of actions you perform (if you\\\'re using the site while your account is inactive)(enditem)(endlist:bullets)
Your account will remain inactive until the next Weekly Cleanup. This process is performed once a week; the time varies. If when this happens your account is inactive yet you have logged in within the last 30 days, your account will be reactivated and you will once again receive notifications as usual.','Posted' => '1208409999','Modified' => '1208410114','Version' => '2')
);		
		
		foreach($helpdata as $row) {
			$attempt = mysql_query("INSERT INTO `". get_config('help') ."` (`Title`, `Category`, `Article`, `Posted`, `Modified`, `Version`) VALUES ('". $row['Title'] ."', '". $row['Category'] ."', '". $row['Article'] ."', '". $row['Posted'] ."', '". $row['Modified'] ."', 1);");
			
			if(!$attempt)
				$error[] = mysql_error();
		}
		
		mysql_close($connection);
		
		if(!isset($error))
			die("<span class=\"success\">Installed!</span>");
		else
			{
			$report = "Install failed for basichelp. Error dump:<br />";
			
			foreach($error as $row) {
				$report .= $error ."\n<br />\n";
				}
			
			send_notification(1, -1, "Package install failed", $report);
			die("<span class=\"error\">Install failed</span>");
			}
	}
	
	else {
		die("<span class=\"error\">Unknown package requested; install failed</span>");
	}
}

else
	{
	if(!isset($_SESSION['familysite']))
		Header("Location: ../login.php?go=admin/index.php");
	
	if(!in_array($_SESSION['familysite'], get_config("mods")))
		Header("Location: ../index.php?error=permissiondenied");
	}

$connection = dbconnect();
?><html>
<head>
<link type="text/css" href="../system/style.css" rel="stylesheet">
<script type="text/javascript" src="../system/engine.js"></script>
<script type="text/javascript">
function install(package) {
var entry = getHTTPObject();

if(entry)
{
  entry.onreadystatechange = function() {
  if(entry.readyState == 4 && entry.status == 200)
    document.getElementById(package + 'status').innerHTML=entry.responseText;
  };
  entry.open("GET", "defaultdata.php?install="+package, true);
  entry.send(null);
}

else
  alert('Fatal error: Your browser does not support AJAX technology, which is required for this to work.\n\nPlease consider upgrading your browser.');
}
</script>
<title><?php print get_table('SiteName'); ?>: Administration</title>
<style type="text/css">
span {
color: #666 }
</style>
<?php include "../header.php"; ?>
</head>

<body>
<?php include "../userinfo.php"; ?>
<h1><?php print get_table('SiteName'); ?> Administration</h1>
<div class="yellow" style="text-align: center">Here you can install additional data that ships with the system.<br />
<span style="color: #666">Note: this panel doesn't check if things are already installed, so don't accidentally duplicate things!</span></div>
<br />

<h2>Help Articles</h2>
<div class="blue">
<ul>
<li><strong>Basic Help Articles</strong> <span id="basichelpstatus"><?php

print '<a href="javascript:void(0);" onclick="install(\'basichelp\');">Install</a>
';

?></span><br />
A set of help articles describing the basic functionality of the site.</li>
</ul>
</div>

<p id="footer"><?php print get_config("SiteName"); ?> Administration - Last System Update: <?php print date("n/j/Y", (getlastmod() + get_table('dateoffset'))); ?></p>
</body>
</html>
<?php
mysql_close($connection);
?>