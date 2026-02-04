<?php
function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void {
    header('Location: ' . $path);
    exit;
}

function base_path(): string {
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    $dir = rtrim(str_replace('\\', '/', dirname($script)), '/');
    return $dir === '/' ? '' : $dir;
}

function url(string $path): string {
    return base_path() . $path;
}
