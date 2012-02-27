<?php
session_start();
if(!isset($_SESSION['familysite']))
  Header("Location: ../login.php?go=help/article.php?article={$_GET['article']}");

include "../system/parse.php";
dbconnect();

//Load article
$articlequery = mysql_query("SELECT * FROM `". get_table('help') ."` WHERE `ID` = ". sanitize($_GET['article']) .";");

if(mysql_num_rows($articlequery) != 1)
  {
  $title = "Error";
  $article = "There is no article here. <a href=\"index.php\">Help Index &raquo;</a>";
  }

else
  {
  $articleinfo = mysql_fetch_assoc($articlequery);
  $title = blogitize($articleinfo['Title']);
  $article = blogitize($articleinfo['Article']);
  }
?><html>
<head>
<link type="text/css" href="../system/style.css" rel="stylesheet">
<link type="text/css" href="help.css" rel="stylesheet">
<style type="text/css">
label, input {
font-size: 100% }

span {
color: #666 }
</style>
<title><?php print get_table('SiteName'); ?> Help: <?php print $title; ?></title>
<?php include "../header.php"; ?>
</head>
<body>
<?php include "../userinfo.php"; ?>
<h1>Help Article: <?php print $title; ?></h1>
<div class="green"><?php print $article; ?></div>
<br />
<p id="footer">Created on <?php print date("n/j/Y", ($articleinfo['Posted'] + get_table('dateoffset'))); ?>. Last modified <?php print date("n/j/Y", ($articleinfo['Modified'] + get_table('dateoffset'))); ?>. This is version <?php print $articleinfo['Version']; ?>.<?php if($_SESSION['familysite'] == 1) print " <a href=\"edit.php?article={$_GET['article']}\">Edit this Article</a>"; ?></p>
</body>
</html>