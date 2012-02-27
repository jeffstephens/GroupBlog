<?php
// Process submitted data
error_reporting(E_ALL);
ini_set('display_errors', '1');
$step = 0;

$config = Array();

$config['version'] = "2.0a";
$config['SiteName'] = "";
$config['dbhost'] = "";
$config['dbusername'] = "";
$config['dbpassword'] = "";
$config['dbname'] = "";
$config['serverinfo'] = "";
$config['publicurl'] = "";
$config['dateoffsethours'] = "";
$config['dbprefix'] = "";
$config['regauth'] = "";

if(isset($_POST['submitted1'])) {
	require "install_library.php";
	require "parse.php";
	
	// Build config file
	
	$config['SiteName'] = $_POST['SiteName'];
	$config['dbhost'] = $_POST['dbhost'];
	$config['dbusername'] = $_POST['dbusername'];
	$config['dbpassword'] = $_POST['dbpassword'];
	$config['dbname'] = $_POST['dbname'];
	$config['serverinfo'] = $_POST['serverinfo'];
	$config['publicurl'] = $_POST['publicurl'];
	$config['dateoffsethours'] = $_POST['dateoffsethours'];
	$config['dbprefix'] = $_POST['dbprefix'];
	$config['regauth'] = $_POST['regauth'];
	
	//buildConfig($config);
	
	// Build database
	
	$connection = mysql_connect($config['dbhost'], $config['dbusername'], $config['dbpassword']);
	
	if($connection) {
		$config['dbname'] = sanitize($config['dbname']);
		$createquery = mysql_query("CREATE DATABASE IF NOT EXISTS `". $config['dbname'] ."`;");
		
		if($createquery) {
			$selectdb = mysql_select_db(sanitize($config['dbname']));
			
			if($selectdb) {
				$sql = dbsetupSQL(sanitize($config['SiteName']), $config['dbname'], sanitize($config['dbprefix']));
				print nl2br($sql);
				$buildquery = mysql_query($sql);
				
				if($buildquery) {
				
				}
				
				else {
					$error1[] = "Could not build database structure. Make sure your MySQL account has sufficient permissions (CREATE, specifically).<br />\n<span style=\"color: #666\">". mysql_error() ."</span>";
				}
			}
			
			else {
			
			}
		}
		
		else {
			$error1[] = "Could not create database `". $config['dbname'] ."`.<br />\n<span style=\"color: #666\">". mysql_error() ."</span>";
		}
	}
	
	else {
		$error1[] = "Database connection couldn't be established. Check your credentials and your database configuration and try again.<br />\n<span style=\"color: #666\">". mysql_error() ."</span>";
	}
	
	$step = 1;
}

elseif(isset($_POST['submitted2'])) {
	
	$step = 2;
}

$topmessage = Array();
$topdivcolor = "green";

$topmessage[0] = "<strong>Hi there!</strong> We'll walk you through the installation of the CMS here. Don't worry, it won't take long.";

if(isset($error1)) {
	$topdivcolor = "red";
	$topmessage[1] = "<strong>Uh oh!</strong> Something went wrong.";
	
	foreach($error1 as $row) {
		$topmessage[1] .= "<br />\n" . "	" . $row;
	}
}

else
	$topmessage[1] = "<strong>Nice!</strong> Basic configuration is done, and the database has been built. We just need a few more things.<br />
	<span style=\"color: #666\">If you want to change these configuration settings in the future, take a look at /system/config.inc.php. (UI coming in a future release)</span>";

// Guess site URL
$urlstring = $_SERVER['SERVER_NAME'];
?><html>
<head>
<title>CMS Setup</title>
<link type="text/css" href="style.css" rel="stylesheet" />
</head>
<body>
<h1>Installation</h1>
<div class="<?php print $topdivcolor; ?>" style="text-align: center">
	<p><?php
	print $topmessage[$step];
	?></p>
</div>

<form action="install.php" method="post">
<?php
if(!isset($_POST['submitted1']) || isset($error1)):
?>
<input type="hidden" name="submitted1" value="true" />

<h2>Site Basics</h2>
<div class="yellow">
	<p>
		<label for="SiteName">Site Name:</label><br />
		<input type="text" name="SiteName" id="SiteName" size="40" value="<?php print $config['SiteName']; ?>" /><br />
		
		<label for="serverinfo">Site Description:</label><br />
		<input type="text" name="serverinfo" id="serverinfo" size="40" value="<?php print $config['serverinfo']; ?>" /> <em>A brief description of what this site is for.</em><br />
		
		<label for="publicurl">Site URL:</label><br />
		<input type="text" name="publicurl" id="publicurl" size="40" value="<?php print $urlstring; ?>" /><br />
		
		<label for="dateoffsethours">Time Offset: (hours)</label><br />
		<input type="text" name="dateoffsethours" id="dateoffsethours" size="40" value="0" value="<?php print $config['dateoffsethours']; ?>" /> <em>Current server time: <?php print date("g:i a, n/j/y"); ?></em><br />
		
		<label for="regauth">Registration Auth Code (optional):</label><br />
		<input type="text" name="regauth" id="regauth" size="40" value="<?php print $config['regauth']; ?>" /> <em>Will only be asked for if you create one here</em>
	</p>
</div>

<h2>Database Setup</h2>
<div class="yellow">
	<p>
		<label for="dbhost">Database Host:</label><br />
		<input type="text" name="dbhost" id="dbhost" value="<?php if(isset($config['SiteName'])) print $config['SiteName']; else print 'localhost'; ?>" onfocus="if(this.value=='localhost') this.select();" size="40" /> <em>Probably localhost</em><br />
		
		<label for="dbusername">Database User:</label><br />
		<input type="text" name="dbusername" id="dbusername" value="<?php print $config['dbusername']; ?>" size="40" /><br />
		
		<label for="dbpassword">Database Password:</label><br />
		<input type="text" name="dbpassword" id="dbpassword" value="<?php print $config['dbpassword']; ?>" size="40" /><br />
		
		<label for="dbname">Database Name:</label><br />
		<input type="text" name="dbname" id="dbname" value="<?php print $config['dbname']; ?>" size="40" /> <em>Will be created if necessary</em><br />
		
		<label for="dbprefix">Database Table Prefix:</label><br />
		<input type="text" name="dbprefix" id="dbprefix" value="<?php print $config['dbprefix']; ?>" size="40" /><br />
	</p>
</div>
<?php
elseif(!isset($_POST['submitted2']) || isset($error2)):
?>

<input type="hidden" name="submitted2" value="true" />

<h2>Administrator Account Setup</h2>
<div class="yellow">
	<p>
		<label for="username">Username:</label><br />
		<input type="text" name="username" id="username" /><br />
		
		<label for="password">Password:</label><br />
		<input type="password" name="password" id="password" /><br />
		
		<label for="cpassword">Password Again:</label><br />
		<input type="password" name="cpassword" id="cpassword" /><br />
		
	</p>
</div>

<?php endif; ?>
<p style="font-size: 200%; text-align: center"><a href="javascript:void(0);" onclick="document.forms[0].submit();">Next Step &raquo;</a></p>
<noscript><p style="text-align: center"><input type="submit" value="Next Step &raquo;" /><br />(Use this or enable Javascript)</p></noscript>
</form>
</body>
</html>