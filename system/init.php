<?php
// Initialization for private pages

session_start();
include "../system/parse.php";

if(!isset($_SESSION['familysite']))
  Header("Location: ". get_config('publicurl') ."/login.php?go=". get_config('publicurl') ."admin/index.php");

if($_SESSION['familysite'] != 1)
  Header("Location: ". get_config('publicurl') ."/index.php?error=permissiondenied");

dbconnect();
?>