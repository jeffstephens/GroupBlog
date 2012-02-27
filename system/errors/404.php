<?php
session_start();
if(!isset($_SESSION['familysite']))
  Header("Location: /family/login.php?go={$_SERVER['REQUEST_URI']}");

include "../parse.php";

//Report problem
html_mail("Jeff Stephens", "jefftheman45@gmail.com", "Error Report (404)", "<h1>Error Report</h1>
<div class=\"red\">A 404 Not Found error was encountered at ". date("g:i a \o\\n D, M j") .".<br />
<strong>Referrer:</strong> {$_SERVER['HTTP_REFERER']}<br />
<strong>Request:</strong> {$_SERVER['REQUEST_URI']}<br />
<strong>User Agent:</strong> {$_SERVER['HTTP_USER_AGENT']}<br />
<strong>IP Address:</strong> {$_SERVER['REMOTE_ADDR']} (". gethostbyaddr($_SERVER['REMOTE_ADDR']) .")<br />
<strong>User:</strong> ". emailauthorlookup($_SESSION['familysite']) ." (#{$_SESSION['familysite']})
</div>");
?><html>
<head>
<link type="text/css" href="/family/system/style.css" rel="stylesheet">
<title>Family Website: Error 404 (File Not Found)</title>
</head>
<body>
<?php include "/web/root/family/userinfo.php"; ?>
<h1>Error 404: File Not Found</h1>
<div class="red">The file you're looking for isn't on the server. If you typed a URL into your browser, you may have mistyped it. You also may have bookmarked a page that no longer exists. Otherwise, there's a broken link somewhere on the site, and that's not good. The problem has been reported and will be looked into as soon as possible.</div>
<br />
<h2>For now...</h2>
<div class="green"><a href="/family/index.php">View the Blog &raquo;</a></div>
</body>
</html>