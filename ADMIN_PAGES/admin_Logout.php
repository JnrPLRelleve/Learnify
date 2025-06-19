<?php
session_start();
session_unset();
session_destroy();
echo "<script>alert('You have been logged out successfully.');</script>";
header('Refresh: 0; url=loginpage_Admin.php');
header('Location: loginpage_Admin.php');
exit();
?>
