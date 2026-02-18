<?php
session_start();

// সব session data clear
$_SESSION = [];

// session destroy
session_destroy();

// 🔁 Landing page এ redirect
header("Location: http://localhost/dashboard/");
exit;
