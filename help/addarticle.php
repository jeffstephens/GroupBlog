<?php
session_start();
if(!isset($_SESSION['familysite']))
  Header("Location: ../login.php?go=help/addarticle.php");

if($_SESSION['familysite'] != 1)
  Header("Location: ../index.php?error=permissiondenied");

include "../system/parse.php";
dbconnect();
?><html>
<head>
<link type="text/css" href="../system/style.css" rel="stylesheet">
<link type="text/css" href="help.css" rel="stylesheet">
<title><?php print get_table('SiteName'); ?>: Add Help Article</title>
<?php include "../header.php"; ?>
</head>
<body>
<?php include "../userinfo.php"; ?>
<br /><br />
<div style="float: left; width: 69%">
<h1>Add Help Article</h1>
<div class="blue">
<form action="savearticle.php?mode=new" method="post">
<label for="title">Article Title:</label> <input type="text" name="title" id="title"><br />
<label for="category">Category:</label> <select name="category" id="category">
<option value="blog">Blog/Comments Help</option>
<option value="notification">Notification Help</option>
<option value="account">Account Help</option>
</select><br />
<label for="article">Article Body:</label><br />
<textarea rows="10" cols="60" name="article" id="article"></textarea><br />
<input type="submit" value="Publish Article &raquo;">
</form>
</div>
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