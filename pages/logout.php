<?php
session_start();
session_destroy();
CYGNUSX.logout();
header('Location: ?page=home');
exit;
