<?php
session_start();
if(!isset($_SESSION['familysite']))
  Header("Location: ../login.php?go=admin/delete.php?mode={$_GET['mode']}%25ID={$_GET['ID']}");

elseif($_SESSION['familysite'] != 1)
  Header("Location: ../index.php?error=permissiondenied");

include "../system/parse.php";
dbconnect();

if(isset($_GET['sure']))
  {
  if($_GET['mode'] == "blog")
    {
    $ID = sanitize($_GET['ID']);
    
    $blogattempt = mysql_query("DELETE FROM `". get_table('blog') ."` WHERE `ID` = '{$ID}' LIMIT 1;");
    
    if($blogattempt)
      {
      $commentattempt = mysql_query("DELETE FROM `". get_table('comments') ."` WHERE `EntryID` = '{$ID}';");
      mysql_query("DELETE FROM `". get_table('files') ."` WHERE `EntryID` = '{$ID}';");
      
      if($commentattempt)
        $success = "The blog entry, its comments, and its attachment were successfully deleted. <a href=\"manageblog.php\">Blog Management &raquo;</a>";
      
      else
        {
        send_notification(1, -2, "Error Report", "
An error occurred while trying to delete a blog entry's comments. The entry itself was successfully deleted.<br />
<strong>Timestamp:</strong> ". date("g:i a, n/j/Y", (time() + get_table('dateoffset'))) ."<br />
<strong>mysql_error():</strong> ". sanitize(mysql_error()) ."<br />
<strong>mysql_query():</strong><br />
<blockquote>DELETE FROM `". get_table('comments') ."` WHERE `EntryID` = \'{$ID}\';</blockquote><br />
<strong>Entry ID:</strong> {$_GET['ID']} (Processed as ". sanitize($_GET['ID']) .")") or die(mysql_error());
        
        $error = "The blog entry was deleted, but its comments weren't. More information is available in your <a href=\"../inbox.php\">Notification Inbox &raquo;</a>.";
        }
      }
    
    else
      {
      send_notification(1, -2, "Error Report", "An error occurred while trying to delete a blog entry.<br />
<strong>Timestamp:</strong> ". date("g:i a, n/j/Y", (time() + get_table('dateoffset'))) ."<br />
<strong>mysql_error():</strong> ". sanitize(mysql_error()) ."<br />
<strong>mysql_query():</strong><br />
<blockquote>DELETE FROM `". get_table('blog') ."` WHERE `ID` = \'{$ID}\' LIMIT 1;</blockquote><br />
<strong>Entry ID:</strong> {$_GET['ID']} (Processed as ". sanitize($_GET['ID']) .")");
      
      $error = "The blog entry couldn't be deleted due to a system error. More information is available in your <a href=\"../inbox.php\">Notification Inbox &raquo;</a>.";
      }
    }
  
  elseif($_GET['mode'] == "comment")
    {
    $ID = sanitize($_GET['ID']);
    
    $attempt = mysql_query("DELETE FROM `". get_table('comments') ."` WHERE `ID` = '". sanitize($_GET['ID']) ."' LIMIT 1;");
    
    if($attempt)
      $success = "The comment was successfully deleted. <a href=\"managecomments.php\">Comment Management &raquo;</a>";
    else
      {
      send_notification(1, -2, "Error Report", "An error occurred while trying to delete a single blog comment.<br />
<strong>Timestamp:</strong> ". date("g:i a, n/j/Y", (time() + get_table('dateoffset'))) ."<br />
<strong>mysql_error():</strong> ". sanitize(mysql_error()) ."<br />
<strong>mysql_query():</strong><br />
<blockquote>DELETE FROM `". get_table('comments') ."` WHERE `ID` = \'". sanitize($_GET['ID']) ."\' LIMIT 1;</blockquote><br />
<strong>Entry ID:</strong> {$_GET['ID']} (Processed as ". sanitize($_GET['ID']) .")");
      
      $error = "The comment couldn't be deleted due to a system error. More information is available in your <a href=\"../inbox.php\">Notification Inbox &raquo;</a>.";
      }
    }
  }
?><html>
<head>
<link type="text/css" href="../system/style.css" rel="stylesheet">
<title><?php print get_table('SiteName'); ?>: Administration: Delete a <?php if($_GET['mode'] == "blog") print "Blog Entry"; elseif($_GET['mode'] == "comment") print "Blog Comment"; else print "Blog Entry or Comment"; ?></title>
<?php include "../header.php"; ?>
</head>
<body>
<?php include "../userinfo.php"; ?>
<h1>Delete a <?php if($_GET['mode'] == "blog") print "Blog Entry"; elseif($_GET['mode'] == "comment") print "Blog Comment"; else print "Blog Entry or Comment"; ?></h1>
<div class="red" style="text-align: center">
<?php
if(!isset($_GET['sure']) OR !isset($success) AND !isset($error))
  {
  if($_GET['mode'] == "blog")
    {
    print "Are you sure you want to delete the following blog entry, its comments, and its attachments?";
    
    if(mysql_num_rows(mysql_query("SELECT * FROM `". get_table('blog') ."` WHERE `ID` = '". sanitize($_GET['ID']) ."';")) == 1)
      print "<form action=\"delete.php?sure&amp;mode=blog&amp;ID={$_GET['ID']}\" method=\"post\" style=\"margin: 0; padding: 0\"><input type=\"submit\" value=\"Delete Blog Entry and Comments\"> <input type=\"button\" value=\"Cancel\" onClick=\" location='manageblog.php';\"></form>";
    }

  elseif($_GET['mode'] == "comment")
    {
    print "Are you sure you want to delete the following comment?";
    
    if(mysql_num_rows(mysql_query("SELECT * FROM `". get_table('comments') ."` WHERE `ID` = '". sanitize($_GET['ID']) ."';")) == 1)
      print "<form action=\"delete.php?sure&amp;mode=comment&amp;ID={$_GET['ID']}\" method=\"post\" style=\"margin: 0; padding: 0\"><input type=\"submit\" value=\"Delete this Comment\"> <input type=\"button\" value=\"Cancel\" onClick=\" location='managecomments.php';\"></form>";
    }

  else
    print "Please click the <img src=\"../system/images/delete.gif\">Delete link from either the <a href=\"manageblog.php\">Blog Management &raquo;</a> or <a href=\"managecomments.php\">Comment Management &raquo;</a> page in order to delete a blog entry or comment. <a href=\"index.php\">Administration Home &raquo;</a>";
  }

if(isset($_GET['sure']))
  {
  if(isset($error))
    print "<div class=\"red\">{$error}</div>";
  elseif(isset($success))
    print "<div class=\"green\">{$success}</div>";
  }
?>
</div>

<?php
if($_GET['mode'] == "blog" AND !isset($_GET['sure']))
  {
  if(mysql_num_rows(mysql_query("SELECT * FROM `". get_table('blog') ."` WHERE `ID` = '". sanitize($_GET['ID']) ."';")) == 0)
    die("<br /><h2>There is no entry with that ID number.</h2>
<div class=\"red\">No entry with ID number <strong>{$_GET['ID']}</strong> exists in the database.</div>");
  
  $entryinfo = mysql_fetch_assoc(mysql_query("SELECT * FROM `". get_table('blog') ."` WHERE `ID` = '". sanitize($_GET['ID']) ."';"));
  $posted = $entryinfo['Posted'];
  
  if(date("n-j-Y", ($posted + get_table('dateoffset'))) == date("n-j-Y"))
    $timestamp = "today at ". date("g:i a", ($posted + get_table('dateoffset')));
  elseif(date("n-j-Y", ($posted + get_table('dateoffset'))) == date("n-j-Y", (time() - 86400)))
    $timestamp = "yesterday at ". date("g:i a", ($posted + get_table('dateoffset')));
  else
    $timestamp = date("\a\\t g:i a l, F jS, Y", ($posted + get_table('dateoffset')));
    
  $commentquery = mysql_num_rows(mysql_query("SELECT * FROM `". get_table('comments') ."` WHERE `EntryID` = {$entryinfo['ID']};"));
  
  if($commentquery == 0)
    $comments = "No comments.";
  elseif($commentquery == 1)
    $comments = "1 comment.";
  else
    $comments = $commentquery . " comments.";
  
  $attachmentquery = mysql_query("SELECT * FROM `". get_table('files') ."` WHERE `EntryID` = {$entryinfo['ID']};");
  $attachmentinfo = mysql_fetch_assoc($attachmentquery);
  
  if($attachmentinfo['Type'] == "picture")
    $attachment = ' <img src="../system/images/picture_icon.gif"> Photo Attachment: <a href="'. $attachmentinfo['Path'] .'">'. $attachmentinfo['Filename'] .'</a>';
  
  elseif($attachmentinfo['Type'] == "video")
    $attachment = ' <img src="../system/images/youtube_icon.gif"> Video Attachment:<br />
'. $attachmentinfo['Path'];
  
  else
    $attachment = ' <img src="../system/images/file_icon.gif"> Document Attachment: <strong>'. $attachmentinfo['Filename'] .'.'. $attachmentinfo['Type'].'</strong>';
  
  print "<br />
<h2>". blogitize($entryinfo['Title']) ."</h2>
<p class=\"infobar\">Posted at {$timestamp} by ". authorlookup($entryinfo['Author']) .". <img src=\"../system/comment.gif\"> {$comments}</p>
<div class=\"green\">". blogitize($entryinfo['Entry']) ."</div>
<br />
<h2>Attachments</h2>
<div class=\"blue\">";

if(mysql_num_rows($attachmentquery) > 0)
  print $attachment;

else
  print "There is no file attachment on this blog post.";

print "
</div>
<br />
<h2>Comments on &quot;{$entryinfo['Title']}&quot;</h2>
";

$commentquery = mysql_query("SELECT * FROM `". get_table('comments') ."` WHERE `EntryID` = {$entryinfo['ID']};");

if(mysql_num_rows($commentquery) > 0)
  print "<div class=\"green\">
<ul class=\"comments\">
";

else
  print "<div class=\"yellow\"><p>There are no comments on this entry.</p></div>";

while($crow = mysql_fetch_assoc($commentquery))
  {
  if(date("n-j-Y", ($crow['Posted'] + get_table('dateoffset'))) == date("n-j-Y"))
  $posted = "today at ". date("g:i a", ($crow['Posted'] + get_table('dateoffset')));
elseif(date("n-j-Y", ($crow['Posted'] + get_table('dateoffset'))) == date("n-j-Y", (time() - 86400)))
  $posted = "yesterday at ". date("g:i a", ($crow['Posted'] + get_table('dateoffset')));
else
  $posted = date("\a\\t g:i a l, F jS", ($crow['Posted'] + get_table('dateoffset')));
  
  $author = authorlookup($crow['Author']);
  
  print "<li><strong>{$author}</strong> said {$posted}...<br />
". blogitize($crow['Comment']) ."</li>\n";
  }

if(mysql_num_rows($commentquery) > 0)
  print "</ul>
</div>";
  }

if($_GET['mode'] == "comment" AND !isset($_GET['sure']))
  {
  if(mysql_num_rows(mysql_query("SELECT * FROM `". get_table('comments') ."` WHERE `ID` = '". sanitize($_GET['ID']) ."';")) == 0)
    die("<br /><h2>There is no blog comment with that ID number.</h2>
<div class=\"red\">No comment with ID number <strong>{$_GET['ID']}</strong> exists in the database.</div>");
  
  $commentinfo = mysql_fetch_assoc(mysql_query("SELECT * FROM `". get_table('comments') ."` WHERE `ID` = '". sanitize($_GET['ID']) ."';"));
  $posted = $commentinfo['Posted'];
  
  if(date("n-j-Y", ($posted + get_table('dateoffset'))) == date("n-j-Y"))
    $timestamp = "today at ". date("g:i a", ($posted + get_table('dateoffset')));
  elseif(date("n-j-Y", ($posted + get_table('dateoffset'))) == date("n-j-Y", (time() - 86400)))
    $timestamp = "yesterday at ". date("g:i a", ($posted + get_table('dateoffset')));
  else
    $timestamp = date("\a\\t g:i a l, F jS, Y", ($posted + get_table('dateoffset')));
  
  $entryinfo = mysql_fetch_assoc(mysql_query("SELECT * FROM `". get_table('blog') ."` WHERE `ID` = {$commentinfo['EntryID']};"));
    
  print "<br />
<ul class=\"comments\">
<li><strong>". authorlookup($commentinfo['Author']) ."</strong> said about the entry below {$timestamp}:</li>
". blogitize($commentinfo['Comment']) ."</ul>";
  
  //Show blog entry the comment is about
  if(mysql_num_rows(mysql_query("SELECT * FROM `". get_table('blog') ."` WHERE `ID` = '{$commentinfo['EntryID']}';")) == 0)
    die("<br /><h2>There is no entry with that ID number.</h2>
<div class=\"red\">No entry with ID number <strong>{$_GET['viewentry']}</strong> exists in the database.</div>");
  
  $entryinfo = mysql_fetch_assoc(mysql_query("SELECT * FROM `". get_table('blog') ."` WHERE `ID` = '{$commentinfo['EntryID']}';"));
  $posted = $entryinfo['Posted'];
  
  if(date("n-j-Y", ($posted + get_table('dateoffset'))) == date("n-j-Y"))
    $timestamp = "today at ". date("g:i a", ($posted + get_table('dateoffset')));
  elseif(date("n-j-Y", ($posted + get_table('dateoffset'))) == date("n-j-Y", (time() - 86400)))
    $timestamp = "yesterday at ". date("g:i a", ($posted + get_table('dateoffset')));
  else
    $timestamp = date("\a\\t g:i a l, F jS, Y", ($posted + get_table('dateoffset')));
    
  $commentquery = mysql_num_rows(mysql_query("SELECT * FROM `". get_table('comments') ."` WHERE `EntryID` = {$entryinfo['ID']};"));
  
  if($commentquery == 0)
    $comments = "No comments";
  elseif($commentquery == 1)
    $comments = "1 comment";
  else
    $comments = $commentquery . " comments";
  
  print "<br />
<h2>". blogitize($entryinfo['Title']) ."</h2>
<p class=\"infobar\">Posted {$timestamp} by ". authorlookup($entryinfo['Author']) .". <img src=\"../system/comment.gif\"> {$comments} including the above comment</p>
<div class=\"green\">". blogitize($entryinfo['Entry']) ."</div>";
  }
?>
</body>
</html>