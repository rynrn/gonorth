<?php
$key = get_option('gonorth_google_server_api_key');
if (!is_string($key) || strlen(trim($key)) < 30) {
    fwrite(STDERR, "key_missing_or_invalid_length
");
    exit(2);
}
$response = wp_remote_post(
    'https://places.googleapis.com/v1/places:searchText',
    [
        'timeout' => 20,
        'headers' => [
            'Content-Type' => 'application/json',
            'X-Goog-Api-Key' => trim($key),
            'X-Goog-FieldMask' => 'places.id,places.displayName,places.formattedAddress',
        ],
        'body' => wp_json_encode([
            'textQuery' => 'יקב הר אודם',
            'languageCode' => 'he',
            'regionCode' => 'IL',
            'maxResultCount' => 1,
        ]),
    ]
);
if (is_wp_error($response)) {
    echo "transport_error
";
    exit(3);
}
$code = (int) wp_remote_retrieve_response_code($response);
$body = json_decode(wp_remote_retrieve_body($response), true);
$count = is_array($body['places'] ?? null) ? count($body['places']) : 0;
$error_status = is_array($body['error'] ?? null) ? (string) ($body['error']['status'] ?? 'unknown') : 'none';
echo "http_code={$code}
";
echo "place_count={$count}
";
echo "error_status={$error_status}
";
exit(($code === 200 && $count >= 1) ? 0 : 4);
