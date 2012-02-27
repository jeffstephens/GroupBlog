<?php
ob_start();
session_start();
if(!isset($_SESSION['familysite']))
  Header("Location: login.php");

include "system/parse.php";
dbconnect();

//Serve data to AJAX applications
if(isset($_GET['ajaxinbox']))
  {
  //Send the inbox
  $query = mysql_query("SELECT * FROM `". get_table('notifications') ."` WHERE `InboxID` = {$_SESSION['familysite']} ORDER BY `Sent` DESC;");

  if(mysql_num_rows($query) > 0)
    {
    print '
  <table style="margin: auto">
  <tr><th><img src="system/images/msgicon.gif" alt="Read/Unread"></th><th>Sender</th><th>Subject</th><th>Sent</th><th>Tools</th></tr>';
    
    $color = "EDEDED";
    
    while($row = mysql_fetch_assoc($query))
      {
      print "
  <tr style=\"background-color: #{$color}\" id=\"msg{$row['ID']}\"><td><a href=\"inbox.php?msg={$row['ID']}\"><img src=\"system/images/msgicon";
      
      if($row['Read'] == 0)
        print ".gif\" alt=\"Unread\">";
      else
        print "_read.gif\" alt=\"Read\">";
      
      print "</a></td><td><a href=\"inbox.php?msg={$row['ID']}\">". authorlookup($row['SenderID']) ."</a></td><td><strong><a href=\"inbox.php?msg={$row['ID']}\">". blogitize($row['Subject']) ."</a></strong></td><td><a href=\"inbox.php?msg={$row['ID']}\">". date("n/j/y", ($row['Sent'] + get_table('dateoffset'))) ."</a></td><td>";
      
      if($row['Read'] == 0)
        print "<a href=\"inbox.php?msg={$row['ID']}\" onClick=\" ajaxread({$row['ID']}); return false\"><img src=\"system/images/msgicon_read.gif\" alt=\"Mark as Read\"></a>";
      else
        print "<a href=\"inbox.php?unread={$row['ID']}\" onClick=\" ajaxunread({$row['ID']}); return false\"><img src=\"system/images/msgicon.gif\" alt=\"Mark as Unread\"></a>";
      
      print " <a href=\"inbox.php?msg={$row['ID']}&amp;delete\" onClick=\" ajaxdelete({$row['ID']}); return false\"><img src=\"system/images/delete.gif\" alt=\"Delete Message\"></a></td></tr>";
      
      if($color == "EDEDED")
        $color = "FFF";
      else
        $color = "EDEDED";
      }
    
    print '
  </table>';
    }

  else
    print '<strong>You have no notifications.</strong>';
  
  die();
  }

