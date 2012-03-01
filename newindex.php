<?php
include "system/parse.php";
include "system/init.php";
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link type="text/css" rel="stylesheet" href="system/style.css" />
<title><?php print get_config('SiteName'); ?> Home</title>
</head>
<body>
<?php include "userinfo.php"; ?>

<h1><?php print get_config('SiteName'); ?></h1>
<div class="green box center">
	<p>Welcome<?php
print ", " . $userinfo['Name'] ."!";
?></p>
</div>
</body>
</html>