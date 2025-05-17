<?php
session_start();
session_unset();
session_destroy();
header("Location: /PTH_WEB/login");
exit();
?>