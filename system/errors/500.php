<?php
session_start();
if(!isset($_SESSION['familysite']))
  Header("Location: /family/login.php?go={$_SERVER['REQUEST_URI']}");

include "../parse.php";

//Report problem
html_mail("Jeff Stephens", "jefftheman45@gmail.com", "Error Report (500)", "<h1>Error Report</h1>
<div class=\"red\">A 500 Internal Server Error was encountered at ". date("g:i a \o\\n D, M j") .".<br />
<strong>Referrer:</strong> {$_SERVER['HTTP_REFERER']}<br />
<strong>Request:</strong> {$_SERVER['REQUEST_URI']}<br />
<strong>User Agent:</strong> {$_SERVER['HTTP_USER_AGENT']}<br />
<strong>IP Address:</strong> {$_SERVER['REMOTE_ADDR']} (". gethostbyaddr($_SERVER['REMOTE_ADDR']) .")<br />
<strong>User:</strong> ". emailauthorlookup($_SESSION['familysite']) ." (#{$_SESSION['familysite']})
</div>");
?><html>
<head>
<link type="text/css" href="/family/system/style.css" rel="stylesheet">
<title>Family Website: Error 500 (Internal Server Error)</title>
</head>
<body>
<?php include "/web/root/family/userinfo.php"; ?>
<h1>Error 500: Internal Server Error</h1>
<div class="red">The fact that you're seeing this page is a very, very bad sign. A 500 error means there's something seriously wrong... the error has been reported and will be looked into promptly. We're sorry for the inconvenience and will try to fix the problem as soon as possible.</div>
<br />
<h2>For now...</h2>
<div class="green"><a href="/family/index.php">View the Blog &raquo;</a></div>
</body>
</html>