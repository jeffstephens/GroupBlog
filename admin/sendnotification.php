<?php
session_start();
if(!isset($_SESSION['familysite']))
  Header("Location: ../login.php?go=admin/index.php");

if($_SESSION['familysite'] != 1)
  Header("Location: ../index.php?error=permissiondenied");

include "../system/parse.php";
dbconnect();

if($_POST['mode'] == "send")
  {
  //Broadcast notification
  $subject = stripslashes($_POST['subject']);
  $body = stripslashes(str_replace("\r\n", "<br />", $_POST['body']));
  
  if(strlen($subject) > 0 AND strlen($body) > 0)
    {
    $recipients = mysql_query("SELECT * FROM `". get_table('users') ."`;");
    
    while($mailrow = mysql_fetch_assoc($recipients))
      {
      send_notification($mailrow['ID'], $_POST['from'], $subject, $body);
      }
    
    $success = "Your global notification has been successfully sent.";
    }
  
  else
    $error = "You need to have both a subject and a body in order to send a notification.";
  }
?><html>
<head>
<link type="text/css" href="../system/style.css" rel="stylesheet">
<title><?php print get_table('SiteName'); ?>: Administration: Send Global Notification</title>
</style>
<?php include "../header.php"; ?>
</head>

<body>
<?php
include "../userinfo.php";

if(isset($success))
  print '
<script type="text/javascript">
getUpdates();
</script>
';

print '
<h1>Send Global Notification</h1>
';

if(isset($error))
  print '<div class="red">'. $error .'</div>';

elseif(isset($success))
  print '<div class="green" style="text-align: center">'. $success .' <a href="../index.php">Home &raquo;</a></div>';

else
  print '<div class="yellow" style="text-align: center">This will deliver a notification to <strong>everyone\'s</strong> inbox on the '.get_table('SiteName'). '.</div>';

print '
<br />
';

if($_POST['mode'] == "preview")
  {
  $subject = stripslashes($_POST['subject']);
  $body = stripslashes(str_replace("\r\n", "<br />", $_POST['body']));
  
  print "<h2>Preview: {$subject}</h2>
<p class=\"infobar\">Sent by ". authornamelookup($_POST['from']) ." on ". date("n/j/y", (time() + get_table('dateoffset'))) .".</p>
<div class=\"green\">{$body}</div>
<br />";
  }

if(!isset($success))
  {
  print '
<h2>Compose Message</h2>
<div class="green">
<form action="sendnotification.php?mail" method="post">
<label>From:</label>
<input type="radio" name="from" id="fromsystem" value="-2"';

  if($_POST['from'] != 1)
    print " checked";
  
  print '> <label for="fromsystem">Family Website System</label>
<input type="radio" name="from" id="fromyou" value="'. $_SESSION['familysite'] . '"';

  if($_POST['from'] == 1)
    print " checked"; 
  
  print '> <label for="fromyou">'. authornamelookup($_SESSION['familysite']) .'</label><br />
<label for="subject">Subject:</label> <input type="text" name="subject" id="subject" size="50"';
  
  if($_POST['mode'] == "preview")
    print ' value="'. $subject .'"';
  
  print '><br />
<label for="body">Body: (HTML is allowed, BBCode will not be parsed, line breaks will be converted)</label><br />
<textarea name="body" id="body" rows="10" cols="60">';
  
  if($_POST['mode'] == "preview")
    print stripslashes($_POST['body']);
  
  print '</textarea><br />
<br />
<input type="radio" name="mode" value="preview" id="preview" checked> <label for="preview">Preview</label>
';
  if(isset($_GET['mail']))
    {
    if(strlen($subject) > 0 AND strlen($body) > 0)
      print '<br /><input type="radio" name="mode" value="send" id="send"> <label for="send" style="font-weight: 900">Send Notification</label>
  ';
    
    else
      print '<br /><strong class="error">You cannot send a notification without both a subject and a body.</strong>
  ';
    }
  print '
<br />
<input type="submit" value="Submit &raquo;">
</form>
</div>';
  }
?>
<p id="footer"><?php print get_table('SiteName'); ?> Administration - Send Global Notification. Last Update: <?php print date("g:i a, n/j/Y", (time() + get_table('dateoffset'))); ?></p>
</body>
</html>