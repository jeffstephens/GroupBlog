<?php
// Initialization for private pages
if(!isset($_SESSION['familysite']))
  Header("Location: ". get_config('publicurl') ."/login.php?go=". get_config('publicurl') ."admin/index.php");

if($_SESSION['familysite'] != 1)
  Header("Location: ". get_config('publicurl') ."/index.php?error=permissiondenied");

dbconnect();
?>