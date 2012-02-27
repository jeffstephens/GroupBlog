<link type="text/css" href="../system/style.css" rel="stylesheet">
<link type="text/css" href="help.css" rel="stylesheet">
<style type="text/css">
.mp3, .m4a {
list-style: url('icons/music.gif') }

.zip {
list-style: url('icons/zip.gif') }

.php, .html, .htm {
list-style: url('icons/webpage.gif') }

.css {
list-style: url('icons/css.gif') }

.jpg, .jpeg {
list-style: url('icons/jpg.gif') }

.js {
list-style: url('icons/js.gif') }

.bmp, .gif, .png {
list-style: url('icons/img.gif') }

.txt {
list-style: url('icons/txt.gif') }

.doc, .docx {
list-style: url('icons/doc.gif') }
</style>

<?php
if(isset($_GET['uploadfile']))
  {
  $filenameparts = explode(".", urlencode($_FILES['file']['name']));
  
  if(move_uploaded_file($_FILES['file']['tmp_name'], "uploads/" . $filenameparts[0] . time() . "." . $filenameparts[1]))
    print '<p class="success"><strong>'. $_FILES['file']['name'] .'</strong> ('. round($_FILES['file']['size'] / 1000) .' KB) was successfully uploaded. <a href="upload.php">View File Listing &raquo;</a></p>';
  else
    print '<p class="error"><strong>'. $_FILES['file']['name'] .'</strong> ('. round($_FILES['file']['size'] / 1000) .' KB) could not be uploaded due to an error. <a href="upload.php">View File Listing &raquo;</a></p>';
  }

else
  {
  print '<ul>
';
  $handle = opendir("uploads");

  while($file = readdir($handle))
    {
    $filename = explode(".", $file);
      $filetype = $filename[1];
      $filename = $filename[0];
    
    if(!is_dir($file))
      print "<li class=\"{$filetype}\">{$file}</li>\n";
    }

  print '</ul>
';
  }
?>