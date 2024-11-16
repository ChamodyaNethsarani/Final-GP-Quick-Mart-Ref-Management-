<?php
session_start();
session_unset();
session_destroy();

// Redirect to login page after logging out
header("Location: login.php");
exit();
?>
