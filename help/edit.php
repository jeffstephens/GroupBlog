<?php
session_start();
if(!isset($_SESSION['familysite']))
  Header("Location: ../login.php?go=help/addarticle.php");

if($_SESSION['familysite'] != 1)
  Header("Location: ../index.php?error=permissiondenied");

include "../system/parse.php";
dbconnect();

//Get article information
$articleinfo = mysql_fetch_assoc(mysql_query("SELECT * FROM `". get_table('help') ."` WHERE `ID` = '". sanitize($_GET['article']) ."';"));

$title = $articleinfo['Title'];
$category = $articleinfo['Category'];
$article = $articleinfo['Article'];
?><html>
<head>
<link type="text/css" href="../system/style.css" rel="stylesheet">
<link type="text/css" href="help.css" rel="stylesheet">
<title><?php print get_table('SiteName'); ?>: Edit Help Article</title>
<?php include "../header.php"; ?>
</head>
<body>
<?php include "../userinfo.php"; ?>
<br /><br />
<div style="float: left; width: 69%">
<h1>Edit Help Article</h1>

<?php
if(mysql_num_rows(mysql_query("SELECT * FROM `". get_table('help') ."` WHERE `ID` = '". sanitize($_GET['article']) ."';")) > 0)
  {
  print '<div class="blue">
<form action="savearticle.php?mode=edit" method="post">
<input type="hidden" name="ID" value="'. $_GET['article'] .'">
<label for="title">Article Title:</label> <input type="text" name="title" id="title" value="'. stripslashes($title) .'"><br />
<label for="category">Category:</label> <select name="category" id="category">
<option value="blog"';
  if($category == "blog") print " selected";

  print '>Blog/Comments Help</option>
<option value="notification"';

  if($category == "notification") print " selected";
  
  print '>Notification Help</option>
<option value="account"';
  
  if($category == "account") print " selected";
  
  print '>Account Help</option>
</select><br />
<label for="article">Article Body:</label><br />
<textarea rows="10" cols="60" name="article" id="article">'. stripslashes($article) .'</textarea><br />
<input type="submit" value="Update Article &raquo;">
</form>
</div>';
  }
else
  print '<div class="red">There is no help article with ID '. $_GET['article'] .'. <a href="index.php">Help Index &raquo;</a></div>';
?>
</div>
<div style="float: right; width: 29%; padding-top: 10px">
<h2>Upload a File</h2>
<div class="orange">
<form action="upload.php?uploadfile" method="post" style="margin: 0; padding: 0" enctype="multipart/form-data" target="uploadframe">
<input type="file" name="file" id="file"><br />
<input type="submit" value="Upload File &raquo;" name="submit">
</form>
</div>
<br />
<h2>Uploaded Files</h2>
<div class="blue">
<iframe src="upload.php" style="border: none; width: 100%; height: 100px" name="uploadframe"></iframe>
</div>
</div>
</body>
</html>