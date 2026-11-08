<?php

$proxies = trim((string) env('TRUSTED_PROXIES', ''));

return [
    // Set * only behind Azure App Service's managed ingress. Local requests
    // trust no proxy by default. Explicit IP addresses/CIDRs are also supported.
    // Forwarded host/prefix headers are deliberately not trusted.
    'proxies' => $proxies === '*'
        ? '*'
        : array_values(array_filter(array_map('trim', explode(',', $proxies)))),
];
