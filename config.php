<?php
declare(strict_types=1);

const SITE_NAME = 'Угур';
const SITE_TAGLINE = 'Автосервис в Струнино';
const SITE_PHONE = '+7 (905) 649-35-47';
const SITE_PHONE_TEL = '+79056493547';
const SITE_ADDRESS = 'Владимирская область, Александровский муниципальный округ, Струнино, улица Фрунзе, 15А';
const SITE_SHORT_ADDRESS = 'ул. Фрунзе, 15А, Струнино';
const SITE_WORK_HOURS = 'ежедневно, 09:00–20:00';
const SITE_MAP_URL = 'https://yandex.ru/maps/-/CTA9rQMB';
const SITE_LATITUDE = 56.371456;
const SITE_LONGITUDE = 38.588130;
const SITE_RATING_VALUE = '5.0';
const SITE_RATING_COUNT = 62;
const SITE_REVIEW_COUNT = 34;
const SITE_VERIFIED_DATE = '19 июня 2026';

const LEADS_CSV = __DIR__ . '/storage/leads.csv';
const RATE_LIMIT_FILE = __DIR__ . '/storage/rate-limit.json';
const LEAD_EMAIL = '';

function site_url(string $path = ''): string
{
    $configured = rtrim((string) getenv('SITE_URL'), '/');

    if ($configured !== '') {
        return $configured . '/' . ltrim($path, '/');
    }

    $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';

    return rtrim($scheme . '://' . $host, '/') . '/' . ltrim($path, '/');
}

function escape_html(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_token(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return (string) $_SESSION['csrf_token'];
}

function is_ajax_request(): bool
{
    return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest'
        || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
}
