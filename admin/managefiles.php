<?php
session_start();
if(!isset($_SESSION['familysite']))
  Header("Location: ../login.php?go=admin/managefiles.php");

include "../system/parse.php";
dbconnect();

//Return data to AJAX application
if(isset($_GET['reload-listing']))
  {
  print '<tr><th>Type</th><th>Filename</th><th>Uploaded By</th><th>Size</th><th>Blog Entry</th><th>Tools</th></tr>
  ';
  
  if($_SESSION['familysite'] != 1)
    $filter = " WHERE `Owner` = {$_SESSION['familysite']}";

  $filequery = mysql_query("SELECT * FROM `". get_table('files') ."`{$filter} ORDER BY `Filename` ASC;");
  $kilobytes = "0";
  $rowcolor = "#FFF";

  if(mysql_num_rows($filequery) == 0)
    print "<p class=\"error\" style=\"text-align: center\">No files have been uploaded.</p><br />";

  else
    {
    while($row = mysql_fetch_assoc($filequery))
      {
      //Get icon for filetype
      if($row['Type'] == "picture")
        $icon = 'picture_icon.gif';
      
      elseif($row['Type'] == "video")
        $icon = 'youtube_icon.gif';
      
      else
        $icon = 'file_icon.gif';
      
      //Get blog entry title
      $entryinfoquery = mysql_query("SELECT * FROM `". get_table('blog') ."` WHERE `ID` = {$row['EntryID']};");
      $entryinfo = mysql_fetch_assoc($entryinfoquery);
      $entrylink = '<a href="../entry.php?entry='. $entryinfo['ID'] .'">'. blogitize($entryinfo['Title']) .'</a>';
      
      if(mysql_num_rows($entryinfoquery) == 0)
        {
        $entrylink =  "No Entry";
        $rowcolor = "FFE5E5";
        }
      
      if($row['Type'] == "picture")
      print "<tr style=\"background-color: {$rowcolor}\"><td><img src=\"../system/images/{$icon}\"></td><td><a href=\"{$row['Path']}\">{$row['Filename']}</a></td><td>". authorlookup($row['Owner']) ."</td><td>-</td><td>{$entrylink}</td><td><a href=\"javascript: void(0);\" onClick=\" deleteFile('{$row['ID']}');\"><img src=\"../system/images/delete.gif\"></a></td></tr>\n";
    
    elseif($row['Type'] == "video")
      print "<tr style=\"background-color: {$rowcolor}\"><td><img src=\"../system/images/{$icon}\"></td><td>Video on YouTube</td><td>". authorlookup($row['Owner']) ."</td><td>-</td><td>{$entrylink}</td><td><a href=\"javascript: void(0);\" onClick=\" deleteFile('{$row['ID']}');\"><img src=\"../system/images/delete.gif\"></a></td></tr>\n";
    
    else  
      print "<tr style=\"background-color: {$rowcolor}\"><td><img src=\"../system/images/{$icon}\"></td><td><a href=\"../{$row['Path']}\">{$row['Filename']}.{$row['Type']}</a></td><td>". authorlookup($row['Owner']) ."</td><td>". round((filesize("../{$row['Path']}") / 1000)) ." KB</td><td>{$entrylink}</td><td><a href=\"javascript: void(0);\" onClick=\" deleteFile('{$row['ID']}');\"><img src=\"../system/images/delete.gif\"></a></td></tr>\n";
      
      if($rowcolor == "#EDEDED")
        $rowcolor = "#FFF";
      else
        $rowcolor = "#EDEDED";
      }
    }
  die();
  }

