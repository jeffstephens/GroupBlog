<?php
session_start();
if(!isset($_SESSION['familysite']))
  Header("Location: login.php?go=blog.php");

include "system/parse.php";
dbconnect();
?><html>
<head>
<link type="text/css" href="system/style.css" rel="stylesheet">
<link rel="alternate" type="application/rss+xml" title="Family Website News Blog" href="feed.php">
<?php
if(isset($_GET['keyword']))
  print '
<link rel="alternate" type="application/rss+xml" title="Search Results Feed for &quot;'. $_GET['keyword'] .'&quot;" href="searchfeed.php?keyword='. $_GET['keyword'] .'">
';
?>
<title><?php print get_table('SiteName'); ?>: News Blog</title>
<?php include "header.php"; ?>
</head>
<body>
<?php include "userinfo.php"; ?>
<form action="blog.php" method="get" style="margin: 0; padding: 0">
<h1>News Blog</h1>
<?php
if(isset($_GET['keyword']))
  {
  $query = mysql_query("SELECT * FROM `". get_table('blog') ."` WHERE `Title` LIKE '%". sanitize($_GET['keyword']) ."%' OR `Entry` LIKE '%". sanitize($_GET['keyword']) ."%' ORDER BY `Posted` DESC;");
  
  if(mysql_num_rows($query) > 0)
    {
    if(mysql_num_rows($query) == 1)
      $results = "1 result";
    
    else
      $results = mysql_num_rows($query) . " results";
    
    print "<div class=\"green\" style=\"text-align: center\">
<p>Displaying {$results} matching <strong>". stripslashes($_GET['keyword']) ."</strong>.<br />
<a href=\"blog.php\">View All Entries &raquo;</a> or <input type=\"text\" value=\"{$_GET['keyword']}\" name=\"keyword\" style=\"font-size: 100%\"><input type=\"submit\" value=\"Search Again &raquo;\"style=\"font-size: 100%\">";
    }
  
  else
    print "<div class=\"red\" style=\"text-align: center\">
<p>There are no entries matching <strong>". stripslashes($_GET['keyword']) ."</strong>.<br />
<a href=\"blog.php\">View All Entries &raquo;</a> or <input type=\"text\" value=\"{$_GET['keyword']}\" name=\"keyword\" style=\"font-size: 100%\"><input type=\"submit\" value=\"Search Again &raquo;\"style=\"font-size: 100%\">";
  }

else
  {
  $query = mysql_query("SELECT * FROM `". get_table('blog') ."` ORDER BY `Posted` DESC LIMIT 30;");
  
  print "<div class=\"yellow\" style=\"text-align: center\">
<p>Keep us all updated on goings-on, announcements, or anything else you wish to share!<br />
<a href=\"addentry.php\">Add an Entry</a> or <input type=\"text\" name=\"keyword\" style=\"font-size: 100%\"><input type=\"submit\" value=\"Search &raquo;\"style=\"font-size: 100%\">";
  }
?>
</p></div>
</form>

<?php
while($row = mysql_fetch_assoc($query))
  {
  $posted = $row['Posted'];
  $author = authorlookup($row['Author']);
  
  if(date("n-j-Y", ($posted + get_table('dateoffset'))) == date("n-j-Y", (time() + get_table('dateoffset'))))
    $timestamp = "today at ". date("g:i a", ($posted + get_table('dateoffset')));
  elseif(date("n-j-Y", ($posted + get_table('dateoffset'))) == date("n-j-Y", ((time() - 86400) + get_table('dateoffset'))))
    $timestamp = "yesterday at ". date("g:i a", ($posted + get_table('dateoffset')));
  else
    $timestamp = date("\a\\t g:i a l, F jS, Y", ($posted + get_table('dateoffset')));
  
  $entry = blogitize($row['Entry']);
  
  $commentquery = mysql_num_rows(mysql_query("SELECT * FROM `". get_table('comments') ."` WHERE `EntryID` = {$row['ID']};"));
  
  if($commentquery == 0)
    $comments = "No comments yet. Add one &raquo;";
  elseif($commentquery == 1)
    $comments = "1 comment &raquo;";
  else
    $comments = $commentquery . " comments &raquo;";
  
  //Check for attachment
  $attachmentquery = mysql_query("SELECT * FROM `". get_table('files') ."` WHERE `EntryID` = {$row['ID']};");
  
  if(mysql_num_rows($attachmentquery) > 0)
    {
    $attachmentinfo = mysql_fetch_assoc($attachmentquery);
    
    if($attachmentinfo['Type'] == "picture")
      $attachment = ' <img src="system/images/picture_icon.gif"> Photo Attachment';
    
    elseif($attachmentinfo['Type'] == "video")
      $attachment = ' <img src="system/images/youtube_icon.gif"> Video Attachment';
    
    else
      $attachment = ' <img src="system/images/file_icon.gif"> Document Attachment';
    }
  
  print "<h2>". blogitize($row['Title']) ."</h2>
<p class=\"infobar\">Posted {$timestamp} by {$author}. <a href=\"entry.php?entry={$row['ID']}\"><img src=\"system/comment.gif\" border=\"0\"> {$comments}</a>{$attachment}</p>
<div class=\"green\">
<p>{$entry}</p>
</div>
";
  
  unset($attachment);
  }
?>
<p id="footer">Displaying <?php print mysql_num_rows($query); ?> out of <?php print mysql_num_rows(mysql_query("SELECT * FROM `". get_table('blog') ."`;")); ?> entries. Last system update: <?php print date("g:ia n/j/y", (getlastmod() + get_table('dateoffset'))); ?> <a href="rss.php<?php if(isset($_GET['keyword'])) print '?keyword='. $_GET['keyword']; ?>"><img src="system/images/rss.gif"></a></p>
</body>
</html>