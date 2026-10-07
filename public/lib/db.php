<?php
/**
 * db.php — SQLite bootstrap + helpers for the Oracle CTF backend.
 *
 * Seeds the real challenge schema: `archivists` (login targets) and
 * `kshetra_rakshak` (warrior records, id=7 is the mystery warrior with
 * name hidden as '???').
 */

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $path = getenv('DB_PATH') ?: dirname(__DIR__, 2) . '/data/oracle_ctf.db';
    $dir  = dirname($path);
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }

    $pdo = new PDO('sqlite:' . $path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_NUM);

    $pdo->exec("CREATE TABLE IF NOT EXISTS archivists (
        id       INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL,
        password TEXT NOT NULL
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS kshetra_rakshak (
        id     INTEGER PRIMARY KEY AUTOINCREMENT,
        name   TEXT NOT NULL,
        side   TEXT NOT NULL,
        father TEXT DEFAULT NULL,
        mother TEXT DEFAULT NULL,
        fate   TEXT DEFAULT NULL
    )");

    $count = (int) $pdo->query("SELECT COUNT(*) FROM kshetra_rakshak")->fetchColumn();
    if ($count === 0) {
        $pdo->exec("INSERT INTO archivists (username, password) VALUES
            ('sanjaya',   'kurukshetra_chronicles_741'),
            ('vyasa',     'mahabharata_sage_9182'),
            ('dhritrash', 'blind_king_pass_3355')");

        $warriors = [
            ['Bhishma',     'Kaurava', 'Shantanu',           'Ganga',                   "Fell on the tenth day, choosing to lie on a bed of arrows until the war's end."],
            ['Drona',       'Kaurava', 'Sage Bharadwaja',    'Unknown',                 'Slain by Dhrishtadyumna on the fifteenth day after laying down his weapons in grief.'],
            ['Karna',       'Kaurava', 'Surya (the Sun God)', 'Kunti',                  'Slain by Arjuna on the seventeenth day while struggling to free his chariot wheel.'],
            ['Abhimanyu',   'Pandava', 'Arjuna',             'Subhadra',                'Killed inside the Chakravyuha on the thirteenth day, surrounded and overwhelmed.'],
            ['Ghatotkacha', 'Pandava', 'Bhima',              'Hidimbi',                 'Slain by Karna with the Vasavi Shakti on the fourteenth day at nightfall.'],
            ['Shikhandi',   'Pandava', 'Drupada',            'Prishati',                'Survived the main war; killed by Ashwatthama on the final night in the camp attack.'],
            ['???',         'Pandava', 'Arjuna',             'Ulupi (a Naga princess)', 'Offered himself to Goddess Kali on the eighth day, then fell in battle the same day.'],
            ['Satyaki',     'Pandava', 'Shini',              'Unknown',                 'One of the few key warriors to survive the entire Kurukshetra war.'],
            ['Kripacharya', 'Kaurava', 'Sage Sharadvana',    'Janapadi',                'One of the chiranjivis — survived the war due to his immortal nature.'],
        ];
        $stmt = $pdo->prepare("INSERT INTO kshetra_rakshak (name, side, father, mother, fate) VALUES (?,?,?,?,?)");
        foreach ($warriors as $w) {
            $stmt->execute($w);
        }
    }

    return $pdo;
}

/**
 * The challenge's (deliberately weak, case-sensitive) input filter.
 */
function oracle_filter(string $input): string
{
    $blocked = ['UNION', 'OR 1=1'];
    return str_replace($blocked, '****', $input);
}

/**
 * Stateless download token issued only after the warrior is correctly named.
 */
function download_token(): string
{
    $secret = getenv('ORACLE_SECRET') ?: 'sanjayas-oracle-first-trial';
    return substr(hash_hmac('sha256', 'iravan-named', $secret), 0, 32);
}
