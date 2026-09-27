<?php
/**
 * Port of src/lib/netlifyGeo.ts, adapted for Awardspace/generic hosting.
 * Netlify's x-nf-geo / x-nf-client-connection-ip headers don't exist off
 * Netlify, so this keeps only the parts that still make sense: pulling a
 * real public IP out of X-Forwarded-For, and an optional X-Country header
 * if your reverse proxy / CDN sets one (e.g. Cloudflare's CF-IPCountry).
 */

function sq_is_private_or_internal_ip(string $ip): bool {
    if ($ip === '') return true;
    if ($ip === '127.0.0.1' || $ip === '::1') return true;

    if (preg_match('/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/', $ip, $m)) {
        $a = (int) $m[1];
        $b = (int) $m[2];
        if ($a === 10) return true;
        if ($a === 172 && $b >= 16 && $b <= 31) return true;
        if ($a === 192 && $b === 168) return true;
        if ($a === 169 && $b === 254) return true;
        if ($a === 100 && $b >= 64 && $b <= 127) return true;
        if ($a === 0) return true;
        return false;
    }

    $lower = strtolower($ip);
    if (str_starts_with($lower, 'fc') || str_starts_with($lower, 'fd')) return true;
    if (str_starts_with($lower, 'fe80')) return true;
    return false;
}

/** @return array{ip: string, country: string} */
function sq_read_geo(): array {
    $ip = '';
    $country = '';

    // Cloudflare sets this if you're behind it; harmless no-op otherwise.
    if (!empty($_SERVER['HTTP_CF_IPCOUNTRY'])) {
        $country = strtoupper($_SERVER['HTTP_CF_IPCOUNTRY']);
    } elseif (!empty($_SERVER['HTTP_X_COUNTRY'])) {
        $country = strtoupper($_SERVER['HTTP_X_COUNTRY']);
    }

    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $chain = array_map('trim', explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
        foreach ($chain as $candidate) {
            if ($candidate !== '' && !sq_is_private_or_internal_ip($candidate)) {
                $ip = $candidate;
                break;
            }
        }
    }
    if ($ip === '' && !empty($_SERVER['REMOTE_ADDR']) && !sq_is_private_or_internal_ip($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    return ['ip' => $ip, 'country' => $country];
}