if(isset($_GET['ajaxdelete']))
  {
  //Delete a notification and send a message regarding the result
  $attempt = mysql_query("DELETE FROM `". get_table('notifications') ."` WHERE `ID` = ". mysql_real_escape_string($_GET['ajaxdelete']) ." AND `InboxID` = ". $_SESSION['familysite'] ." LIMIT 1;");
  
  if($attempt)
    print "This notification has been successfully deleted from your inbox.";
  else
    {
    $notificationinfo = mysql_fetch_assoc(mysql_query("SELECT * FROM `". get_table('notifications') ."` WHERE `ID` = ". mysql_real_escape_string($_GET['ajaxdelete']) ." LIMIT 1;"));
    
    print "This notification couldn't be deleted due to a system error. This error has been reported and will be fixed as soon as possible. We apologize for the inconvenience.";
    
    //Report error
    send_notification(1, -2, "Error Report", "An error occurred while attempting to AJAX-delete a notification.<br />
<strong>mysql_error():</strong> ". mysql_error() ."<br />
<strong>Timestamp:</strong> ". date("g:i a \o\\n D, M j", (time() + get_table('dateoffset'))) ."</div>
<br />
<h2>Notification Information</h2>
<div class=\"red\">
<strong>ID:</strong> {$notificationinfo['ID']} (Processed as ". mysql_real_escape_string($_GET['delete']) .")<br />
<strong>Read:</strong> {$notificationinfo['Read']}<br />
<strong>Subject:</strong> {$notificationinfo['Subject']}<br />
<strong>Sent By:</strong> ". authorlookup($notificationinfo['SenderID']) ." (#{$notificationinfo['SenderID']})<br />
<strong>Sent On:</strong> ". date("n/j/y", ($notificationinfo['Sent'] + get_table('dateoffset'))) ." (". ($notificationinfo['Sent'] + get_table('dateoffset')) .")<br />
<strong>Body:</strong><br />
<blockquote>{$notificationinfo['Body']}</blockquote>");
    }
  
  die();
  }

if(isset($_GET['ajaxunread']))
  {
  //Mark the message as unread
  $attempt = mysql_query("UPDATE `". get_table('notifications') ."` SET `Read` = 0 WHERE `ID` = ". mysql_real_escape_string($_GET['ajaxunread']) ." AND `InboxID` = ". $_SESSION['familysite'] ." AND `Read` = 1 LIMIT 1;");
    
    if($attempt)
      print "The message was successfully marked as unread.";
    else
      print "The message could not be marked as unread due to a system error. This error has been reported and will be fixed as soon as possible. We apologize for the inconvenience.";
  
  $notificationinfo = mysql_fetch_assoc(mysql_query("SELECT * FROM `". get_table('notifications') ."` WHERE `ID` = ". mysql_real_escape_string($_GET['ajaxunread']) ." LIMIT 1;"));
  
  if(!$attempt)
    send_notification(1, -2, "Error Report", "An error occurred while attempting to mark a notification as unread.<br />
<strong>mysql_error():</strong> ". mysql_error() ."<br />
<strong>Timestamp:</strong> ". date("g:i a \o\\n D, M j") ."</div>
<br />
<h2>Notification Information</h2>
<div class=\"red\">
<strong>ID:</strong> {$notificationinfo['ID']} (Processed as ". mysql_real_escape_string($_GET['delete']) .")<br />
<strong>Read:</strong> {$notificationinfo['Read']}<br />
<strong>Subject:</strong> {$notificationinfo['Subject']}<br />
<strong>Sent By:</strong> ". authorlookup($notificationinfo['SenderID']) ." (#{$notificationinfo['SenderID']})<br />
<strong>Sent On:</strong> ". date("n/j/y", ($notificationinfo['Sent'] + get_table('dateoffset'))) ." (". ($notificationinfo['Sent'] + get_table('dateoffset')) .")<br />
<strong>Body:</strong><br />
<blockquote>{$notificationinfo['Body']}</blockquote>"); //Report the error
  
  die();
  }

if(isset($_GET['ajaxread']))
  {
  //Mark the message as read
  $attempt = mysql_query("UPDATE `". get_table('notifications') ."` SET `Read` = 1 WHERE `ID` = ". mysql_real_escape_string($_GET['ajaxread']) ." AND `InboxID` = ". $_SESSION['familysite'] ." AND `Read` = 0 LIMIT 1;");
  
  $notificationinfo = mysql_fetch_assoc(mysql_query("SELECT * FROM `". get_table('notifications') ."` WHERE `ID` = ". mysql_real_escape_string($_GET['ajaxread']) ." LIMIT 1;"));
  
  if(!$attempt)
    send_notification(1, -2, "Error Report", "An error occurred while attempting to AJAX-mark a notification as read.<br />
<strong>mysql_error():</strong> ". mysql_error() ."<br />
<strong>Timestamp:</strong> ". date("g:i a \o\\n D, M j") ."</div>
<br />
<h2>Notification Information</h2>
<div class=\"red\">
<strong>ID:</strong> {$notificationinfo['ID']} (Processed as ". mysql_real_escape_string($_GET['delete']) .")<br />
<strong>Read:</strong> {$notificationinfo['Read']}<br />
<strong>Subject:</strong> {$notificationinfo['Subject']}<br />
<strong>Sent By:</strong> ". authorlookup($notificationinfo['SenderID']) ." (#{$notificationinfo['SenderID']})<br />
<strong>Sent On:</strong> ". date("n/j/y", ($notificationinfo['Sent'] + get_table('dateoffset'))) ." (". ($notificationinfo['Sent'] + get_table('dateoffset')) .")<br />
<strong>Body:</strong><br />
<blockquote>{$notificationinfo['Body']}</blockquote>"); //Report the error
  
  if($attempt)
    die("This message has been marked as read.");
  else
    die("This message could not be marked as unread due to a system error. This error has been reported.");
  }
?><html>
<head>
<title><?php print get_table('SiteName'); ?>: Notification Inbox</title>
<link type="text/css" rel="stylesheet" href="system/style.css">
<style type="text/css">
th {
background-color: #003366;
color: #FFF }

img {
border: none }
</style>
<script type="text/javascript" src="system/engine.js"></script>
<script type="text/javascript">
//¡¡ES EL TIEMPO DE AJAX!!
function reloadinbox()
{
var reloadrequest = getHTTPObject();

  if(reloadrequest)
  {
    reloadrequest.onreadystatechange = function() {
    if(reloadrequest.readyState == 4 && reloadrequest.status == 200)
      document.getElementById('inbox').innerHTML=reloadrequest.responseText;
    };
    reloadrequest.open("GET", "inbox.php?ajaxinbox", true);
    reloadrequest.send(null);
  }

else
  alert('Uh oh. Your inbox couldn\'t be reloaded. Kindly hit the refresh button, will you?');
}

function ajaxdelete(ID)
{
  if(confirm("This will permanently delete this notification. (This does not affect the blog or comments)"))
    {
    var deleterequest = getHTTPObject();

    if(deleterequest)
    {
      deleterequest.onreadystatechange = function() {
      if(deleterequest.readyState == 4 && deleterequest.status == 200)
        alert(deleterequest.responseText);
        reloadinbox();
      };
      deleterequest.open("GET", "inbox.php?ajaxdelete="+ID, true);
      deleterequest.send(null);
    }
    }
}

function ajaxunread(ID)
{
var unreadrequest = getHTTPObject();

  if(unreadrequest)
  {
    unreadrequest.onreadystatechange = function() {
    if(unreadrequest.readyState == 4 && unreadrequest.status == 200)
      alert(unreadrequest.responseText);
      reloadinbox();
    };
    unreadrequest.open("GET", "inbox.php?ajaxunread="+ID, true);
    unreadrequest.send(null);
  }

else
  alert('The message couldn\'t be marked as unread. Try again later, please.');
}

function ajaxread(ID)
{
var readrequest = getHTTPObject();

  if(readrequest)
  {
    readrequest.onreadystatechange = function() {
    if(readrequest.readyState == 4 && readrequest.status == 200)
      alert(readrequest.responseText);
      reloadinbox();
    };
    readrequest.open("GET", "inbox.php?ajaxread="+ID, true);
    readrequest.send(null);
  }

else
  alert('The message couldn\'t be marked as read. Try again later, please.');
}
</script>
<?php include "header.php"; ?>
</head>

<body>
<?php
include "userinfo.php";

if(!isset($_GET['msg']))
  print '<h1>Notification Inbox</h1>';

elseif(isset($_GET['delete']))
  print '<h1>Delete Notification</h1>';

else
  print '<h1>Viewing Notification</h1>';

print "\n\n";

if(isset($_GET['deleteall']))
  {
  if(!isset($_GET['sure']))
    print '<div class="red" style="text-align: center">Do you really want to delete all of your notifications?
<form action="inbox.php?deleteall&amp;sure" method="post">
<input type="submit" value="Clear Inbox"> <input type="button" value="Cancel" onClick=" document.location=\'inbox.php\'">
</form>
</div>';
  
  else
    {
    //COMMENCE DELETION
    $attempt = mysql_query("DELETE FROM `". get_table('notifications') ."` WHERE `InboxID` = {$_SESSION['familysite']};");
    
    if($attempt)
      print "<div class=\"green\" style=\"text-align: center\">Your inbox has been cleared.</div>\n";
    else
      {
      print "<div class=\"red\" style=\"text-align: center\">Your inbox couldn't be cleared due to a system error. This error has been reported.</div>\n";
      send_notification(1, -2, "Error Report", "<strong>An error occurred while trying to delete all notifications.</strong><br />
  <br />
  <strong>mysql_error():</strong> ". mysql_error() ."<br />
  <strong>mysql_query:</strong><br />
  <blockquote>DELETE FROM `". get_table('notifications') ."` WHERE `InboxID` = {$_SESSION['familysite']};</blockquote>");
      }
    }
  }

elseif(isset($_GET['msg']))
  {
  if(isset($_GET['delete']) AND !isset($_GET['sure']))
    {
    print '<div class="red" style="text-align: center">
Are you sure you want to delete this notification? Once you delete it, it\'s permanently gone. (The blog entry or comment will not be affected.)<br />
<form action="inbox.php?sure&delete='. $_GET['msg'] .'" method="post">
<input type="submit" value="Delete Notification"> <input type="button" value="Cancel" onClick=" location=\'inbox.php?msg='. $_GET['msg'] .'\';">
</form>
</div>';
    }
  
  else
    {
    $nextquery = mysql_query("SELECT * FROM `". get_table('notifications') ."` WHERE `InboxID` = {$_SESSION['familysite']} AND `ID` < {$_GET['msg']} ORDER BY `ID` DESC LIMIT 1;");
    $prevquery = mysql_query("SELECT * FROM `". get_table('notifications') ."` WHERE `InboxID` = {$_SESSION['familysite']} AND `ID` > {$_GET['msg']} ORDER BY `ID` ASC LIMIT 1;");
    
    if(mysql_num_rows($prevquery) == 1)
      {
      $prevmsg = mysql_fetch_assoc($prevquery);
      $prev = '<a href="inbox.php?msg='. $prevmsg['ID'] .'">&laquo;Previous Message (&quot;'. stripslashes($prevmsg['Subject']) .'&quot;)</a> | ';
      }
    
    else
      $prev = 'No Newer Messages | ';
      
    if(mysql_num_rows($nextquery) == 1)
      {
      $nextmsg = mysql_fetch_assoc($nextquery);
      $next = ' | <a href="inbox.php?msg='. $nextmsg['ID'] .'">Next Message (&quot;'. stripslashes($nextmsg['Subject']) .'&quot;)&raquo;</a>';
      }
    
    else
      $next = ' | No Older Messages';
    
    print '<div class="yellow" style="text-align: center">
'. $prev .'<a href="inbox.php">Inbox</a>'. $next .'
</div>';
    }
  }

elseif(isset($_GET['delete']) AND isset($_GET['sure']))
  {
  //Delete message if they've already said they're sure
  $attempt = mysql_query("DELETE FROM `". get_table('notifications') ."` WHERE `ID` = ". mysql_real_escape_string($_GET['delete']) ." AND `InboxID` = ". $_SESSION['familysite'] ." LIMIT 1;");
  
  if($attempt)
    print "<div class=\"green\" style=\"text-align: center\">This notification has been successfully deleted from your inbox.</div>";
  else
    {
    $notificationinfo = mysql_fetch_assoc(mysql_query("SELECT * FROM `". get_table('notifications') ."` WHERE `ID` = ". mysql_real_escape_string($_GET['delete']) ." LIMIT 1;"));
    
    print "<div class=\"red\" style=\"text-align: center\">This notification couldn't be deleted due to a system error. This error has been reported and will be fixed as soon as possible. We apologize for the inconvenience.</div>";
    
    //Report error
    send_notification(1, -2, "Error Report", "An error occurred while attempting to delete a notification.<br />
<strong>mysql_error():</strong> ". mysql_error() ."<br />
<strong>Timestamp:</strong> ". date("g:i a \o\\n D, M j", (time() + get_table('dateoffset'))) ."</div>
<br />
<h2>Notification Information</h2>
<div class=\"red\">
<strong>ID:</strong> {$notificationinfo['ID']} (Processed as ". mysql_real_escape_string($_GET['delete']) .")<br />
<strong>Read:</strong> {$notificationinfo['Read']}<br />
<strong>Subject:</strong> {$notificationinfo['Subject']}<br />
<strong>Sent By:</strong> ". authorlookup($notificationinfo['SenderID']) ." (#{$notificationinfo['SenderID']})<br />
<strong>Sent On:</strong> ". date("n/j/y", ($notificationinfo['Sent'] + get_table('dateoffset'))) ." (". ($notificationinfo['Sent'] + get_table('dateoffset')) .")<br />
<strong>Body:</strong><br />
<blockquote>{$notificationinfo['Body']}</blockquote>");
    }
  }

elseif(isset($_GET['unread']))
    {
    //Mark message as unread
    $attempt = mysql_query("UPDATE `". get_table('notifications') ."` SET `Read` = 0 WHERE `ID` = ". mysql_real_escape_string($_GET['unread']) ." AND `InboxID` = ". $_SESSION['familysite'] ." AND `Read` = 1 LIMIT 1;");
    
    if($attempt)
      print "<div class=\"green\" style=\"text-align: center\">The message was successfully marked as unread.</div>";
    else
      print "<div class=\"red\" style=\"text-align: center\">The message could not be marked as unread due to a system error. This error has been reported and will be fixed as soon as possible. We apologize for the inconvenience.</div>";
  
  $notificationinfo = mysql_fetch_assoc(mysql_query("SELECT * FROM `". get_table('notifications') ."` WHERE `ID` = ". mysql_real_escape_string($_GET['unread']) ." LIMIT 1;"));
  
  if(!$attempt)
    send_notification(1, -2, "Error Report", "An error occurred while attempting to mark a notification as unread.<br />
<strong>mysql_error():</strong> ". mysql_error() ."<br />
<strong>Timestamp:</strong> ". date("g:i a \o\\n D, M j", (time() + get_table('dateoffset'))) ."</div>
<br />
<h2>Notification Information</h2>
<div class=\"red\">
<strong>ID:</strong> {$notificationinfo['ID']} (Processed as ". mysql_real_escape_string($_GET['delete']) .")<br />
<strong>Read:</strong> {$notificationinfo['Read']}<br />
<strong>Subject:</strong> {$notificationinfo['Subject']}<br />
<strong>Sent By:</strong> ". authorlookup($notificationinfo['SenderID']) ." (#{$notificationinfo['SenderID']})<br />
<strong>Sent On:</strong> ". date("n/j/y", ($notificationinfo['Sent'] + get_table('dateoffset'))) ." (". ($notificationinfo['Sent'] + get_table('dateoffset')) .")<br />
<strong>Body:</strong><br />
<blockquote>{$notificationinfo['Body']}</blockquote>"); //Report the error
    }

else
  print '<div class="yellow" style="text-align: center">Below are the notifications you\'ve received on this site. Notifications are automated messages from the Family Website system.<br />
<a href="inbox.php?deleteall"><img src="system/images/delete.gif"> Delete All Notifications ('. mysql_num_rows(mysql_query("SELECT * FROM `". get_table('notifications') ."` WHERE `InboxID` = {$_SESSION['familysite']} ORDER BY `Sent` DESC;")) .')</a><noscript><br />
<p class="error">Your browser does not support javascript, so you can\'t use some of the cool features on this page. (Note: You can still do everything, just not as quickly) <a href="http://www.mozilla.com/en-US/firefox/?from=getfirefox">Get Firefox</a> so that you can. (Internet Explorer works too).</noscript></div>';

print "\n<br />\n";

if(isset($_GET['msg']))
  {
  //Check to make sure this message belongs to them
  if(mysql_num_rows(mysql_query("SELECT * FROM `". get_table('notifications') ."` WHERE `ID` = ". sanitize($_GET['msg']) ." AND `InboxID` = {$_SESSION['familysite']};")) == 0)
    Header("Location: index.php?error=permissiondenied");
  
  //Mark this message as read
  $attempt = mysql_query("UPDATE `". get_table('notifications') ."` SET `Read` = 1 WHERE `ID` = ". mysql_real_escape_string($_GET['msg']) ." AND `InboxID` = ". $_SESSION['familysite'] ." AND `Read` = 0 LIMIT 1;");
  
  $notificationinfo = mysql_fetch_assoc(mysql_query("SELECT * FROM `". get_table('notifications') ."` WHERE `ID` = ". mysql_real_escape_string($_GET['msg']) ." LIMIT 1;"));
  
  if(!$attempt)
    send_notification(1, -2, "Error Report", "<h1>Error Report</h1>
<div class=\"red\">An error occurred while attempting to mark a notification as read.<br />
<strong>mysql_error():</strong> ". mysql_error() ."<br />
<strong>Timestamp:</strong> ". date("g:i a \o\\n D, M j") ."</div>
<br />
<h2>Notification Information</h2>
<div class=\"red\">
<strong>ID:</strong> {$notificationinfo['ID']} (Processed as ". mysql_real_escape_string($_GET['delete']) .")<br />
<strong>Read:</strong> {$notificationinfo['Read']}<br />
<strong>Subject:</strong> {$notificationinfo['Subject']}<br />
<strong>Sent By:</strong> ". authorlookup($notificationinfo['SenderID']) ." (#{$notificationinfo['SenderID']})<br />
<strong>Sent On:</strong> ". date("n/j/y", ($notificationinfo['Sent'] + get_table('dateoffset'))) ." (". ($notificationinfo['Sent'] + get_table('dateoffset')) .")<br />
<strong>Body:</strong><br />
<blockquote>{$notificationinfo['Body']}</blockquote>
</div>"); //Report the error
  
  $query = mysql_query("SELECT * FROM `". get_table('notifications') ."` WHERE `ID` = ". mysql_real_escape_string($_GET['msg']) ." LIMIT 1;");
  $msg = mysql_fetch_assoc($query);
  
  print "<h2>". blogitize($msg['Subject']) ."</h2>
<p class=\"infobar\">Sent by ". authorlookup($msg['SenderID']) ." on ". date("n/j/y", ($msg['Sent'] + get_table('dateoffset'))) .". <a href=\"inbox.php?unread={$msg['ID']}\"><img src=\"system/images/msgicon.gif\"> Mark as Unread</a> or <a href=\"inbox.php?msg={$msg['ID']}&amp;delete\"><img src=\"system/images/delete.gif\" onClick=\" ajaxdelete({$row['ID']}); return false\"> Delete this Notification</a></p>
<div class=\"green\">{$msg['Body']}</div>
";
  }

else
  {
  print '<div id="inbox">';
  
  $query = mysql_query("SELECT * FROM `". get_table('notifications') ."` WHERE `InboxID` = {$_SESSION['familysite']} ORDER BY `Sent` DESC;");

  if(mysql_num_rows($query) > 0)
    {
    if(mysql_num_rows($query) >= 40)
      print '<div class="red" style="text-align: center; border-top: 1px solid #000; border-bottom: 1px solid #000; padding-bottom: 3px">
<p style="font-weight: 900; margin: 0; padding: 0; font-size: 110%">Inbox Message Limit</p>
You currently have <strong>'. mysql_num_rows($query) .'</strong> notifications in your inbox out of your allowed 50. If you\'d like to save any, please delete some now. Otherwise, they will be deleted from oldest to newest until you only have 50.
</div>
<br />';
    
    print '
  <table style="margin: auto">
  <tr><th><img src="system/images/msgicon.gif" alt="Read/Unread"></th><th>Sender</th><th>Subject</th><th>Sent</th><th>Tools</th></tr>';
    
    $color = "EDEDED";
    
    while($row = mysql_fetch_assoc($query))
      {
      print "
  <tr style=\"background-color: #{$color}\" id=\"msg{$row['ID']}\"><td><a href=\"inbox.php?msg={$row['ID']}\"><img src=\"system/images/msgicon";
      
      if($row['Read'] == 0)
        print ".gif\" alt=\"Unread\">";
      else
        print "_read.gif\" alt=\"Read\">";
      
      print "</a></td><td><a href=\"inbox.php?msg={$row['ID']}\">". authorlookup($row['SenderID']) ."</a></td><td><strong><a href=\"inbox.php?msg={$row['ID']}\">". blogitize($row['Subject']) ."</a></strong></td><td><a href=\"inbox.php?msg={$row['ID']}\">". date("n/j/y", ($row['Sent'] + get_table('dateoffset'))) ."</a></td><td>";
      
      if($row['Read'] == 0)
        print "<a href=\"inbox.php?msg={$row['ID']}\" onClick=\" ajaxread({$row['ID']}); return false\"><img src=\"system/images/msgicon_read.gif\" alt=\"Mark as Read\"></a>";
      else
        print "<a href=\"inbox.php?unread={$row['ID']}\" onClick=\" ajaxunread({$row['ID']}); return false\"><img src=\"system/images/msgicon.gif\" alt=\"Mark as Unread\"></a>";
      
      print " <a href=\"inbox.php?msg={$row['ID']}&amp;delete\" onClick=\" ajaxdelete({$row['ID']}); return false\"><img src=\"system/images/delete.gif\" alt=\"Delete Message\"></a></td></tr>";
      
      if($color == "EDEDED")
        $color = "FFF";
      else
        $color = "EDEDED";
      }
    
    print '
  </table></div>';
    }

  else
    print '<strong>You have no notifications.</strong></div>';
  }

print "<p id=\"footer\">". get_table('SiteName') ." Notification System | Last System Update: ". date("n/j/y", (getlastmod() + get_table('dateoffset'))) ."</p>";
?>
</body>
</html>