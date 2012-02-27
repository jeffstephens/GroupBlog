<?php
session_start();

if(isset($_SESSION['familysite']))
  Header("Location: index.php");

include "system/parse.php";
dbconnect();
?><html>
<head>
<title><?php print get_table('SiteName'); ?>: Reset Password</title>
<link type="text/css" rel="stylesheet" href="system/style.css">
<script type="text/javascript">
function checkPasswords()
{
if(document.getElementById('newpass').value != document.getElementById('cnewpass').value)
  document.getElementById('pass_status').innerHTML='Please make sure your passwords match.';

else
  document.getElementById('pass_status').innerHTML='';
}
</script>
<?php include "header.php"; ?>
</head>
<body>
<h1>Reset Your Password</h1>
<?php
if(isset($_POST['newpass']) AND isset($_POST['ID']))
  {
  if($_POST['newpass'] == $_POST['cnewpass'])
    {
    $salt = '$2a$07$5%TZkl3pEE^)(dFFf*&70$';
	$password = crypt(sanitize($_POST['newpass']), $salt);
    $attempt = mysql_query("UPDATE `". get_table('users') ."` SET `Password` = '". $password ."' WHERE `ID` = {$_POST['ID']} LIMIT 1;");
    
    if($attempt)
      print '<div class="green" style="text-align: center">
Your password has been changed! You can now <a href="login.php">login &raquo;</a> with your new password.
</div>';
    
    else
      {
      print '<div class="red" style="text-align: center">
Your password couldn\'t be changed due to a system error. Please try again later.
</div>';
      
      send_notification(1, -2, "Error Report", "An error occurred while trying to reset a password.<br />
<br />
<strong>mysql_error():</strong> ". mysql_error() ."<br />
<strong>mysql_query:</strong><br />
<blockquote>UPDATE `". get_table('users') ."` SET `Password` = '". $password ."' WHERE `ID` = {$_POST['ID']} LIMIT 1;</blockquote>");
      }
    }
  
  else
    print '<div class="red" style="text-align: center">Please <a href="resetpassword.php">start over &raquo;</a> and make sure your passwords match this time.</div>';
  }

else
{
  print '
<div class="yellow" style="text-align: center">
If you\'ve forgotten your password, don\'t panic! There is a three-step process that can get you back on track.
</div>
<br />
';

if(!isset($_POST['email']))
  print '<h2>Step 1: Enter your Email Address</h2>
<div class="blue">
<form action="resetpassword.php" method="post">
<input type="text" size="50" name="email"><br />
<input type="submit" value="Next Step &raquo;">
</form>
</div>';

else
  {
  //Process email address
  $userinfo = mysql_query("SELECT * FROM `". get_table('users') ."` WHERE `Email` = '". sanitize($_POST['email']) ."';");
  
  if(mysql_num_rows($userinfo) != 1)
    print '<h2>Step 1: Enter your Email Address</h2>
<div class="red">
<form action="resetpassword.php" method="post">
<label for="email">That email address could not be found. Try again:</label><br />
<input type="text" size="50" id="email" name="email"><br />
<input type="submit" value="Next Step &raquo;">
</form>
</div>';
  
  else
    {
    $userinfo = mysql_fetch_assoc($userinfo);
    
    if(isset($_POST['email']))
      {
      print '<h2>Step 1: Complete!</h2>
<div class="green">Your email address, <strong>'. $_POST['email'] .'</strong>, has identified you as <strong>'. $userinfo['Name'] .'</strong>.</div>
<br />';
      
      if(!isset($_POST['secreta']))
        {
        print '
<h2>Step 2: Answer your Secret Question</h2>
';
      if(strlen($userinfo['secretq']) > 0 AND strlen($userinfo['secreta']) > 0)
        print '<div class="blue">
<form action="resetpassword.php" method="post">
<input type="hidden" name="email" value="'. $_POST['email'] .'">
<label for="secreta">'. stripslashes($userinfo['secretq']) .'</label><br />
<input type="text" size="50" name="secreta" id="secreta"><br />
<input type="submit" value="Next Step &raquo;">
</form>
</div>';
      
      else
        print '<div class="red">
<strong>Uh oh!</strong> Looks like you didn\'t set up a secret question in your account. <a href="mailto:jefftheman45@gmail.com">Email Jeff</a> and tell him to reset your password for you.<br />
<br />
For future reference, you can set up your secret question under &quot;Set Preferences&quot; once you login.</div>';
        }
      
      else
        {
        if($userinfo['secreta'] != $_POST['secreta'])
          print '
<h2>Step 2: Answer your Secret Question</h2>
<div class="red">
<strong>That answer is incorrect. Try again:</strong><br />
<form action="resetpassword.php" method="post">
<input type="hidden" name="email" value="'. $_POST['email'] .'">
<label for="secreta">'. stripslashes($userinfo['secretq']) .'</label><br />
<input type="text" size="50" name="secreta" id="secreta"><br />
<input type="submit" value="Next Step &raquo;">
</form>
</div>';
        
        else
          print '
<h2>Step 2: Complete!</h2>
<div class="green">You answered your secret question correctly.</div>
<br />
<h2>Step 3: Choose a New Password</h2>
<div class="blue">
<form action="resetpassword.php" method="post">
<input type="hidden" name="email" value="'. $_POST['email'] .'">
<input type="hidden" name="secreta" value="'. $_POST['secreta'] .'">
<input type="hidden" name="ID" value="'. $userinfo['ID'] .'">
<label for="newpass">New Password:</label> <input type="password" name="newpass" id="newpass"><br />
<label for="cnewpass">Confirm New Password:</label> <input type="password" name="cnewpass" id="cnewpass" onBlur=" checkPasswords();"> <span id="pass_status" class="error"></span>
<br />
<input type="submit" value="Reset Password &raquo;">
</div>';
        }
      }
    }
  }
}
?>
</body>
</html>