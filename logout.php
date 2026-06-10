<?php
require_once __DIR__ . '/includes/functions.php';
if (is_logged_in()) { logout_user(); flash_set('info', '已登出，期待再見。'); }
redirect('index.php');
