<?php
session_start();
if(!isset($_SESSION['familysite']))
  Header("Location: login.php");

include "system/parse.php";
dbconnect();
?><html>
<head>
<title><?php print get_table('SiteName'); ?>: Add Media</title>
<link type="text/css" href="system/style.css" rel="stylesheet">
<?php include "header.php"; ?>
</head>

<body>
<p style="text-align: right; margin: 0; padding: 0; color: #666"><?php print authornamelookup($_SESSION['familysite']); ?> | <a href=" javascript: void(0);" onClick=" window.close();">Close Window</a></p>

<?php
if(!isset($_POST['type']))
  print '<h2>Step 1: Choose Media Type</h2>
<div class="green">
<strong>What kind of media would you like to add?</strong>

<form action="addmedia.php" method="post">
<input type="radio" name="type" value="picture" id="picture" checked> <label for="picture"><img src="system/images/picture_icon.gif"> Picture</label><br />
<input type="radio" name="type" value="video" id="video"> <label for="video"><img src="system/images/youtube_icon.gif"> Video</label><br />
<input type="radio" name="type" value="document" id="document"> <label for="document"><img src="system/images/file_icon.gif"> Document</label><br />
<input type="submit" value="Next Step &raquo;">
</form>
</div>';

elseif(!isset($_POST['url']) AND !isset($_POST['videocode']) AND !isset($_FILES['file']['name']))
  {
  $type = $_POST['type'];
  
  if($type == "picture")
    print '<h2>Step 2: Add your Picture</h2>
<div class="green">
<strong>Your picture needs to be online somewhere.</strong>
<a href="javascript: void(0);" onClick=" window.open(\'http://www.imageshack.us/\',\'imageshack\',\'resizable=yes,scrollbars=yes,toolbar=yes,location=yes,directories=yes,status=yes,menubar=yes\');" onMouseOver=" document.getElementById(\'imageshackinfo\').style.display=\'inline\';">Imageshack</a> works great, but anywhere will do.
<span style="color: #666; display: none" id="imageshackinfo">
<br />
If you use Imageshack, be sure to copy the <strong>direct link</strong>.</span>
<br />
<br />
<form action="addmedia.php" method="post" onSubmit=" window.opener.attachFile(\'picture\', document.getElementById(\'url\').value);">
<input type="hidden" name="type" value="picture">
<label for="url"><img src="system/images/picture_icon.gif"> Picture URL:</label><input type="text" name="url" id="url" size="50"><br />
<input type="submit" value="Add Picture &raquo;">
</form>
</div>';
  
  elseif($type == "video")
    print '<h2>Step 2: Add your Video</h2>
<div class="green">
<strong>Your video needs to be hosted on <a href="javascript: void(0);" onClick=" window.open(\'http://www.youtube.com/\',\'youtube\',\'resizable=yes,scrollbars=yes,toolbar=yes,location=yes,directories=yes,status=yes,menubar=yes\');">YouTube</a>.</strong> (It\'s free and easy)
<br />
<br />
<form action="addmedia.php" method="post" onSubmit=" window.opener.attachFile(\'video\', document.getElementById(\'videocode\').value);">
<input type="hidden" name="type" value="video">
<label for="videocode">Paste the <img src="system/images/youtube_icon.gif"> <strong>embed code</strong>:</label><br />
<textarea name="videocode" id="videocode" rows="4" cols="40"></textarea><br />
<input type="submit" value="Add Video &raquo;">
</form>
</div>';
  
  elseif($type == "document")
    print '<h2>Step 2: Add your Document</h2>
<div class="green">
<strong>Your document needs to be less than 1<abbr title="Megabyte">MB</abbr> in size.</strong> This probably won\'t be a problem.
<br /><br />
<form action="addmedia.php" method="post" enctype="multipart/form-data">
<input type="hidden" name="type" value="document">
<input type="hidden" name="MAX_FILE_SIZE" value="1000000">
<label for="file">Upload your Document:</label> <input type="file" name="file" id="file" size="50"><br />
<input type="submit" value="Upload Document &raquo;">
</div>';
  
  else
    print '<h2>An Error has Occurred</h2>
<div class="red">
<strong>An unknown error has occurred.</strong> Please <a href="addmedia.php">start over</a> adding your media.
</div>';
  }

elseif(isset($_POST['url']) OR isset($_POST['videocode']) OR isset($_FILES['file']['name']))
  {
  if(isset($_POST['url']))
    print "<h2>Picture Attached!</h2>
<div class=\"green\">Your picture has been attached to this blog post. <a href=\"javascript: void(0);\" onClick=\" window.close();\">Close Window &raquo;</a>
</div>
<br />
<br />
<h2>Picture Preview</h2>
<div class=\"blue\">
<strong>This is what will appear along with your blog post.</strong>
<br />
<br />
<img src=\"{$_POST['url']}\" alt=\"Your Picture\">
</div>";
  
  elseif(isset($_POST['videocode']))
    print "<h2>Video Attached!</h2>
<div class=\"green\">Your video has been attached to this blog post. <a href=\"javascript: void(0);\" onClick=\" window.close();\">Close Window &raquo;</a></div>
<br />
<h2>Video Preview</h2>
<div class=\"blue\">
<strong>This is what will appear along with your blog post.</strong>
<br /><br />
{$_POST['videocode']}
</div>";
  
  elseif(isset($_FILES['file']['name']))
    {
    //Upload document
    $uploadname = str_replace(" ", "-", $_FILES['file']['name']);
    
    $fileparts = explode(".", $uploadname);
    
    $fileparts[0] = str_replace(".", "_", trim($fileparts[0]));
    $fileparts[1] = mb_convert_case($fileparts[1], MB_CASE_LOWER);
    
    $type = trim($fileparts[1]);
    
    $bannedtypes = Array("exe", "bat", "jpg", "jpeg", "png", "tif", "tiff", "bmp", "gif", "mov", "wmv", "mpg", "mpeg");
    
    if(!in_array($type, $bannedtypes))
    {
    //Make sure this name's unique
    $savename = "uploads/" . $fileparts[0] . "-" . (time() + get_table('dateoffset')) . "-" . $_SESSION['familysite'] . "." . $fileparts[1];
    
    if(move_uploaded_file($_FILES['file']['tmp_name'], $savename))
      {
      if(mysql_query("INSERT INTO `". get_table('files') ."` (`Type`, `Owner`, `Filename`, `Path`, `EntryID`) VALUES ('{$type}', '{$_SESSION['familysite']}', '{$fileparts[0]}', '{$savename}', 0);"))
        {
        print '<h2>File Uploaded!</h2>
<div class="green">
Your file, <strong><img src="system/images/file_icon.gif"> '. $_FILES['file']['name'] .'</strong>, has been uploaded.<br />
<br />
<form action="javascript: void(0);" onSubmit=" window.opener.attachFile(\'document\', \''. $savename .'\'); window.close();" style="text-align: center">
<input type="submit" value="Attach Document and Close Window &raquo;">
</form>
</div>';
        }
      
      else
        {
        send_notification(1, -2, 'Error Report', '<strong>An error occurred while uploading a file.</strong><br />
'. mysql_error() .'<br />
<br />
<strong>mysql_query():</strong><br />
<blockquote>INSERT INTO `". get_table(\'files\') ."` (`Type`, `Owner`, `Filename`, `Path`, `EntryID`) VALUES (' . $type .', ' . $_SESSION['familysite'] .', ' . $_FILES['file']['name'] . ', ' . $savename . ', 0);</blockquote>
<br /><br />
There is also an orphaned file as a result. The user who uploaded it will be notified. <a href="admin/managefiles.php">File Management &raquo;</a>');

        print '<h2>Upload Error</h2>
<div class="red">
An error has occurred. Your file was <strong>not</strong> uploaded. This error has been reported. You can <a href="addmedia.php">try again</a> if you like. The problem should be resolved within a few days, though.
</div>';
        }
      }
    
    elseif($_FILES['file']['error'] == 2)
      print '<h2>Upload Error</h2>
<div class="red">
The file you\'re trying to upload is too big. Documents are limited to 1<abbr title="Megabyte">MB</abbr> in size. <a href="addmedia.php">Try again &raquo;</a>
</div>';
    
    else
      {
      send_notification(1, -2, 'Error Report', '<strong>An error occurred while uploading a file.</strong> (Error '. $_FILES['file']['error'] .') No file was saved to the server.<br />
<br />
<strong>User:</strong> '. authornamelookup($_SESSION['familysite']) .' (#'. $_SESSION['familysite'] .')<br />
<strong>Original Name:</strong> '. $_FILES['file']['name'] .'<br />
<strong>tmp_name:</strong> '. $_FILES['file']['tmp_name'] .'<br />
<strong>$fileparts[0]:</strong> '. $fileparts[0] .'<br />
<strong>$fileparts[1]:</strong> '. $fileparts[1] . '<br />
<strong>$savename:</strong> '. $savename);

        print '<h2>Upload Error</h2>
<div class="red">
An error has occurred. Your file was <strong>not</strong> uploaded. This error has been reported. You can <a href="addmedia.php">try again</a> if you like. The problem should be resolved within a few days, though.
</div>';
      }
    }
  
  else
    print '<h2>Upload Error</h2>
<div class="red">
That type of file is not allowed. Please upload something that doesn\'t end in <strong>.'. $type .'</strong>. <a href="addmedia.php">Try again &raquo;</a>
</div>';
  }
  
  else
    print '<h2>An Error has Occurred</h2>
<div class="red">
<strong>An unknown error has occurred.</strong> Please <a href="addmedia.php">start over</a> adding your media.
</div>';
    }
?>
</body>
</html>