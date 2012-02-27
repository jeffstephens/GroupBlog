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
<title><?php print get_table('SiteName'); ?>: Search Help</title>
<?php include "../header.php"; ?>
</head>
<body>
<?php include "../userinfo.php"; ?>
<h1>Search Help</h1>
<div class="yellow">
<form action="search.php" method="get"><label for="keyword">Search Again:</label> <input type="text" name="keyword" id="keyword" value="<?php print $_GET['keyword']; ?>"> <input type="submit" value="Search&raquo;">
</div>
<br />
<h2>Results in Blog/Comments Help</h2>
<div class="green">
<?php
$query = mysql_query("SELECT * FROM `". get_table('help') ."` WHERE `Title` LIKE '%". sanitize($_GET['keyword']) ."%' AND `Category` = 'blog' OR `Article` LIKE '%". sanitize($_GET['keyword']) ."%' AND `Category` = 'blog' ORDER BY `Title` ASC;");

if(mysql_num_rows($query) == 0)
  print "<strong>No results.</strong>";
else
  {
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
  
  print "</ul>\n";
  }
?>
</div>
<br />

<h2>Results in Notification Help</h2>
<div class="green">
<?php
$query = mysql_query("SELECT * FROM `". get_table('help') ."` WHERE `Title` LIKE '%". sanitize($_GET['keyword']) ."%' AND `Category` = 'notification' OR `Article` LIKE '%". sanitize($_GET['keyword']) ."%' AND `Category` = 'notification' ORDER BY `Title` ASC;");

if(mysql_num_rows($query) == 0)
  print "<strong>No results.</strong>";
else
  {
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
  
  print "</ul>\n";
  }
?>
</div>
<br />

<h2>Results in Account Help</h2>
<div class="green">
<?php
$query = mysql_query("SELECT * FROM `". get_table('help') ."` WHERE `Title` LIKE '%". sanitize($_GET['keyword']) ."%' AND `Category` = 'account' OR `Article` LIKE '%". sanitize($_GET['keyword']) ."%' AND `Category` = 'account' ORDER BY `Title` ASC;");

if(mysql_num_rows($query) == 0)
  print "<strong>No results.</strong>";
else
  {
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
  
  print "</ul>\n";
  }
?>
</div>
<br />
<h2>Still Have a Question?</h2>
<div class="blue">
Go to the <a href="index.php">Help Index &raquo;</a> and ask your question at the bottom of the page.
</div>
<br />
<p id="footer"><?php print get_table('SiteName'); ?> Help Search - Last System Update: <?php print date("n/j/Y", (getlastmod() + get_table('dateoffset'))); ?></p>
</body>
</html>