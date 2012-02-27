<?php
ob_start();
session_start();
include "../system/parse.php";

if(!isset($_SESSION['familysite']))
  Header("Location: ../login.php?go=admin/addpoll.php");

if(!in_array($_SESSION['familysite'], get_table('mods')))
  Header("Location: ../index.php?error=permissiondenied");

dbconnect();

if(isset($_GET['preview']))
  {
  //Return a preview of the poll for AJAX
  $answers = explode("\n", trim($_POST['answers']));
  
  if(strlen($_POST['question']) == 0)
    print "<p class=\"error\">Your poll must have a question.</p>\n";
  
  if(count($answers) <= 1)
    print "<p class=\"error\">You must have at least two choices for a poll.</p>\n";
  
  if(count($answers) > 20)
    print "<p class=\"error\">You can have no more than 20 choices for a poll.</p>\n";
  
  print "<strong>". stripslashes($_POST['question']) ."</strong><br />
";
  
  for($i = 0; $i < count($answers); $i++)
    print "<input type=\"radio\" id=\"l{$i}\" name=\"pollpreview\" value=\"{$i}\"> <label for=\"l{$i}\">". stripslashes($answers[$i]) ."</label><br />
";
  
  print "<input type=\"button\" value=\"Vote &raquo;\">";
  
  die();
  }

