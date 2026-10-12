from pathlib import Path
root = Path("memory/work/issue93-navigation")
f = root / "functions.staged.php"
data = f.read_bytes()
nl = b"\r\n" if b"\r\n" in data else b"\n"
replacements = [
    (
        b"    if ( ! $is_closed && $has_valid_location ) echo '<a href=\"https://www.google.com/maps?q='",
        b"    if ( ! $is_closed && $has_valid_location && $waze_allowed ) echo '<a href=\"https://www.google.com/maps?q='",
    ),
    (
        b"    echo '</div></nav>';\n    if ( $is_closed ) {".replace(b"\n", nl),
        (
            "    echo '</div></nav>';\n"
            "    if ( ! $is_closed && $has_valid_location && ! $waze_allowed ) {\n"
            "        echo '<p class=\"gn-place-overview__limit\">המפה מסמנת את מיקום המקום בלבד. אין יעד ניווט מאומת לרכב; בדקו את הוראות ההגעה בעמוד לפני הנסיעה.</p>';\n"
            "    }\n"
            "    if ( $is_closed ) {"
        ).encode("utf-8").replace(b"\n", nl),
    ),
]
for old, new in replacements:
    if data.count(old) != 1: raise SystemExit(f"functions: expected one match, got {data.count(old)}")
    data = data.replace(old, new)
f.write_bytes(data)
s = root / "single-gd_place.staged.php"
data = s.read_bytes()
nl = b"\r\n" if b"\r\n" in data else b"\n"
old = b"          </div>\n\n          <div class=\"gn-booking-card__share\">".replace(b"\n", nl)
new = (
    "          </div>\n"
    "          <?php if (!$waze_allowed) : ?>\n"
    "          <p>המפה מסמנת את מיקום המקום בלבד. אין יעד ניווט מאומת לרכב; בדקו את הוראות ההגעה בעמוד לפני הנסיעה.</p>\n"
    "          <?php endif; ?>\n\n"
    "          <div class=\"gn-booking-card__share\">"
).encode("utf-8").replace(b"\n", nl)
if data.count(old) != 1: raise SystemExit(f"single: expected one match, got {data.count(old)}")
data = data.replace(old, new)
s.write_bytes(data)
print("staged notice and suppressed Google Maps action for flagged listings")
