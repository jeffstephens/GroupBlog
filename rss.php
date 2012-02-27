<?php
session_start();
if(!isset($_SESSION['familysite']))
  Header("Location: login.php?go=rss.php");

include "system/parse.php";
dbconnect();
?><html>
<head>
<link rel="alternate" type="application/rss+xml" title="<?php print get_table('SiteName'); ?> News Blog" href="blogfeed.php">
<link rel="alternate" type="application/rss+xml" title="<?php print get_table('SiteName'); ?> Recent Activity Feed" href="feed.php">
<?php if(isset($_GET['entry'])) print '<link rel="alternate" type="application/rss+xml" title="Comment Feed" href="commentfeed.php?entryID='. $_GET['entry'] .'">';
if(isset($_GET['keyword'])) print '<link rel="alternate" type="application/rss+xml" title="Search Results Feed: '. $_GET['keyword'] .'" href="searchfeed.php?keyword='. $_GET['keyword'] .'">'; ?>
<link type="text/css" rel="stylesheet" href="system/style.css">
<title><?php print get_table('SiteName'); ?>: RSS</title>
</head>

<body>
<?php include "userinfo.php"; ?>
<h1>RSS Feeds</h1>
<div class="yellow" style="text-align: center">With RSS feeds, you can read the blog on other sites like Google or Windows Live.</div>
<br />
<?php
if(isset($_GET['entry']))
  {
  $entryinfo = mysql_fetch_assoc(mysql_query("SELECT * FROM `". get_table('blog') ."` WHERE `ID` = ". sanitize($_GET['entry']) .";"));
  $entrytitle = $entryinfo['Title'];
  
  print '<h2>Comment Feed</h2>
<div class="blue">You can use this comment feed to watch all comments added to <strong>'. blogitize($entrytitle) .'</strong>.<br />
<br />
<a href="http://www.google.com/ig/adde?moduleurl=http://'. get_table('publicurl') .'/commentfeed.php?entryID='. $_GET['entry'] .'"><img src="http://buttons.googlesyndication.com/fusion/add.gif" style="width:104px; height:17px;border:0px;" alt="Add to Google" /></a>
<a href="http://www.live.com/?add=http://'. get_table('publicurl') .'/commentfeed.php?entryID='. $_GET['entry'] .'"><img src="system/images/addtolive.jpg" border="0"/></a>
Or, if you\'d like to add it to another RSS reader, here\'s the feed: <a href="commentfeed.php?entryID='. $_GET['entry'] .'"><img src="system/images/rss.gif"></a></div>
<br />';
  }

if(isset($_GET['keyword']))
  {
  print '<h2>Search Results Feed</h2>
<div class="blue">You can use this feed to monitor blog entries related to <strong>'. $_GET['keyword'] .'</strong>.<br />
<br />
<a href="http://www.google.com/ig/adde?moduleurl=http://'. get_table('publicurl') .'/searchfeed.php?keyword='. $_GET['keyword'] .'"><img src="http://buttons.googlesyndication.com/fusion/add.gif" style="width:104px; height:17px;border:0px;" alt="Add to Google" /></a>
<a href="http://www.live.com/?add=http://'. get_table('publicurl') .'/searchfeed.php?keyword='. $_GET['keyword'] .'"><img src="system/images/addtolive.jpg" border="0"/></a>
Or, if you\'d like to add it to another RSS reader, here\'s the feed: <a href="searchfeed.php?keyword='. $_GET['keyword'] .'"><img src="system/images/rss.gif"></a><br />
</div>
<br />';
  }

print '<h2>Entry Feed</h2>
<div class="blue">You can use the entry feed to see the latest blog posts.<br />
<br />
<a href="http://www.google.com/ig/adde?moduleurl=http://'. get_table('publicurl') .'/blogfeed.php"><img src="http://buttons.googlesyndication.com/fusion/add.gif" style="width:104px; height:17px;border:0px;" alt="Add to Google" /></a>
<a href="http://www.live.com/?add=http://'. get_table('publicurl') .'/blogfeed.php"><img src="system/images/addtolive.jpg" border="0"/></a>
Or, if you\'d like to add it to another RSS reader, here\'s the feed: <a href="blogfeed.php"><img src="system/images/rss.gif"></a></div>
<br />
<h2>Recent Activity Feed</h2>
<div class="blue">You can use the recent activity feed to see all the latest activity on the family site, both blog entries and comments.<br />
<br />
<a href="http://www.google.com/ig/adde?moduleurl=http://'. get_table('publicurl') .'/feed.php"><img src="http://buttons.googlesyndication.com/fusion/add.gif" style="width:104px; height:17px;border:0px;" alt="Add to Google" /></a>
<a href="http://www.live.com/?add=http://'. get_table('publicurl') .'/feed.php"><img src="system/images/addtolive.jpg" border="0"/></a>
Or, if you\'d like to add it to another RSS reader, here\'s the feed: <a href="feed.php"><img src="system/images/rss.gif"></a></div>';
?>
</body>
</html>