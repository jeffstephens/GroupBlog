<?php
ob_start();
session_start();
include "../system/parse.php";

if(!isset($_SESSION['familysite']))
  Header("Location: ../login.php?go=admin/manageusers.php");

if($_SESSION['familysite'] != 1)
  Header("Location: ../index.php?error=permissiondenied");

dbconnect();
?><html>
<head>
<link type="text/css" rel="stylesheet" href="../system/style.css">
<title><?php print get_table('SiteName'); ?>: User Management</title>
<?php include "../header.php"; ?>
</head>

<body>
<?php include "../userinfo.php"; ?>
<h1>User Management</h1>
<div class="yellow" style="text-align: center">
Here you can view the status of user accounts on the site.</a>
</div>

<br />

<h2>Inactive Accounts</h2>
<div class="green">
Inactive accounts are accounts which have not been logged into within 30 days. They do not receive automated notifications.<br />
<span style="color: #666">If a user is listed as not having logged in for less than 30 days, their account will automatically be re-activated during the Weekly Cleanup.</span>

<?php
$query = mysql_query("SELECT * FROM `". get_table('users') ."` WHERE `Active` = 0 ORDER BY `LastVisit` DESC;");

if(mysql_num_rows($query) > 0)
  {
  print "\n<br />\n<br /><ul>\n";
  
  while($row = mysql_fetch_assoc($query))
    print "<li><strong>{$row['Name']}</strong> ({$row['Email']})<br />
<span style=\"color: #666\">Hasn't logged in for ". ceil((time() - $row['LastVisit']) / 60 / 60 / 24) ." days<br />
Registered ". date("g:i a, n/j/Y", $row['Registered']) ."</span></li>\n";
  
  print "</ul>";
  }

else
  print "<br /><strong>No accounts are currently inactive.</strong>";
?>
</div>
<br />
<h2>Accounts Approaching Inactivity</h2>
<div class="blue">
These accounts have not been logged into for 15 days or more.

<?php
$query = mysql_query("SELECT * FROM `". get_table('users') ."` WHERE (UNIX_TIMESTAMP() - `LastVisit`) > 1296000 AND (UNIX_TIMESTAMP() - `LastVisit`) < 2592000;");

if(mysql_num_rows($query) > 0)
  {
    print "\n<br />\n<br /><ul>\n";
  
  while($row = mysql_fetch_assoc($query))
    print "<li><strong>{$row['Name']}</strong> ({$row['Email']})<br />
<span style=\"color: #666\">Hasn't logged in for ". ceil((time() - $row['LastVisit']) / 60 / 60 / 24) ." days<br />
Registered ". date("g:i a, n/j/Y", ($row['Registered'] + get_table('dateoffset'))) ."</span></li>\n";
  
  print "</ul>";
  }

else
  print "<br /><strong>No accounts are currently in this category.</strong>";
?>
</div>
<br />
<p id="footer"><?php print get_table('SiteName'); ?> Administration: User Management. Last updated: <?php print date("g:i a, n/j/Y", (getlastmod() + get_table('dateoffset'))); ?></p>
</body>
</html>