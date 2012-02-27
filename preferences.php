<?php
ob_start();
session_start();

if(!isset($_SESSION['familysite']))
  Header("Location: login.php?go=preferences.php");

include "system/parse.php";
dbconnect();

//AJAX Stuff
if(isset($_GET['suggestquestion']))
  {
  //Return a random secret question
  $questions = Array("What is your mother's maiden name?", "What was your first dog's name?", "What was your first boyfriend/girlfriend's name?", "What middle school did you attend?", "In what city were you born?", "What is your favorite movie or book?", "What is your favorite flower?", "What is your grandmother-in-law's first name?");
  
  $choice = rand(0, (count($questions) - 1));
  
  die($questions[$choice]);
  }

if(isset($_GET['check']))
  {
  //Return message to AJAX application.  
  if(isset($_POST['email']))
    {
    if(mysql_num_rows(mysql_query("SELECT * FROM `". get_table('users') ."` WHERE `Email` = '". mysql_real_escape_string($_POST['email']) ."' AND `ID` != {$_SESSION['familysite']};")) > 0)
      die("bad");
    else
      die("good");
    }
  }

//Look up user info
$info = mysql_fetch_assoc(mysql_query("SELECT * FROM `". get_table('users') ."` WHERE `ID` = ". sanitize($_SESSION['familysite']) .";"));

