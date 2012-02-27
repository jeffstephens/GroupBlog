<?php
ob_start();
session_start();
include "../system/parse.php";

if(!isset($_SESSION['familysite']))
  Header("Location: ../login.php?go=admin/managepolls.php");

dbconnect();
?><html>
<head>
<link type="text/css" rel="stylesheet" href="../system/style.css">
<style type="text/css">
span {
color: #666 }
</style>
<title><?php print get_table('SiteName'); ?>: Administration: Manage Polls</title>
<?php include "../header.php"; ?>
</head>
<body>
<?php include "../userinfo.php"; ?>
<h1>Manage Polls</h1>
<?php
if(isset($_GET['delete']) AND !isset($_GET['sure']))
  {
  print '<div class="red" style="text-align: center">';
  
  $pollquery = mysql_query("SELECT * FROM `". get_table('pollq') ."` WHERE `ID` = ". sanitize($_GET['delete']) .";");
  
  if(mysql_num_rows($pollquery) == 1)
    {
    print '
<strong>Are you sure you want to delete this poll?</strong>
<form action="managepolls.php?delete='. $_GET['delete'] .'&sure" method="post">
<input type="submit" value="Delete Poll"> <input type="button" value="Cancel" onClick=" document.location=\'managepolls.php\';">
</form>';
    }
  
  else
    print '<strong>There is no poll with that ID number.</strong>';
  }

elseif(isset($_GET['delete']) AND isset($_GET['sure']))
  {
  $attempt = mysql_query("DELETE FROM `". get_table('pollq') ."` WHERE `ID` = ". sanitize($_GET['delete']) ." LIMIT 1;");
  $answerattempt = mysql_query("DELETE FROM `". get_table('polla') ."` WHERE `Question` = ". sanitize($_GET['delete']) .";");
  
  if($attempt AND $answerattempt)
    print '<div class="green" style="text-align: center">
This poll has been successfully deleted. <a href="managepolls.php">Poll Management &raquo;</a>';
  
  else
    {
    print '<div class="red">
Your poll could not be deleted due to an error. This error has been reported.';
    
    send_notification(1, -2, "Error Report", "An error occurred while trying to delete a poll.<br />
<strong>Poll Deletion:</strong> ". mysql_error() ."<br />
<blockquote>DELETE FROM `". get_table('pollq') ."` WHERE `ID` = ". sanitize($_GET['delete']) ." LIMIT 1;</blockquote>
<br />
<strong>Response Deletion:</strong> ". mysql_error() ."<br />
<blockquote>DELETE FROM `". get_table('polla') ."` WHERE `Question` = ". sanitize($_GET['delete']) .";</blockquote>");
    }
  }

else
  {     
  print '<div class="yellow" style="text-align: center">
';
  if($_SESSION['familysite'] == 1)
    print "View and delete polls added by users. <a href=\"addpoll.php\">Create New Poll &raquo;</a>";

  else
    print "Viewing polls from now until the end of time. <a href=\"addpoll.php\">Create New Poll &raquo;</a>";
  }
?>
</div>
<br />
<?php
if(isset($_GET['delete']) AND !isset($_GET['sure']) AND mysql_num_rows($pollquery) == 1)
  {
  $pollinfo = mysql_fetch_assoc($pollquery);
  
  print '<h2>'. stripslashes($pollinfo['Question']) .'</h2>
<div class="blue">
';
  
    $answers = explode("
", stripslashes($pollinfo['Answers']));

    print "<ul>\n";

    for($i = 0; $i < count($answers); $i++)
    print "<li>{$answers[$i]}</li>\n";

    print "</ul>\n";
  }

elseif(isset($_GET['sure']))
  ; //Don't display anything down here

else
    {
    print '<h2>Present and Future Polls</h2>
<div class="blue">
';

  $pollquery = mysql_query("SELECT * FROM `". get_table('pollq') ."` WHERE `Week` >= ". date("W", (time() + get_table('dateoffset'))) ." AND `Year` >= ". date("Y", (time() + get_table('dateoffset'))) ." ORDER BY `Week` ASC, `Year` ASC;");

  if(mysql_num_rows($pollquery) == 0)
    print "There are no current or future polls.";

  else
    {
    print "<ul>\n";
    
    while($row = mysql_fetch_assoc($pollquery))
      {
      $responses = explode("
", stripslashes($row['Answers']));
      $responselist = "";
      
      for($i = 0; $i < count($responses); $i++)
        {
        $responselist .= "{$responses[$i]}";
        
        if($i < (count($responses) - 1))
          $responselist .= ", ";
        }
      
      if($row['Week'] == date("W", (time() + get_table('dateoffset'))))
        $activeweek = ", currently active";
      
      elseif($row['Year'] == date("Y", (time() + get_table('dateoffset'))))
        {
        $activeweekcount = ($row['Week'] - date("W", (time() + get_table('dateoffset'))));
        
        if($activeweekcount > 1)
          $activeweek = ", active {$activeweekcount} weeks from now";
        else
          $activeweek = ", active {$activeweekcount} week from now";
        }
      else
        $activeweek = ", active in week {$row['Week']} of {$row['Year']}";
      
      if($_SESSION['familysite'] == 1)
        $controls = " <a href=\"managepolls.php?delete={$row['ID']}\"><img src=\"../system/images/delete.gif\"> Delete Poll</a>";
      
       elseif($row['Creator'] == $_SESSION['familysite'] AND "{$row['Week']}{$row['Year']}" != date("W", (time() + get_table('dateoffset'))) . date("Y", (time() + get_table('dateoffset'))))
        $controls = " <a href=\"managepolls.php?delete={$row['ID']}\"><img src=\"../system/images/delete.gif\"> Delete Poll</a>";
      
      print "<li><strong>". stripslashes($row['Question']) ."</strong><br />
  <span>Created by ". authorlookup($row['Creator']) ."{$activeweek}.{$controls}</span><br />
  <span>Responses:</span> {$responselist}</li><br />\n";
      
      unset($controls);
      }
    
    print "\n</ul>";
    }
  }
?>
</div>

<br />
<p id="footer"><?php print get_table('SiteName'); ?> Administration - Poll Management. Last Update: <?php print date("g:i a, n/j/y", (getlastmod() + get_table('dateoffset'))); ?></p>
</body>
</html>