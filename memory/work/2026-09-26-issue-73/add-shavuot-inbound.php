<?php
$post = get_post(427);
$before = (string) $post->post_content;
$marker = 'gn73-shavuot-inbound';
if (false === strpos($before, $marker)) {
    $addition = '<!-- gn73-shavuot-inbound --><p>מתכננים חופשה סביב החג? עברו גם אל <a href="https://gonorth.co.il/shavuot-north-israel/">מדריך שבועות בצפון</a> למסלולים, חקלאות, יקבים ולינה.</p>';
    $after = rtrim($before) . "

" . $addition . "
";
    $result = wp_update_post(['ID' => 427, 'post_content' => $after], true);
    if (is_wp_error($result)) {
        echo wp_json_encode(['error' => $result->get_error_message()]);
        return;
    }
} else {
    $after = $before;
}
echo wp_json_encode(['ID' => 427, 'updated' => $after !== $before, 'before_hash' => hash('sha256', $before), 'after_hash' => hash('sha256', $after)], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
