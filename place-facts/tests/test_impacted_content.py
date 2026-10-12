import importlib.util, unittest
from pathlib import Path
P=Path(__file__).parents[1]/"scripts"/"impacted-content.py"; S=importlib.util.spec_from_file_location("impacted_content",P); M=importlib.util.module_from_spec(S); S.loader.exec_module(M)
class ImpactedContentTests(unittest.TestCase):
    def test_finds_url_or_name_and_skips_source_listing(self):
        changes=[{"wordpress_id":173,"name":"אגמון החולה","url":"https://gonorth.co.il/places/agamon/","field":"business_hours"}]
        rows=[{"id":173,"type":"gd_place","title":"אגמון החולה","content":"אגמון החולה"},{"id":432,"type":"post","title":"מדריך","url":"https://gonorth.co.il/guide/","content":"ראו אגמון החולה"},{"id":500,"type":"page","title":"אזור","url":"https://gonorth.co.il/area/","content":"https://gonorth.co.il/places/agamon/"}]
        result=M.find_impacts(changes,rows); self.assertEqual({432,500},{row["content_id"] for row in result})
if __name__=="__main__": unittest.main()
