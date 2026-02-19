<?php
require_once '../src/session.php';
destroySession();
header('Location: login.php');
exit;