if(isset($_GET['delete']))
  {
  //Look up file information
  $fileinfo = mysql_fetch_assoc(mysql_query("SELECT * FROM `". get_table('files') ."` WHERE `ID` = ". sanitize($_GET['delete']) ." LIMIT 1;"));
  
  if($fileinfo['Type'] == "picture" OR $fileinfo['Type'] == "video")
    {
    $attempt = mysql_query("DELETE FROM `". get_table('files') ."` WHERE `ID` = ". sanitize($_GET['delete']) ." LIMIT 1;");
    
    if($attempt)
      {
      if(!isset($_GET['entrypage']))
        print "<p class=\"success\" style=\"text-align: center\">The file has been deleted.</p>";
      else
        print "The file has been successfully deleted.";
      }
    else
      {
      if(!isset($_GET['entrypage']))
        print "<p class=\"error\" style=\"text-align: center\">The file couldn't be deleted.</p>";
      else
        print "The file couldn't be deleted due to a system error.";
      
      send_notification(1, -2, "Error Report", "An error occurred while trying to delete a photo or video.<br /><strong>". mysql_error() ."</strong>");
      }
    }
  
  else
    {
    if(unlink("../{$fileinfo['Path']}"))
      {
      //Remove from database
      mysql_query("DELETE FROM `". get_table('files') ."` WHERE `ID` = ". sanitize($_GET['delete']) ." LIMIT 1;");
      
      if(!isset($_GET['entrypage']))
        print "<p class=\"success\" style=\"text-align: center\">The file has been deleted.</p>";
      else
        print "The file has been successfully deleted.";
      }
    
    else
      {
      if(!isset($_GET['entrypage']))
        print "<p class=\"error\" style=\"text-align: center\">The file couldn't be deleted.</p>";
      else
        print "The file couldn't be deleted due to a system error.";
      }
    }
  
  if(isset($_GET['entrypage']))
    die();
  else
    die(" <a href=\"javascript: void(0);\" onClick=\" reloadListing();\">Show File Listing &raquo;</a>");
  }
