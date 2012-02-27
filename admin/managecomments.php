<?php
session_start();
if(!isset($_SESSION['familysite']))
  Header("Location: ../login.php?go=admin/managecomments.php");

if($_SESSION['familysite'] != 1)
  Header("Location: ../index.php?error=permissiondenied");

include "../system/parse.php";
dbconnect();

if(isset($_GET['viewcomment']))
  {
  if(mysql_num_rows(mysql_query("SELECT * FROM `". get_table('comments') ."` WHERE `ID` = '". sanitize($_GET['viewcomment']) ."';")) == 0)
    die("<br /><h2>There is no blog comment with that ID number.</h2>
<div class=\"red\">No comment with ID number <strong>{$_GET['viewcomment']}</strong> exists in the database.</div>");
  
  $commentinfo = mysql_fetch_assoc(mysql_query("SELECT * FROM `". get_table('comments') ."` WHERE `ID` = '". sanitize($_GET['viewcomment']) ."';"));
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
<li><strong>". authorlookup($commentinfo['Author']) ."</strong> said about <strong>". blogitize($entryinfo['Title']) ."</strong> {$timestamp}: <a href=\"delete.php?mode=comment&amp;ID={$commentinfo['ID']}\"><img src=\"../system/images/delete.gif\" border=\"0\">Delete this Comment</a></li>
". blogitize($commentinfo['Comment']) ."</ul>";
  die();
  }
?><html>
<head>
<link type="text/css" href="../system/style.css" rel="stylesheet">
<title><?php print get_table('SiteName'); ?>: Administration: Manage Blog Comments</title>
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
  search.open("GET", "search.php?keyword="+ document.getElementById('keyword').value +"&mode=comments", true);
  search.send(null);
}

else
  alert('Fatal error: Your browser does not support AJAX technology, which is required for this site to work.\n\nPlease consider upgrading your browser.');
}

function openComment(ID)
{
var entry = getHTTPObject();

if(entry)
{
  entry.onreadystatechange = function() {
  if(entry.readyState == 4 && entry.status == 200)
    document.getElementById('commentview').innerHTML=entry.responseText;
  };
  entry.open("GET", "managecomments.php?viewcomment="+ID, true);
  entry.send(null);
}

else
  alert('Fatal error: Your browser does not support AJAX technology, which is required for this site to work.\n\nPlease consider upgrading your browser.');
}

function recentComments()
{
var recent = getHTTPObject();

if(recent)
{
  recent.onreadystatechange = function() {
  if(recent.readyState == 4 && recent.status == 200)
    document.getElementById('browseresults').innerHTML=recent.responseText;
  };
  recent.open("GET", "search.php?mode=recentcomments&keyword=null", true);
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
<h1>Manage Blog Comments</h1>
<div class="yellow" style="text-align: center">Here you can delete blog comments.</div>
<br />
<h2>Browse Blog Comments</h2>
<div class="blue">
<input type="text" name="keyword" id="keyword" value="Search..." style="color: #666" onFocus=" if(this.value=='Search...') { this.value=''; this.style.color='#000'; }" onBlur=" if(this.value=='') { this.style.color='#666'; this.value='Search...'; }" onKeyDown=" search();"> <input type="button" value="X" onClick=" document.getElementById('keyword').value='Search...'; document.getElementById('keyword').style.color='#666'; search();"> <input type="button" value="Search" onClick=" search();"> You can also <a href="javascript: void(0);" onClick=" recentComments();">view the most recent comments</a> or <a href="javascript: void(0);" onClick=" openComment(prompt('Enter the ID number for the comments you want to view.'));">view a comment by ID number</a>.<br />
<span id="browseresults">Type a query to begin.</span>
</div>
<span id="commentview"></span>
<br />
<p id="footer">Manage Blog Comments - Last System Update: <?php print date("n/j/Y", (getlastmod() + get_table('dateoffset'))); ?></p>
</body>
</html>