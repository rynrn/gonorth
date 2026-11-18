Detection rules:
- **Apps:** every directory with `package.json` or `pyproject.toml`/`setup.cfg`/`requirements*.txt`,
  excluding `node_modules`, `.venv`, `dist`, `build`. A monorepo root with workspaces is the aggregate,
  not an app.
- **`verify`:** first existing of package.json scripts `verify:ci`, `verify`, `ci`, `check`; Makefile
  targets `verify`, `check`, `ci`; tox/nox/pre-commit.
- **npm apps** (use the lockfile's tool: pnpm/yarn/npm): `install: npm ci`; `lint`, `typecheck`
  (`typecheck`/`tsc`), `test` (non-watch).
- **Python apps:** install via `uv sync`/`poetry install`/`pip install -r requirements*.txt`; lint via
  ruff/flake8; typecheck via mypy/pyright; test via `pytest -q` else `python -m unittest discover`.
- **Root `test`:** single app → that app's test; otherwise `verify`.
- No secrets or machine-specific paths in the file.
