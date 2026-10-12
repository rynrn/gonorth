#!/usr/bin/env bash
set -euo pipefail
check(){ name="$1"; url="$2"; marker="$3"; tmp=$(mktemp); code=$(curl -L -sS -o "$tmp" -w '%{http_code}' "$url"); canonical=$(grep -c 'rel="canonical"' "$tmp" || true); rtl=$(grep -c 'dir="rtl"' "$tmp" || true); image=$(grep -c 'gonorth-listing-illustrations' "$tmp" || true); text=$(grep -c "$marker" "$tmp" || true); printf '{"name":"%s","url":"%s","http":%s,"canonical":%s,"rtl":%s,"image":%s,"marker":%s}\n' "$name" "$url" "$code" "$canonical" "$rtl" "$image" "$text"; rm -f "$tmp"; }
check 'TRIO' 'https://gonorth.co.il/places/trio-%d7%98%d7%a8%d7%99%d7%95-%d7%a7%d7%a4%d7%94/' 'טריו קפה'
check 'שירי פסטה' 'https://gonorth.co.il/places/%d7%a9%d7%99%d7%a8%d7%99-%d7%a4%d7%a1%d7%98%d7%94/' 'שירי פסטה'
check 'קמפינג מטיילים דפנה' 'https://gonorth.co.il/places/%d7%a7%d7%9e%d7%a4%d7%99%d7%a0%d7%92-%d7%9e%d7%98%d7%99%d7%99%d7%9c%d7%99%d7%9d-%d7%93%d7%a4%d7%a0%d7%94/' 'קמפינג מטיילים דפנה'
check 'מלון מטיילים ארץ דפנה' 'https://gonorth.co.il/places/%d7%9e%d7%9c%d7%95%d7%9f-%d7%9e%d7%98%d7%99%d7%99%d7%9c%d7%99%d7%9d-%d7%90%d7%a8%d7%a5-%d7%93%d7%a4%d7%a0%d7%94/' 'מלון מטיילים ארץ דפנה'
check 'קלומפוס' 'https://gonorth.co.il/places/%d7%a7%d7%9c%d7%95%d7%9e%d7%a4%d7%95%d7%a1/' 'קלומפוס'
check 'הטנק הסורי ההפוך' 'https://gonorth.co.il/places/%d7%94%d7%98%d7%a0%d7%a7-%d7%94%d7%a1%d7%95%d7%a8%d7%99-%d7%94%d7%94%d7%a4%d7%95%d7%9a/' 'הטנק הסורי ההפוך'
check 'שמורת טבע הבניאס' 'https://gonorth.co.il/places/%d7%a9%d7%9e%d7%95%d7%a8%d7%aa-%d7%98%d7%91%d7%a2-%d7%94%d7%91%d7%a0%d7%99%d7%90%d7%a1/' 'הגשר התלוי'
