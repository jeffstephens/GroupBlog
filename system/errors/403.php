<?php
session_start();
if(!isset($_SESSION['familysite']))
  Header("Location: /family/login.php?go={$_SERVER['REQUEST_URI']}");

include "../parse.php";

//Report problem
html_mail("Jeff Stephens", "jefftheman45@gmail.com", "Error Report (403)", "<h1>Error Report</h1>
<div class=\"red\">A 403 Forbidden error was encountered at ". date("g:i a \o\\n D, M j") .".<br />
<strong>Referrer:</strong> {$_SERVER['HTTP_REFERER']}<br />
<strong>Request:</strong> {$_SERVER['REQUEST_URI']}<br />
<strong>User Agent:</strong> {$_SERVER['HTTP_USER_AGENT']}<br />
<strong>IP Address:</strong> {$_SERVER['REMOTE_ADDR']} (". gethostbyaddr($_SERVER['REMOTE_ADDR']) .")<br />
<strong>User:</strong> ". emailauthorlookup($_SESSION['familysite']) ." (#{$_SESSION['familysite']})
</div>");
?><html>
<head>
<link type="text/css" href="/family/system/style.css" rel="stylesheet">
<title>Family Website: Error 403 (Forbidden)</title>
</head>
<body>
<?php include "/web/root/family/userinfo.php"; ?>
<h1>Error 403: Forbidden</h1>
<div class="red">You are not permitted to view this file or directory. More than likely you're nosing around in things you shouldn't be! (Just kidding...) On the off-chance that you were forbidden by mistake, the error has been reported and will be looked into.</div>
<br />
<h2>For now...</h2>
<div class="green"><a href="/family/index.php">View the Blog &raquo;</a></div>
</body>
</html>