<?php
session_start();
if(isset($_SESSION['familysite']))
	Header("Location: index.php");

include "system/parse.php";

if(isset($_GET['login']))
	{
	dbconnect();
	
	$email = mysql_real_escape_string($_POST['email']);
	$salt = get_config('salt');
	$password = crypt(mysql_real_escape_string($_POST['password']), $salt);
	
	$query = mysql_query("SELECT * FROM `". get_table('users') ."` WHERE `Email` = '{$email}' AND `Password` = '{$password}';");
	
	if(mysql_num_rows($query) == 1)
	{
	$info = mysql_fetch_assoc($query);
	$_SESSION['familysite'] = $info['ID'];
	
	if(isset($_POST['go']))
		Header("Location: process.php?go={$_POST['go']}");
	elseif((time() - $info['LastVisit']) > 2592000)
		Header("Location: loginmessage.php?welcomeback&diff=". (time() - $info['LastVisit']));
	else
		Header("Location: process.php?go=index.php");
	}
	
	else
		$error .= "Your email/password combination is incorrect.";
  }
?><html>
<head>
<link type="text/css" href="system/style.css" rel="stylesheet" />
<link type="text/css" href="system/sysform.css" rel="stylesheet" />
<script type="text/javascript">
function prepForm() {
	document.getElementById('email').select();
}

function focusEmail(value, target) {
	if(value=="Email Address...") {
		target.value = '';
		target.className='activeinput';
	}
}

function blurEmail(value, target) {
	if(value=='' || value=="Email Address...") {
		target.value = "Email Address...";
		target.className='passiveinput';
	}
}

function focusPass(value, target) {
	if(value=="Password...") {
		target.value = "";
		target.type="password";
		target.className="activeinput";
	}
}

function blurPass(value, target) {
	if(value=='' || value=="Password...") {
		target.value = "Password...";
		target.type = "text";
		target.className="passiveinput";
	}
}
</script>
<title>Login to <?php print get_table('SiteName'); ?></title>
<?php include "header.php"; ?>
</head>
<body>
<form action="login.php?login" method="post">
<fieldset>
<legend>Please login to view <?php print get_config('SiteName'); ?>.</legend>
<?php
if(isset($error))
  print "<p class=\"error\">{$error}</p>";

if(isset($_GET['go']))
  print "<input type=\"hidden\" name=\"go\" value=\"". str_replace("%", "&amp;", $_GET['go']) ."\">";
if(isset($_POST['go']))
  print "<input type=\"hidden\" name=\"go\" value=\"". str_replace("%", "&amp;", $_POST['go']) ."\">";
?>
<div class="center">
<input type="text" name="email" id="email" value="Email Address..." onfocus="focusEmail(this.value, this);" onblur="blurEmail(this.value, this);" class="passiveinput"><br />
<input type="text" name="password" id="password" value="Password..." onfocus="focusPass(this.value, this);" onblur="blurPass(this.value, this);" class="passiveinput"><br />
<p class="submit"><input type="submit" id="login" value="Login &raquo;"></p>
</div>
<p class="formfooter"><a href="resetpassword.php">Forgotten Password?</a></p>
</fieldset>
</form>
<script src="http://www.google-analytics.com/urchin.js" type="text/javascript">
</script>
<script type="text/javascript">
_uacct = "UA-3172541-1";
urchinTracker();
</script>
</body>
</html>