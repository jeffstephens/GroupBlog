<?php
session_start();
if(!isset($_SESSION['familysite']))
  Header("Location: login.php");

include "system/parse.php";
dbconnect();
?><html>
<head>
<title><?php print get_table('SiteName'); ?>: Overview</title>
<style type="text/css">
body {
background-color: #FFF }

table #blog, tr #blog, td #blog {
background-color: #FFF;
text-align: left !important;
border: none }

tbody #blog {
background-color: #FFF;
border: none }

	tbody #blog p {
		margin-bottom: 0;
	}
	
	tbody #blog h2 {
		margin-top: 1em;
	}

h1 {
padding: 0;
margin: 0 }

ul, li {
text-align: left }

#sidebar {
vertical-align: top;
background: #FFFFE5 }

  #sidebar h2 {
  border: 0;
  color: #FFF;
  font-size: 110%;
  background-color: #003366;
  text-align: center }
  
  #sidebar h2 a:link, #sidebar h2 a:visited, #sidebar h2 a:hover, #sidebar h2 a:focus, #sidebar h2 a:active {
  color: #FFF }
  
  #sidebar h2 a:hover, #sidebar h2 a:focus, #sidebar h2 a:active {
  background-color: #003366 }

#error {
text-align: left;
margin-bottom: 15px }

