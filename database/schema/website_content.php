<?php
/**
 * Column defs for the Website-Content CMS tables (hero slides, awards, activity gallery).
 * Shared by migrate.php (fresh installs) and upgrade_website_content.php (safe on live data).
 * Assumes $PK, $INT, $BOOL, $TS are already defined by the includer.
 */
return [
    'awards' => "
        id $PK,
        year TEXT,
        title TEXT NOT NULL,
        org TEXT,
        description TEXT,
        image TEXT,
        sort $INT DEFAULT 0,
        is_published $BOOL NOT NULL DEFAULT 1,
        created_at $TS
    ",
    'activity_categories' => "
        id $PK,
        name TEXT NOT NULL,
        sort $INT DEFAULT 0,
        is_published $BOOL NOT NULL DEFAULT 1,
        created_at $TS
    ",
    'activity_photos' => "
        id $PK,
        category_id $INT NOT NULL,
        image TEXT,
        caption TEXT,
        sort $INT DEFAULT 0,
        is_published $BOOL NOT NULL DEFAULT 1,
        created_at $TS
    ",
    'hero_slides' => "
        id $PK,
        image TEXT,
        alt TEXT,
        sort $INT DEFAULT 0,
        is_published $BOOL NOT NULL DEFAULT 1,
        created_at $TS
    ",
];
