<?php
session_start();
if(!isset($_SESSION['familysite']))
  Header("Location: login.php?go=addentry.php");

include "system/parse.php";
?><html>
<head>
<link type="text/css" href="system/style.css" rel="stylesheet">
<title><?php print get_table('SiteName'); ?>: Add Blog Entry</title>
<script type="text/javascript">
//Function for handling file attachments
function attachFile(type,content)
{
if(type == "picture")
  {
  document.getElementById('attachment_display').innerHTML='<input type="hidden" name="attachment_type" value="picture"><input type="hidden" name="attachment" value="'+ content +'"><img src="system/images/picture_icon.gif"> '+ content +' <strong>[<a href="javascript: void(0);" onClick=" window.open(\'addmedia.php\',\'uploadwindow\',\'width=700,height=300,resizable=no,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no\');">Change</a>]</strong>';
  }

else if(type == "video")
  {
  document.getElementById('attachment_display').innerHTML='<input type="hidden" name="attachment_type" value="video"><textarea name="attachment">'+ content +'</textarea><img src="system/images/youtube_icon.gif"> Video Attached <strong>[<a href="javascript: void(0);" onClick=" window.open(\'addmedia.php\',\'uploadwindow\',\'width=700,height=300,resizable=no,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no\');">Change</a>]</strong>';
  document.getElementById('embed_code').style.visibility='hidden';
  }

else if(type == "document")
  {
  document.getElementById('attachment_display').innerHTML='<input type="hidden" name="attachment_type" value="document"><input type="hidden" value="' + content + '" name="attachment"><img src="system/images/file_icon.gif"> '+ content;
  }

else
  document.getElementById('attachment_display').innerHTML='<span class="error">An error has occurred.</span> <a href="javascript: void(0);" onClick=" window.open(\'addmedia.php\',\'uploadwindow\',\'width=700,height=300,resizable=no,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no\');">Attach File &raquo;</a> <img src="system/images/youtube_icon.gif" alt="Videos"> <img src="system/images/picture_icon.gif" alt="Pictures"> <img src="system/images/file_icon.gif" alt="Documents">';
}
</script>
<style type="text/css">
#attachment_display textarea {
display: none;
visibility: hidden }
</style>
<?php include "header.php"; ?>
</head>
<body>
<?php
include "userinfo.php";

