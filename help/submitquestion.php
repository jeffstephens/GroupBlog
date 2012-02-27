<?php
session_start();
if(!isset($_SESSION['familysite']))
  Header("Location: ../login.php?go=help/");

include "../system/parse.php";
dbconnect();

send_notification(1, -1, "Help Question", "<strong>". authornamelookup($_SESSION['familysite']) ." (#{$_SESSION['familysite']}) asks...</strong><br />
<br />
<blockquote><strong>". stripslashes(sanitize($_POST['subject'])) ."</strong><br />
". stripslashes(sanitize($_POST['body'])) ."</blockquote>");

send_notification($_SESSION['familysite'], -1, "Question Confirmation", "This message is just confirming that your question has been submitted and will be answered as soon as possible.<br />
<br />
<strong>". stripslashes(sanitize($_POST['subject'])) ."</strong><br />
<blockquote>". stripslashes(sanitize($_POST['body'])) ."</blockquote>");
?><html>
<head>
<link type="text/css" href="../system/style.css" rel="stylesheet">
<link type="text/css" href="help.css" rel="stylesheet">
<title><?php print get_table('SiteName'); ?>: Submit a Question</title>
<?php include "../header.php"; ?>
</head>
<body>
<?php include "../userinfo.php"; ?>
<h1>Submit a Question</h1>
<div class="green">
Your question has been successfully submitted. You will receive a message in your <a href="../inbox.php">Notification Inbox &raquo;</a> when your question is answered.
</div>
</body>
</html>