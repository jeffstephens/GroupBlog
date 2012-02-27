<?php
session_start();
if(!isset($_SESSION['familysite']))
  Header("Location: ../login.php?go=help/addarticle.php");

if($_SESSION['familysite'] != 1)
  Header("Location: ../index.php?error=permissiondenied");

include "../system/parse.php";
dbconnect();

//Save the article
if($_GET['mode'] == "new")
  {
  if(strlen($_POST['title']) > 0 AND isset($_POST['category']) AND strlen($_POST['article']) > 0)
    {
    $title = sanitize($_POST['title']);
    $category = sanitize($_POST['category']);
    $article = sanitize($_POST['article']);
    
    $attempt = mysql_query("INSERT INTO `". get_table('help') ."` (`Title`, `Category`, `Article`, `Posted`, `Modified`, `Version`) VALUES ('{$title}', '{$category}', '{$article}', ". time() .", ". time() .", 1);");
    
    if($attempt)
      $success = "Your article, &quot;{$title}&quot;, was successfully added. <a href=\"index.php\">Help Index &raquo;</a>";
    
    else
      {
      //Report error
      send_notification(1, -2, "Error Report", "<h1>Error Report</h1>
  <div class=\"red\">
An error occurred while adding a help article.
<strong>Timestamp:</strong> ". date("g:i a, n/j/Y", (time() + get_table("dateoffset"))) ."<br />
<strong>mysql_error():</strong> ". mysql_error() ."<br />
<br />
<strong>Title:</strong> {$_POST['title']} (Processed as {$title})<br />
<strong>Category:</strong> {$_POST['category']} (Processed as {$category})<br />
<strong>Article:</strong><br />
<blockquote>{$_POST['article']}</blockquote><br />
<strong>Processed Article:</strong><br />
<blockquote>{$article}</blockquote><br />
<strong>Query:</strong><br />
<blockquote>INSERT INTO `". get_table('help') ."` (`Title`, `Category`, `Article`, `Posted`, `Modified`, `Version`) VALUES ('{$title}', '{$category}', '{$article}', ". time() .", ". time() .", 1);</blockquote>
  </div>");
      
      $error = "Your article, &quot;{$title}&quot;, couldn't be added due to a system error. There is more information in your <a href=\"../inbox.php\">notification inbox &raquo;</a>. <a href=\"index.php\">Help Index&raquo;</a>";
      }
    }

  else
    $error = "Please fill out all of the fields.";
  }

if($_GET['mode'] == "edit")
  {
  if(strlen($_POST['title']) > 0 AND isset($_POST['category']) AND strlen($_POST['article']) > 0 AND isset($_POST['ID']))
    {
    $title = sanitize($_POST['title']);
    $category = sanitize($_POST['category']);
    $article = sanitize($_POST['article']);
    
    $attempt = mysql_query("UPDATE `". get_table('help') ."` SET `Title` = '{$title}', `Category` = '{$category}', `Article` = '{$article}', `Modified` = ". time() .", `Version` = (`Version` + 1) WHERE `ID` = ". sanitize($_POST['ID']) ." LIMIT 1;");
    
    if($attempt)
      $success = "Your article, &quot;{$title}&quot;, was successfully updated. <a href=\"article.php?article=". sanitize($_POST['ID']) ."\">View Article &raquo;</a>";
    
    else
      {
      //Report error
      send_notification(1, -2, "Error Report", "<h1>Error Report</h1>
  <div class=\"red\">
An error occurred while updating a help article.
<strong>Timestamp:</strong> ". date("g:i a, n/j/Y", (time() + get_table("dateoffset"))) ."<br />
<strong>mysql_error():</strong> ". mysql_error() ."<br />
<br />
<strong>Title:</strong> {$_POST['title']} (Processed as {$title})<br />
<strong>Category:</strong> {$_POST['category']} (Processed as {$category})<br />
<strong>Article:</strong><br />
<blockquote>{$_POST['article']}</blockquote><br />
<strong>Processed Article:</strong><br />
<blockquote>{$article}</blockquote><br />
<strong>Query:</strong><br />
<blockquote>INSERT INTO `". get_table('help') ."` (`Title`, `Category`, `Article`, `Posted`, `Modified`, `Version`) VALUES ('{$title}', '{$category}', '{$article}', ". time() .", ". time() .", 1);</blockquote>
  </div>");
      
      $error = "Your article, &quot;{$title}&quot;, couldn't be updated due to a system error. There is more information in your <a href=\"../inbox.php\">notification inbox &raquo;</a>. <a href=\"index.php\">Help Index&raquo;</a>";
      }
    }

  else
    $error = "Please fill out all of the fields.";
  }

else
  $error = "No save mode was specified. <a href=\"index.php\">Help Index &raquo;</a>";
?><html>
<head>
<link type="text/css" href="../system/style.css" rel="stylesheet">
<link type="text/css" href="help.css" rel="stylesheet">
<title><?php print get_table('SiteName'); ?>: Add Help Article</title>
<?php include "../header.php"; ?>
</head>
<body>
<?php include "../userinfo.php"; ?>
<h1>Add Help Article</h1>
<?php
if(isset($success))
  print "<div class=\"green\">{$success}</div>";
elseif(isset($error))
  print "<div class=\"red\">{$error} <a href=\"addarticle.php\">Add Article &raquo;</a> or <a href=\"index.php\">Help Index &raquo;</a></div>";
?>
</body>
</html>