<?php
//Search.php
//Search for blog entries and comments for deletion.
session_start();
if(!isset($_SESSION['familysite']))
  Header("Location: ../login.php?go=admin/index.php");

if($_SESSION['familysite'] != 1)
  Header("Location: ../index.php?error=permissiondenied");

include "../system/parse.php";
dbconnect();

if($_GET['keyword'] == "" OR $_GET['keyword'] == "Search...")
  die("Type a query to begin.");

if($_GET['mode'] == "blog")
  {
  $query = mysql_query("SELECT * FROM `". get_table('blog') ."` WHERE `ID` LIKE '%". sanitize($_GET['keyword']) ."%' OR `Title` LIKE '%". sanitize($_GET['keyword']) ."%' OR `Entry` LIKE '%". sanitize($_GET['keyword']) ."%' ORDER BY `Posted` DESC LIMIT 10;");
  
  print "<ul>\n";
  
  while($row = mysql_fetch_assoc($query))
    {
    $entrypreviewarray = explode(" ", strip_tags(blogitize($row['Entry'])));
    
    for($i = 0; $i < 30; $i++)
      $entrypreview .= " ". $entrypreviewarray[$i];
    
    $posted = $row['Posted'];
    
    if(date("n-j-Y", ($posted + get_table('dateoffset'))) == date("n-j-Y"))
      $timestamp = "today at ". date("g:i a", ($posted + get_table('dateoffset')));
    elseif(date("n-j-Y", ($posted + get_table('dateoffset'))) == date("n-j-Y", (time() - 86400)))
      $timestamp = "yesterday at ". date("g:i a", ($posted + get_table('dateoffset')));
    else
      $timestamp = date("\a\\t g:i a l, F jS, Y", ($posted + get_table('dateoffset')));
    
    print "<li><strong><a href=\"#blogview\" onClick=\" document.getElementById('blogview').innerHTML='Loading entry...'; document.getElementById('blogview').style.color='#666'; openEntry('{$row['ID']}'); document.getElementById('blogview').style.color='#000';\">". blogitize($row['Title']) ."</a></strong> <span style=\"color: #666\">{$timestamp} by ". authorlookup($row['Author']) ."</span><br />
". trim($entrypreview) ."...</li>\n";
    
    unset($entrypreview);
    }
  
  $results = mysql_num_rows(mysql_query("SELECT * FROM `". get_table('blog') ."` WHERE `ID` LIKE '%". sanitize($_GET['keyword']) ."%' OR `Title` LIKE '%". sanitize($_GET['keyword']) ."%' OR `Entry` LIKE '%". sanitize($_GET['keyword']) ."%' ORDER BY `Posted` DESC;"));
  
  if($results > 10)
    print "<li><em>There are ". ($results - 10) ." more results as well. Try refining your search.</em></li>";
  
  print "</ul>\n";
  
  die();
  }

if($_GET['mode'] == "comments")
  {
  $query = mysql_query("SELECT * FROM `". get_table('comments') ."` WHERE `ID` LIKE '%". sanitize($_GET['keyword']) ."%' OR `Comment` LIKE '%". sanitize($_GET['keyword']) ."%' ORDER BY `Posted` DESC LIMIT 10;");
  
  print "<ul class=\"comments\">\n";
  
  while($row = mysql_fetch_assoc($query))
    {
    $commentpreviewarray = explode(" ", strip_tags(blogitize($row['Comment'])));
    
    for($i = 0; $i < 30; $i++)
      $commentpreview .= " ". $commentpreviewarray[$i];
    
    $posted = $row['Posted'];
      
    if(date("n-j-Y", ($posted + get_table('dateoffset'))) == date("n-j-Y"))
      $timestamp = "today at ". date("g:i a", ($posted + get_table('dateoffset')));
    elseif(date("n-j-Y", ($posted + get_table('dateoffset'))) == date("n-j-Y", (time() - 86400)))
      $timestamp = "yesterday at ". date("g:i a", ($posted + get_table('dateoffset')));
    else
      $timestamp = date("\a\\t g:i a l, F jS, Y", ($posted + get_table('dateoffset')));
    
    $entryinfo = mysql_fetch_assoc(mysql_query("SELECT * FROM `". get_table('blog') ."` WHERE `ID` = {$row['EntryID']};"));
    
    print "<li><a href=\" #commentview\" onClick=\" document.getElementById('commentview').innerHTML='Loading comment...'; document.getElementById('commentview').style.color='#666'; openComment('{$row['ID']}'); document.getElementById('commentview').style.color='#000';\"><strong>". authorlookup($row['Author']) ."</strong> said</a> <span style=\"color: #666\">about <strong>". blogitize($entryinfo['Title']) ."</strong> {$timestamp}:</span><br />
". trim($commentpreview) ."</li>\n";
    
    unset($commentpreview);
    }
  
  $results = mysql_num_rows(mysql_query("SELECT * FROM `". get_table('comments') ."` WHERE `ID` LIKE '%". sanitize($_GET['keyword']) ."%' OR `Comment` LIKE '%". sanitize($_GET['keyword']) ."%' ORDER BY `Posted` DESC;"));
  
  if($results > 10)
    print "<li style=\"list-style: url('../system/images/bullet.gif')\"><em>There are ". ($results - 10) ." more results as well. Try refining your search.</em></li>";
  
  print "</ul>\n";
  
  die();
  }

