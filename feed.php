<?php
header('Content-Type: application/xml'); //so that RSS readers don't get thrown off when they see .php
										 //and so that it's an actual xml file even though it's .php

//This is the RSS Feed for the Family Blog.
include "system/parse.php";
dbconnect();

//Functions to make things work more better.

function rssblogitize($input)
{
//To add BBCode, line breaks, stripslashes, etc.
$input = stripslashes($input);
$input = str_replace("(list:bullets)", "", $input);
$input = str_replace("(list:numbers)", "", $input);
$input = str_replace("(item)", "
-", $input);
$input = str_replace("(enditem)", "", $input);
$input = str_replace("(endlist:bullets)", "", $input);
$input = str_replace("(endlist:numbers)", "", $input);
$input = str_replace("(bold)", "", $input);
$input = str_replace("(endbold)", "", $input);
$input = str_replace("(italic)", "", $input);
$input = str_replace("(enditalic)", "", $input);
$input = str_replace("(underline)", "", $input);
$input = str_replace("(endunderline)", "", $input);
$input = str_replace("(b)", "", $input);
$input = str_replace("(/b)", "", $input);
$input = str_replace("(i)", "", $input);
$input = str_replace("(/i)", "", $input);
$input = str_replace("ñ", "n", $input);
$input = str_replace("&ntilde;", "n", $input);
$input = preg_replace('/\(link:(.+?)\)(.+?)\(endlink\)/', '$2', $input);
$input = htmlentities($input);

return $input;
}

$globalTD = date("r"); //This is the date in the correct format. It will provide the timezone which makes up for time differentials.

//Print actual feed.

print "<?xml version=\"1.0\" encoding=\"ISO-8859-1\" ?>
<rss version=\"2.0\">
	<channel>
		<title>". get_table('SiteName') ." Recent Activity Feed</title>
		<description>A summary of recent blog entries and comments on the ". get_table('SiteName') .".</description>
		<link>http://". get_table('publicurl') ."/</link>
		<lastBuildDate>{$globalTD}</lastBuildDate>
		<pubDate>{$globalTD}</pubDate>
		<generator>Family Website Feed Generator 2.1</generator>
		<image>
			<url>http://". get_table('publicurl') ."/favicon.gif</url>
			<title>". get_table('SiteName') ." Recent Activity Feed</title>
			<link>http://". get_table('publicurl') ."</link>
		</image>
		";

//Generate a 'news feed' of blog entries and comments
$recententries = mysql_query("SELECT * FROM `". get_table('blog') ."` ORDER BY `Posted` DESC LIMIT 10;");
$recentcomments = mysql_query("SELECT * FROM `". get_table('comments') ."` ORDER BY `Posted` DESC LIMIT 10;");

$feed = Array();
$loopcount = 0;

while($row1 = mysql_fetch_assoc($recententries))
  {
  $feed[$loopcount] = $row1['Posted'] . "|Entry|". $row1['Author'] ."|". $row1['ID'] ."|". $row1['Title'] ."|". $row1['Entry'];
  $loopcount++;
  }

while($row2 = mysql_fetch_assoc($recentcomments))
  {
  $entryinfo = mysql_fetch_assoc(mysql_query("SELECT * FROM `". get_table('blog') ."` WHERE `ID` = {$row2['EntryID']};"));
  
  $feed[$loopcount] = $row2['Posted'] . "|Comment|". $row2['Author'] ."|". $row2['EntryID'] ."|". $entryinfo['Title'] ."|". $row2['Comment'];
  $loopcount++;
  }

sort($feed);
$feed = array_reverse($feed) or send_notification(1, -2, "Error Report", "An error occurred while trying to reverse the news feed array.");

for($i = 0; $i < 20; $i++)
  {
  $item = explode("|", $feed[$i]);
  $type = $item[1];
  
  if($type == "Entry")
    print "  <item>
    <title>". rssblogitize($item[4]) ."</title>
    <description>". rssblogitize($item[5]) ."

-". rssblogitize(authornamelookup($item[2])) ."</description>
    <link>http://". get_table('publicurl') ."/entry.php?entry={$item[3]}</link>
    <guid>http://". get_table('publicurl') ."/entry.php?entry={$item[3]}</guid>
    <author>". rssblogitize(authornamelookup($item[2])) ."</author>
    <pubDate>". date("r", $item[0]) ."</pubDate>
  </item>
";
  
  elseif($type == "Comment")
    print "  <item>
    <title>Comment on &quot;". rssblogitize($item[4]) ."&quot;</title>
    <description>". rssblogitize($item[5]) ."

-". rssblogitize(authornamelookup($item[2])) ."</description>
    <link>http://". get_table('publicurl') ."/entry.php?entry={$item[3]}</link>
    <guid>http://". get_table('publicurl') ."/entry.php?entry={$item[3]}</guid>
    <author>". rssblogitize(authornamelookup($item[2])) ."</author>
    <pubDate>". date("r", $item[0]) ."</pubDate>
  </item>
";
  }

//Close 'er up

print "		
	</channel>
</rss>";
?>