//Save preferences
if(isset($_GET['save']))
  {
  $salt = get_config('salt');
  if(crypt(mysql_real_escape_string($_POST['auth']), $salt) == $info['Password'])
    {
    //Email address
    if(mysql_num_rows(mysql_query("SELECT * FROM `". get_table('users') ."` WHERE `Email` = '". mysql_real_escape_string($_POST['email']) ."' AND `ID` != {$_SESSION['familysite']};")) == 0 AND $info['Email'] != $_POST['email'])
      {
      $attempt = mysql_query("UPDATE `". get_table('users') ."` SET `Email` = '". sanitize($_POST['email']) ."' WHERE `ID` = {$_SESSION['familysite']} LIMIT 1;");
      
      if($attempt)
        $success .= "Your email address has been updated. You must now login with <u>". sanitize($_POST['email']) ."</u>.<br />";
      else
        {
        $error .= "Your email address couldn't be updated due to a system error.<br />";
        $errorlog .= "<strong>Email address update failed:</strong> ". mysql_error() ."<br />\n";
        }
      }
    
    //Name
    if($info['Name'] != sanitize($_POST['name']))
      {
      $attempt = mysql_query("UPDATE `". get_table('users') ."` SET `Name` = '". sanitize($_POST['name']) ."' WHERE `ID` = {$_SESSION['familysite']};");
      
      if($attempt)
        $success .= "Your name has been changed to ". sanitize($_POST['name']) .".<br />";
      else
        {
        $error .= "Your name couldn't be changed due to a system error.<br />";
        $errorlog .= "<strong>Name change failed:</strong> ". mysql_error() ."<br />";
        }
      }
    
    //Password
    if(isset($_POST['changepass']))
      {
      if($_POST['newpass'] == $_POST['confirmpass'])
        {
        $salt = '$2a$07$5%TZkl3pEE^)(dFFf*&70$';
		$password = crypt(sanitize($_POST['newpass']), $salt);
        $attempt = mysql_query("UPDATE `". get_table('users') ."` SET `Password` = '". $password ."' WHERE `ID` = {$_SESSION['familysite']} LIMIT 1;");
        
        if($attempt)
          $success .= "Your password has been changed.<br />";
        else
          {
          $error .= "Your password couldn't be changed due to a system error.<br />";
          $errorlog .= "<strong>Password change failed:</strong> ". mysql_error() ."<br />\n";
          }
        }
      else
        $error .= "Your new and confirmation password boxes don't match.";
      }
    
    //Secret question/answer
    if($_POST['secretq'] != $info['secretq'] OR $_POST['secreta'] != $info['secreta'])
      {
      $attempt = mysql_query("UPDATE `". get_table('users') ."` SET `secretq` = '". sanitize($_POST['secretq']) ."', `secreta` = '". sanitize($_POST['secreta']) ."' WHERE `ID` = {$_SESSION['familysite']} LIMIT 1;");
      
      if($attempt)
        $success .= "Your secret question and answer have been updated.<br />";
      else
        {
        $error .= "Your secret question and answer couldn't be updated due to a system error.<br />";
        $errorlog .= "<strong>Secret question/answer update failed:</strong> ". mysql_error() ."<br />\n";
        }
      }
    
    //Poll of the Week Subscription
    if(isset($_POST['pollreport']) AND $info['pollreport'] == 0)
      {
      $attempt = mysql_query("UPDATE `". get_table('users') ."` SET `pollreport` = 1 WHERE `ID` = {$_SESSION['familysite']} LIMIT 1;");
      
      if($attempt)
        $success .= "You are now subscribed to the Poll of the Week summary.<br />";
      else
        {
        $error .= "You couldn't be subscribed to the Poll of the Week summary due to a system error.<br />";
        $errorlog .= "<strong>Poll of the Week subscription failed:</strong> ". mysql_error() ."<br />\n";
        }
      }
    
    if(!isset($_POST['pollreport']) AND $info['pollreport'] == 1)
      {
      $attempt = mysql_query("UPDATE `". get_table('users') ."` SET `pollreport` = 0 WHERE `ID` = {$_SESSION['familysite']} LIMIT 1;");
      
      if($attempt)
        $success .= "You are no longer subscribed to the Poll of the Week summary.<br />";
      else
        {
        $error .= "You couldn't be unsubscribed to the Poll of the Week summary due to a system error.<br />";
        $errorlog .= "<strong>Poll of the Week subscription removal failed:</strong> ". mysql_error() ."<br />\n";
        }
      }
    
    //Update report
    if($_POST['checkinterval'] != $info['UpdateInterval'])
      {
      $attempt = mysql_query("UPDATE `". get_table('users') ."` SET `UpdateInterval` = '". sanitize($_POST['checkinterval']) ."' LIMIT 1;");
      
      if($attempt)
        $success .= "Your update interval has been saved.<br />";
      else
        {
        $error .= "Your update interval couldn't be saved due to a system error.<br />";
        $errorlog .= "<strong>Update interval save failed:</strong> ". mysql_error() ."<br />\n";
        }
      }
    
    //Send a notification with a summary of the changes made.
    if(isset($success) OR isset($error))
      {
      $msgbody = "This report is to notify you of recent changes you made to your account.<br />\n<br />\n";
      
      if(isset($success))
        $msgbody .= "<span class=\"success\">{$success}</span>\n";
      
      if(isset($error))
        $msgbody .= "<span class=\"error\">{$error}</span>\n";
      
      $msgbody .= "<br />You can change these or any other settings in the <a href=\"preferences.php\">Preferences</a> panel.";
      
      send_notification($_SESSION['familysite'], -2, "Recent Preference Update", $msgbody);
      }
    
    //Send a master error report to Jeff if errors occurred
    if(isset($errorlog))
      errorlog("Errors occurred while updating preferences. Details are below.<br />\n<br />\n<span class=\"error\">{$errorlog}</span>");
    }
  
  else
    $error .= "Your <a href=\"javascript: void(0);\" onClick=\" selectTab('save');\">current password</a> is incorrect.";
  }
?><html>
<head>
<link type="text/css" rel="stylesheet" href="system/style.css">
<title><?php print get_table('SiteName'); ?>: Preferences</title>
<script type="text/javascript" src="system/engine.js"></script>
<script type="text/javascript">
function checkemail(value)
{
var request = getHTTPObject();

if(request)
{
  request.onreadystatechange = function() {
  if(request.readyState == 4 && request.status == 200)
    {
    if(request.responseText=="bad")
      {
      document.getElementById('email').style.background='#FFE5E5';
      document.getElementById('emailstatus').innerHTML="<br />"+value+" is already in use. Please choose another email address.";
      }
    else if(request.responseText=="good")
      {
      document.getElementById('email').style.background='#E6FFCC';
      document.getElementById('emailstatus').innerHTML="";
      }
    }
  };
  request.open("POST", "preferences.php?check", true);
  request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  request.send("email="+value);
}
}

