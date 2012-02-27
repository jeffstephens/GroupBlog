<?php
session_start();
if(!isset($_SESSION['familysite']))
  Header("Location: ../login.php?go=help/");

include "../system/parse.php";
dbconnect();
?><html>
<head>
<link type="text/css" href="../system/style.css" rel="stylesheet">
<link type="text/css" href="help.css" rel="stylesheet">
<style type="text/css">
label, input {
font-size: 100% }

span {
color: #666 }

ul {
list-style: url('document.gif') }

li a:visited {
color: #000 }
</style>
<title><?php print get_table('SiteName'); ?>: Help</title>
<?php include "../header.php"; ?>
</head>
<body>
<?php include "../userinfo.php"; ?>
<h1>Help</h1>
<div class="yellow"><form action="search.php" method="get" style="margin: 0; padding: 0">
Choose a topic below, or <label for="searchbox">search:</label> <input type="text" size="40" id="searchbox" name="keyword"><input type="submit" value="Search &raquo;">
</form></div>
<br />
<h2>Blog/Comments Help</h2>
<div class="green">
<?php
$query = mysql_query("SELECT * FROM `". get_table('help') ."` WHERE `Category` = 'blog' ORDER BY `Title` ASC;");

if(mysql_num_rows($query) > 0)
  print "<ul>\n";

while($row = mysql_fetch_assoc($query))
  {
  $previewarray = explode(" ", strip_tags(blogitize($row['Article'])));
  
  for($i = 0; $i < 25; $i++)
    $preview .= $previewarray[$i] . " ";
  
  $preview = trim($preview);
  
  print "<li><a href=\"article.php?article={$row['ID']}\">". blogitize($row['Title']) ."</a> <span style=\"font-size: 80%\">Version {$row['Version']} | Last Updated ". date("n/j/Y", ($row['Modified'] + get_table('dateoffset'))) ."</span><br />
<span>{$preview}...</span></li>\n";
  
  unset($preview);
  }

if(mysql_num_rows($query) > 0)
  print "</ul>\n";

else
  print "There are currently no articles in this category.";
?>
</div>
<br />
<h2>Notification Help</h2>
<div class="green">
<?php
$query = mysql_query("SELECT * FROM `". get_table('help') ."` WHERE `Category` = 'notification' ORDER BY `Title` ASC;");

if(mysql_num_rows($query) > 0)
  print "<ul>\n";

while($row = mysql_fetch_assoc($query))
  {
  $previewarray = explode(" ", strip_tags(blogitize($row['Article'])));
  
  for($i = 0; $i < 25; $i++)
    $preview .= $previewarray[$i] . " ";
  
  $preview = trim($preview);
  
  print "<li><a href=\"article.php?article={$row['ID']}\">". blogitize($row['Title']) ."</a> <span style=\"font-size: 80%\">Version {$row['Version']} | Last Updated ". date("n/j/Y", ($row['Modified'] + get_table('dateoffset'))) ."</span><br />
<span>{$preview}...</span></li>\n";

  unset($preview);
  }

if(mysql_num_rows($query) > 0)
  print "</ul>\n";

else
  print "There are currently no articles in this category.";
?>
</div>
<br />
<h2>Account Help</h2>
<div class="green">
<?php
$query = mysql_query("SELECT * FROM `". get_table('help') ."` WHERE `Category` = 'account' ORDER BY `Title` ASC;");

if(mysql_num_rows($query) > 0)
  print "<ul>\n";

while($row = mysql_fetch_assoc($query))
  {
  $previewarray = explode(" ", strip_tags(blogitize($row['Article'])));
  
  for($i = 0; $i < 25; $i++)
    $preview .= $previewarray[$i] . " ";
  
  $preview = trim($preview);
  
  print "<li><a href=\"article.php?article={$row['ID']}\">". blogitize($row['Title']) ."</a> <span style=\"font-size: 80%\">Version {$row['Version']} | Last Updated ". date("n/j/Y", ($row['Modified'] + get_table('dateoffset'))) ."</span><br />
<span>{$preview}...</span></li>\n";
  
  unset($preview);
  }

if(mysql_num_rows($query) > 0)
  print "</ul>\n";

else
  print "There are currently no articles in this category.";
?>
</div>
<br />
<h2>Need more help?</h2>
<div class="blue">
<form action="search.php" method="get" style="margin: 0; padding: 0">
First, try the <label for="searchbox2">search:</label> <input type="text" size="40" id="searchbox2" name="keyword"><input type="submit" value="Search &raquo;">
</form>
<br />
<form action="submitquestion.php" method="post">
If you still have an unanswered question, ask it here and then submit your question below.<br />
<label for="subject">Subject:</label> <input type="text" name="subject" id="subject"><br />
<label for="body">Your Question:</label><br />
<textarea name="body" id="body" rows="10" cols="50"></textarea><br />
<input type="submit" value="Submit Question &raquo;">
</form>
</div>
<p id="footer"><?php print get_table('SiteName'); ?> Help Repository - Last system update: <?php print date("g:i a, n/j/y", (getlastmod() + get_table('dateoffset'))); ?></p>
</body>
</html>