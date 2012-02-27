<?php
session_start();
if(!isset($_SESSION['familysite']))
  Header("Location: ../login.php?go=admin/index.php");

if($_SESSION['familysite'] != 1)
  Header("Location: ../index.php?error=permissiondenied");

include "../system/parse.php";
dbconnect();
?><html>
<head>
<link type="text/css" href="../system/style.css" rel="stylesheet">
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
<div class="yellow" style="text-align: center">Here you can view many aspects of the core system and manage website data.</div>
<br />
<h2>Administrative Actions</h2>
<div class="orange">
<ul>
  <li><a href="sendnotification.php">Send Global Notification</a></li>
</ul>
</div>

<br />
<h2>Data Management</h2>
<div class="green">
<ul>
  <li><a href="manageblog.php">Manage Blog Entries</a></li>
  <li><a href="managecomments.php">Manage Comments</a></li>
  <li><a href="managefiles.php">Manage Uploaded Files</a></li>
  <li><a href="managepolls.php">Manage Polls</a></li>
  <li><a href="managenotifications.php">Manage Notifications</a></li>
  <li><a href="manageusers.php">Manage Users</a></li>
  <li><a href="defaultdata.php">Install Default Data</a></li>
</ul>
</div>
<br />
<h2>Server Configuration</h2>
<div class="blue">
<?php print get_table('serverinfo'); ?><br />
<span>Software Version:</span> <?php print get_table('version'); ?><br />
<span>Site Title:</span> <?php print get_table('SiteName'); ?><br />
<span>Database Host:</span> <?php print get_table('dbhost'); ?><br />
<span>Database in Use:</span> <?php print get_table('dbname'); ?><br />
<span>Database Username:</span> <?php if(get_table('dbusername') == "root") print '<span class="error">';
  print get_table('dbusername');
  if(get_table('dbusername') == "root") print '</span> <span style="font-size: 80%"><em>Using the root account is not recommended for security reasons. You might want to create a new database account with access only to the <strong>'. get_table('dbname') .'</strong> database.</em></span>'; ?><br />
<span>Blog Table:</span> <?php print get_table('blog'); ?><br />
<span>Comment Table:</span> <?php print get_table('comments'); ?><br />
<span>User Table:</span> <?php print get_table('users'); ?><br />
<span>Help Table:</span> <?php print get_table('help'); ?><br />
<span>Notification Table:</span> <?php print get_table('notifications'); ?><br />
<span>Poll Question Table:</span> <?php print get_table('pollq'); ?><br />
<span>Poll Answer Table:</span> <?php print get_table('polla'); ?><br />
<span>File Tracking Table:</span> <?php print get_table('files'); ?><br />
<span>Installation Directory:</span> http://<?php print get_table('publicurl'); ?>/<br />
<span>Local Server Time:</span> <?php print date("g:i a, n/j/Y"); ?><br />
<span>Family Website Adjusted Time:</span> <?php print date("g:i a, n/j/Y", (time() + get_table('dateoffset'))); ?> (<?php print get_table('dateoffsethours'); ?> hour difference)
</div>
<br />
<p id="footer"><?php print get_config("SiteName"); ?> Administration - Last System Update: <?php print date("n/j/Y", (getlastmod() + get_table('dateoffset'))); ?></p>
</body>
</html>