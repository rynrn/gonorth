import importlib.util, unittest
from pathlib import Path
P=Path(__file__).parents[1]/"validate-place-records.py"; S=importlib.util.spec_from_file_location("validator",P); M=importlib.util.module_from_spec(S); S.loader.exec_module(M)
class ValidatorTests(unittest.TestCase):
    def test_official_non_google_source_is_valid(self):
        r=M.validate([{"name":"חוות הבקר","country":"ישראל","region":"צפון","latitude":33.01,"longitude":35.69,"source_url":"https://example.org/official"}])
        self.assertTrue(r["ok"]); self.assertEqual([],r["warnings"])
    def test_blocks_bad_defaults_coordinates_and_duplicates(self):
        r=M.validate([{"name":"קפה צפון","country":"United States","region":"Pennsylvania","latitude":40,"longitude":-75,"google_place_id":"same"},{"name":"קפה-צפון","country":"ישראל","region":"צפון","latitude":32.9,"longitude":35.5,"google_place_id":"same"}])
        codes={x["code"] for x in r["errors"]}
        self.assertFalse(r["ok"]); self.assertTrue({"blocked_country","blocked_region","coordinates_outside_israel","duplicate_normalized_name","duplicate_google_place_id"}.issubset(codes))
if __name__=="__main__": unittest.main()
