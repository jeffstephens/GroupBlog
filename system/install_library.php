<?php
ini_set('display_errors', '1');

function dbsetupSQL($sitename, $dbname, $prefix) {
	$query = "DROP TABLE IF EXISTS `" . $prefix . "blog`;
CREATE TABLE IF NOT EXISTS `" . $prefix . "blog` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Title` varchar(255) DEFAULT NULL,
  `Author` varchar(255) DEFAULT NULL,
  `Posted` int(11) DEFAULT NULL,
  `Entry` text,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='". $sitename ." blog database' ;

DROP TABLE IF EXISTS `" . $prefix . "comments`;
CREATE TABLE IF NOT EXISTS `" . $prefix . "comments` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Author` varchar(255) DEFAULT NULL,
  `Posted` int(11) DEFAULT NULL,
  `Comment` text,
  `EntryID` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='comments for ". $sitename ."' ;

DROP TABLE IF EXISTS `". $prefix . "files`;
CREATE TABLE IF NOT EXISTS `". $prefix . "files` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Type` varchar(10) NOT NULL DEFAULT '',
  `Owner` int(11) NOT NULL DEFAULT '0',
  `Filename` varchar(255) NOT NULL DEFAULT '' COMMENT 'The friendly filename (without the timestamp)',
  `Path` text NOT NULL,
  `EntryID` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `". $prefix . "help`;
CREATE TABLE IF NOT EXISTS `". $prefix . "help` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Title` varchar(255) DEFAULT NULL,
  `Category` varchar(255) DEFAULT NULL,
  `Article` text,
  `Posted` int(11) DEFAULT NULL,
  `Modified` int(11) DEFAULT NULL,
  `Version` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='help database for ". $sitename ."';

DROP TABLE IF EXISTS `". $prefix . "notifications`;
CREATE TABLE IF NOT EXISTS `". $prefix . "notifications` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `InboxID` int(11) NOT NULL DEFAULT '0',
  `SenderID` int(11) NOT NULL DEFAULT '0',
  `Sent` int(11) NOT NULL DEFAULT '0',
  `Read` tinyint(1) NOT NULL DEFAULT '0',
  `Subject` varchar(255) NOT NULL DEFAULT '',
  `Body` text NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 PACK_KEYS=0 COMMENT='Notification database for ". $sitename ."';

DROP TABLE IF EXISTS `". $prefix . "polla`;
CREATE TABLE IF NOT EXISTS `". $prefix . "polla` (
  `Question` int(11) NOT NULL DEFAULT '0',
  `Voter` int(11) NOT NULL DEFAULT '0',
  `Choice` int(11) NOT NULL DEFAULT '0',
  `Timestamp` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Poll answers for ". $sitename ."';

DROP TABLE IF EXISTS `". $prefix . "pollq`;
CREATE TABLE IF NOT EXISTS `". $prefix . "pollq` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Creator` int(11) DEFAULT NULL,
  `Week` int(11) DEFAULT NULL,
  `Year` int(11) NOT NULL DEFAULT '0',
  `Question` varchar(255) DEFAULT NULL,
  `Answers` text,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='Poll questions for ". $sitename ."' ;

DROP TABLE IF EXISTS `". $prefix . "users`;
CREATE TABLE IF NOT EXISTS `". $prefix . "users` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) DEFAULT NULL,
  `Email` varchar(255) DEFAULT NULL,
  `Password` varchar(255) DEFAULT NULL,
  `Registered` int(11) DEFAULT NULL,
  `LastVisit` int(11) DEFAULT NULL,
  `UpdateInterval` int(11) DEFAULT NULL,
  `secretq` varchar(255) NOT NULL DEFAULT '' COMMENT 'Secret Question for password recovery',
  `secreta` varchar(255) NOT NULL DEFAULT '' COMMENT 'Secret Answer for password recovery',
  `pollreport` tinyint(1) NOT NULL DEFAULT '0',
  `Active` int(1) NOT NULL DEFAULT '1' COMMENT 'Whether the account is active or not',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 PACK_KEYS=0 COMMENT='user database for ". $sitename ."' ;

INSERT INTO `f_help` (`ID`, `Title`, `Category`, `Article`, `Posted`, `Modified`, `Version`) VALUES
(1, 'What if I forget my password?', 'account', '(bold)If you forget your password:(endbold)\r\nIn case it happens, be sure to set up your account with a Secret Question and Answer on the (link:../preferences.php)preferences(endlink) page. Then, if you forget your password, you can recover it by answering this question.\r\n\r\nJust click \\&quot;Forgot your Password?\\&quot; on the login screen and you can begin the password reset process. It\\''s a piece of cake.\r\n\r\nIn any other case, just email me at jefftheman45@gmail.com and I\\''ll reset your password for you.', 1199776369, 1203056479, 4),
(2, 'The timestamp is way off!', 'blog', 'If it says a blog entry or comment was posted way off of when it actually was, this is because of a time zone difference error. Please just let me know where it\\''s showing up with either the form on the bottom of the (link:index.php)Help Index(endlink) page or by emailing me at (link:mailto:jefftheman45@gmail.com)jefftheman45@gmail.com(endlink) and I\\''ll fix it right away.\r\n\r\nAlso, if you ever see that something is listed as being posted \\&quot;yesterday\\&quot; when you just posted it, it\\''s another similar error. Please let me know, and I\\''ll fix it as soon as possible.', 1199779422, 1199859790, 2),
(3, 'I saw a weird error.', 'blog', 'If instead of the blog you saw something about \\&quot;mysql\\&quot;, it\\''s just a problem with the server. Check back in a few minutes or so, and the problem should fix itself.\r\n\r\nHowever, these errors should not be showing up, and you should instead see the generic error, \\&quot;(bold)Error:(endbold) A connection to the database could not be established. Please try again later or check your configuration file.\\&quot; In this case, do the same thing; it\\''s a temporary issue that will go away by itself.', 1199859313, 1199859313, 1),
(4, 'What happens when I delete a notification?', 'notification', 'When you delete a notification, (bold)all that\\''s deleted is the message.(endbold) NO blog entries or comments are affected. Notifications are simply messages letting you know about something going on on the family site for your convenience. When you delete them, nothing else is affected except that message.\r\n\r\nIn fact, it\\''s sometimes better to delete old notifications because it makes it easier to find the ones you want.', 1199860746, 1203054857, 2),
(5, 'Where are my notifications?', 'notification', 'On the homepage, a maximum of five notifications are shown, and only from the past week. This is to help clean up and organize the homepage. And don\\''t worry -- all of your older notifications are always in your (link:../inbox.php)Notification Inbox(endlink).\r\n\r\nAlso, you\\''re only allowed to have 50 notifications at any given time. Each week, old notifications are purged so that everyone has no more than 50 in their inbox. If there are notifications you want to keep, be sure to delete other ones so the ones you want are saved.', 1201330051, 1205217227, 3),
(6, 'How do I write a new entry?', 'blog', 'If you\\''d like to create a new blog entry to share with everyone on the site, it\\''s easy! First, click on the \\&quot;(link:../addentry.php)Add Entry(endlink)\\&quot; link in the upper-left hand corner of your screen. Then, simply fill out a title for your post, write the entry itself, and after you preview it once, you can choose the (bold)Publish Entry(endbold) option and after you hit (bold)Go(endbold), everyone can see your post!\r\n\r\nNote: If you\\''d like, you can also attach a photo that goes along with the entry. This is completely optional, and if you\\''re on a slower internet connection, you might want to skip this step. However, it\\''s appreciated if we can see what you\\''re talking about with a photo! ;)', 1201998310, 1201998436, 2),
(7, 'How do I add a blog comment?', 'blog', 'If you\\''re looking at someone\\''s entry and you\\''d like to share a story of your own, or just make a comment on it, you can add one by following these steps:\r\n\r\n(list:numbers)(item)From either the (link:../index.php)Homepage(endlink) or the (link:../blog.php)Blog Browser(endlink), click on the comment link. It will either say \\&quot;1 comment\\&quot; or \\&quot;No comments yet. Add one\\&quot;.(enditem)(item)You are now viewing just one entry. At the bottom of the entry, there is a listing of comments. Click on the \\&quot;Add Comment\\&quot; link, fill out your comment, and post it. Now everyone can see what you said!(enditem)(endlist:numbers)', 1201998818, 1201998885, 3),
(8, 'How do I edit attachments?', 'blog', '(bold)Updated as of Version 1.4(endbold)\r\n\r\nTo change an attachment on an existing blog entry, simply view that blog entry and click on (bold)[Delete](endbold) next to the title of the attachement. (Either \\&quot;Photo Attachment\\&quot;, \\&quot;Video Attachment\\&quot;, or \\&quot;File Attachment\\&quot;.) You will be prompted to confirm this action. Click (bold)OK(endbold), and the attachment is gone.\r\n(italic)Note: You can only do this on your own blog entries.(enditalic)\r\n\r\nTo add an attachment to an existing blog entry, view that blog entry and click (bold)attach a file &raquo;(endbold) underneath the blog comments. Click (bold)Add Media &raquo;(endbold), and you will be prompted for a photo, video, or file attachment. Click (bold)Next Step(endbold), and the file has been attached!\r\n(italic)Note: You can only do this on your own blog entries.(enditalic)', 1204528884, 1208409662, 4),
(9, 'What does it mean if my account is inactive?', 'account', 'If you receive a notice that your account is inactive, this is because you haven\\''t logged in for 30 or more days. When this happens, you no longer receive notifications for:(list:bullets)\r\n(item)New Blog Entries(enditem)(item)New Blog Comments(enditem)(item)The Poll of the Week Subscription (if you are subscribed)(enditem)(endlist:bullets)\r\nYou will, however, still receive:(list:bullets)\r\n(item)Announcements from the site administrator (Family Website System)(enditem)(item)Responses to help questions(enditem)(item)Confirmations of actions you perform (if you\\''re using the site while your account is inactive)(enditem)(endlist:bullets)\r\nYour account will remain inactive until the next Weekly Cleanup. This process is performed once a week; the time varies. If when this happens your account is inactive yet you have logged in within the last 30 days, your account will be reactivated and you will once again receive notifications as usual.', 1208409999, 1208410114, 2);";

	return $query;
}

function buildConfig($config) {
	$version = "2.0a";
	$lockdown = "There is a temporary problem with ". $config['SiteName'] .", and all activity has been suspended until it can be resolved. Please check back in a few hours; the problem is being worked on. We apologize for this inconvenience.";
	
	$output = '<?php
// Configuration file for family website
$config = Array();

// Software version
$config[\'version\'] = "'. $version .'";
$config[\'SiteName\'] = "'. $config['SiteName'] . '"; // The name of the site

// Optional "lockdown" (uncomment to activate)
// $config[\'Lockdown\'] = "'. $lockdown .'";

// Optional value for the lockdown to automatically expire. Enter in the format "10 September 2008 11:34pm";
// $config[\'Lockdown_Expiration\'] = "";

$config[\'dbhost\'] = "'. $config['dbhost'] .'"; // The database server to connect to
$config[\'dbusername\'] = "'. $config['dbusername'] .'"; // The mysql username
$config[\'dbpassword\'] = "'. $config['dbpassword'] .'"; // The mysql password
$config[\'dbname\'] = "'. $config['dbname'] .'"; // The database to use
// A summary of what this server is used for:
$config[\'serverinfo\'] = "'. $config['serverinfo'] .'";
$config[\'publicurl\'] = "'. $config['publicurl'] .'"; // The absolute URL to be linked to, including the installation directory (no trailing slash)
$config[\'dateoffsethours\'] = "'. $config['dateoffsethours'] .'"; // The number of hours that should be ADDED to the time (negative values for subtraction)
$config[\'regauth\'] = "'. $config['regauth'] .'"; // The authorization code required to register. Leave blank to hide this field on the registration page.

// Don\'t change this; it converts the dateoffsethours value from seconds to hours
$config[\'dateoffset\'] = ($config[\'dateoffsethours\'] * 60 * 60);

// Allow these users limited administrative privilages (User IDs)
$config[\'mods\'] = Array(1);

// Database tables:
$config[\'dbprefix\'] = "'. $config['dbprefix'] .'";
$config[\'blog\'] = $config[\'dbprefix\'] . "blog";
$config[\'comments\'] = $config[\'dbprefix\'] . "comments";
$config[\'help\'] = $config[\'dbprefix\'] . "help";
$config[\'users\'] = $config[\'dbprefix\'] . "users";
$config[\'notifications\'] = $config[\'dbprefix\'] . "notifications";
$config[\'pollq\'] = $config[\'dbprefix\'] . "pollq";
$config[\'polla\'] = $config[\'dbprefix\'] . "polla";
$config[\'files\'] = $config[\'dbprefix\'] . "files";

// Password encryption salt (don\'t change this unless you know what you\'re doing)
$config[\'salt\'] = "'. $config['salt'] .'";
?>';
	
	// Back up old config first
	if(file_exists("config.inc.php")) {
		$oldconfig = @file_get_contents("config.inc.php");
		file_put_contents("config.inc.bak.php", $oldconfig);
	}
	
	// Create new config file
	$handle = fopen("config.inc.php", "w");
	$write = fwrite($handle, $output);
	fclose($handle);

	if($write === false)
		return false;
	
	return true;
}
?>