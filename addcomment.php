<?php
session_start();
if(!isset($_SESSION['familysite']))
  Header("Location: login.php?go=addcomment.php?entry={$_GET['entry']}");

if(!isset($_GET['entry']))
  Header("Location: index.php");

include "system/parse.php";
dbconnect();

if(mysql_num_rows(mysql_query("SELECT * FROM `". get_table('blog') ."` WHERE `ID` = ". sanitize($_GET['entry']) ." LIMIT 1;")) == 0)
  Header("Location: index.php?error=invalidaddentry");

//Load entry
$entry = mysql_fetch_assoc(mysql_query("SELECT * FROM `". get_table('blog') ."` WHERE `ID` = ". sanitize($_GET['entry']) ." LIMIT 1;"));
$title = blogitize($entry['Title']);
$posted = $entry['Posted'];
$author = authorlookup($entry['Author']);

if(date("n-j-Y", ($posted + get_table('dateoffset'))) == date("n-j-Y", (time() + get_table('dateoffset'))))
  $timestamp = "today at ". date("g:i a", ($posted + get_table('dateoffset')));
elseif(date("n-j-Y", ($posted + get_table('dateoffset'))) == date("n-j-Y", ((time() - 86400) + get_table('dateoffset'))))
  $timestamp = "yesterday at ". date("g:i a", ($posted + get_table('dateoffset')));
else
  $timestamp = date("\a\\t g:i a l, F jS", ($posted + get_table('dateoffset')));

if(isset($_GET['save']) AND $_POST['mode'] == "publish")
  {
  if(strlen($_POST['comment']) > 0)
    {
    //Post comment
    $posted = time();
    
    $attempt = mysql_query("INSERT INTO `". get_table('comments') ."` (`Author`, `Posted`, `Comment`, `EntryID`) VALUES ({$_SESSION['familysite']}, {$posted}, '". sanitize($_POST['comment']) ."', ". sanitize($_GET['entry']) .");");
    
    if($attempt)
      {
      $success = "Your comment has been successfully posted. <a href=\"entry.php?entry={$_GET['entry']}\">Back to &quot;{$title}&quot;&raquo;</a>";
      $commentinfo = mysql_fetch_assoc(mysql_query("SELECT * FROM `". get_table('comments') ."` WHERE `Posted` = {$posted} AND `Author` = {$_SESSION['familysite']} LIMIT 1;"));
      notify('comment', $_GET['entry'], $commentinfo['ID']);
      }
    else
      {
      $error = "Your comment couldn't be posted due to a system error. Please try again later.";
      send_notification(1, -2, "Error Report", "<h1>Error Report</h1>
<div class=\"red\">An error occurred while attempting to add a blog comment.<br />
<strong>mysql_error():</strong> ". mysql_error() ."<br />
<strong>Timestamp:</strong> ". date("g:i a \o\\n D, M j", (time() + get_table('dateoffset'))) ."</div>
<br />
<h2>Blog Entry Information</h2>
<div class=\"red\">
<strong>ID:</strong> ". sanitize($_GET['entry']) ."<br />
<strong>Title:</strong> {$title}<br />
<strong>Author:</strong> {$author}<br />
<strong>Timestamp:</strong> ". date("g:i a \o\\n D, M j", $posted) ."<br />
<strong>Entry:</strong><br />
<blockquote>{$entry['Entry']}</blockquote>
</div>
<br />
<h2>Comment Information</h2>
<div class=\"red\">
<strong>Author:</strong> ". authorlookup($_SESSION['familysite']) ." (#{$_SESSION['familysite']})<br />
<strong>Timestamp:</strong> ". date("g:i a \o\\n D, M j") ."<br />
<strong>Comment:</strong><br />
<blockquote>". sanitize($_POST['comment']) ."</blockquote>
</div>");
      }
    }
  
  else
    $error = "Please enter a comment.";
  }
?><html>
<head>
<link type="text/css" href="system/style.css" rel="stylesheet">
<title><?php print get_table('SiteName'); ?>: Add Comment to &quot;<?php print strip_tags($title); ?>&quot;</title>
<?php include "header.php"; ?>
</head>
<body>
<?php include "userinfo.php"; ?>
<br /><br />
<h1><?php print $title;?></h1>
<p class="infobar"><?php print "Posted {$timestamp} by {$author}.";?></p>
<div class="green"><?php print blogitize($entry['Entry']); ?></div>
<?php
if($_POST['mode'] == "preview")
  {
  print "<br />
<h2>Comment Preview</h2>
<div class=\"green\">
<ul class=\"comments\">
  <li><strong>you</strong> said today at ". date("g:i a", (time() + get_table('dateoffset'))) ."...<br />
  ". blogitize($_POST['comment']) ."</li>
</ul>
</div>";
  }
?>
<br />
<h2>Add a Comment to &quot;<?php print $title; ?>&quot;</h2>
<?php
if(isset($error) OR $_POST['mode'] != "publish")
  {
  print '
<div style="float: left; width: 50%" class="green">
<form action="addcomment.php?save&amp;entry='. $_GET['entry'] .'" method="post" style="margin: 0; padding: 0">';

if(isset($error))
  print "<p class=\"error\">{$error}</p>";

print '
<label for="post">Comment:</label><br />
<textarea name="comment" id="comment" rows="10" cols="50">';

  if($_POST['mode'] == "preview") print stripslashes($_POST['comment']);

  print '</textarea><br />
<input type="radio" name="mode" value="preview" id="preview" checked><label for="preview">Preview Comment</label><br />
<input type="radio" name="mode" value="publish" id="publish"><label for="publish">Publish Comment</label><br />
<input type="submit" value="Go &raquo;"><input type="button" value="&laquo; Cancel" onClick=" if(confirm(\'Are you sure? If you proceed, all text will be lost.\')) document.location=\'entry.php?entry='. $_GET['entry'] .'\';">
</form>
</div>
<div style="float: right; width: 50%; text-align: center" class="green">
<center>
';

  include "codeguide.php";

  print '
</center>
</div>';
  }

if(isset($success))
  print "<p class=\"success\">{$success}</p>";
?>
</body>
</html>