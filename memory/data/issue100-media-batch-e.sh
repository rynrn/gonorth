#!/usr/bin/env bash
set -euo pipefail
WP=(wp --path=/var/www/gonorth --allow-root);d=/var/www/gonorth/wp-content/uploads/gonorth-listing-illustrations;install -d -m 0755 "$d"
cp /tmp/issue100-e-google-photos/ein-2.jpg "$d/issue100-ein-hadayag-google.jpg"
a=$("${WP[@]}" media import "$d/issue100-ein-hadayag-google.jpg" --skip-copy --post_id=488 --title='עין עלומים — עין הדייג' --porcelain)
"${WP[@]}" post meta update "$a" _wp_attachment_image_alt 'בריכת עין עלומים עין הדייג בחורשת אקליפטוסים'
"${WP[@]}" post meta update "$a" _gonorth_image_role content
"${WP[@]}" post meta update "$a" _gonorth_google_photo_fetched_at '2026-10-01T21:45:00+03:00'
"${WP[@]}" post meta update "$a" _gonorth_google_photo_attribution_name 'Jamal Kiwan'
"${WP[@]}" post meta update "$a" _gonorth_google_photo_attribution_uri 'https://maps.google.com/maps/contrib/116454028586176072069'
"${WP[@]}" post meta update "$a" _gonorth_google_photo_name 'places/ChIJ0_yTaVihHhURH0kwZx0Y3hc/photos/Aa-ngMYNMAyuC_OXKGVFw15j5lKbyU2Kh9Eg62ra6MyyP7nDkrI5MeLjQoATpkZOauWczLxwaNZ6-2kshdnsvv9oEI_zx9kOdany4sIWUEEn-dfWL-GzyJnRXR0v_011Wm1SBfsFnde53b-2QgiKPUgN_tlGa5ry1n96ZXAUQwrkIjA7E9NFY41IQzFh0mtNJ8VOgYC0J2tK91AYOCMrz7_09mOiy86tI9LAW4FUNJnWig-ZMHNSxbBRyohNYQAVmodYJK767a9xdfJaSa6w8OnlJZYH9mp5XQgytrlbop1RDvlJRayY_VKpiYy9u1tjhGFGDqvwfpEukJ_vcVR_ym-QVIge1ZAvzhhEeRGmS61aVcC470Ffnh86BZK8H6w9CHzaJbl2sTMCxQ_1vvpCk68TJBuOWNpKj9M5jI3-OdxyR7EAag'
"${WP[@]}" post meta update 488 _thumbnail_id "$a"
echo "$a"

