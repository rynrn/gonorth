<?php
if (!function_exists('wc_get_orders')) {
    echo json_encode(array('woocommerce_available' => false));
    return;
}
$out = array('woocommerce_available' => true, 'counts' => array());
foreach (array('pending', 'processing', 'completed', 'on-hold', 'cancelled', 'refunded', 'failed') as $status) {
    $result = wc_get_orders(array('status' => $status, 'limit' => 1, 'paginate' => true, 'return' => 'ids'));
    $out['counts'][$status] = is_object($result) && isset($result->total) ? (int) $result->total : null;
}
echo wp_json_encode($out);
