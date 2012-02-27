<?php
$lastaccess = $_COOKIE['LastAccess'];
include "system/parse.php";

@mysql_connect(get_table('dbhost'), get_table('dbusername'), get_table('dbpassword'));
@mysql_select_db(get_table('dbname'));

$report = "";

$newmsgs = @mysql_num_rows(@mysql_query("SELECT * FROM `". get_table('notifications') ."` WHERE `InboxID` = {$_SESSION['familysite']} AND `Sent` > {$lastaccess};"));

if(mysql_error())
  {
  $error = true;
  $newmsgs_error = mysql_error();
  }

if($newmsgs == 1)
  $report .= "<br />You have 1 new notification. <a href=\"http://". get_table('publicurl') ."/inbox.php\">Inbox &raquo;</a>";

elseif($newmsgs > 1)
  $report .= "<br />You have {$newmsgs} new notifications. <a href=\"http://". get_table('publicurl') ."/inbox.php\">Inbox &raquo;</a>";

$newvotes = @mysql_num_rows(@mysql_query("SELECT * FROM `". get_table('polla') ."` WHERE `Timestamp` > {$lastaccess};"));

if(mysql_error())
  {
  $error = true;
  $newvotes_error = mysql_error();
  }

if($newvotes > 0)
  $report .= "<br />There are new votes in the <a href=\"http://". get_table('publicurl') ."/vote.php\">Poll &raquo;</a>.";

if(strlen($report) == 0)
  $report = "<br />No new activity.";

if(isset($error))
  {
  $report = "<br />An error occurred while checking for updates.";
  send_notification(1, -2, "Error Report", "An error occurred while checking for updates.<br />
<br />
<strong>New Messages Query:</strong> ({$newmsgs_error})<br />
<blockquote>SELECT * FROM `". get_table('notifications') ."` WHERE `InboxID` = {$_SESSION['familysite']} AND `Sent` > {$lastaccess};</blockquote>
<br />
<strong>New Votes Query:</strong> ({$newvotes_error})<br />
<blockquote>SELECT * FROM `". get_table('polla') ."` WHERE `Timestamp` > {$lastaccess};</blockquote>");
  }

die("<p style=\" margin: 0; padding: 0\"><strong>". get_table('SiteName') ." Update:</strong> {$report}</p>");
?>