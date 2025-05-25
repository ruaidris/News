<?php
// init.php

// افتح الجلسة مرة واحدة
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// استدعِ db.php لمرة واحدة فقط
require_once __DIR__ . '/db.php';
