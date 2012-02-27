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
";

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