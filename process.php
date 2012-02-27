<?php
//Process tasks that need to be completed before anyone uses the family site.
ob_start();
session_start();

include "system/parse.php";
dbconnect();

$lastprocesshandle = fopen("system/lastprocess.txt", "r");
$lastprocess = fread($lastprocesshandle, filesize("system/lastprocess.txt"));
fclose($lastprocesshandle);

//Check to see if there are any polls of the week whose summaries haven't been sent out, and if there are, send summaries to users who want them.
$sendqueue = mysql_query("SELECT * FROM `". get_table('pollq') ."` WHERE `published` = 0 AND `Week` < ". date("W", (time() + get_table('dateoffset'))) ." AND `Year` <= ". date("Y", (time() + get_table('dateoffset'))) .";");

if(mysql_num_rows($sendqueue) > 0)
  {
  while($poll = mysql_fetch_assoc($sendqueue))
    {
    //Go through the mailing process for every poll that hasn't been published yet.
    $msg = "This week's poll has been completed. Here are the results. Alternatively, you can view them <a href=\"vote.php?poll={$poll['ID']}\">here</a>.<br />
<span style=\"color: #666\">This poll was created by </span>". authornamelookup($poll['Creator']) ."<span style=\"color: #666\">.</span><br />
<br />
<strong>". stripslashes($poll['Question']) ."</strong>
<br />
<table>\n<tr><td style=\"text-align: right\">\n";
    
    $answers = explode("\n", stripslashes($poll['Answers']));
    $totalvotes = mysql_num_rows(mysql_query("SELECT * FROM `". get_table('polla') ."` WHERE `Question` = '{$poll['ID']}';"));
    
    for($i = 0; $i < count($answers); $i++)
      $msg .= "{$answers[$i]}<br />\n";
    
    $msg .= "</td>\n<td style=\"text-align: left\">";
    
    for($j = 0; $j < count($answers); $j++)
      {
      $votes = mysql_num_rows(mysql_query("SELECT * FROM `". get_table('polla') ."` WHERE `Question` = '{$poll['ID']}' AND `Choice` = ". ($j + 1) .";"));
      $msg .= "<img src=\"system/images/pollbar.jpg\" width=\"". (($votes * 20) + 1) ."\" height=\"16\"> {$votes} (". round((100 * ($votes / $totalvotes)), 1) ."%)<br />\n";
      }
    
    $msg .= "</td></tr>\n</table>
<br />
<p style=\"text-align: center; font-size: 80%; color: #666\">You are receiving this message because you are subscribed to get Poll of the Week reports. You can change this in your <a href=\"preferences.php?specialpref=pollresults\">preferences</a>.";
    
    //Mail it out to every user that wants it.
    $recipients = mysql_query("SELECT * FROM `". get_table('users') ."` WHERE `pollreport` = 1 AND `Active` = 1;");
    
    while($user = mysql_fetch_assoc($recipients))
      send_notification($user['ID'], -2, "Poll of the Week Results", $msg);
    
    //Mark this poll as having been mailed out.
    mysql_query("UPDATE `". get_table('pollq') ."` SET `published` = 1 WHERE `ID` = {$poll['ID']};");
    }
  }


if((time() - $lastprocess) > 604800)
  {
  //Purge peoples' inboxes so that they have a maximum of 50 notifications
  $errors = "";
  
  $users = mysql_query("SELECT * FROM `". get_table('users') ."` ORDER BY `ID` ASC;");
  if(mysql_error()) $errors .= "<br />\n". mysql_error();
  
  while($row = mysql_fetch_assoc($users))
    {
    $msgcount = mysql_num_rows(mysql_query("SELECT * FROM `". get_table('notifications') ."` WHERE `InboxID` = {$row['ID']};"));
    if($msgcount > 50)
      {
      //Delete old notifications until there are only 50
      $targets = ($msgcount - 50); //The number of messages to be deleted
      
      $deletequery = mysql_query("SELECT * FROM `". get_table('notifications') ."` WHERE `InboxID` = {$row['ID']} ORDER BY `ID` ASC LIMIT {$targets};");
      if(mysql_error()) $errors .= "<br />\n". mysql_error();
      
      while($deleterow = mysql_fetch_assoc($deletequery))
        {
        mysql_query("DELETE FROM `". get_table('notifications') ."` WHERE `InboxID` = {$row['ID']} AND `ID` = {$deleterow['ID']} LIMIT 1;");
        if(mysql_error()) $errors .= "<br />\n". mysql_error();
        }
      }
    }
    
  
  //Check for accounts that have had no activity in 30 days and classify them as "inactive"
  $inactive_accounts = mysql_query("SELECT * FROM `". get_table('users') ."` WHERE (UNIX_TIMESTAMP() - `LastVisit`) > 2592000;");
  if(mysql_num_rows($inactive_accounts) > 0)
    {
    $inactivation = mysql_query("UPDATE `". get_table('users') ."` SET `Active` = 0 WHERE (UNIX_TIMESTAMP() - `LastVisit`) > 2592000;");
    if(mysql_error()) $errors .= "<br />\n". mysql_error();
    }

  //Do the inverse; re-activate accounts that need it
  $active_accounts = mysql_query("SELECT * FROM `". get_table('users') ."` WHERE (UNIX_TIMESTAMP() - `LastVisit`) < 2592000 AND `Active` = 0;");
  if(mysql_num_rows($active_accounts) > 0)
    {
    $activation = mysql_query("UPDATE `". get_table('users') ."` SET `Active` = 1 WHERE (UNIX_TIMESTAMP() - `LastVisit`) < 2592000 AND `Active` = 0;");
    if(mysql_error()) $errors .= "<br />\n". mysql_error();
    }
  
  $updatehandle = fopen("system/lastprocess.txt", "w");
  fwrite($updatehandle, time());
  fclose($updatehandle);
  
  if(strlen($errors) == 0)
    send_notification(1, -2, "Site Cleanup Complete", "The weekly site cleanup has been completed with no errors.");
  else
    send_notification(1, -2, "Site Cleanup Errors", "<div class=\"red\">
Errors occurred during the site cleanup.<br />
{$errors}.
</div>");
  }

Header("Location: {$_GET['go']}");
?>