<?php
ob_start();
session_start();
dbconnect();

setcookie("LastAccess", time()) or errorlog("The LastAccess cookie could not be set.");

$userinfo = @mysql_fetch_assoc(@mysql_query("SELECT * FROM `". get_table('users') ."` WHERE `ID` = ". sanitize($_SESSION['familysite']) .";"));

if(mysql_error())
  {
  die(error('Database Error', "A database connection error has occurred. This error has <strong>not</strong> been reported. Please try again later.<br />
<br />
User info could not be retrieved. (userinfo.php)<br />
<br />
<strong>mysql_query:</strong><br />
<blockquote>SELECT * FROM `". get_table('users') ."` WHERE `ID` = ". sanitize($_SESSION['familysite']) .";</blockquote>
<br />
<strong>mysql_error():</strong> ". mysql_error()));
  }
?><!--User Info Bar
Last Updated <?php print date("n/j/y", (getlastmod() + get_table('dateoffset'))); ?>-->
<!-- [If IE]>
<style type="text/css">
#updatebox {
position: absolute !important }
</style>
<![endif] -->

<script type="text/javascript" src="http://<?php print get_table('publicurl'); ?>/system/engine.js"></script>
<script type="text/javascript">
function hideUpdates()
{
Effect.SlideUp('updatebox');
}

function getUpdates()
{
var request = getHTTPObject();

if(request)
{
  request.onreadystatechange = function() {
  if(request.readyState == 4 && request.status == 200)
    {
    document.getElementById('updatebox').innerHTML=request.responseText;
    Effect.SlideDown('updatebox');
    setTimeout("hideUpdates()", 30000);
    }
  };
  request.open("GET", "http://<?php print get_table('publicurl'); ?>/updates.php", true);
  request.send(null);
}

else
  document.getElementById('updatebox').innerHTML="<p style=\" margin: 0; padding: 0\"><strong>Could not check for updates.</strong> Your browser does not support AJAX.</p>";
}

<?php
if($userinfo['UpdateInterval'] > 0)
  print 'setInterval("getUpdates()", '. ($userinfo['UpdateInterval'] * 1000) .');
';
?>
</script>
<div id="updatebox" style="position: fixed; width: 30%; left: 35%; top: 0; background-color: #FFFFCC; text-align: center; border: 1px solid #000; border-top: none !important; display: none; opacity: 0.9"></div>

<div style="width: 100%; border-bottom: 1px solid #000; padding-bottom: 2px" class="blue" id="userinfo">
<table style="width: 100%; padding: 0; margin: 0; font-size: 110%">
<tr>
<td style="text-align: left; width: 50%">
<a href="<?php print "http://" . get_table('publicurl'); ?>/index.php">Home</a>
|
<a href="<?php print "http://" . get_table('publicurl'); ?>/addentry.php">Add Entry</a>
|
<a href="<?php print "http://" . get_table('publicurl'); ?>/help/">Help</a><?php
if($_SESSION['familysite'] == 1 && strstr($_SERVER['REQUEST_URI'], "help"))
  print " | <a href=\"http://" . get_table('publicurl') ."/help/addarticle.php\">Add Article</a>";

if($_SESSION['familysite'] == 1)
  print " | <a href=\"http://". get_table('publicurl') ."/admin/\">Admin</a>";
?>
</td>
<td style="text-align: right !important">
<strong>Welcome, <?php
if(!function_exists("dbconnect")) include "system/parse.php";
dbconnect();

mysql_query("UPDATE `". get_table('users') ."` SET `LastVisit` = ". time() ." WHERE `ID` = {$_SESSION['familysite']} LIMIT 1;");

print $userinfo['Name'];
?>!</strong> <a href="<?php print "http://" . get_table('publicurl'); ?>/preferences.php">Set Preferences</a> | <a href="<?php print "http://" . get_table('publicurl'); ?>/logout.php">Logout</a>
</td>
</tr>
</table>
</div>
<br />