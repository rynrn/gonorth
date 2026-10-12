"""Compare a Chrome resource snapshot with GoNorth's template budgets.

TransferSize is a browser network observation, not a Core Web Vital.
"""
import argparse
import json
from pathlib import Path

DEFAULT_BUDGETS = Path(__file__).resolve().parents[1] / "data" / "issue-86-asset-budgets.json"
GROUPS = {"link": "stylesheets", "script": "scripts", "img": "images"}


def main() -> int:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("snapshot", type=Path)
    parser.add_argument("--budgets", type=Path, default=DEFAULT_BUDGETS)
    args = parser.parse_args()
    budgets = json.loads(args.budgets.read_text(encoding="utf-8-sig"))["templates"]
    rows = json.loads(args.snapshot.read_text(encoding="utf-8-sig"))
    if not isinstance(rows, list):
        parser.error("snapshot must contain a list")
    seen = set()
    exceeded = 0
    for row in rows:
        kind = row.get("kind")
        if kind not in budgets or kind in seen or row.get("status") != 200:
            parser.error(f"unexpected or duplicate template/status: {kind}")
        seen.add(kind)
        sums = {group: 0 for group in GROUPS.values()}
        largest = (0, "")
        for resource in row.get("resources", []):
            group = GROUPS.get(resource.get("type"))
            if group is None:
                continue
            amount = resource.get("transfer")
            if not isinstance(amount, (int, float)) or amount < 0:
                parser.error(f"invalid transfer size in {kind}")
            sums[group] += amount
            if group == "images" and amount > largest[0]:
                largest = (amount, resource.get("path", ""))
        print(kind)
        for group, amount in sums.items():
            limit = budgets[kind][group]
            status = "OVER" if amount > limit else "OK"
            exceeded += status == "OVER"
            print(f"  {group}: {amount:,.0f}/{limit:,} B {status}")
        limit = budgets[kind]["single_image"]
        status = "OVER" if largest[0] > limit else "OK"
        exceeded += status == "OVER"
        print(f"  largest image: {largest[0]:,.0f}/{limit:,} B {status} {largest[1]}")
    if seen != set(budgets):
        parser.error("snapshot must include archive, place, and article")
    print(f"budget exceedances: {exceeded}")
    return 1 if exceeded else 0


if __name__ == "__main__":
    raise SystemExit(main())
