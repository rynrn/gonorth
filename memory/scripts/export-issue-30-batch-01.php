<?php
$out = array();
foreach ( array( 56, 169, 171 ) as $id ) {
	$out[] = array(
		'id' => $id,
		'title' => get_the_title( $id ),
		'url' => get_permalink( $id ),
		'post_content' => get_post_field( 'post_content', $id ),
		'post_excerpt' => get_post_field( 'post_excerpt', $id ),
		'data' => GoNorth_Place_Data::get( $id ),
	);
}
echo wp_json_encode( $out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
