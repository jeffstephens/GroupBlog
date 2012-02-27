<?php
session_start();

if(!isset($_SESSION['familysite']))
  Header("Location: login.php?go=". $_SERVER['REQUEST_URI']);

include "system/parse.php";
dbconnect();
?><html>
<head>
<link type="text/css" href="system/style.css" rel="stylesheet">
<title><?php print get_table("SiteName"); ?> - Welcome Back!</title>
<?php include "header.php"; ?>
</head>

<body>
<?php include "userinfo.php"; ?>
<h1>Welcome Back!</h1>
<div class="green" style="text-align: center">It looks like you haven't been here in a while, <?php print $userinfo['Name']; ?>. Have a look at what's been going on!</div>
<br />
<table style="width: 100%">
<tr style="vertical-align: top">
  <td style="width: 33%">
  <h2>Your Notifications</h2>
  <?php
  $query = @mysql_query("SELECT * FROM `". get_table('notifications') ."` WHERE `InboxID` = {$_SESSION['familysite']} AND (". time() ." - `Sent`) <= ". sanitize($_GET['diff']) ." ORDER BY `Sent` DESC;");
  $msgcount = @mysql_num_rows($query);
  
  print "<strong>You received {$msgcount} notifications in your absence.</strong> <a href=\"inbox.php\">Inbox &raquo;</a>";
  ?>
  </td>
  
  <td style="width: 33%">
  <h2>New Blog Entries</h2>
  <?php
  $query = @mysql_query("SELECT * FROM `". get_table('blog') ."` WHERE (". time() ." - `Posted`) <= ". sanitize($_GET['diff']) ." ORDER BY `Posted` DESC;");
  $entrycount = mysql_num_rows($query);
  
  if($entrycount >= 3)
    {
    $loopcount = 0;
    while($row = mysql_fetch_assoc($query))
      {
      if($loopcount <= 2)
        {
        if($loopcount == 2)
          $summary .= "and <strong><a href=\"entry.php?entry={$row['ID']}\">". blogitize($row['Title']) ."</a></strong>";
        else
          $summary .= "<strong><a href=\"entry.php?entry={$row['ID']}\">". blogitize($row['Title']) ."</a></strong>, ";
        
        $loopcount++;
        }
      
      else
        break;
      }
    }
  
  elseif($entrycount >= 2)
    {
    $loopcount = 0;
    while($row = mysql_fetch_assoc($query))
      {
      if($loopcount <= 1)
        {
        if($loopcount == 1)
          $summary .= "and <strong><a href=\"entry.php?entry={$row['ID']}\">". blogitize($row['Title']) ."</a></strong>";
        else
          $summary .= "<strong><a href=\"entry.php?entry={$row['ID']}\">". blogitize($row['Title']) ."</a></strong>, ";
        
        $loopcount++;
        }
      
      else
        break;
      }
    }
  
  elseif($entrycount >= 1)
    {
    $loopcount = 0;
    while($row = mysql_fetch_assoc($query))
      {
      if($loopcount <= 0)
        {
        $summary = "<strong><a href=\"entry.php?entry={$row['ID']}\">". blogitize($row['Title']) ."</a></strong>";
        
        $loopcount++;
        }
      
      else
        break;
      }
    }
  
  else
    $summary = "nothing";
  
  print "People blogged about {$summary} while you were gone.";
  ?>
  </td>
  
  <td style="width: 33%">
  <h2>New Blog Comments</h2>
  <?php  
  $query = @mysql_query("SELECT DISTINCT `EntryID` FROM `". get_table('comments') ."` WHERE (". time() ." - `Posted`) <= ". sanitize($_GET['diff']) ." ORDER BY `Posted` DESC;");
  $commentcount = mysql_num_rows($query);
  
  if($commentcount >= 3)
    {
    $loopcount = 0;
    while($row = mysql_fetch_assoc($query))
      {
      if($loopcount <= 2)
        {
        $entryinfo = mysql_fetch_assoc(mysql_query("SELECT * FROM `". get_table('blog') ."` WHERE `ID` = {$row['EntryID']} LIMIT 1;"));
        
        if($loopcount == 2)
          $commentsummary .= "and <strong><a href=\"entry.php?entry={$row['EntryID']}\">". blogitize($entryinfo['Title']) ."</a></strong>";
        else
          $commentsummary .= "<strong><a href=\"entry.php?entry={$row['EntryID']}\">". blogitize($entryinfo['Title']) ."</a></strong>, ";
        
        $loopcount++;
        }
      
      else
        break;
      }
    }
  
  elseif($commentcount >= 2)
    {
    $loopcount = 0;
    while($row = mysql_fetch_assoc($query))
      {
      if($loopcount <= 1)
        {
        $entryinfo = mysql_fetch_assoc(mysql_query("SELECT * FROM `". get_table('blog') ."` WHERE `ID` = {$row['EntryID']} LIMIT 1;"));
        
        if($loopcount == 1)
          $commentsummary .= "and <strong><a href=\"entry.php?entry={$row['EntryID']}\">". blogitize($entryinfo['Title']) ."</a></strong>";
        else
          $commentsummary .= "<strong><a href=\"entry.php?entry={$row['EntryID']}\">". blogitize($entryinfo['Title']) ."</a></strong>, ";
        
        $loopcount++;
        }
      
      else
        break;
      }
    }
  
  elseif($commentcount >= 1)
    {
    $loopcount = 0;
    while($row = mysql_fetch_assoc($query))
      {
      if($loopcount <= 0)
        {
        $entryinfo = mysql_fetch_assoc(mysql_query("SELECT * FROM `". get_table('blog') ."` WHERE `ID` = {$row['EntryID']} LIMIT 1;"));
        
        $commentsummary = "<strong><a href=\"entry.php?entry={$row['EntryID']}\">". blogitize($entryinfo['Title']) ."</a></strong>";
        
        $loopcount++;
        }
      
      else
        break;
      }
    }
  
  else
    $commentsummary = "nothing";
  
  print "People discussed {$commentsummary} while you were out.";
  ?>
  </td>