if(isset($_GET['save']))
  {
  if(mysql_num_rows(mysql_query("SELECT * FROM `". get_table('pollq') ."` WHERE `Week` = '". date("W", (time() + get_table('dateoffset'))) ."' AND `Year` = '". date("Y", (time() + get_table('dateoffset'))) ."';")) == 0)
    {
    //No poll for this week; make one
    $question = sanitize($_POST['question']);
    $answers = sanitize($_POST['answers']);
    $displayanswersarray = explode("\\r\\n", $answers);
    
    if(strlen($question) == 0)
      $error = "Your poll must have a question.";
    
    elseif(count($displayanswersarray) <= 1 OR count($displayanswersarray) > 20)
      $error = "You must have between 2 and 20 options.";
    
    else
      {
      $displayanswers = "";
      
      for($i = 0; $i < count($displayanswersarray); $i++)
      $displayanswers .= "<input type=\"radio\" id=\"l{$i}\" name=\"pollpreview\" value=\"{$i}\"> <label for=\"l{$i}\">{$displayanswersarray[$i]}</label><br />
  ";
      
      $entryweek = date("W", time() + get_config('dateoffset'));
      $entryyear = date("Y", time() + get_config('dateoffset'));
      
      $attempt = mysql_query("INSERT INTO `". get_table('pollq') ."` (`Creator`, `Week`, `Year`, `Question`, `Answers`, `published`) VALUES ('{$_SESSION['familysite']}', '{$entryweek}', '{$entryyear}', '{$question}', '{$answers}', 0);");
      
      if($attempt)
        {
        $success = "Your poll has been successfully added.";
        send_notification($_SESSION['familysite'], -2, "Poll Confirmation", "<strong>Your poll was successfully added.</strong> It will be active for the rest of this week. Thanks for contributing!<br />
<br />
<strong>". stripslashes($_POST['question']) ."</strong><br />
". stripslashes($displayanswers));
        }
      
      else
        {
        $error = "Your poll couldn't be added due to a system error. This error has been reported.";
        send_notification(1, -2, "Error Report", "<strong>An error occurred while trying to add a poll.</strong><br />
  User: ". authornamelookup($_SESSION['familysite']) ." (#{$_SESSION['familysite']})<br />
  Timestamp: ". date("g:i a, n/j/y", (time() + get_table('dateoffset'))) ."<br />
  mysql_error: ". mysql_error() ."<br />
  mysql_query:<br />
  <blockquote>INSERT INTO `". get_table('pollq') ."` (`Creator`, `Week`, `Year`, `Question`, `Answers`, `published`) VALUES ('{$_SESSION['familysite']}', '{$entryweek}', '{$entryyear}', '{$question}', '{$answers}', 0);</blockquote><br />
  Week: {$entryweek}
  Year: {$entryyear}
  Question:<br />
  <blockquote>{$_POST['question']}</blockquote><br />
  Processed Question:<br />
  <blockquote>". stripslashes($question) ."</blockquote><br />
  Responses:<br />
  <blockquote>{$_POST['answers']}</blockquote><br />
  Processed Reponses:<br />
  <blockquote>". stripslashes($answers) ."</blockquote>");
        }
      }
    }
  
else
    {
    //There is a poll for this week. Make one for the next empty week.
    $question = sanitize($_POST['question']);
    $answers = sanitize($_POST['answers']);
    $displayanswersarray = explode("\\r\\n", $answers);
    
    if(strlen($question) == 0)
      $error = "Your poll must have a question.";
    
    elseif(count($displayanswersarray) <= 1 OR count($displayanswersarray) > 20)
      $error = "You must have between 2 and 20 options.";
    
    else
      {
      $displayanswers = "";
      
      for($i = 0; $i < count($displayanswersarray); $i++)
      $displayanswers .= "<input type=\"radio\" id=\"l{$i}\" name=\"pollpreview\" value=\"{$i}\"> <label for=\"l{$i}\">{$displayanswersarray[$i]}</label><br />
  ";
      
      $farthestpoll = mysql_fetch_assoc(mysql_query("SELECT * FROM `". get_table('pollq') ."` ORDER BY `Year` DESC, `Week`  DESC LIMIT 1;"));
      $farthestweek = $farthestpoll['Week'];
      $farthestyear = $farthestpoll['Year'];
      
      if($farthestweek == 52)
        {
        $entryyear = $farthestyear + 1;
        $entryweek = 1;
        }
      else
        {
        $entryyear = $farthestyear;
        $entryweek = $farthestweek + 1;
        }
      
      $attempt = mysql_query("INSERT INTO `". get_table('pollq') ."` (`Creator`, `Week`, `Year`, `Question`, `Answers`) VALUES ('{$_SESSION['familysite']}', '{$entryweek}', '{$entryyear}', '{$question}', '{$answers}');");
      
      if($attempt)
        {
        $success = "Your poll has been successfully added.";
        send_notification($_SESSION['familysite'], -2, "Poll Confirmation", "<strong>Your poll was successfully added.</strong> It will be active in week {$entryweek} of {$entryyear}. Thanks for contributing!<br />
  <br />
  <strong>". stripslashes($question) ."</strong><br />
  ". stripslashes($displayanswers));
        }

      else
        {
        $error = "Your poll couldn't be added due to a system error. This error has been reported.";
        send_notification(1, -2, "Error Report", "<strong>An error occurred while trying to add a poll.</strong><br />
  User: ". authornamelookup($_SESSION['familysite']) ." (#{$_SESSION['familysite']})<br />
  Timestamp: ". date("g:i a, n/j/y", (time() + get_table('dateoffset'))) ."<br />
  mysql_error: ". mysql_error() ."<br />
  mysql_query:<br />
  <blockquote>INSERT INTO `". get_table('pollq') ."` (`Creator`, `Week`, `Year`, `Question`, `Answers`) VALUES ('{$_SESSION['familysite']}', '{$entryweek}', '{$entryyear}', '{$question}', '{$answers}');</blockquote><br />
  Week: {$entryweek}
  Year: {$entryyear}
  Question:<br />
  <blockquote>{$_POST['question']}</blockquote><br />
  Processed Question:<br />
  <blockquote>{$question}</blockquote><br />
  Responses:<br />
  <blockquote>{$_POST['answers']}</blockquote><br />
  Processed Reponses:<br />
  <blockquote>{$answers}</blockquote>");
        }
      }
    }
  }
?><html>
<head>
<link type="text/css" href="../system/style.css" rel="stylesheet">
<title><?php print get_table('SiteName'); ?>: Add Poll Question</title>
<script type="text/javascript" src="../system/engine.js"></script>
<script type="text/javascript">
function updatePreview()
{
var preview = getHTTPObject();

if(preview)
{
  preview.onreadystatechange = function() {
  if(preview.readyState == 4 && preview.status == 200)
    document.getElementById('preview').innerHTML=preview.responseText;
  };
  preview.open("POST", "addpoll.php?preview", true);
  preview.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  preview.send("question="+document.forms[0].question.value+"&answers="+document.forms[0].answers.value);
}

else
  {
  document.getElementById('preview').innerHTML="An error has occured and a preview could not be generated.";
  }
}
</script>
<?php include "../header.php"; ?>
</head>

<body>
<?php include "../userinfo.php"; ?>
<h1>Add Poll Question</h1>
<?php
if(isset($success))
  print "<div class=\"green\" style=\"text-align: center\">{$success} <a href=\"../index.php\">Home &raquo;</a></div>";

elseif(isset($error))
  print "<div class=\"red\" style=\"text-align: center\">{$error} <a href=\"addpoll.php\">Try Again &raquo;</a></div>";

else
  {
  $advancepolls = mysql_num_rows(mysql_query("SELECT * FROM `". get_table('pollq') ."` WHERE `Week` > '". date("W", (time() + get_table('dateoffset'))) ."' AND `Year` >= '". date("Y", (time() + get_table('dateoffset'))) ."';"));
  
  print '<div class="yellow" style="text-align: center">Create a new poll. <a href="managepolls.php">View Advance Polls ('. $advancepolls .') &raquo;</a></div>
<br />
<h2>Create your Question</h2>
<div class="blue">
<form action="addpoll.php?save" method="post">
<label for="question">Question:</label> <input type="text" name="question" id="question" onBlur=" updatePreview();" maxlength="255"';
if(isset($_POST['question'])) print ' value="'. $_POST['question'] .'"';
print '><br />
<label for="answers">Responses: (Each on a new line)</label><br />
<textarea rows="5" cols="45" name="answers" id="answers" onBlur=" updatePreview();">'. $_POST['answers'] .'</textarea><br />
<input type="submit" value="Create Poll &raquo;">
</form>
</div>
<br />
<h2>Preview</h2>
<div class="green" id="preview">A preview of your poll will appear here.</div>';
  }
?>
<br />
<p id="footer"><?php print get_table('SiteName'); ?> Administration - Last Updated: <?php print date("g:i a, n/j/y", (getlastmod() + get_table('dateoffset'))); ?></p>
</body>
</html>