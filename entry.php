<?php
session_start();
if(!isset($_SESSION['familysite']))
  Header("Location: login.php?go=entry.php?entry={$_GET['entry']}");

if(!isset($_GET['entry']))
  Header("Location: index.php?error=invalidentry");

include "system/parse.php";
dbconnect();

if(mysql_num_rows(mysql_query("SELECT * FROM `". get_table('blog') ."` WHERE `ID` = ". sanitize($_GET['entry']) ." LIMIT 1;")) == 0)
  Header("Location: index.php?error=invalidentry");

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
  $timestamp = date("\a\\t g:i a l, F jS, Y", ($posted + get_table('dateoffset')));
?><html>
<head>
<link type="text/css" href="system/style.css" rel="stylesheet">
<link rel="alternate" type="application/rss+xml" title="<?php print get_table('SiteName'); ?> News Blog" href="commentfeed.php?entryID=<?php print sanitize($_GET['entry']); ?>">
<title><?php print get_table('SiteName') . ": " .  strip_tags($title); ?></title>
<?php
if($entry['Author'] == $_SESSION['familysite'])
  print '<script type="text/javascript">
function deleteFile(ID)
{
if(confirm("Are you sure you want to delete this file?"))
  {
  var dfile = getHTTPObject();

  if(dfile)
  {
    dfile.onreadystatechange = function() {
    if(dfile.readyState == 4 && dfile.status == 200)
      {
      alert(dfile.responseText);
      document.reload();
      }
    };
    dfile.open("GET", "admin/managefiles.php?entrypage&delete="+ID, true);
    dfile.send(null);
  }

  else
    alert(\'Fatal error: Your browser does not support AJAX technology, which is required for this site to work. Please consider upgrading your browser.\');
  }
}
</script>';

include "header.php";
?>

</head>
<body>
<?php include "userinfo.php"; ?>
<h1><?php print $title;?></h1>
<p class="infobar"><?php print "Posted {$timestamp} by {$author}.";?></p>
<div class="green"><?php print blogitize($entry['Entry']); ?></div>
<br />
<h2>Comments on &quot;<?php print $title . '&quot;<span style="font-size: 70%"> | <a href="addcomment.php?entry='. $_GET['entry'] .'">Add Comment &raquo;</a></span>'; ?></h2>
<?php
$commentquery = mysql_query("SELECT * FROM `". get_table('comments') ."` WHERE `EntryID` = {$entry['ID']};");

if(mysql_num_rows($commentquery) > 0)
  print "<div class=\"green\">
<ul class=\"comments\">
";

else
  print "<div class=\"yellow\"><p>There are no comments on this entry yet. <a href=\"addcomment.php?entry={$_GET['entry']}\">Add one &raquo;</a></p></div>";

while($crow = mysql_fetch_assoc($commentquery))
  {
  if(date("n-j-Y", ($crow['Posted'] + get_table('dateoffset'))) == date("n-j-Y", (time() + get_table('dateoffset'))))
  $posted = "today at ". date("g:i a", ($crow['Posted'] + get_table('dateoffset')));
elseif(date("n-j-Y", ($crow['Posted'] + get_table('dateoffset'))) == date("n-j-Y", ((time() - 86400) + get_table('dateoffset'))))
  $posted = "yesterday at ". date("g:i a", ($crow['Posted'] + get_table('dateoffset')));
else
  $posted = date("\a\\t g:i a l, F jS", ($crow['Posted'] + get_table('dateoffset')));
  
  $author = authorlookup($crow['Author']);
  
  print "<li><strong>{$author}</strong> said {$posted}...<br />
". blogitize($crow['Comment']) ."</li>\n";
  }

if(mysql_num_rows($commentquery) > 0)
  print "</ul>
</div>
<br />";

$attachmentquery = mysql_query("SELECT * FROM `". get_table('files') ."` WHERE `EntryID` = {$entry['ID']};");

if(mysql_num_rows($attachmentquery) > 0)
  {
  $attachmentinfo = mysql_fetch_assoc($attachmentquery);
  
  if($attachmentinfo['Type'] == "picture")
    {
    print "\n<h2>Photo Attachment ";
    
    if($entry['Author'] == $_SESSION['familysite'])
      print '<span style="font-size: 60%">[<a href="javascript: void(0);" onClick=" deleteFile(\''. $attachmentinfo['ID'] .'\');">Delete</a>]</span>';
    
    print "</h2>
<div class=\"blue\">A photo was included with this blog post.<br />
<a href=\"{$attachmentinfo['Path']}\"><img src=\"system/images/picture_icon.gif\"> {$attachmentinfo['Filename']}</a></div>
<br />";
    }
  
  elseif($attachmentinfo['Type'] == "video")
    {
    print "\n<h2>Video Attachment ";
    
    if($entry['Author'] == $_SESSION['familysite'])
      print '<span style="font-size: 60%">[<a href="javascript: void(0);" onClick=" deleteFile(\''. $attachmentinfo['ID'] .'\');">Delete</a>]</span>';
    
    print "</h2>
<div class=\"blue\">". stripslashes($attachmentinfo['Path']) ."</div>
<br />";
    }
  
  else
    {
    print "\n<h2>File Attachment ";
    
    if($entry['Author'] == $_SESSION['familysite'])
      print '<span style="font-size: 60%">[<a href="javascript: void(0);" onClick=" deleteFile(\''. $attachmentinfo['ID'] .'\');">Delete</a>]</span>';
    
    print "</h2>
<div class=\"blue\"><a href=\"". stripslashes($attachmentinfo['Path']) ."\"><img src=\"system/images/file_icon.gif\"> ". stripslashes($attachmentinfo['Filename']) .".". $attachmentinfo['Type'] ."</a></div>
<br />";
    }
  }

elseif($_SESSION['familysite'] == $entry['Author'])
  print "\n<img src=\"system/images/file_icon.gif\"> <strong>You can <a href=\"admin/attachfile.php?entryID={$_GET['entry']}\">attach a file &raquo;</a> to this entry if you like.</strong>";
?>
<p id="footer">Displaying one out of <?php print mysql_num_rows(mysql_query("SELECT * FROM `". get_table('blog') ."`;")); ?> entries. Last system update: <?php print date("g:ia n/j/y", (getlastmod() + get_table('dateoffset'))); ?> <a href="rss.php?entry=<?php print $_GET['entry']; ?>"><img src="system/images/rss.gif"></a></p>
</body>
</html>