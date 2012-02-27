<?php
session_start();
unset($_SESSION['familysite']);
Header("Location: index.php");
?>