?><html>
<head>
<link type="text/css" href="../system/style.css" rel="stylesheet">
<style type="text/css">
th {
background-color: #003366;
color: #FFF }
</style>
<title><?php print get_table('SiteName'); ?>: Administration: Manage Uploaded Files</title>
<script type="text/javascript" src="../system/engine.js"></script>
<script type="text/javascript">
function reloadListing()
{
var listing = getHTTPObject();

if(listing)
{
  listing.onreadystatechange = function() {
  if(listing.readyState == 4 && listing.status == 200)
    document.getElementById('filelisting').innerHTML=listing.responseText;
  };
  listing.open("GET", "managefiles.php?reload-listing", true);
  listing.send(null);
}

else
  document.getElementById('filelisting').innerHTML="<p class=\"error\">Fatal error: Your browser does not support AJAX technology, which is required for this site to work. Please consider upgrading your browser.</p>";
}

function deleteFile(ID)
{
if(confirm("Are you sure you want to delete this file?"))
  {
  var dfile = getHTTPObject();

  if(dfile)
  {
    dfile.onreadystatechange = function() {
    if(dfile.readyState == 4 && dfile.status == 200)
      document.getElementById('filelisting').innerHTML=dfile.responseText;
    };
    dfile.open("GET", "managefiles.php?delete="+ID, true);
    dfile.send(null);
  }

  else
    document.getElementById('filelisting').innerHTML="<p class=\"error\">Fatal error: Your browser does not support AJAX technology, which is required for this site to work. Please consider upgrading your browser.</p>";
  }
}
</script>
<?php include "../header.php"; ?>
</head>

<body>
<?php include "../userinfo.php"; ?>
<h1>Manage Uploaded Files</h1>
<?php
if($_SESSION['familysite'] == 1 AND !isset($_GET['quickfix']))
  print "<div class=\"yellow\" style=\"text-align: center\">Here you can view and delete attachments that have been added by users.";

elseif(!isset($_GET['quickfix']))
  print "<div class=\"yellow\" style=\"text-align: center\">Here you can view and delete attachments you've added to blog entries.";

else
  {
  $files = mysql_query("SELECT * FROM `". get_table('files') ."` WHERE `Owner` = {$_SESSION['familysite']} AND `EntryID` = 0;");
  
  while($row2 = mysql_fetch_assoc($files))
    {
    unlink("../{$row2['Path']}");
    mysql_query("DELETE FROM `". get_table('files') ."` WHERE `Owner` = {$_SESSION['familysite']} AND `EntryID` = 0 AND `ID` = {$row2['ID']} LIMIT 1;");
    }
  
  print "<div class=\"green\" style=\"text-align: center\">The problem has been fixed. <a href=\"../index.php\">Home &raquo;</a>";
  }
?></div>
<?php
if(!isset($_GET['quickfix']))
  {
  print '
<br />
<table style="margin: auto" id="filelisting">
<tr><th>Type</th><th>Filename</th><th>Uploaded By</th><th>Size</th><th>Blog Entry</th><th>Tools</th></tr>
';

if($_SESSION['familysite'] != 1)
  $filter = " WHERE `Owner` = {$_SESSION['familysite']}";

$filequery = mysql_query("SELECT * FROM `". get_table('files') ."`{$filter} ORDER BY `Filename` ASC;");
$kilobytes = "0";
$rowcolor = "#FFF";

if(mysql_num_rows($filequery) == 0)
  print "<p class=\"error\" style=\"text-align: center\">You haven't attached anything yet.</p><br />";

else
  {
  while($row = mysql_fetch_assoc($filequery))
    {
    //Get icon for filetype
    if($row['Type'] == "picture")
      $icon = 'picture_icon.gif';
    
    elseif($row['Type'] == "video")
      $icon = 'youtube_icon.gif';
    
    else
      $icon = 'file_icon.gif';
    
    //Get blog entry title
    $entryinfoquery = mysql_query("SELECT * FROM `". get_table('blog') ."` WHERE `ID` = {$row['EntryID']};");
    $entryinfo = mysql_fetch_assoc($entryinfoquery);
    $entrylink = '<a href="../entry.php?entry='. $entryinfo['ID'] .'">'. blogitize($entryinfo['Title']) .'</a>';
    
    if(mysql_num_rows($entryinfoquery) == 0)
      {
      $entrylink =  "No Entry";
      $rowcolor = "FFE5E5";
      $orphan = "yes";
      }
    
    if($row['Type'] == "picture")
      print "<tr style=\"background-color: {$rowcolor}\"><td><img src=\"../system/images/{$icon}\"></td><td><a href=\"{$row['Path']}\">{$row['Filename']}</a></td><td>". authorlookup($row['Owner']) ."</td><td>-</td><td>{$entrylink}</td><td><a href=\"javascript: void(0);\" onClick=\" deleteFile('{$row['ID']}');\"><img src=\"../system/images/delete.gif\"></a></td></tr>\n";
    
    elseif($row['Type'] == "video")
      print "<tr style=\"background-color: {$rowcolor}\"><td><img src=\"../system/images/{$icon}\"></td><td>Video on YouTube</td><td>". authorlookup($row['Owner']) ."</td><td>-</td><td>{$entrylink}</td><td><a href=\"javascript: void(0);\" onClick=\" deleteFile('{$row['ID']}');\"><img src=\"../system/images/delete.gif\"></a></td></tr>\n";
    
    else  
      {
      print "<tr style=\"background-color: {$rowcolor}\"><td><img src=\"../system/images/{$icon}\"></td><td><a href=\"../{$row['Path']}\">{$row['Filename']}.{$row['Type']}</a></td><td>". authorlookup($row['Owner']) ."</td><td>". round((filesize("../{$row['Path']}") / 1000)) ." KB</td><td>{$entrylink}</td><td>
";
      
      if(isset($orphan))
        print '<a href="attachfile.php?fileID='. $row['ID'] .'"><img src="../system/images/plus_icon.gif" alt="Attach"></a>';
      
      print "
<a href=\"javascript: void(0);\" onClick=\" deleteFile('{$row['ID']}');\"><img src=\"../system/images/delete.gif\"></a></td></tr>\n";
      }
    
    if($rowcolor == "#EDEDED")
      $rowcolor = "#FFF";
    else
      $rowcolor = "#EDEDED";
    
    unset($orphan);
    }
  }

print '
</table>
<br />';
}
?>
<p id="footer"><?php print get_table('SiteName'); ?> Administration - File Management. Last Updated: <?php print date("g:i a, n/j/y", (time() + get_table('dateoffset'))); ?></p>
</table>
</body>
</html>