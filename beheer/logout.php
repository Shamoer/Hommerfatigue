<?php
require __DIR__ . '/_bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
    logoutUser();
}
redirect(adminUrl('login.php'));
