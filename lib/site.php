<?php
/** Port of the constants in src/lib/site.ts / layout.tsx metadata block */

const SQ_SITE_NAME = 'squids.';
const SQ_SITE_URL  = 'https://squids.co.za';
const SQ_SITE_DESC = "South Africa's free community forum. Join communities, share posts, and connect with people who get it. Proudly built for South Africans.";

function sq_page_title(string $title): string {
    return $title === '' ? SQ_SITE_NAME . ' — South Africa\'s Free Community Forum' : "{$title} | " . SQ_SITE_NAME;
}

/** Port of ago() in src/lib/utils.ts */
function sq_ago(int $ts): string {
    $d = time() - $ts;
    if ($d < 60) return 'just now';
    if ($d < 3600) return floor($d / 60) . 'm ago';
    if ($d < 86400) return floor($d / 3600) . 'h ago';
    if ($d < 604800) return floor($d / 86400) . 'd ago';
    return date('j M Y', $ts);
}

/** Port of num() in src/lib/utils.ts */
function sq_num(int $n): string {
    $abs = abs($n);
    if ($abs >= 1000000) return round($n / 1000000, 1) . 'M';
    if ($abs >= 1000) return round($n / 1000, 1) . 'K';
    return (string) $n;
}
