<?php
core::$module['account']->logoutUser();
header('location: login.php');
?>