</tr>
<tr>
  <td colspan="3">
  <br />
  <br />
  </td>
</tr>
<tr>
  <td style="width: 33%">
  </td>
  <td style="width: 33%">
  <h2>Poll of the Week</h2>
  <?php
$currentdate = date("mdy", (time() + get_table('dateoffset')));
$thisweekpollquery = mysql_query("SELECT * FROM `". get_table('pollq') ."` WHERE `Week` = '". date("W", (time() + get_table('dateoffset'))) ."' AND `Year` = '". date("Y", (time() + get_table('dateoffset'))) ."';");
$thisweekpollinfo = mysql_fetch_assoc($thisweekpollquery);

if(mysql_num_rows($thisweekpollquery) == 0)
  {
  print "There is no poll this week. <a href=\"admin/addpoll.php\">Create One &raquo;</a><br />";
  }

elseif(mysql_num_rows(mysql_query("SELECT * FROM `". get_table('polla') ."` WHERE `Question` = '{$thisweekpollinfo['ID']}' AND `Voter` = '{$_SESSION['familysite']}';")) == 0)
  {
  //Haven't voted yet.
  $pollinfo = mysql_fetch_assoc(mysql_query("SELECT * FROM `". get_table('pollq') ."` WHERE `Week` = '". date("W") ."' AND `Year` = '". date("Y") ."';"));
  
  print "<form action=\"vote.php\" method=\"post\" style=\"text-align: left\">
<input type=\"hidden\" name=\"pollID\" value=\"{$pollinfo['ID']}\">
<strong>". stripslashes($pollinfo['Question']) ."</strong><br />\n";
  
  $answers = explode("\n", $pollinfo['Answers']);
  
  for($i = 0; $i < count($answers); $i++)
    print "<input type=\"radio\" id=\"option". ($i + 1) ."\" name=\"pollchoice\" value=\"". ($i + 1) ."\"> <label for=\"option". ($i + 1) ."\">{$answers[$i]}</label><br />
";
  
  print "<input type=\"submit\" value=\"Vote &raquo;\"> <a href=\"admin/addpoll.php\">Create New Poll &raquo;</a>";
  
  print "
</form>";
  }

else
  print "<strong>Somehow, you already voted in this week's poll...</strong>";
  ?>
  </td>
  <td style="width: 33%">
  <p style="font-size: 200%"><a href="index.php"><?php print get_table('SiteName'); ?> Home &raquo;</a></p>
  </td>
</tr>
</table>
</body>
</html>