if(isset($_GET['save']))
  {
  if($_POST['mode'] == "preview")
    {
    //Preview
    $handle = fopen("lastblog.txt", "w");
    fwrite($handle, $_POST['post']);
    fclose($handle);
    
    if(isset($_POST['attachment']) AND isset($_POST['attachment_type']))
      {
      if($_POST['attachment_type'] == "video")
        $attachment = ' <img src="system/images/youtube_icon.gif"> Video Attachment';
      
      elseif($_POST['attachment_type'] == "picture")
        $attachment = ' <img src="system/images/picture_icon.gif"> Photo Attachment';
      
      elseif($_POST['attachment_type'] == "document")
        $attachment = ' <img src="system/images/file_icon.gif"> Document Attachment';
      }
    
    print "<h1>Preview: ". blogitize($_POST['title']) ."</h1>
<p class=\"infobar\">Posted today at ". date("g:i a", (time() + get_table('dateoffset'))) ." by you.{$attachment}</p>
<div class=\"green\">". blogitize($_POST['post']) ."</div>
<br />";
    
    if(strpos($_POST['post'], "\n(item)"))
      print "<h2>Tips/Warnings</h2>
<div class=\"yellow\"><ul>
<li>To eliminate the extra line breaks in between your bullet points or numbered items, put all of the (item)s on one line.<br />
<em>The code isn't pretty, but the end result is.</em></li>
</ul></div><br />\n";
    }
  
  if($_POST['mode'] == "publish")
    {
    //Publish entry
    dbconnect();
    
    $timestamp = time();
    
    $attempt = mysql_query("INSERT INTO `". get_table('blog') ."` (`Title`, `Author`, `Posted`, `Entry`) VALUES ('". sanitize($_POST['title']) ."', '{$_SESSION['familysite']}', {$timestamp}, '". sanitize($_POST['post']) ."');");
    
    if($attempt)
      {
      $success = "Your entry, entitled &quot;". blogitize($_POST['title']) ."&quot;, was successfully posted.";
      $entryinfo = mysql_fetch_assoc(mysql_query("SELECT * FROM `". get_table('blog') ."` WHERE `Posted` = {$timestamp} AND `Author` = {$_SESSION['familysite']};"));
      
      //Send notification to everyone
      notify("entry", $entryinfo['ID'], null);
      
      if(isset($_POST['attachment_type']) AND isset($_POST['attachment']))
        {
        //Attach file
        if($_POST['attachment_type'] == "picture")
          {
          $filename = explode("/", $_POST['attachment']);
          $lastelement = (count($filename) - 1);
          $filename = $filename[$lastelement];
          
          mysql_query("INSERT INTO `". get_table('files') ."` (`Type`, `Owner`, `Filename`, `Path`, `EntryID`) VALUES ('picture', {$_SESSION['familysite']}, '{$filename}', '". sanitize($_POST['attachment']) ."', {$entryinfo['ID']});");
          }
        
        elseif($_POST['attachment_type'] == "video")
          {
          $filename = "YouTube Video";
          
          mysql_query("INSERT INTO `". get_table('files') ."` (`Type`, `Owner`, `Filename`, `Path`, `EntryID`) VALUES ('video', {$_SESSION['familysite']}, '{$filename}', '". str_replace("&gt;", ">", str_replace("&lt;", "<", $_POST['attachment'])) ."', {$entryinfo['ID']});");
          }
        
        else
          {
          $fileID = mysql_fetch_assoc(mysql_query("SELECT * FROM `". get_table('files') ."` WHERE `Path` = '". sanitize($_POST['attachment']) ."';"));
          $fileID = $fileID['ID'];
          
          mysql_query("UPDATE `". get_table('files') ."` SET `EntryID` = {$entryinfo['ID']} WHERE `ID` = {$fileID} LIMIT 1;");
          }
        }
      }
    else
      {
      $error = "Your entry could not be posted due to a system error. This problem has been reported.";
      send_notification(1, -2, "Error Report", "An error occurred while attempting to add a blog entry.<br />
<strong>mysql_error():</strong> ". mysql_error() ."<br /></div>
<br />
<h2>Blog Entry Information</h2>
<div class=\"red\">
<strong>Title:</strong> {$_POST['title']} (Processed as ". sanitize($_POST['title']) .")<br />
<strong>Author:</strong> ". emailauthorlookup($_SESSION['familysite']) ." (#{$_SESSION['familysite']})<br />
<strong>Entry:</strong><br />
<blockquote>{$_POST['post']}</blockquote><br />
<strong>Entry (Processed):</strong>
<blockquote>". sanitize($_POST['post']) ."</blockquote>");
      }
    }
  }

if($_POST['mode'] != "publish")
  {
  print '
<h1>Add a Blog Entry</h1>
<div style="float: left; width: 50%" class="green">
<form action="addentry.php?save" method="post" style="margin: 0; padding: 0" enctype="multipart/form-data" onSubmit=" document.getElementById(\'submit\').disabled=\'disabled\';">
<label for="title">Post Title:</label><br /><input type="text" name="title" id="title" size="50"';

  if($_POST['mode'] == "preview") print " value=\"". htmlentities(blogitize($_POST['title'])) ."\"";

  print '><br />

<span id="attachment_display">
';

if(isset($_POST['attachment']) AND isset($_POST['attachment_type']))
  {
  if($_POST['attachment_type'] == "video")
    print '<input type="hidden" name="attachment_type" value="video"><textarea name="attachment">'. $_POST['attachment'] .'</textarea>
<img src="system/images/youtube_icon.gif"> Video Attachment <strong>[<a href="javascript: void(0);" onClick=" window.open(\'addmedia.php\',\'uploadwindow\',\'width=700,height=300,resizable=no,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no\');">Change</a>]</strong>';
  
  elseif($_POST['attachment_type'] == "picture")
    print '<input type="hidden" name="attachment_type" value="picture"><input type="hidden" name="attachment" value="'. $_POST['attachment'] .'">
<img src="system/images/picture_icon.gif"> '. $_POST['attachment'] .' <strong>[<a href="javascript: void(0);" onClick=" window.open(\'addmedia.php\',\'uploadwindow\',\'width=700,height=300,resizable=no,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no\');">Change</a>]</strong>';
  
  elseif($_POST['attachment_type'] == "document")
    print '<input type="hidden" name="attachment_type" value="document"><input type="hidden" name="attachment" value="'. $_POST['attachment'] .'"><img src="system/images/file_icon.gif"> '. $_POST['attachment'];
  }

else
  print '<a href="javascript: void(0);" onClick=" window.open(\'addmedia.php\',\'uploadwindow\',\'width=700,height=300,resizable=no,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no\');">Add Media (Optional) &raquo;</a> <img src="system/images/youtube_icon.gif" alt="Videos"> <img src="system/images/picture_icon.gif" alt="Pictures"> <img src="system/images/file_icon.gif" alt="Documents"> <span style="color: #666">You can also do this later.</span>';

print '
</span>
<br />

<label for="post">Post Text:</label><br />
<textarea name="post" id="post" rows="10" cols="50">';

  if($_POST['mode'] == "preview")
    {
    print stripslashes($_POST['post']);
    
    if(strlen($_POST['title']) > 0 AND strlen($_POST['post']) > 0)
      $publishoption = '<input type="radio" name="mode" value="publish" id="publish"><label for="publish">Publish Entry</label><br />';
    else
      $publishoption = '<p class="error">This post can\'t be published until it has a title and some content.</p>';
    }

  print '</textarea><br />
<input type="radio" name="mode" value="preview" id="preview" checked><label for="preview">Preview Entry</label><br />
'. $publishoption .'
<input type="submit" value="Go &raquo;" id="submit">
</form>
</div>
<div style="float: right; width: 50%; text-align: center" class="green">
<center>
';

  include "codeguide.php";

  print '
</center>
</div>';
  }

else
  {
  print "<h1>Add a Blog Entry</h1>";
  
  if(isset($success))
    print "<p class=\"success\">{$success} <a href=\"index.php\">Blog &raquo;</a></p>
";
  else
    print "<p class=\"error\">{$error} <a href=\"index.php\">Blog &raquo;</a></p>
";
  }
?>
</body>
</html>