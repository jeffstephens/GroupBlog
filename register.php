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
    
    if(get_option($regauth) == $auth)
      {
      if(mysql_num_rows(mysql_query("SELECT * FROM `". get_table('users') ."` WHERE `Email` = '". mysql_real_escape_string($_POST['email']) ."';")) == 0)
        {
        if($password == $cpassword)
          {
          $attempt = mysql_query("INSERT INTO `". get_table('users') ."` (`Name`, `Email`, `Password`, `Registered`, `UpdateInterval`) VALUES ('{$name}', '{$email}', '{$password}', ". time() .", 60);");
          
          if($attempt)
            {
            $ID = mysql_fetch_assoc(mysql_query("SELECT * FROM `". get_table('users') ."` WHERE `Email` = '{$email}';"));
            $ID = $ID['ID'];
            $_SESSION['familysite'] = $ID;
            $success = "You have been successfully registered and logged in to the ". mb_convert_case(get_table('SiteName'), MB_CASE_LOWER) .". Welcome aboard, {$name}! Now, take a look at some preferences you can set.";
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
    
    send_notification($infoquery['ID'], -2, get_table('SiteName') ." Registration Confirmation", "Thanks for registering on the ". mb_convert_case(get_table('SiteName'), MB_CASE_LOWER) ."! We just want to thank you for using the site, and we hope you enjoy it!<br /><br />
<strong>Take a look at some of our brand-new features:</strong><br />
<ul>
  <li>You can now attach photos to blog posts. <a href=\"entry.php?entry=73\">Take a look at an example &raquo;</a></li>
  <li>You can now search the blog! <a href=\"blog.php\">Enter your keywords on this page</a>.</li>
  <li>There is a weekly poll that you can see in the sidebar on the <a href=\"index.php\">home page</a>. Votes are anonymous. You can also add your own poll ideas and have everyone vote on them!</li>
  <li>If you use a mobile device such as an iPhone, iPod Touch, Palm Pilot, etc. there is a mobile version of the site that is optimized for your handheld device.</li>
</ul>
<br />
Enjoy the new features, and if you have any questions, you might want to check out the <a href=\"help/\">Help Section &raquo;</a>.");
    
    send_notification(1, -2, "New ". get_table('SiteName') ." Member Registered", "<strong>{$infoquery['Name']}</strong> has registered for the family website!");
    
    $pagedone = 2;
    }
  }
?><html>
<head>
<link type="text/css" href="system/style.css" rel="stylesheet">
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
<style type="text/css">
body {
background-color: #A3A3A3 }
</style>
<title>Register for the <?php print get_table('SiteName'); ?></title>
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

  print '<label for="name">Name:</label> <input type="text" name="name" id="name"';
  
  if(isset($_POST['name'])) print " value=\"{$_POST['name']}\"";
  
  print '><br />
<label for="email">Email Address:</label> <input type="text" name="email" id="email" size="40" onBlur=" check(\'email\', this.value);"';

  if(isset($_POST['email'])) print " value=\"{$_POST['email']}\"";
  
  print '> <span id="emailstatus"></span><br />
<label for="password">Password:</label> <input type="password" name="password" id="password"><br />
<label for="cpassword">Password Again:</label> <input type="password" name="cpassword" id="cpassword" onBlur=" checkpasses();"> <span id="pass_status" class="error"></span><br />
<label for="auth">Authorization Code:</label> <input type="text" name="auth" id="auth"';
  
  if(isset($_GET['auth'])) print " value=\"{$_GET['auth']}\">"; else print "> You should have recieved this in an email.";
  
  print '<br />
<input type="submit" value="Register&raquo;" name="submit">
</fieldset>
</form>';
  }
?>

</body>
</html>