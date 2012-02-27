<?php
session_start();
if(!isset($_SESSION['familysite']))
  Header("Location: ../login.php?go=admin/managefiles.php");

include "../system/parse.php";
dbconnect();

if(isset($_POST['entryID']) AND isset($_POST['entryID']))
  {
  if($_POST['mode'] == "existing")
    {
    //Attach existing file to entry
    $attempt = mysql_query("UPDATE `". get_table('files') ."` SET `EntryID` = '". sanitize($_POST['entryID']) ."' WHERE `ID` = '". sanitize($_POST['fileID']) ."';");
    
    if($attempt)
      $success = true;
    else
      {
      send_notification(1, -2, "Error Report", "An error occurred while attempting to attach an existing file to an existing entry.<br />
<strong>entryID:</strong> {$_POST['entryID']}<br />
<strong>fileID:</strong> {$_POST['fileID']}");
      
      $error = true;
      }
    }
  
  else
    {
    //Attach new file to entry
    $entryID = $_POST['entryID'];
    
    if(mysql_num_rows(mysql_query("SELECT * FROM `". get_table('files') ."` WHERE `EntryID` = {$entryID};")) > 0)
      {
      send_notification(1, -2, "Error Report", authornamelookup($_SESSION['familysite']) ." (#{$_SESSION['familysite']}) tried to attach a file to a blog entry that already had an attachment!");
      
      die(error("System Error", "A system error has occurred. Please proceed to the <a href=\"../index.php\">Homepage</a>."));
      }
    
    if($_POST['attachment_type'] == "picture")
      {
      $filename = explode("/", $_POST['attachment']);
      $lastelement = (count($filename) - 1);
      $filename = $filename[$lastelement];
      
      mysql_query("INSERT INTO `". get_table('files') ."` (`Type`, `Owner`, `Filename`, `Path`, `EntryID`) VALUES ('picture', {$_SESSION['familysite']}, '{$filename}', '". sanitize($_POST['attachment']) ."', {$entryID});");
      }
    
    elseif($_POST['attachment_type'] == "video")
      {
      $filename = "YouTube Video";
      
      mysql_query("INSERT INTO `". get_table('files') ."` (`Type`, `Owner`, `Filename`, `Path`, `EntryID`) VALUES ('video', {$_SESSION['familysite']}, '{$filename}', '". str_replace("&gt;", ">", str_replace("&lt;", "<", $_POST['attachment'])) ."', {$entryID});");
      }
    
    else
      {
      $fileID = mysql_fetch_assoc(mysql_query("SELECT * FROM `". get_table('files') ."` WHERE `Path` = '". sanitize($_POST['attachment']) ."';"));
      $fileID = $fileID['ID'];
      
      mysql_query("UPDATE `". get_table('files') ."` SET `EntryID` = {$entryID} WHERE `ID` = {$fileID} LIMIT 1;");
      }
    
    if(mysql_error())
      {
      $error = true;
      send_notification(1, -2, "Error Report", "An error occurred while attempting to add a new attachment to an existing entry.<br />
<strong>fileID:</strong> {$fileID}<br />
<strong>entryID:</strong> {$entryID}<br />
<strong>attachment_type:</strong> {$_POST['attachment_type']}<br />
<strong>attachment:</strong><br />
<blockquote>{$_POST['attachment']}</blockquote>");
      }
    
    else
      $success = true;
    }
  }
?><html>
<head>
<script type="text/javascript">
//Function for handling file attachments
function attachFile(type,content)
{
if(type == "picture")
  {
  document.getElementById('attachment_display').innerHTML='<input type="hidden" name="attachment_type" value="picture"><input type="hidden" name="attachment" value="'+ content +'"><img src="../system/images/picture_icon.gif"> '+ content +' <strong>[<a href="javascript: void(0);" onClick=" window.open(\'../addmedia.php\',\'uploadwindow\',\'width=700,height=300,resizable=no,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no\');">Change</a>]</strong>';
  }

else if(type == "video")
  {
  document.getElementById('attachment_display').innerHTML='<input type="hidden" name="attachment_type" value="video"><textarea name="attachment">'+ content +'</textarea><img src="../system/images/youtube_icon.gif"> Video Attached <strong>[<a href="javascript: void(0);" onClick=" window.open(\'../addmedia.php\',\'uploadwindow\',\'width=700,height=300,resizable=no,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no\');">Change</a>]</strong>';
  document.getElementById('embed_code').style.visibility='hidden';
  }

else if(type == "document")
  {
  document.getElementById('attachment_display').innerHTML='<input type="hidden" name="attachment_type" value="document"><input type="hidden" value="' + content + '" name="attachment"><img src="../system/images/file_icon.gif"> '+ content;
  }

else
  document.getElementById('attachment_display').innerHTML='<span class="error">An error has occurred.</span> <a href="javascript: void(0);" onClick=" window.open(\'../addmedia.php\',\'uploadwindow\',\'width=700,height=300,resizable=no,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no\');">Attach File &raquo;</a> <img src="system/images/youtube_icon.gif" alt="Videos"> <img src="system/images/picture_icon.gif" alt="Pictures"> <img src="system/images/file_icon.gif" alt="Documents">';
}
</script>
<style type="text/css">
#attachment_display textarea, .hidden {
display: none;
visibility: hidden }
</style>
<link type="text/css" href="../system/style.css" rel="stylesheet">
<title><?php print get_table('SiteName'); ?>: Administration: Attach a File</title>
<?php include "../header.php"; ?>
</head>

<body>
<?php include "../userinfo.php";?>
<h1>Attach a File</h1>
<?php
if(isset($success))
  {
  print '<div class="green" style="text-align: center">
Your file has been succesfully attached! <a href="';
  if(isset($_POST['entrypage']))
    print '../entry.php?entry='. $_POST['entryID'] .'">Back to Blog Entry';
  else
    print 'managefiles.php">File Management';
   
   print ' &raquo;</a>
</div>';
  }

elseif(isset($error))
  {
  print '<div class="red" style="text-align: center">
Your file couldn\'t be attached due to a system error. This error has been reported. Please try again later. <a href="';
  if(isset($_POST['entrypage']))
    print '../entry.php?entry='. $_POST['entryID'] .'">Back to Blog Entry';
  else
    print 'managefiles.php">File Management';
   
   print ' &raquo;</a>
</div>';
  }

else
  {
  if(isset($_GET['fileID']) OR isset($_POST['attachment']))
    {
    if(isset($_GET['fileID']))
      {
      $fileID = $_GET['fileID'];
      $fileinfo = mysql_fetch_assoc(mysql_query("SELECT * FROM `". get_table('files') ."` WHERE `ID` = ". sanitize($fileID) .";"));
      }
    
    print '<div class="yellow" style="text-align: center">
Here, you can attach files to existing blog entries.
</div>
<br />
<h2>Step 2: Choose a Blog Entry</h2>
<div class="blue">
<form action="attachfile.php" method="post">
<input type="hidden" name="fileID" value="'. $_GET['fileID'] .'">
<input type="hidden" name="mode" value="';

if(isset($_GET['fileID']))
  print 'existing">';

else
  {
  print 'new">
<input type="hidden" name="attachment_type" value="'. $_POST['attachment_type'] .'">';
  
  if($_POST['attachment_type'] == "video")
    print "<textarea name=\"attachment\" class=\"hidden\">{$_POST['attachment']}</textarea>\n";
  
  else
    print '<input type="hidden" name="attachment" value="'. $_POST['attachment'] .'">
';
  }

if($_POST['attachment_type'] == "picture")
  $filedisplay = '<img src="../system/images/picture_icon.gif"> '. $_POST['attachment'];

elseif($_POST['attachment_type'] == "video")
  $filedisplay = '<img src="../system/images/youtube_icon.gif"> this video';

elseif(isset($_GET['fileID']))
  $filedisplay = '<img src="../system/images/file_icon.gif"> '. $fileinfo['Filename'] .'.'. $fileinfo['Type'];

else
  $filedisplay = '<img src="../system/images/file_icon.gif"> '. $_POST['attachment'];

print '
Choose a blog entry to attach <strong>'. $filedisplay .'</strong> to:<br />
<br />
<select name="entryID">
';
    
    $entrylist = mysql_query("SELECT * FROM `". get_table('blog') ."` WHERE `Author` = {$_SESSION['familysite']} ORDER BY `Posted` DESC;");
    
    while($row = mysql_fetch_assoc($entrylist))
      {
      if(mysql_num_rows(mysql_query("SELECT * FROM `". get_table('files') ."` WHERE `EntryID` = {$row['ID']};")) == 0)
        print '<option value="'. $row['ID'] .'">'. textemailblogitize($row['Title']) .'</option>
';
      }
    
    if(mysql_num_rows($entrylist) == 0)
      print "<option disabled>You have no blog entries without attachments.</option>";
    
    print '</select>
<input type="submit" value="Attach File &raquo;">
</form>
</div>';
    }
  
  else
    {
    print '<div class="yellow" style="text-align: center">
Here, you can attach files to existing blog entries.
</div>
<br />
<h2>Step 1: Choose or Add a File</h2>
<div class="blue">
<form action="attachfile.php" method="post">';
    
    if(isset($_GET['entryID']))
      print "\n<input type=\"hidden\" value=\"". sanitize($_GET['entryID']) ."\" name=\"entryID\">\n<input type=\"hidden\" name=\"entrypage\" value=\"true\">\n";
    
    $attachmentquery = mysql_query("SELECT * FROM `". get_table('files') ."` WHERE `Owner` = {$_SESSION['familysite']} AND `EntryID` = 0;");
    
    if(mysql_num_rows($attachmentquery) > 0)
      {
      print "<strong>You have existing files that aren't attached to a blog entry.</strong> Click on one to choose it.<br />";
      
      while($row = mysql_fetch_assoc($attachmentquery))
        {
        if($row['Type'] == "picture")
          print '<img src="../system/images/picture_icon.gif"> <a href="attachfile.php?fileID='. $row['ID'] .'">'. $row['Filename'] .'</a><br />';
        elseif($row['Type'] == "video")
          print '<img src="../system/images/youtube_icon.gif"> <a href="attachfile.php?fileID='. $row['ID'] .'">Video</a><br />';
        else
          print '<img src="../system/images/file_icon.gif"> <a href="attachfile.php?fileID='. $row['ID'] .'">'. $row['Filename'] .'.'. $row['Type'] .'</a><br />';
        }
      }
    
    else
      print "<strong>You don't have any existing files not attached to a blog entry.</strong>";
    
    print "<br /><br /><span id=\"attachment_display\"><a href=\"javascript: void(0);\" onClick=\" window.open('../addmedia.php','uploadwindow','width=700,height=300,resizable=no,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no');\">Add Media &raquo;</a></span>";
    
    print '
<br /><br />
<input type="submit" value="Next Step &raquo;">
</form>
</div>';
    }
  }
?>
<br />
<p id="footer"><?php print get_table('SiteName'); ?> Administration - Attach a File. Last updated <?php print date("g:i a, n/j/Y", (getlastmod() + get_table('dateoffset'))); ?></p>
</body>
</html>