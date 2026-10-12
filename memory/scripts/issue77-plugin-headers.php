<?php
require_once ABSPATH . 'wp-admin/includes/plugin.php';
$items = array();
foreach ( get_plugins() as $path => $data ) {
    $items[] = array(
        'path' => $path,
        'name' => $data['Name'] ?? '',
        'license' => $data['License'] ?? '',
        'requires_plugins' => $data['RequiresPlugins'] ?? '',
        'requires_wp' => $data['RequiresWP'] ?? '',
        'requires_php' => $data['RequiresPHP'] ?? '',
    );
}
echo wp_json_encode( $items, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
