#!/usr/bin/env bash
set -euo pipefail
WP=(wp --path=/var/www/gonorth --allow-root)
dir=/var/www/gonorth/wp-content/uploads/gonorth-listing-illustrations
install -d -m 0755 "$dir"
cp /tmp/issue100-b-google-photos/funraider-1.jpg "$dir/issue100-funraider-google.jpg"
cp /tmp/issue100-b-google-photos/nicetomeat-3.jpg "$dir/issue100-nicetomeat-google.jpg"
fun_id=$("${WP[@]}" media import "$dir/issue100-funraider-google.jpg" --skip-copy --post_id=476 --title='פאן ריידר בצפון — טיול ריידרים בעמק החולה' --porcelain)
nice_id=$("${WP[@]}" media import "$dir/issue100-nicetomeat-google.jpg" --skip-copy --post_id=477 --title='Nice to Meat — מסעדה בצומת יסוד המעלה' --porcelain)
"${WP[@]}" post meta update "$fun_id" _wp_attachment_image_alt 'קבוצת מטיילים בריידרים של פאן ריידר בצפון בעמק החולה'
"${WP[@]}" post meta update "$nice_id" _wp_attachment_image_alt 'מנות במסעדת Nice to Meat בצומת יסוד המעלה'
for id in "$fun_id" "$nice_id"; do
  "${WP[@]}" post meta update "$id" _gonorth_image_role content
  "${WP[@]}" post meta update "$id" _gonorth_google_photo_fetched_at '2026-10-01T19:47:00+03:00'
done
"${WP[@]}" post meta update "$fun_id" _gonorth_google_photo_attribution_name 'פאן ריידר בצפון'
"${WP[@]}" post meta update "$fun_id" _gonorth_google_photo_attribution_uri 'https://maps.google.com/maps/contrib/112010401957027261337'
"${WP[@]}" post meta update "$fun_id" _gonorth_google_photo_name 'places/ChIJs8iEj3KhHhURvORrh6SY09E/photos/Aa-ngMZJpZ0yHZcWCmjcV2JVfo7JXv1yhD1q_KvPzQDARyWEiW3Cu4tfL-uvb8-ro-MekOdus3wK8Ba5bvXHuvHJKqWbcgt76cu-7r85OSn6HZ-DZfSPmKJ2u3v4DczZr4m0HssQq4DeACpllfLg-hhVM7pYT_KoJe1WpZ1iouye_YWarn-ePQgrltPsYlJ893TrEJv6_V-u8OvrAikcjBUTBebvnPOcfx7CrDdtXkwMUh0e_2Q_8oy7dq88GnXHFLFGaIgoIe0fhZuvHnJ1I-nmnQ_786GSIq-cIE9XaaAmm_BXFERlL-3_aOpliXvgXK9bqt6rP12FQRxCz6-dvjfGAWjLmTx_QNxmSybD23OrWdBipf0RBZd1WBLiNQYI_Mjg5x40LCboxiRTqhRskBeWjNBAlg'
"${WP[@]}" post meta update "$nice_id" _gonorth_google_photo_attribution_name 'Nice to meat'
"${WP[@]}" post meta update "$nice_id" _gonorth_google_photo_attribution_uri 'https://maps.google.com/maps/contrib/118252933833320610946'
"${WP[@]}" post meta update "$nice_id" _gonorth_google_photo_name 'places/ChIJT1nuvL8fHBURJh8BTXoSWQQ/photos/Aa-ngMYd3wREPGt9Jqz4WzBC0_qTnZ8Kd1qh1_A4v9Pxrh17DTGjsMWjzMb8aRwpeysDHhpqpWdA6cyMh9eQLnhcHUxVqXBMZoonGFvmapgTL7f5rM2gk72MoNDCvOmvVrtoMrh45Itfv4h9-awgIyEO9Su0G86GqFyWW_FdBTosABh4Ei0am0Fwg89EbJLCNVKcg6HuFM9IQEFrJlm81A1E-WY-HAMVcL9oGMazN13Iov64SUJH5FraCjWzFFRdDhwAQXgedZpFNmYgKSPm6prPPvYYvA5naGd1FpGfv7SyCLC_0NbFRYklr0fE3B8GrQDBGZf6ZIfYMNEiKZkuwtsyc4VpGPCPa1-rzEAxWSCWFNrg4Wgk9iADWB5EE4tJ9esJfq9cnd0e3gyE22jtmr2Dtpn_3GwnfJJqHEoiS4px6EeuSYGkNIBeMRruHRc5RZMU'
"${WP[@]}" post meta update 476 _thumbnail_id "$fun_id"
"${WP[@]}" post meta update 477 _thumbnail_id "$nice_id"
printf '{"funraider_attachment":%s,"nicetomeat_attachment":%s}\n' "$fun_id" "$nice_id"

