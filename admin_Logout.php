<?php
session_start();
session_unset();
session_destroy();
header('Location: ADMIN_PAGES/loginpage_Admin.php');
exit();
?>
