from pathlib import Path
root = Path("memory/work/issue93-navigation")
targets = [
    (
        "functions",
        [
            (
                "    $has_valid_location = is_numeric( $lat ) && is_numeric( $lng ) && (float) $lat > 29 && (float) $lat < 34 && (float) $lng > 34 && (float) $lng < 36;\n",
                "    $has_valid_location = is_numeric( $lat ) && is_numeric( $lng ) && (float) $lat > 29 && (float) $lat < 34 && (float) $lng > 34 && (float) $lng < 36;\n"
                "    // Some venues have a map pin but no verified driving destination.\n"
                "    $waze_allowed = get_post_meta( $post_id, '_gonorth_hide_driving_navigation', true ) !== '1';\n",
            ),
            (
                "    if ( ! $is_closed && ( $has_valid_location || $phone ) ) {\n",
                "    if ( ! $is_closed && ( ( $has_valid_location && $waze_allowed ) || $phone ) ) {\n",
            ),
            (
                "        if ( $has_valid_location ) echo '<a href=\"https://waze.com/ul?ll=' . esc_attr( $lat ) . ',' . esc_attr( $lng ) . '&navigate=yes\" target=\"_blank\" rel=\"noopener\" class=\"gn-single-action gn-single-action--primary\">ניווט ב-Waze</a>';\n",
                "        if ( $has_valid_location && $waze_allowed ) echo '<a href=\"https://waze.com/ul?ll=' . esc_attr( $lat ) . ',' . esc_attr( $lng ) . '&navigate=yes\" target=\"_blank\" rel=\"noopener\" class=\"gn-single-action gn-single-action--primary\">ניווט ב-Waze</a>';\n",
            ),
        ],
    ),
    (
        "single-gd_place",
        [
            (
                "$lng        = get_post_meta($post_id, 'geodir_post_longitude', true);\n",
                "$lng        = get_post_meta($post_id, 'geodir_post_longitude', true);\n"
                "$waze_allowed = get_post_meta($post_id, '_gonorth_hide_driving_navigation', true) !== '1';\n",
            ),
            (
                "            <a href=\"https://www.waze.com/ul?ll=<?php echo esc_attr($lat); ?>%2C<?php echo esc_attr($lng); ?>&navigate=yes\"\n",
                "            <?php if ($waze_allowed) : ?>\n"
                "            <a href=\"https://www.waze.com/ul?ll=<?php echo esc_attr($lat); ?>%2C<?php echo esc_attr($lng); ?>&navigate=yes\"\n",
            ),
            (
                "              נווט ב-Waze\n            </a>\n",
                "              נווט ב-Waze\n            </a>\n            <?php endif; ?>\n",
            ),
        ],
    ),
]
for stem, replacements in targets:
    source = root / f"{stem}.before.php"
    destination = root / f"{stem}.staged.php"
    data = source.read_bytes()
    line_ending = b"\r\n" if b"\r\n" in data else b"\n"
    for old, new in replacements:
        old_bytes = old.encode("utf-8").replace(b"\n", line_ending)
        new_bytes = new.encode("utf-8").replace(b"\n", line_ending)
        count = data.count(old_bytes)
        if count != 1:
            raise SystemExit(f"{stem}: expected one match, got {count}")
        data = data.replace(old_bytes, new_bytes)
    destination.write_bytes(data)
    print(destination, len(data))
