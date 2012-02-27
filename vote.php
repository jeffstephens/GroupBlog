<?php
session_start();
if(!isset($_SESSION['familysite']))
  Header("Location: login.php?go=vote.php");

include "system/parse.php";
dbconnect();

if(isset($_POST['pollID']))
  {
  //Record vote for this user
  $vote = $_POST['pollchoice'];
  
  if(mysql_num_rows(mysql_query("SELECT * FROM `". get_table('polla') ."` WHERE `Voter` = {$_SESSION['familysite']} AND `Question` = {$_POST['pollID']};")) == 0)
    {
    $attempt = mysql_query("INSERT INTO `". get_table('polla') ."` (`Question`, `Voter`, `Choice`, `Timestamp`) VALUES ('{$_POST['pollID']}', '{$_SESSION['familysite']}', '{$vote}', '". time() ."');");
    
    if($attempt)
      $success = "Your vote has been recorded.";
    
    else
      {
      $error = "Your vote couldn't be recorded due to a system error. This error has been reported.";
      send_notification(1, -2, "Error Report", "<strong>An error occurred while attempting to record a vote.</strong><br />
<strong>Timestamp:</strong> ". date("g:i a, n/j/y", (time() + get_table('dateoffset'))) ."<br />
<strong>mysql_error:</strong> ". mysql_error() .";
<strong>mysql_query:</strong><br />
<blockquote>INSERT INTO `". get_table('polla') ."` (`Question`, `Voter`, `Choice`, `Timestamp`) VALUES ('{$_POST['pollID']}', '{$_SESSION['familysite']}', '{$vote}', '". time() ."');</blockquote><br />
<strong>pollID:</strong> {$_POST['pollID']}<br />
<strong>Vote:</strong> {$vote}");
      }
    }
  
  else
    $error = "You can only vote in a given poll once.";
  }
?><html>
<head>
<link type="text/css" href="system/style.css" rel="stylesheet">
<style type="text/css">
span {
color: #666 }
</style>
<title><?php print get_table('SiteName'); ?>: Poll of the Week</title>
<?php include "header.php"; ?>
</head>

<body>
<?php
include "userinfo.php";

print "\n<h1>";

if(isset($_GET['archive']))
  print "Poll Archive";

else
  print "Poll of the Week";

print "</h1>\n";

if(isset($_GET['archive']))
  {
  print '<div class="yellow" style="text-align: center">View the results of every poll before this week. <a href="vote.php">View Current Poll &raquo;</a></div>';
  
  $thisyear = mysql_query("SELECT * FROM `". get_table('pollq') ."` WHERE `Week` <= ". date("W", (time() + get_table('dateoffset'))) ." AND `Year` = ". date("Y", (time() + get_table('dateoffset'))) ." ORDER BY `Week` DESC;");
  
  print "<br />\n<h2>This Year</h2>\n<div class=\"blue\">\n<ul>\n";
  
  while($row = mysql_fetch_assoc($thisyear))
    {
    print "<li><a href=\"vote.php?poll={$row['ID']}\">". stripslashes($row['Question']) ."</a>";
    
    if($row['Week'] == date("W", (time() + get_table('dateoffset'))) AND $row['Year'] == date("Y", (time() + get_table('dateoffset'))))
      print " <strong>(Current Poll)</strong>";
    
    print "</li>\n";
    }
  
  print "</ul>\n</div>\n<br /><h2>Older</h2>\n<div class=\"blue\">\n";
  
  $older = mysql_query("SELECT * FROM `". get_table('pollq') ."` WHERE `Year` < ". date("Y", (time() + get_table('dateoffset'))) ." ORDER BY `Week` DESC;");
  
  if(mysql_num_rows($older) > 0)
    {
    print "<ul>\n";
    
    while($row = mysql_fetch_assoc($older))
      {
      print "<li><a href=\"vote.php?poll={$row['ID']}\">". stripslashes($row['Question']) ."</a></li>\n";
      }
    
    print "</ul>\n";
    }
  
  else
    print "There are no polls older than a year. Check back in ". (date("Y", (time() + get_table('dateoffset'))) + 1) ."!";
  
  print "</div>";
  }

else
  {
  if(isset($success))
    print '<div class="green" style="text-align: center">'. $success .' <a href="index.php">Home &raquo;</a></div>';

  elseif(isset($error))
    print '<div class="red" style="text-align: center">'. $error .' <a href="index.php">Home &raquo;</a></div>';

  else
    print '<div class="yellow" style="text-align: center">Vote in the poll and view detailed results for the Poll of the Week.</div>';

  print "\n<br />\n";

  if(strlen($_GET['poll']) > 0)
    $poll = sanitize($_GET['poll']);

  else
    {
    $pollID = mysql_query("SELECT `ID` FROM `". get_table('pollq') ."` WHERE `Week` = '". date("W", (time() + get_table('dateoffset'))) ."' AND `Year` = '". date("Y", (time() + get_table('dateoffset'))) ."' LIMIT 1;");
    
    if(mysql_num_rows($pollID) == 1)
      {
      $poll = mysql_fetch_assoc($pollID);
      $poll = $poll['ID'];
      }
    
    else
      $poll = -1;
    }
     
  if($poll > 0)
    {
    $pollquery = mysql_query("SELECT * FROM `". get_table('pollq') ."` WHERE `ID` = '{$poll}' LIMIT 1;");

    if(mysql_num_rows($pollquery) > 0)
      {
      $pollinfo = mysql_fetch_assoc($pollquery);
      $totalvotes = mysql_num_rows(mysql_query("SELECT * FROM `". get_table('polla') ."` WHERE `Question` = {$poll};"));
      $usertotal = mysql_num_rows(mysql_query("SELECT * FROM `". get_table('users') ."`;"));
      $answers = explode("\n", stripslashes($pollinfo['Answers']));
      
      if($totalvotes == 0)
        {
        $totalvotes++;
        $usersvoting = 0;
        }
      else
        $usersvoting = round(($totalvotes / $usertotal) * 100);
      
      print "<h2>". stripslashes($pollinfo['Question']) ."</h2>\n<div class=\"blue\">\n<table>\n<tr><td style=\"text-align: right\">\n";
      
      for($i = 0; $i < count($answers); $i++)
        print "{$answers[$i]}<br />\n";
      
      print "</td>\n<td style=\"text-align: left\">";
      
      for($j = 0; $j < count($answers); $j++)
        {
        $votes = mysql_num_rows(mysql_query("SELECT * FROM `". get_table('polla') ."` WHERE `Question` = {$poll} AND `Choice` = ". ($j + 1) .";"));
        print "<img src=\"system/images/pollbar.jpg\" width=\"". (($votes * 20) + 1) ."\" height=\"16\"> {$votes} (". round(100 * ($votes / $totalvotes), 1) ."%)<br />\n";
        }
      
      print "</td></tr>\n</table>\n</div>\n<br /><h2>More Poll Statistics</h2>\n<div class=\"green\">";
      
      //Calculate some stats about this poll    
      print "{$usersvoting}% <span>of all users have voted in this poll, which was created by </span>". authorlookup($pollinfo['Creator']) .".\n</div>";
      
      //List 5 most recent polls, link to all
      print "<br />\n<h2>Recent Polls</h2>\n<div class=\"yellow\">";
      
      $recentpolls = mysql_query("SELECT * FROM `". get_table('pollq') ."` WHERE `Week` <= ". date("W", (time() + get_table('dateoffset'))) ." AND `Year` <= ". date("Y", (time() + get_table('dateoffset'))) ." ORDER BY `Year` DESC, `Week` DESC LIMIT 5;");
      
      print "<ul>\n";
      
      while($row = mysql_fetch_assoc($recentpolls))
        {
        print "<li><a href=\"vote.php?poll={$row['ID']}\">". stripslashes($row['Question']) ."</a>";
        
        if($row['Week'] == date("W", (time() + get_table('dateoffset'))) AND $row['Year'] == date("Y", (time() + get_table('dateoffset'))))
          print " <strong>(Current Poll)</strong>";
        
        print "</li>\n";
        }
      
      print "<li><a href=\"vote.php?archive\">View All Old Polls &raquo;</a></li>\n</ul>\n</div>\n";
      }
      
    else
      print "<h2>Error: Invalid Poll ID</h2>
    <div class=\"red\">There is no poll with that ID. <a href=\"vote.php\">View this week &raquo;</a></div>";
    }

  else
    "<h2>There is no poll this week.</h2>
  <div class=\"yellow\">Hopefully, someone will add one soon.</div>";
}
?>
<br />
<p id="footer">Poll of the Week - Last Update: <?php print date("n/j/y", (getlastmod() + get_table('dateoffset'))); ?></p>
</body>
</html>