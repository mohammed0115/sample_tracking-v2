<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$supported = ['ar', 'en'];
$default = 'ar';

if (isset($_GET['lang']) && in_array($_GET['lang'], $supported, true)) {
    $_SESSION['lang'] = $_GET['lang'];
}

$lang = $_SESSION['lang'] ?? $default;
if (!in_array($lang, $supported, true)) {
    $lang = $default;
}

$path = __DIR__ . '/../lang/' . $lang . '.php';
$fallback = __DIR__ . '/../lang/' . $default . '.php';

$translations = file_exists($path) ? include $path : [];
$fallbackTranslations = file_exists($fallback) ? include $fallback : [];

function current_lang(): string {
    return $_SESSION['lang'] ?? 'ar';
}

function is_rtl(): bool {
    return current_lang() === 'ar';
}

function __(string $key, array $replace = []): string {
    global $translations, $fallbackTranslations;
    $value = $translations[$key] ?? $fallbackTranslations[$key] ?? $key;
    foreach ($replace as $k => $v) {
        $value = str_replace(':' . $k, (string)$v, $value);
    }
    return $value;
}
