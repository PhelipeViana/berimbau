<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/services/auth.php';

api_auth_logout();

if (api_is_htmx()) {
    header('HX-Redirect: ../login_view.php');
    exit;
}

header('Location: ../login_view.php');
exit;