if($_GET['mode'] == 'recentblog')
  {
  $query = mysql_query("SELECT * FROM `". get_table('blog') ."` ORDER BY `Posted` DESC LIMIT 15;");
  
  print "<ul>\n";
  
    while($row = mysql_fetch_assoc($query))
    {
    $entrypreviewarray = explode(" ", blogitize($row['Entry']));
    
    for($i = 0; $i < 30; $i++)
      $entrypreview .= " ". $entrypreviewarray[$i];
    
    print "<li><strong><a href=\"#blogview\" onClick=\" document.getElementById('blogview').innerHTML='Loading entry...'; document.getElementById('blogview').style.color='#666'; openEntry('{$row['ID']}'); document.getElementById('blogview').style.color='#000';\">". blogitize($row['Title']) ."</a></strong> <span style=\"color: #666\">". date("n/j/Y", ($row['Posted'] + get_table('dateoffset'))) ." by ". authorlookup($row['Author']) ."</span><br />
". trim($entrypreview) ."...</li>\n";
    
    unset($entrypreview);
    }
  
  print "</ul>\n";
  
  die();
  }

if($_GET['mode'] == 'recentcomments')
  {
  $query = mysql_query("SELECT * FROM `". get_table('comments') ."` ORDER BY `Posted` DESC LIMIT 15;");
  
  print "<ul>\n";
  
    while($row = mysql_fetch_assoc($query))
    {
    $commentpreviewarray = explode(" ", strip_tags(blogitize($row['Comment'])));
    
    for($i = 0; $i < 30; $i++)
      $commentpreview .= " ". $commentpreviewarray[$i];
    
    $posted = $row['Posted'];
      
    if(date("n-j-Y", ($posted + get_table('dateoffset'))) == date("n-j-Y"))
      $timestamp = "today at ". date("g:i a", ($posted + get_table('dateoffset')));
    elseif(date("n-j-Y", ($posted + get_table('dateoffset'))) == date("n-j-Y", (time() - 86400)))
      $timestamp = "yesterday at ". date("g:i a", ($posted + get_table('dateoffset')));
    else
      $timestamp = date("\a\\t g:i a l, F jS, Y", ($posted + get_table('dateoffset')));
    
    $entryinfo = mysql_fetch_assoc(mysql_query("SELECT * FROM `". get_table('blog') ."` WHERE `ID` = {$row['EntryID']};"));
    
    print "<li><a href=\" #commentview\" onClick=\" document.getElementById('commentview').innerHTML='Loading comment...'; document.getElementById('commentview').style.color='#666'; openComment('{$row['ID']}'); document.getElementById('commentview').style.color='#000';\"><strong>". authorlookup($row['Author']) ."</strong> said</a> <span style=\"color: #666\">about <strong>". blogitize($entryinfo['Title']) ."</strong> {$timestamp}:</span><br />
". trim($commentpreview) ."...</li>\n";
    
    unset($commentpreview);
    }
  
  print "</ul>\n";
  
  die();
  }
?>