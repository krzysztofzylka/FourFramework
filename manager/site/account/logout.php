<?php
unset($_SESSION['userID']);
session_destroy();
header('location: index.php');
?>