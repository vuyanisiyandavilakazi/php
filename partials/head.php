<?php
/**
 * Port of the <head> portion of src/app/layout.tsx: metadata, viewport,
 * favicons, organization/website JSON-LD schema.
 *
 * Expects $pageTitle, $pageDescription, $canonicalPath (e.g. '/communities')
 * to optionally be set before including this.
 */
$pageTitle       = $pageTitle       ?? '';
$pageDescription = $pageDescription ?? SQ_SITE_DESC;
$canonicalPath   = $canonicalPath   ?? '/';
$canonicalUrl    = rtrim(SQ_SITE_URL, '/') . $canonicalPath;

$organizationSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'Organization',
    '@id' => SQ_SITE_URL . '/#organization',
    'name' => 'squids.',
    'alternateName' => 'squids.',
    'legalName' => 'squids.',
    'url' => SQ_SITE_URL,
    'logo' => ['@type' => 'ImageObject', 'url' => SQ_SITE_URL . '/brand/mark.svg', 'width' => 512, 'height' => 512],
    'sameAs' => ['https://twitter.com/squids101_za', 'https://x.com/squids101_za'],
];
$websiteSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'WebSite',
    '@id' => SQ_SITE_URL . '/#website',
    'name' => 'squids.',
    'url' => SQ_SITE_URL,
    'potentialAction' => [
        '@type' => 'SearchAction',
        'target' => SQ_SITE_URL . '/search?q={search_term_string}',
        'query-input' => 'required name=search_term_string',
    ],
];
?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, viewport-fit=cover">
<meta name="theme-color" content="#080810" media="(prefers-color-scheme: dark)">
<meta name="theme-color" content="#f0f0f8" media="(prefers-color-scheme: light)">
<title><?= htmlspecialchars(sq_page_title($pageTitle)) ?></title>
<meta name="description" content="<?= htmlspecialchars($pageDescription) ?>">
<meta name="application-name" content="squids.">
<meta name="google" content="notranslate">
<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
<link rel="canonical" href="<?= htmlspecialchars($canonicalUrl) ?>">

<meta property="og:site_name" content="squids.">
<meta property="og:locale" content="en_ZA">
<meta property="og:type" content="website">
<meta property="og:url" content="<?= htmlspecialchars($canonicalUrl) ?>">
<meta property="og:title" content="<?= htmlspecialchars(sq_page_title($pageTitle)) ?>">
<meta property="og:description" content="<?= htmlspecialchars($pageDescription) ?>">
<meta property="og:image" content="<?= SQ_SITE_URL ?>/assets/og-default.jpg">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:site" content="@squids101_za">
<meta name="twitter:creator" content="@squids101_za">
<meta name="twitter:title" content="<?= htmlspecialchars(sq_page_title($pageTitle)) ?>">
<meta name="twitter:description" content="<?= htmlspecialchars($pageDescription) ?>">
<meta name="twitter:image" content="<?= SQ_SITE_URL ?>/assets/og-default.jpg">

<link rel="manifest" href="/site.webmanifest">
<link rel="icon" href="/brand/mark.svg" type="image/svg+xml">
<link rel="icon" href="/assets/favicon-32.png" sizes="32x32" type="image/png">
<link rel="icon" href="/assets/favicon-16.png" sizes="16x16" type="image/png">
<link rel="apple-touch-icon" href="/assets/apple-touch-icon.png" sizes="180x180">
<link rel="shortcut icon" href="/brand/mark.svg">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=Fraunces:opsz,wght@9..144,400;9..144,600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

<link rel="stylesheet" href="/globals.css">
<link rel="stylesheet" href="/app.css">

<script type="application/ld+json" nonce="<?= htmlspecialchars($SQ_NONCE) ?>"><?= json_encode($organizationSchema, JSON_UNESCAPED_SLASHES) ?></script>
<script type="application/ld+json" nonce="<?= htmlspecialchars($SQ_NONCE) ?>"><?= json_encode($websiteSchema, JSON_UNESCAPED_SLASHES) ?></script>
