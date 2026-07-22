<?php
/**
 * One-off, idempotent import of the EXISTING static hero/awards/gallery images
 * into the new Website-Content CMS tables. Safe to run on live production data —
 * each table is only populated if currently empty, so re-running is a no-op.
 *
 * Run once, right after upgrade_website_content.php:
 *   php database/seed_website_content_from_static.php
 */
require __DIR__ . '/../app/bootstrap.php';

use App\Core\Database;

function copyStatic(string $relSrc, string $subdir, string $destName): ?string
{
    $src = BASE_PATH . '/public/assets/img/' . $relSrc;
    if (!is_file($src)) { return null; }
    $dir = BASE_PATH . '/storage/uploads/' . $subdir;
    if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
    $dest = $subdir . '/' . $destName;
    @copy($src, BASE_PATH . '/storage/uploads/' . $dest);
    return $dest;
}

// ---- Hero slides ----
if ((int) Database::scalar("SELECT COUNT(*) FROM hero_slides") > 0) {
    echo "hero_slides already populated, skipping.\n";
} else {
    $heroSource = [
        ['img/photos/hero.jpg','Students learning at IT Training Institute'],
        ['img/photos/lab.jpg','Hands-on computer lab'],
        ['img/photos/campus-1.jpg','Our campus'],
        ['img/photos/campus-2.jpg','Classrooms'],
        ['img/photos/campus-3.jpg','Training in progress'],
        ['img/photos/campus-4.jpg','Practical labs'],
        ['img/photos/campus-5.jpg','Student activities'],
        ['img/photos/campus-6.jpg','Institute facilities'],
    ];
    $sort = 0;
    foreach ($heroSource as [$rel, $alt]) {
        $sort++;
        $stored = copyStatic(ltrim(str_replace('img/', '', $rel), '/'), 'hero', 'seed-' . $sort . '.jpg');
        Database::run("INSERT INTO hero_slides (image,alt,sort,is_published) VALUES (?,?,?,1)", [$stored, $alt, $sort]);
    }
    echo "  ✓ hero_slides imported (" . count($heroSource) . ")\n";
}

// ---- Awards ----
if ((int) Database::scalar("SELECT COUNT(*) FROM awards") > 0) {
    echo "awards already populated, skipping.\n";
} else {
    $sort = 0;
    foreach (\App\Content::awards() as $a) {
        $sort++;
        $stored = copyStatic($a['image'], 'awards', 'seed-' . $sort . '.jpg');
        Database::run("INSERT INTO awards (year,title,org,description,image,sort,is_published) VALUES (?,?,?,?,?,?,1)",
            [$a['year'], $a['title'], $a['org'], $a['desc'], $stored, $sort]);
    }
    echo "  ✓ awards imported (" . count(\App\Content::awards()) . ")\n";
}

// ---- Activities (import the old flat campus gallery as one "Campus Tour" category) ----
if ((int) Database::scalar("SELECT COUNT(*) FROM activity_categories") > 0) {
    echo "activity_categories already populated, skipping.\n";
} else {
    $catId = (int) Database::run("INSERT INTO activity_categories (name,sort,is_published) VALUES (?,?,1)", ['Campus Tour', 1]);
    $sort = 0;
    foreach (\App\Content::gallery() as $rel) {
        $sort++;
        $stored = copyStatic($rel, 'activities', 'seed-' . $sort . '.jpg');
        Database::run("INSERT INTO activity_photos (category_id,image,caption,sort,is_published) VALUES (?,?,?,?,1)",
            [$catId, $stored, null, $sort]);
    }
    echo "  ✓ activity_photos imported (" . count(\App\Content::gallery()) . ") under category 'Campus Tour'\n";
}

// ---- Facilities ----
if ((int) Database::scalar("SELECT COUNT(*) FROM facilities") > 0) {
    echo "facilities already populated, skipping.\n";
} else {
    $sort = 0;
    foreach (\App\Content::facilities() as $f) {
        $sort++;
        $stored = copyStatic($f['image'], 'facilities', 'seed-' . $sort . '.jpg');
        Database::run("INSERT INTO facilities (title,description,image,sort,is_published) VALUES (?,?,?,?,1)",
            [$f['title'], $f['desc'], $stored, $sort]);
    }
    echo "  ✓ facilities imported (" . count(\App\Content::facilities()) . ")\n";
}

echo "Static-content import complete.\n";
