#!/usr/bin/env bash
# Download a verified image library for the ITTI site.
# Tries Unsplash (topic-specific); falls back to picsum (scenes) / pravatar (portraits)
# so every target file ALWAYS exists. Stored locally → self-contained, fast, offline-safe.
set -u
ROOT="G:/IT Training/public/assets/img"
mkdir -p "$ROOT/photos" "$ROOT/courses" "$ROOT/faculty" "$ROOT/awards"
UA="Mozilla/5.0"
MINSIZE=7000

dl () { # dl <outfile> <primary_url> <fallback_url>
  local out="$1" primary="$2" fallback="$3"
  curl -fsSL -A "$UA" "$primary" -o "$out" 2>/dev/null
  if [ ! -f "$out" ] || [ "$(wc -c < "$out" 2>/dev/null || echo 0)" -lt "$MINSIZE" ]; then
    curl -fsSL -A "$UA" "$fallback" -o "$out" 2>/dev/null
    echo "  fallback  $(basename "$out")"
  else
    echo "  ok        $(basename "$out")"
  fi
}

uns () { echo "https://images.unsplash.com/photo-$1?auto=format&fit=crop&w=$2&q=70"; }
pic () { echo "https://picsum.photos/seed/$1/$2/$3"; }
pra () { echo "https://i.pravatar.cc/500?img=$1"; }

echo "== scenes =="
dl "$ROOT/photos/hero.jpg"     "$(uns 1523240795612-9a054b0db644 1600)" "$(pic ittihero 1600 1000)"
dl "$ROOT/photos/about.jpg"    "$(uns 1503676260728-1c00da094a0b 1200)" "$(pic ittiabout 1200 900)"
dl "$ROOT/photos/mission.jpg"  "$(uns 1517245386807-bb43f82c33c4 1200)" "$(pic ittimission 1200 900)"
dl "$ROOT/photos/lab.jpg"      "$(uns 1531545514256-b1400bc00f31 1200)" "$(pic ittilab 1200 800)"
dl "$ROOT/photos/cta.jpg"      "$(uns 1524178232363-1fb2b075b655 1600)" "$(pic itticta 1600 700)"
dl "$ROOT/photos/campus-1.jpg" "$(uns 1562774053-701939374585 900)"     "$(pic itticampus1 900 700)"
dl "$ROOT/photos/campus-2.jpg" "$(uns 1541339907198-e08756dedf3f 900)"  "$(pic itticampus2 900 700)"
dl "$ROOT/photos/campus-3.jpg" "$(uns 1577896851231-70ef18881754 900)"  "$(pic itticampus3 900 700)"
dl "$ROOT/photos/campus-4.jpg" "$(uns 1588072432836-e10032774350 900)"  "$(pic itticampus4 900 700)"
dl "$ROOT/photos/campus-5.jpg" "$(uns 1573164713988-8665fc963095 900)"  "$(pic itticampus5 900 700)"
dl "$ROOT/photos/campus-6.jpg" "$(uns 1581092160562-40aa08e78837 900)"  "$(pic itticampus6 900 700)"

echo "== courses =="
dl "$ROOT/courses/ccna-200-301.jpg"          "$(uns 1558494949-ef010cbdcc31 800)" "$(pic ccna 800 500)"
dl "$ROOT/courses/ethical-hacking.jpg"       "$(uns 1550751827-4bd374c3f58b 800)" "$(pic ethical 800 500)"
dl "$ROOT/courses/cyber-security.jpg"        "$(uns 1614064641938-3bbee52942c7 800)" "$(pic cyber 800 500)"
dl "$ROOT/courses/cctv-camera-installation.jpg" "$(uns 1557597774-9d273605dfa9 800)" "$(pic cctv 800 500)"
dl "$ROOT/courses/cpp.jpg"                   "$(uns 1515879218367-8466d910aaa4 800)" "$(pic cpp 800 500)"
dl "$ROOT/courses/oop.jpg"                   "$(uns 1517180102446-f3ece451e9d8 800)" "$(pic oop 800 500)"
dl "$ROOT/courses/html.jpg"                  "$(uns 1542831371-29b0f74f9713 800)" "$(pic html 800 500)"
dl "$ROOT/courses/java.jpg"                  "$(uns 1517694712202-14dd9538aa97 800)" "$(pic java 800 500)"
dl "$ROOT/courses/python.jpg"                "$(uns 1526379095098-d400fd0bf935 800)" "$(pic python 800 500)"

echo "== faculty (portraits) =="
dl "$ROOT/faculty/1.jpg" "$(uns 1507003211169-0a1dd7228f2d 500)" "$(pra 12)"
dl "$ROOT/faculty/2.jpg" "$(uns 1500648767791-00dcc994a43e 500)" "$(pra 13)"
dl "$ROOT/faculty/3.jpg" "$(uns 1472099645785-5658abf4ff4e 500)" "$(pra 14)"
dl "$ROOT/faculty/4.jpg" "$(uns 1494790108377-be9c29b29330 500)" "$(pra 5)"
dl "$ROOT/faculty/5.jpg" "$(uns 1506794778202-cad84cf45f1d 500)" "$(pra 33)"
dl "$ROOT/faculty/6.jpg" "$(uns 1438761681033-6461ffad8d80 500)" "$(pra 45)"

echo "== awards =="
dl "$ROOT/awards/award-1.jpg" "$(uns 1567427017947-545c5f8d16ad 700)" "$(pic award1 700 500)"
dl "$ROOT/awards/award-2.jpg" "$(uns 1552581234-26160f608093 700)"    "$(pic award2 700 500)"
dl "$ROOT/awards/award-3.jpg" "$(uns 1523580494863-6f3031224c94 700)" "$(pic award3 700 500)"
dl "$ROOT/awards/award-4.jpg" "$(uns 1571260899304-425eee4c7efc 700)" "$(pic award4 700 500)"

echo "DONE"
