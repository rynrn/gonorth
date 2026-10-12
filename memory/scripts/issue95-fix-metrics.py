from pathlib import Path
import re
p=Path('wordpress/plugins/gonorth-place-data/includes/class-gonorth-corrections.php')
s=p.read_text(encoding='utf-8')
pattern=r"strtotime\( \(string\) get_post_meta\( (\$post(?:->ID|_id)), '(_gn_[^']+)', true \) \. ' UTC' \)"
s,n=re.subn(pattern,lambda m:"self::meta_utc_timestamp( "+m.group(1)+", '"+m.group(2)+"' )",s)
assert n==5,n
p.write_text(s,encoding='utf-8')
b=Path('wordpress/plugins/gonorth-place-data/gonorth-place-data.php')
t=b.read_text(encoding='utf-8').replace('1.18.6','1.18.7')
b.write_text(t,encoding='utf-8')