function suggestQuestion()
{
var qrequest = getHTTPObject();

if(qrequest)
{
  qrequest.onreadystatechange = function() {
  if(qrequest.readyState == 4 && qrequest.status == 200)
    document.getElementById('secretq').value=qrequest.responseText;
  };
  qrequest.open("GET", "preferences.php?suggestquestion", true);
  qrequest.send(null);
}
}

function checkpasses()
{
if(document.forms[0].newpass.value != document.forms[0].confirmpass.value)
  {
  document.getElementById('newpass').style.background='#FFE5E5';
  document.getElementById('confirmpass').style.background='#FFE5E5';
  document.getElementById('pass_status').innerHTML="Please make sure your passwords match.";
  document.getElementById('changepass').checked='';
  document.getElementById('changepass').disabled='disabled';
  }
else
  {
  document.getElementById('newpass').style.background='#E6FFCC';
  document.getElementById('confirmpass').style.background='#E6FFCC';
  document.getElementById('pass_status').innerHTML="";
  document.getElementById('changepass').checked='checked';
  document.getElementById('changepass').disabled='';
  }
}

function selectTab(panel)
{
//Deselect all tabs
document.getElementById('account').className='normal';
document.getElementById('site').className='normal';
document.getElementById('save').className='normal';
document.getElementById('special').className='normal';

//Hide all panels
document.getElementById('accountpanel').style.display='none';
document.getElementById('sitepanel').style.display='none';
document.getElementById('savepanel').style.display='none';
document.getElementById('specialpanel').style.display='none';

//Select chosen tab; show respective panel
document.getElementById(panel).className='selected';
document.getElementById(panel+'panel').style.display='block';
}
</script>
<style type="text/css">
#tabbar {
background-color: #CCC;
padding: 2px;
text-align: center }

#tabbar a {
background: #EDEDED;
padding: 2px;
text-decoration: none;
margin: 5px }

#tabbar a.selected:link, #tabbar a.selected:visited {
background: #003366;
color: #FFF;
font-weight: 900 }

.panel {
background-color: #003366;
padding-top: 5px;
padding-left: 5px;
padding-right: 5px;
padding-bottom: 1px }

.panel p {
background-color: #F0F0F0;
padding: 2px }

.panel strong {
font-size: 120% }
</style>
<?php include "header.php"; ?>
</head>

<body onLoad=" selectTab('<?php if($_GET['specialpref'] == "pollresults") print 'special'; else print 'account'; ?>');">
<?php include "userinfo.php"; ?>
<h1>Preferences</h1>
<div class="yellow" style="text-align: center">Here you can set preferences and edit account details.<?php
if(isset($success)) print "<br /><span class=\"success\">{$success}</span>";
if(isset($error)) print "<br /><span class=\"error\">{$error}</span>"; ?></div>
<br />
<div id="tabbar">
<a href="javascript: void(0);" onClick="selectTab('account');" id="account" accesskey="a"><u>A</u>ccount Settings</a>
<a href="javascript: void(0);" onClick="selectTab('site');" id="site" accesskey="s"><u>S</u>ite Settings</a>
<?php
if(isset($_GET['specialpref']))
  {
  if($_GET['specialpref'] == "pollresults")
    print '<a href="javascript: void(0);" onClick="selectTab(\'special\');" id="special" accesskey="o">P<u>o</u>ll Subscription</a>
';
  }

else
  print "<span id=\"special\" style=\"display: none\"></span>";
?>
<a href="javascript: void(0);" onClick="selectTab('save');" id="save" accesskey="v">Sa<u>v</u>e Settings</a>
</div>
<form action="preferences.php?save" method="post">

