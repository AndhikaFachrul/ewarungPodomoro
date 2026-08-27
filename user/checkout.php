<?php
require_once __DIR__ . '/../functions/security.php';

check_login();
header('Location: cart.php');
exit();
