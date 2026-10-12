<?php
if ( ! function_exists( 'wp_cache_clear_cache' ) ) { throw new RuntimeException( 'WP Super Cache purge unavailable' ); }
wp_cache_clear_cache();
echo "WP Super Cache cleared\n";
