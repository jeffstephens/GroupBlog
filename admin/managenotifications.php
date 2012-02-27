<?php
ob_start();
session_start();
include "../system/parse.php";

if(!isset($_SESSION['familysite']))
  Header("Location: ../login.php?go=admin/managenotifications.php");

if($_SESSION['familysite'] != 1)
  Header("Location: ../index.php?error=permissiondenied");

dbconnect();
?><html>
<head>
<link type="text/css" rel="stylesheet" href="../system/style.css">
<title><?php print get_table('SiteName'); ?>: Notification Management</title>
<?php include "../header.php"; ?>
</head>

<body>
<?php include "../userinfo.php"; ?>
<h1>Notification Management</h1>
<div class="yellow" style="text-align: center">
You can use this to view all users who have more than 50 notifications and view other notification information. <a href="sendnotification.php">Send Global Notification &raquo;</a>
</div>

<br />

<h2>Users Exceeding Notification Limit</h2>
<?php
$users = mysql_query("SELECT * FROM `". get_table('users') ."`;");
$exceedcount = 0;

while($row = mysql_fetch_assoc($users))
  {
  $msgcount = mysql_num_rows(mysql_query("SELECT * FROM `". get_table('notifications') ."` WHERE `InboxID` = {$row['ID']};"));
  if($msgcount > 50)
    {
    if($exceedcount == 0)
      print "<div class=\"red\">\n<ul>\n";
    
    print "<li>". authornamelookup($row['ID']) ." ({$msgcount})</li>\n";
    $exceedcount++;
    }
  }

if($exceedcount > 0)
  print "</ul>\n<br />\nThese users' inboxes will be reduced to 50 notifications at the next site cleanup.";

else
  print "<div class=\"green\">\nNo users have more than 50 notifications.";
?>
</div>
<br />
<h2>Weekly Site Cleanup</h2>
<div class="blue">
<?php
$lastprocesshandle = fopen("../system/lastprocess.txt", "r");
$lastprocess = fread($lastprocesshandle, filesize("../system/lastprocess.txt"));
fclose($lastprocesshandle);
?>
Once a week, the site is &quot;cleaned up&quot; automatically. This process consists of deleting notifications until no user has more than 50 in his or her inbox. The last cleanup occurred at <strong><?php print date("g:i a, n/j/y", ($lastprocess + get_table('dateoffset'))); ?></strong>, which means the next cleanup is scheduled to occur at <strong><?php print date("g:i a, n/j/y (\\t\h\i\s l)", (($lastprocess + 604800) + get_table('dateoffset'))); ?></strong>.
</div>
<br />
<h2>Notification Statistics</h2>
<div class="gray">
<?php
$tableinfo = mysql_fetch_assoc(mysql_query("SHOW TABLE STATUS LIKE '". get_table('notifications') ."';"));

print "Total Existing Notifications: <strong>{$tableinfo['Rows']}</strong><br />
Notifications Sent To Date: <strong>". ($tableinfo['Auto_increment'] - 1) ."</strong><br />
Average Notification Size: <strong>". round($tableinfo['Avg_row_length'] / 1000, 1) ."KB</strong><br />
Size of All Notifications: <strong>". round($tableinfo['Data_length'] / 1000, 1) ."KB</strong>";
?>
</div>
<br />
<p id="footer"><?php print get_table('SiteName'); ?> Administration - Manage Notifications. Last Update: <?php print date("g:i a, n/j/y", (getlastmod() + get_table('dateoffset'))); ?></p>
</body>
</html>