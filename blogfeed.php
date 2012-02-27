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

function rssemaillookup($id)
{
if($id == 0)
  $author = "someone using the old family site";

else
  {
  dbconnect();
  
  $query = mysql_fetch_assoc(mysql_query("SELECT * FROM `". get_table('users') ."` WHERE `ID` = ". sanitize($id) .";"));
  $author = $query['Email'];
  }

return $author;
}

function rssauthorlookup($id)
{
if($id == 0)
  $author = "someone using the old family site";

else
  {
  dbconnect();
  
  $query = mysql_fetch_assoc(mysql_query("SELECT * FROM `". get_table('users') ."` WHERE `ID` = ". sanitize($id) .";"));
  $author = htmlentities($query['Name']);
  }

return $author;
}

$globalTD = date("r"); //This is the date in the correct format. It will provide the timezone which makes up for time differentials.

//Print actual feed.

print "<rss version=\"2.0\">
	<channel>
		<title>". get_table('SiteName') ." News Blog</title>
		<description>Recent blog entries entries from the ". get_table('SiteName') ."</description>
		<link>http://". get_table('publicurl') ."/</link>
		<lastBuildDate>{$globalTD}</lastBuildDate>
		<pubDate>{$globalTD}</pubDate>
		<generator>Family Website Feed Generator 2.1</generator>
		<image>
			<url>http://". get_table('publicurl') ."/favicon.gif</url>
			<title>". get_table('SiteName') ." News Blog</title>
			<link>http://". get_table('publicurl') ."</link>
		</image>
		";

//Print out the blog entries.
$query = @mysql_query("SELECT * FROM `". get_table('blog') ."` ORDER BY `Posted` DESC LIMIT 10;");

while($row = @mysql_fetch_assoc($query))
	{
	//Print a full item clause
	$commentcount = mysql_num_rows(mysql_query("SELECT * FROM `". get_table('comments') ."` WHERE `EntryID` = '{$row['ID']}';"));
	
	if($commentcount == 1)
		$comment_s = "";
	else
		$comment_s = "s";
	
	print "
		<item>
			<title>". rssblogitize($row['Title']) ."</title>
			<description>". rssblogitize($row['Entry']) ."

-". rssblogitize(authornamelookup($row['Author'])) ."</description>
			<link>http://". get_table('publicurl') ."/entry.php?entry={$row['ID']}</link>
			<author>". rssblogitize(authornamelookup($row['Author'])) . "</author>
			<comments>http://". get_table('publicurl') ."/entry.php?entry={$row['ID']}</comments>
			<guid>http://". get_table('publicurl') ."/entry.php?entry={$row['ID']}</guid>
			<pubDate>". date("r", $row['Posted']) ."</pubDate>
		</item>
		";
	}

//Close 'er up

print "		
	</channel>
</rss>";
?>