.msg {
border-bottom: 1px solid #000;
background-color: #FFF;
width: 100% }
  
  .read {
  background-color: #EDEDED }
  
  .unread ul, .unread li {
  list-style: url('system/images/msgicon.gif') }
  
  .read ul, .read li {
  list-style: url('system/images/msgicon_read.gif') }
  
  .msg span {
  color: #666 }
  
  .msg a {
  display: block;
  text-decoration: none !important }
  
  .msg a:link, .msg a:visited {
  color: #000 }
  
  .msg a:hover, .msg a:active, .msg a:focus {
  background-color: #E5EBFF;
  color: #000 }
</style>
<link type="text/css" href="system/style.css" rel="stylesheet">
<link rel="alternate" type="application/rss+xml" title="Family Website News Blog" href="blogfeed.php">
<link rel="alternate" type="application/rss+xml" title="Family Website Recent Activity Feed" href="feed.php">
<?php include "header.php"; ?>
</head>

<body>
<?php include "userinfo.php"; ?>
<table width="100%" border="0">
<?php
if(isset($_GET['error']))
  {
  print "<div id=\"error\" style=\"text-align: center\">
<h1>An error has occurred.</h1>
<div class=\"red\">";
  
  if($_GET['error'] == "invalidentry")
    print "This blog entry does not exist. It may have been deleted, or you may have clicked a bad link.";
  
  elseif($_GET['error'] == "invalidaddentry")
    print "You are attempting to add a blog comment to a post that does not exist. Please do not do this.";
  
  elseif($_GET['error'] == "permissiondenied")
    print "You do not have permission to view that page.";
  
  else
    print "An unknown error has occurred. Please don't do strange things.";
  
  print " <a href=\"index.php\">Hide This &raquo;</a></div>
</div>
";
  }
?>
<tr>
<td id="blog" width="70%" style="vertical-align: top">
<?php
//Display notice if there's no poll for this week
$checkthispoll = Array();
$checkthispoll['CurrentWeek'] = date("W", (time() + get_table('dateoffset')));
$checkthispoll['CurrentYear'] = date("Y", (time() + get_table('dateoffset')));

$thispoll_query = mysql_query("SELECT * FROM `". get_table('pollq') ."` WHERE `Week` = {$checkthispoll['CurrentWeek']} AND `Year` = {$checkthispoll['CurrentYear']} LIMIT 1;");

if(mysql_num_rows($thispoll_query) == 0)
  {
  print '<div class="red" style="text-align: center; border-top: 1px solid #000; border-bottom: 1px solid #000; padding-bottom: 3px">
<p style="font-weight: 900; margin: 0; padding: 0; font-size: 110%">There\'s no poll for this week!</p>
There\'s no poll running right now. Why not <a href="admin/addpoll.php">add one &raquo;</a> so everyone can vote on it?
</div>
<br />';
  }

else
{
//Display notice if there's no poll for next week and there is one for this week
$checknextpoll = Array();
$checknextpoll['CurrentWeek'] = date("W", (time() + get_table('dateoffset')));
$checknextpoll['CurrentYear'] = date("Y", (time() + get_table('dateoffset')));

if($checknextpoll['CurrentWeek'] == "52")
  {
  $checknextpoll['NextWeek'] = "1";
  $checknextpoll['NextYear'] = ($checknextpoll['CurrentYear'] + 1);
  }

else
  {
  $checknextpoll['NextWeek'] = ($checknextpoll['CurrentWeek'] + 1);
  $checknextpoll['NextYear'] = $checknextpoll['CurrentYear'];
  }

$nextpoll_query = mysql_query("SELECT * FROM `". get_table('pollq') ."` WHERE `Week` = {$checknextpoll['NextWeek']} AND `Year` = {$checknextpoll['NextYear']} LIMIT 1;");

if(mysql_num_rows($nextpoll_query) == 0)
  {
  print '<div class="red" style="text-align: center; border-top: 1px solid #000; border-bottom: 1px solid #000; padding-bottom: 3px">
<p style="font-weight: 900; margin: 0; padding: 0; font-size: 110%">There\'s no poll for next week!</p>
No poll of the week has been created for next week! <a href="admin/addpoll.php">Create one &raquo;</a> and have everyone vote on your idea.
</div>
<br />';
  }
}

//Check for orphaned files
if(mysql_num_rows(mysql_query("SELECT * FROM `". get_table('files') ."` WHERE `Owner` = {$_SESSION['familysite']} AND `EntryID` = 0;")))
  print '<div class="red" style="text-align: center; border-top: 1px solid #000; border-bottom: 1px solid #000; padding-bottom: 3px">
<p style="font-weight: 900; margin: 0; padding: 0; font-size: 110%">Cluttered Files</p>
You\'ve uploaded files that aren\'t attached to a blog entry. They have a red background on the <a href="admin/managefiles.php">File Management</a> page. Please take care of them.
<form action="admin/managefiles.php?quickfix" method="post" style="margin: 0; padding: 0">
<input type="submit" value="Fix For Me &raquo;">
</form>
</div>
<br />';

//Check for 100 post celebration
$totalposts = mysql_num_rows(mysql_query("SELECT * FROM `". get_table('blog') ."`;"));
if($totalposts >= 100 AND $totalposts <= 105)
  print '<div class="green" style="text-align: center; border-top: 1px solid #000; border-bottom: 1px solid #000; padding-bottom: 3px">
<p style="font-weight: 900; margin: 0; padding: 0; font-size: 110%">We Have 100 Posts!</p>
There are now '. $totalposts .' blog entries on this website! Thanks for the help! <img src="system/emoticons/smile.gif">
</div>
<br />';

//Check if their account is inactive
$accountinfo = mysql_fetch_assoc(mysql_query("SELECT * FROM `". get_table('users') ."` WHERE `ID` = {$_SESSION['familysite']};"));

if($accountinfo['Active'] == 0)
  print '<div class="orange" style="text-align: center; border-top: 1px solid #000; border-bottom: 1px solid #000; padding-bottom: 3px">
<p style="font-weight: 900; margin: 0; padding: 0; font-size: 110%">Account Inactive</p>
Because you haven\'t logged in to your account in a long time, it has been set to &quot;Inactive&quot;. As long as it has this status, you will not receive automated notifications such as new blog entry or comment reports. Your account will be re-activated automatically within a week, and this notice will go away when that happens.
</div>
<br />';
?>
<h1>Recent Blog Entries</h1>
<div class="yellow" style="text-align: center">These are some of the most recent blog entries. <a href="addentry.php">Add a New Entry &raquo;</a> or <a href="blog.php">View the Full Blog &raquo;</a></div>
<br />
<?php
$query = mysql_query("SELECT * FROM `". get_table('blog') ."` ORDER BY `Posted` DESC LIMIT 5;");
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
      $attachment = ' <img src="system/images/picture_icon.gif"> <a href="entry.php?entry='. $row['ID'] .'">Photo Attachment</a>';
    
    elseif($attachmentinfo['Type'] == "video")
      $attachment = ' <img src="system/images/youtube_icon.gif"> <a href="entry.php?entry='. $row['ID'] .'">Video Attachment</a>';
    
    else
      $attachment = ' <img src="system/images/file_icon.gif"> <a href="entry.php?entry='. $row['ID'] .'">Document Attachment</a>';
    }
  
  print "<h2>". blogitize($row['Title']) ."</h2>
<p class=\"infobar\">Posted {$timestamp} by {$author}. <a href=\"entry.php?entry={$row['ID']}\"><img src=\"system/comment.gif\" border=\"0\" alt=\"Comments\"> {$comments}</a>{$attachment}</p>
<div class=\"green\">
<p>{$entry}</p>
</div>
";
  
  unset($attachment);
  }
 
$entrycount = mysql_num_rows(mysql_query("SELECT * FROM `". get_table('blog') ."`;"));

print '<p id="footer">Displaying ';

if($entrycount < 5)
	print $entrycount;
else
	print '5';

print ' out of '. $entrycount .' entries. <a href="blog.php">View More &raquo;</a> Last system update: '. date("g:ia n/j/y", (getlastmod() + get_table('dateoffset'))) .' <a href="rss.php"><img src="system/images/rss.gif"></a></p>';
?>

<!--Check for compatibility problems-->
<noscript>
<br />
<div class="red" style="text-align: center; border-top: 1px solid #000; border-bottom: 1px solid #000; padding-bottom: 3px">
<p style="font-weight: 900; margin: 0; padding: 0; font-size: 110%">Javascript Disabled</p>
You currently either have Javascript disabled in your browser or your browser doesn't support it. There are many features on <?php print get_table('SiteName'); ?> that use Javascript, so you might want to consider <a href="http://www.google.com/support/bin/answer.py?answer=23852">enabling it</a> or <a href="https://www.google.com/chrome/">upgrading your browser</a>.
</div>
</noscript>
<?php
/* TODO: update this to check for IE6 and suggest Chrome instead of Firefox

if(strstr($_SERVER['HTTP_USER_AGENT'], "MSIE"))
  print '<br />
<div class="red" style="text-align: center; border-top: 1px solid #000; border-bottom: 1px solid #000; padding-bottom: 3px">
<p style="font-weight: 900; margin: 0; padding: 0; font-size: 110%">Internet Explorer Detected</p>
We have detected that you are viewing the '. get_table('SiteName') .' using Internet Explorer. While it will function correctly, it looks and works better in a browser such as <a href="http://www.mozilla.com/en-US/firefox/?from=getfirefox">Firefox</a>.
</div>';*/
?>
</td>

<td id="sidebar">
<h2><a href="inbox.php">Notification Inbox (Displaying <?php
$query = mysql_query("SELECT * FROM `". get_table('notifications') ."` WHERE `InboxID` = {$_SESSION['familysite']} AND (". time() ." - `Sent`) <= 604800 ORDER BY `Sent` DESC LIMIT 5;");
$msgcount = mysql_num_rows($query);
print $msgcount; ?>)</a></h2>
<?php
if($msgcount == 0)
  print "You have no notifications from the past week.<br />";

else
  {
  //Inbox
  while($row = mysql_fetch_assoc($query))
    {
    if($row['Read'] == 0)
      {
      $status = "unread";
      $emphasis = "strong";
      }
    else
      {
      $status = "read";
      $emphasis = "u";
      }
    
    $bodydata = explode(" ", strip_tags(blogitize($row['Body'])));
    $body = "";
    
    for($i = 0; $i < 20; $i++)
      {
      $body = $body . " " . $bodydata[$i];
      }
    
    $body = trim($body);
    
    print "<div class=\"msg {$status}\">
<a href=\"inbox.php?msg={$row['ID']}\">
<ul>
<li><{$emphasis}>". blogitize($row['Subject']) ."</{$emphasis}> <span>". date("n/j/y", ($row['Sent'] + get_table('dateoffset'))) ."</span><br />
{$body}...</li>
</ul>
</a>
</div>";
    }
  
  print "<br />\n";
  }

$totalmsgs = mysql_num_rows(mysql_query("SELECT * FROM `". get_table('notifications') ."` WHERE `InboxID` = {$_SESSION['familysite']};"));
  
  if($totalmsgs > $msgcount)
    {
    $moremsgs = $totalmsgs - $msgcount;
    
    if($moremsgs != 1)
      {
      $is = "are";
      $s = "s";
      }
    
    else
      {
      $is = "is";
      $s = "";
      }
    
    print "<p style=\"text-align: left; margin: 0; padding: 0\"><strong>There {$is} {$moremsgs} other notification{$s} in your <a href=\"inbox.php\">Inbox &raquo;</a>.</strong></p>";
    }
?>
<br />
<h2>Poll of the Week</h2>
<?php
$thisweekpollquery = mysql_query("SELECT * FROM `". get_table('pollq') ."` WHERE `Week` = '". date("W", (time() + get_table('dateoffset'))) ."' AND `Year` = '". date("Y", (time() + get_table('dateoffset'))) ."';");
$thisweekpollinfo = mysql_fetch_assoc($thisweekpollquery);

if(mysql_num_rows($thisweekpollquery) == 0)
  {
  print "There is no poll this week. <a href=\"admin/addpoll.php\">Create One &raquo;</a><br />";
  }

elseif(mysql_num_rows(mysql_query("SELECT * FROM `". get_table('polla') ."` WHERE `Question` = '{$thisweekpollinfo['ID']}' AND `Voter` = '{$_SESSION['familysite']}';")) == 0)
  {
  //Haven't voted yet.
  $pollinfo = mysql_fetch_assoc(mysql_query("SELECT * FROM `". get_table('pollq') ."` WHERE `Week` = '". date("W", (time() + get_table('dateoffset'))) ."' AND `Year` = '". date("Y", (time() + get_table('dateoffset'))) ."';"));
  
  print "<form action=\"vote.php\" method=\"post\" style=\"text-align: left\">
<input type=\"hidden\" name=\"pollID\" value=\"{$pollinfo['ID']}\">
<p style=\"text-align: left; margin: 0; padding: 0\"><strong>". stripslashes($pollinfo['Question']) ."</strong></p><br />\n";
  
  $answers = explode("\n", stripslashes($pollinfo['Answers']));
  
  for($i = 0; $i < count($answers); $i++)
    print "<input type=\"radio\" id=\"option". ($i + 1) ."\" name=\"pollchoice\" value=\"". ($i + 1) ."\"> <label for=\"option". ($i + 1) ."\">{$answers[$i]}</label><br />
";
  
  print "<input type=\"submit\" value=\"Vote &raquo;\"> <a href=\"admin/addpoll.php\">Create New Poll &raquo;</a>";
  
  print "
</form>";
  }

else
  {
  //Already voted. Display some basic results.
  $answers = explode("\n", stripslashes($thisweekpollinfo['Answers']));
  
  print "<p style=\"text-align: left; margin: 0; padding: 0\"><strong>". stripslashes($thisweekpollinfo['Question']) ."</strong></p>
<table border=\"0\" style=\"vertical-align: top\">";
  
  for($i = 0; $i < count($answers); $i++)
    {
    $votes = mysql_num_rows(mysql_query("SELECT * FROM `". get_table('polla') ."` WHERE `Question` = {$thisweekpollinfo['ID']} AND `Choice` = ". ($i + 1) .";"));
    
    print "
  <tr>
    <td style=\"text-align: right; background-color: #FFFFE5; width: 75%\">{$answers[$i]}</td>
    <td style=\"text-align: left\"><img src=\"system/images/pollbar.jpg\" width=\"". (($votes * 5) + 1) ."\" height=\"16\"> {$votes}</td>
  </tr>\n";
    }
    
    print "\n</table>
<a href=\"vote.php\">View Details &raquo;</a> or <a href=\"admin/addpoll.php\">Create New Poll &raquo;</a>";

print "
<br />";
  }

$query = mysql_query("SELECT * FROM `". get_table('users') ."` WHERE (". time() ." - `Registered`) < 2592000 ORDER BY `Registered` DESC;");

if(mysql_num_rows($query) > 0)
  {
  print "<br />
<h2>Recently Joined Members</h2>
<ul>
";

  while($row = mysql_fetch_assoc($query))
    {
    print "<li><strong>{$row['Name']}</strong> (". date("n/j/y", ($row['Registered'] + get_table('dateoffset'))) .")</li>
  ";
    }

  print "</ul>
";
  }
?>
<br />
<?php
$entrycount = mysql_num_rows(mysql_query("SELECT * FROM `". get_table('blog') ."` WHERE `Author` = '{$_SESSION['familysite']}';"));
$commentcount = mysql_num_rows(mysql_query("SELECT * FROM `". get_table('comments') ."` WHERE `Author` = '{$_SESSION['familysite']}';"));

if($entrycount > 0 OR $commentcount > 0)
  print '
<h2>Your Statistics</h2>
<ul>
You\'ve posted...
  <li>'. $entrycount .' blog entries</li>
  <li>'. $commentcount .' blog comments</li>
</ul>';
?>
</td>
</tr>
</table>
</body>
</html>