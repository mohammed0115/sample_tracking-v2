<?php
// Application Configuration
define('APP_NAME', 'Sample Tracking System');
define('APP_URL', 'http://localhost/php');
define('UPLOAD_DIR', __DIR__ . '/../public/uploads/');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB

// Session Configuration
define('SESSION_LIFETIME', 7200); // 2 hours

// Pagination
define('RECORDS_PER_PAGE', 10);

// Date Format
define('DATE_FORMAT', 'Y-m-d');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');

// Supported Languages
define('SUPPORTED_LANGUAGES', ['ar', 'en']);
define('DEFAULT_LANGUAGE', 'ar');

// Timezone
date_default_timezone_set('UTC');
