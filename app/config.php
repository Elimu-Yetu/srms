<?php

define('DB_DRIVER', 'mysql');

// SQLite: file lives outside the pages that get served.
define('DB_SQLITE_PATH', __DIR__ . '/../storage/database/elimu_srms.sqlite');

// MySQL / MariaDB settings (ignored when DB_DRIVER is 'sqlite')
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'elimu_srms');
define('DB_USER', 'srms');
define('DB_PASS', 'eydo');

// ── Application ─────────────────────────────────────────────────────────────
define('APP_NAME',      'ElimuYetu SRMS');
define('APP_VERSION',   '1.0.0');
define('ORG_NAME',      'ELIMU YETU ORGANIZATION');
define('ORG_MOTTO',     'Ninaweza, nitafanya, najiamini');
define('SESSION_IDLE_MINUTES', 30);   // auto logout after inactivity
define('MAX_LOGIN_TRIES', 5);         // then a 10-minute cool-off
define('STUDENT_NO_PREFIX', 'EY');    // -> EY-2026-0001
define('TIMEZONE', 'Africa/Dar_es_Salaam');

// ── Paths ───────────────────────────────────────────────────────────────────
define('BASE_PATH',    dirname(__DIR__));
define('STORAGE_PATH', BASE_PATH . '/storage');
define('UPLOAD_PATH',  STORAGE_PATH . '/uploads/photos');

// Set to true only while developing — shows PHP errors on screen.
define('DEBUG', true);