<div id="accountpanel" class="panel" style="display: none">
<p>
<label for="email">Email Address:</label> <input type="text" name="email" id="email" size="50" value="<?php print $info['Email']; ?>" onBlur="checkemail(this.value);"> <span id="emailstatus" class="error"></span><br />
<label for="name">Name:</label> <input type="text" name="name" id="name" value="<?php print $info['Name']; ?>"><br />
<br />
<strong>Change your Password</strong> <input type="checkbox" name="changepass" id="changepass" value="true"><br />
<label for="newpass">New:</label> <input type="password" name="newpass" id="newpass" onKeyDown=" document.getElementById('changepass').checked='checked';"><br />
<label for="confirmpass">Confirm:</label> <input type="password" name="confirmpass" id="confirmpass" onBlur=" checkpasses();"><span id="pass_status" class="error"></span><br />
<br />
<strong>Secret Question/Answer</strong><br />
<label for="secretq">Question:</label> <input type="text" id="secretq" name="secretq" size="50" <?php print ' value="'. stripslashes($info['secretq']) .'"'; ?>> <a href=" javascript: void(0);" onClick=" suggestQuestion();">Suggest Question</a><br />
<label for="secreta">Answer:</label> <input type="text" id="secreta" name="secreta" size="50" <?php print ' value="'. stripslashes($info['secreta']) .'"'; ?>><br />
<span style="color: #666">Type a question and answer that only you would know the answer to. If you forget your password, this can be used to identify you and you can reset your password.</span>
<br />
<br />
<input type="button" value="Save Settings" style="font-size: 100%" onClick=" selectTab('save');">
</p>
</div>

<div id="sitepanel" class="panel" style="display: none">
<p>
<?php
if($_GET['specialpref'] != "pollresults")
  {
  print '<input type="checkbox" id="pollreport" name="pollreport"';
  if($info['pollreport'] == 1) print " checked";
  print '> <label for="pollreport">Send me a summary of the poll of the week when it ends</label><br />';
  }
?>
<label for="checkinterval">Check for Updates Every:</label> <select name="checkinterval" id="checkinterval">
<option value="30"<?php if($info['UpdateInterval'] == 30) print " selected"; ?>>30 Seconds</option>
<option value="60"<?php if($info['UpdateInterval'] == 60) print " selected"; ?>>1 Minute</option>
<option value="300"<?php if($info['UpdateInterval'] == 300) print " selected"; ?>>5 Minutes</option>
<option value="600"<?php if($info['UpdateInterval'] == 600) print " selected"; ?>>10 Minutes</option>
<option value="0"<?php if($info['UpdateInterval'] == 0) print " selected"; ?>>Don't Check</option>
</select>
<span style="color: #666">After this much idle time, you will get an update report on the top of your screen. (<a href="javascript: void(0);" onClick=" getUpdates();">Preview</a>)</span>
<br />
<br />
<input type="button" value="Save Settings" style="font-size: 100%" onClick=" selectTab('save');">
</p>
</div>

<?php
if(isset($_GET['specialpref']))
  {
  print '
<div id="specialpanel" class="panel" style="display: none">
<p>';

  if($_GET['specialpref'] == "pollresults")
    {
    print '<input type="checkbox" id="special_pollreport" name="special_pollreport"'; 
    if($info['pollreport'] == 1) print " checked";
    print '> <label for="special_pollreport">Send me a summary of the poll of the week when it ends</label>';
    }
  
  print '<br />
<br />
<input type="button" value="Save Settings" style="font-size: 100%" onClick=" selectTab(\'save\');">
</p>
</div>';
  }

else
  print "<span id=\"specialpanel\" style=\"display: none\"></span>";
?>

<div id="savepanel" class="panel" style="display: none">
<p>
<strong>Current Password:</strong><br />
<input type="password" name="auth" id="auth"><br />
<input type="submit" value="Save Preferences &raquo;">
</p>
</div>
</form>
<noscript><p class="error">Looks like your browser doesn't support this preferences panel. Either upgrade or enable javascript, or use the <a href="oldprefs.php">old preferences &raquo;</a>.</p></noscript>
</body>
</html>