<?php
// Logout Handler
session_start();
session_destroy();
header('Location: ../signin.html');
exit;
?> 