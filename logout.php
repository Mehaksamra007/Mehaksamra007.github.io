<?php
require_once 'auth_functions.php';
logoutUser();
header("Location: index.php");
exit;
?>