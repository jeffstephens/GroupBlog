<?php
session_start();
if(isset($_SESSION['familysite']) AND !isset($_GET['register']) AND !isset($_GET['check']))
  Header("Location: index.php");

include "system/parse.php";

if(isset($_GET['check']))
  {
  //Return message to AJAX application.
  
  dbconnect();
  
  if(strlen($_POST['email']) > 0)
    {
    if(mysql_num_rows(mysql_query("SELECT * FROM `". get_table('users') ."` WHERE `Email` = '". mysql_real_escape_string($_POST['email']) ."';")) == 0)
      die("<span class=\"success\">{$_POST['email']} is available.</span>");
    else
      die("<span class=\"error\"><strong>Warning:</strong> <u>{$_POST['email']}</u> is already in use. Please use another email address.");
    }
  else
    die();
  }

if(isset($_GET['register']))
  {
  if($_GET['step'] == "1")
    {
    //Register user.
    dbconnect();
    
    $name = mysql_real_escape_string($_POST['name']);
    $email = mysql_real_escape_string($_POST['email']);
    $password = mysql_real_escape_string($_POST['password']);
    $cpassword = mysql_real_escape_string($_POST['cpassword']);
    $auth = mysql_real_escape_string($_POST['auth']);
    
    if(get_config($regauth) == $auth)
      {
      if(mysql_num_rows(mysql_query("SELECT * FROM `". get_table('users') ."` WHERE `Email` = '". mysql_real_escape_string($_POST['email']) ."';")) == 0)
        {
        if($password == $cpassword)
          {
          $password = crypt($password, get_config('salt'));
          $attempt = mysql_query("INSERT INTO `". get_table('users') ."` (`Name`, `Email`, `Password`, `Registered`, `UpdateInterval`) VALUES ('{$name}', '{$email}', '{$password}', ". time() .", 60);");
          
          if($attempt)
            {
            $ID = mysql_fetch_assoc(mysql_query("SELECT * FROM `". get_table('users') ."` WHERE `Email` = '{$email}';"));
            $ID = $ID['ID'];
            $_SESSION['familysite'] = $ID;
            $success = "You have been successfully registered and logged in to ". get_table('SiteName') .". Welcome aboard, {$name}! Now, take a look at some preferences you can set.";
            $pagedone = 1;
            }
          else
            $error .= "You could not be registered due to a system error. Details: ". mysql_error();
          }
        
        else
          $error .= "Please make sure your passwords match.";
        }
      
      else
        $error .= "<u>{$email}</u> is already in use. Please choose a different email address.";
      }
    
    else
      $error .= "The authorization code you provided is incorrect. (You provided {$auth})<br />";
    }
  
  elseif($_GET['step'] == "2")
    {
    //Save preferences
    dbconnect();
    
    $updateinterval = $_POST['CheckInterval'];
    
    $attempt = mysql_query("UPDATE `". get_table('users') ."` SET `UpdateInterval` = {$updateinterval} WHERE `ID` = ". mysql_real_escape_string($_POST['ID']) ." LIMIT 1;");
    
    if($attempt)
      $success = "Your preferences have been saved. You're done! <a href=\"guide.php\">Get Started &raquo;</a>";
    else
      {
      $error = "A system error occurred and your preferences weren't saved... You can change them later in the Control Panel, however. <a href=\"guide.php\">Get Started &raquo;</a>";

      send_notification(1, -2, "Error Report", "<h1>Error Report</h1>
<div class=\"red\">An error occurred in Registration Step 2.<br />
<strong>mysql_error():</strong> ". mysql_error() ."<br />
<strong>Timestamp</strong>: ". date("g:i a \o\\n D, M j", (time() + get_table('dateoffset'))) ."</div>");
      }
    
    $infoquery = mysql_fetch_assoc(mysql_query("SELECT * FROM `". get_table('users') ."` WHERE `ID` = '". mysql_real_escape_string($_POST['ID']) ."';"));
    
    send_notification($infoquery['ID'], -2, get_table('SiteName') ." Registration Confirmation", "Thanks for registering on ". get_table('SiteName') ."! We just want to thank you for using the site, and we hope you enjoy it!<br /><br />
<strong>Getting Started:</strong><br />
<ul>
  <li>Write a blog post! Click <a href=\"addentry.php\">Add Entry</a> at the top-left of any page.</li>
  <li>Comment on an existing post by clicking the &quot;comments&quot; link right below its title.</li>
  <li>Vote in the weekly poll, or submit your own poll! You can do this from the <a href=\"index.php\">home page</a>.</li>
  <li>If you want to change your password, set a security question, or adjust other preferences, visit the <a href=\"preferences.php\">Preferences</a> page.</li>
</ul>
<br />
Enjoy the new features, and if you have any questions, you might want to check out the <a href=\"help/\">Help Section &raquo;</a>.");
    
    send_notification(1, -2, "New ". get_table('SiteName') ." Member Registered", "<strong>{$infoquery['Name']}</strong> has just signed up!");
    
    $pagedone = 2;
    }
  }
?><html>
<head>
<link type="text/css" href="system/style.css" rel="stylesheet">
<link type="text/css" href="system/sysform.css" rel="stylesheet" />
<script type="text/javascript" src="system/engine.js"></script>
<script type="text/javascript">
function checkpasses()
{
if(document.regform.password.value != document.regform.cpassword.value)
  {
  document.getElementById('pass_status').innerHTML="Please make sure your passwords match.";
  document.regform.submit.disabled = 'disabled';
  }
else
  {
  document.getElementById('pass_status').innerHTML="";
  document.regform.submit.disabled = '';
  }
}
</script>
<title>Register for <?php print get_table('SiteName'); ?></title>
<?php include "header.php"; ?>
</head>
<body>
<?php
if(isset($success))
  {
  if($pagedone == 1)
    {
    print '<form action="register.php?register&amp;step=2" method="post" name="regform">
<fieldset>
<legend>Preferences (Step 2 of 2)</legend>';
print "<p class=\"success\">{$success}</p>";
print '
<input type="hidden" name="ID" value="'. $ID .'">
';
print '
<label for="CheckInterval">Check for Updates Every:</label> <select name="CheckInterval" id="CheckInterval">
<option value="30">30 Seconds</option>
<option value="60" selected>1 Minute</option>
<option value="300">5 Minutes</option>
<option value="600">10 Minutes</option>
<option value="0">Don\'t Check</option>
</select>
<em>After this much idle time, you will get an update report on the top of your screen.</em>
<br />
<br />
<input type="submit" value="Save Preferences &raquo;"><br />
</fieldset>
</form>';
    }
  
  elseif($pagedone == 2)
    {
    print "<form>
<fieldset>
<legend>Registration Complete</legend>";

    if(isset($success))
      print "
<p class=\"success\">{$success}</p>";
      if(isset($error))
      print "
<p class=\"error\">{$error}</p>";
    
    print "</fieldset>
</form>";
    }
  }

if(!isset($success) AND $pagedone != 2)
  {
  print '<form action="register.php?register&amp;step=1';
  
  if(isset($_GET['auth'])) print "&amp;auth=". urlencode($_GET['auth']) ."";
  
  print '" method="post" name="regform">
<fieldset>
<legend>Registration (Step 1 of 2)</legend>';

  if(isset($error))
    print "<p class=\"error\">The following errors were found:<br />{$error}</p>";

  print '<label for="name">Name:</label><br /><input type="text" name="name" id="name"';
  
  if(isset($_POST['name'])) print " value=\"{$_POST['name']}\"";
  
  print '><br />
<label for="email">Email Address:</label><br /><input type="text" name="email" id="email" size="40" onBlur=" check(\'email\', this.value);"';

  if(isset($_POST['email'])) print " value=\"{$_POST['email']}\"";
  
  print '> <span id="emailstatus"></span><br />
<label for="password">Password:</label><br /><input type="password" name="password" id="password"><br />
<label for="cpassword">Password Again:</label><br /><input type="password" name="cpassword" id="cpassword" onBlur=" checkpasses();"> <span id="pass_status" class="error"></span><br />';
	
	if(strlen(get_config('regauth'))>0) {
		print '<label for="auth">Authorization Code:</label><br /><input type="text" name="auth" id="auth"';
		
		if(isset($_GET['auth'])) print " value=\"{$_GET['auth']}\">"; else print "> You should have recieved this in an email.";
	}
	
	else
		print '<input type="hidden" name="regauth" value="" />';
  
  print '<br />
<input type="submit" value="Register &raquo;" name="submit">
</fieldset>
</form>';
  }
?>

</body>
</html>