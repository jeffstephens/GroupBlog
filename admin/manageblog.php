<?php
session_start();
if(!isset($_SESSION['familysite']))
  Header("Location: ../login.php?go=admin/manageblog.php");

if($_SESSION['familysite'] != 1)
  Header("Location: ../index.php?error=permissiondenied");

include "../system/parse.php";
dbconnect();

if(isset($_GET['viewentry']))
  {
  if(mysql_num_rows(mysql_query("SELECT * FROM `". get_table('blog') ."` WHERE `ID` = '". sanitize($_GET['viewentry']) ."';")) == 0)
    die("<br /><h2>There is no entry with that ID number.</h2>
<div class=\"red\">No entry with ID number <strong>{$_GET['viewentry']}</strong> exists in the database.</div>");
  
  $entryinfo = mysql_fetch_assoc(mysql_query("SELECT * FROM `". get_table('blog') ."` WHERE `ID` = '". sanitize($_GET['viewentry']) ."';"));
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
  
  if(mysql_num_rows($attachmentquery) == 1)
    {
    $attachmentinfo = mysql_fetch_assoc($attachmentquery);
    
    if($attachmentinfo['Type'] == "picture")
      $attachment = ' <img src="../system/images/picture_icon.gif"> Photo Attachment';
    
    elseif($attachmentinfo['Type'] == "video")
      $attachment = ' <img src="../system/images/youtube_icon.gif"> Video Attachment';
    
    else
      $attachment = ' <img src="../system/images/file_icon.gif"> Document Attachment';
    }
  
  print "<br />
<h2>". blogitize($entryinfo['Title']) ."</h2>
<p class=\"infobar\">Posted at {$timestamp} by ". authorlookup($entryinfo['Author']) .". <img src=\"../system/comment.gif\"> {$comments}{$attachment} <a href=\"delete.php?mode=blog&amp;ID={$entryinfo['ID']}\"><img src=\"../system/images/delete.gif\" border=\"0\">Delete this Entry (and its comments)</a></p>
<div class=\"green\">". blogitize($entryinfo['Entry']) ."</div>";
  die();
  }
?><html>
<head>
<link type="text/css" href="../system/style.css" rel="stylesheet">
<title><?php print get_table('SiteName'); ?>: Administration: Manage Blog Entries</title>
<script type="text/javascript" src="../system/engine.js"></script>
<script type="text/javascript">
function search()
{
var search = getHTTPObject();

if(search)
{
  search.onreadystatechange = function() {
  if(search.readyState == 4 && search.status == 200)
    document.getElementById('browseresults').innerHTML=search.responseText;
  };
  search.open("GET", "search.php?keyword="+ document.getElementById('keyword').value +"&mode=blog", true);
  search.send(null);
}

else
  alert('Fatal error: Your browser does not support AJAX technology, which is required for this site to work.\n\nPlease consider upgrading your browser.');
}

function openEntry(ID)
{
var entry = getHTTPObject();

if(entry)
{
  entry.onreadystatechange = function() {
  if(entry.readyState == 4 && entry.status == 200)
    document.getElementById('blogview').innerHTML=entry.responseText;
  };
  entry.open("GET", "manageblog.php?viewentry="+ID, true);
  entry.send(null);
}

else
  alert('Fatal error: Your browser does not support AJAX technology, which is required for this site to work.\n\nPlease consider upgrading your browser.');
}

function recentEntries()
{
var recent = getHTTPObject();

if(recent)
{
  recent.onreadystatechange = function() {
  if(recent.readyState == 4 && recent.status == 200)
    document.getElementById('browseresults').innerHTML=recent.responseText;
  };
  recent.open("GET", "search.php?mode=recentblog&keyword=null", true);
  recent.send(null);
}

else
  alert('Fatal error: Your browser does not support AJAX technology, which is required for this site to work.\n\nPlease consider upgrading your browser.');
}
</script>
<?php include "../header.php"; ?>
</head>

<body>
<?php include "../userinfo.php"; ?>
<h1>Manage Blog Entries</h1>
<div class="yellow" style="text-align: center">Here you can delete blog posts.</div>
<br />
<h2>Browse Blog Entries</h2>
<div class="blue">
<input type="text" name="keyword" id="keyword" value="Search..." style="color: #666" onFocus=" if(this.value=='Search...') { this.value=''; this.style.color='#000'; }" onBlur=" if(this.value=='') { this.style.color='#666'; this.value='Search...'; }" onKeyDown=" search();"> <input type="button" value="X" onClick=" document.getElementById('keyword').value='Search...'; document.getElementById('keyword').style.color='#666'; search();"> <input type="button" value="Search" onClick=" search();"> You can also <a href="javascript: void(0);" onClick=" recentEntries();">view the most recent entries</a> or <a href="javascript: void(0);" onClick=" openEntry(prompt('Enter the ID number for the entry you want to view.'));">view an entry by ID number</a>.<br />
<span id="browseresults">Type a query to begin.</span>
</div>
<span id="blogview"></span>
<br />
<p id="footer">Manage Blog Entries - Last System Update: <?php print date("n/j/Y", (getlastmod() + get_table('dateoffset'))); ?></p>
</body>
</html>