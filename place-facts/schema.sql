PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS metadata (
    key TEXT PRIMARY KEY,
    value TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS schema_migrations (
    version INTEGER PRIMARY KEY,
    name TEXT NOT NULL,
    applied_at TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS places (
    id INTEGER PRIMARY KEY,
    wordpress_id INTEGER NOT NULL UNIQUE,
    slug TEXT NOT NULL,
    name TEXT NOT NULL,
    url TEXT NOT NULL,
    google_place_id TEXT,
    google_maps_url TEXT,
    website_url TEXT,
    category TEXT,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
);

CREATE INDEX IF NOT EXISTS idx_places_google_place_id
    ON places (google_place_id);
CREATE INDEX IF NOT EXISTS idx_places_slug
    ON places (slug);

CREATE TABLE IF NOT EXISTS place_facts (
    place_id INTEGER PRIMARY KEY,
    business_status TEXT NOT NULL DEFAULT 'unknown'
        CHECK (business_status IN ('operational', 'temporarily_closed', 'permanently_closed', 'unknown')),
    phone TEXT,
    address TEXT,
    city TEXT,
    regular_hours_json TEXT,
    current_hours_json TEXT,
    last_checked_at TEXT,
    verification_source TEXT,
    source_url TEXT,
    updated_at TEXT NOT NULL,
    FOREIGN KEY (place_id) REFERENCES places(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_place_facts_due
    ON place_facts (last_checked_at);
CREATE INDEX IF NOT EXISTS idx_place_facts_status
    ON place_facts (business_status);

CREATE TABLE IF NOT EXISTS place_verifications (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    place_id INTEGER NOT NULL,
    source TEXT NOT NULL,
    source_url TEXT,
    checked_at TEXT NOT NULL,
    received_at TEXT NOT NULL,
    confidence REAL,
    result_json TEXT NOT NULL,
    outcome TEXT NOT NULL
        CHECK (outcome IN ('unchanged', 'changes_detected', 'error')),
    error_message TEXT,
    FOREIGN KEY (place_id) REFERENCES places(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_place_verifications_place_checked
    ON place_verifications (place_id, checked_at DESC);

CREATE TABLE IF NOT EXISTS place_changes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    place_id INTEGER NOT NULL,
    verification_id INTEGER NOT NULL,
    field TEXT NOT NULL,
    old_value TEXT,
    new_value TEXT,
    detected_at TEXT NOT NULL,
    review_status TEXT NOT NULL DEFAULT 'pending'
        CHECK (review_status IN ('pending', 'applied', 'rejected')),
    confidence REAL,
    reviewed_at TEXT,
    review_note TEXT,
    FOREIGN KEY (place_id) REFERENCES places(id) ON DELETE CASCADE,
    FOREIGN KEY (verification_id) REFERENCES place_verifications(id) ON DELETE CASCADE
);

CREATE UNIQUE INDEX IF NOT EXISTS idx_place_changes_one_pending_field
    ON place_changes (place_id, field)
    WHERE review_status = 'pending';
CREATE INDEX IF NOT EXISTS idx_place_changes_review
    ON place_changes (review_status, detected_at);
