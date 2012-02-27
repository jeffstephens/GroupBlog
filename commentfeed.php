<?php
Header('Content-Type: application/xml'); //so that RSS readers don't get thrown off when they see .php
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


$globalTD = date("r", (time() - 25200)); //This is the date in the correct format. It will provide the timezone, so we don't have to
					   //subtract 8 hours like usual.

//Get blog entry information
$entryinfo = mysql_fetch_assoc(mysql_query("SELECT * FROM `". get_table('blog') ."` WHERE `ID` = ". sanitize($_GET['entryID']) .";"));

//Print actual feed.

print "<rss version=\"2.0\">
	<channel>
		<title>". get_table('SiteName') .": Comment Feed for &quot;". rssblogitize($entryinfo['Title']) ."&quot;</title>
		<description>Comments added to ". rssblogitize(authornamelookup($entryinfo['Author'])) ."'s blog entry, &quot;". rssblogitize($entryinfo['Title']) ."&quot;</description>
		<link>http://". get_table('publicurl') ."/entry.php?entry=". sanitize($_GET['entryID']) ."</link>
		<lastBuildDate>{$globalTD}</lastBuildDate>
		<pubDate>{$globalTD}</pubDate>
		<generator>Family Website Feed Generator 2.1</generator>
		<image>
			<url>http://". get_table('publicurl') ."/favicon.gif</url>
			<title>". get_table('SiteName') .": Comment Feed for &quot;". rssblogitize($entryinfo['Title']) ."&quot;</title>
			<link>http://". get_table('publicurl') ."/entry.php?entry=". sanitize($_GET['entryID']) ."</link>
		</image>
		";

//Print out the blog entries.
$query = @mysql_query("SELECT * FROM `". get_table('comments') ."` WHERE `EntryID` = ". sanitize($_GET['entryID']) ." ORDER BY `Posted` DESC LIMIT 10;");

while($row = @mysql_fetch_assoc($query))
	{
	//Print a full item clause	
	print "
		<item>
			<title>Comment by ". rssblogitize(authornamelookup($row['Author'])) ."</title>
			<description>". rssblogitize($row['Comment']) ."</description>
			<link>http://". get_table('publicurl') ."/entry.php?entry={$row['ID']}</link>
			<author>". rssblogitize(authornamelookup($row['Author'])) . "</author>
			<comments>http://". get_table('publicurl') ."/entry.php?entry={$row['ID']}</comments>
			<guid>http://". get_table('publicurl') ."/entry.php?entry={$row['ID']}</guid>
			<pubDate>". date("r", ($row['Posted'] - 25200)) ."</pubDate>
		</item>
		";
	}

//Close 'er up

print "		
	</channel>
</rss>";
?>