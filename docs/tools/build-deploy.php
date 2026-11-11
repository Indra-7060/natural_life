<?php
/**
 * Builds the upload package for ordinary PHP + MySQL hosting (cPanel type).
 *
 *   php docs/tools/build-deploy.php
 *
 * Output, in deploy/:
 *   sugarlif-upload.zip   two folders: "sugarlif" (application, goes OUTSIDE the web root)
 *                         and "public_html" (the web root contents)
 *   sugarlif_db.sql       the database, ready to import with phpMyAdmin
 *
 * The local .env is never copied. The package gets its own production .env with
 * a fresh encryption key and blank database, payment and SMTP values.
 * MySQL must be running. Development tool only.
 */

$root = str_replace('\\', '/', dirname(__DIR__, 2)) . '/';
$out  = $root . 'deploy/';
$dump = 'C:/xampp/mysql/bin/mysqldump.exe';

if (! is_dir($out)) {
    mkdir($out, 0775, true);
}

// ---- 1. database ---------------------------------------------------------
$sqlFile = $out . 'sugarlif_db.sql';
$cmd = '"' . $dump . '" --user=root --host=localhost --single-transaction --skip-comments --skip-add-locks'
     . ' --no-tablespaces --default-character-set=utf8mb4 --result-file="' . $sqlFile . '" sugarlif_db';
exec($cmd . ' 2>&1', $o, $code);
if ($code !== 0 || ! is_file($sqlFile) || filesize($sqlFile) < 10000) {
    exit("Database export failed: " . implode("\n", $o) . "\n");
}
echo 'database export: ' . round(filesize($sqlFile) / 1024) . " KB\n";

// ---- 2. production .env ---------------------------------------------------
$env = file_get_contents($root . '.env.example');
$env = str_replace(["\r\n", "\r"], "\n", $env);
$swap = [
    '/^# Sugarlif \/ Natural Life — environment configuration \(EXAMPLE\)$/m' => '# Sugarlif / Natural Life — LIVE SERVER configuration',
    '/^# Copy this file to "\.env".*$/m'             => '# Fill in every value marked CHANGE_ME. Keep this file private.',
    '/^CI_ENVIRONMENT = .*$/m'                       => 'CI_ENVIRONMENT = production',
    '/^app\.baseURL = .*$/m'                         => "app.baseURL = 'https://CHANGE_ME-your-domain/'",
    '/^# app\.forceGlobalSecureRequests = true.*$/m' => 'app.forceGlobalSecureRequests = true',
    '/^encryption\.key =.*$/m'                       => 'encryption.key = hex2bin:' . bin2hex(random_bytes(32)),
    '/^# DATABASE \(.*\)$/m'                         => '# DATABASE (create the database and user in the hosting panel first)',
    '/^database\.default\.database = .*$/m'          => 'database.default.database = CHANGE_ME_database_name',
    '/^database\.default\.username = .*$/m'          => 'database.default.username = CHANGE_ME_database_user',
    '/^database\.default\.password =.*$/m'           => "database.default.password = 'CHANGE_ME_database_password'",
    // the first admin account comes from the imported database, not from this file
    '/^#-+\n# FIRST ADMIN ACCOUNT.*?\nadmin\.defaultPassword = .*\n\n/ms' => '',
    '/^# E-MAIL \(SMTP\)\. Leave SMTPHost empty to write e-mails to$/m' => '# E-MAIL (SMTP). While SMTPHost is empty, e-mails are only saved to',
    '/^# writable\/emails\/ instead of sending them.*$/m'               => '# writable/emails/ and customers receive nothing.',
];
foreach ($swap as $pattern => $to) {
    $env = preg_replace($pattern, $to, $env, 1, $count);
    if ($count !== 1) {
        exit("Could not prepare the production .env (pattern $pattern)\n");
    }
}

// ---- 3. zip ---------------------------------------------------------------
$zipFile = $out . 'sugarlif-upload.zip';
@unlink($zipFile);
$zip = new ZipArchive();
if ($zip->open($zipFile, ZipArchive::CREATE) !== true) {
    exit("Could not create $zipFile\n");
}

$count = 0;
$addTree = function (string $from, string $to, array $skip = []) use ($zip, &$count): void {
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($from, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $f) {
        $rel = str_replace('\\', '/', substr($f->getPathname(), strlen($from)));
        foreach ($skip as $s) {
            if (preg_match($s, $rel)) {
                continue 2;
            }
        }
        $zip->addFile($f->getPathname(), $to . $rel);
        $count++;
    }
};

// application, kept outside the web root
$addTree($root . 'app/', 'sugarlif/app/');
$addTree($root . 'vendor/', 'sugarlif/vendor/');
foreach (['spark', 'preload.php', 'composer.json', 'composer.lock', 'LICENSE'] as $f) {
    $zip->addFile($root . $f, 'sugarlif/' . $f);
    $count++;
}
$zip->addFromString('sugarlif/.env', $env);
$deny = "<IfModule authz_core_module>\n\tRequire all denied\n</IfModule>\n<IfModule !authz_core_module>\n\tDeny from all\n</IfModule>\n";
$zip->addFromString('sugarlif/.htaccess', $deny);

// empty writable folders (no local sessions, logs, cache or saved e-mails)
foreach (['cache', 'logs', 'session', 'uploads', 'emails', 'debugbar'] as $d) {
    $zip->addFromString("sugarlif/writable/$d/index.html", '');
}
$zip->addFromString('sugarlif/writable/index.html', '');
$zip->addFromString('sugarlif/writable/.htaccess', $deny);

// web root: the application now sits in ../sugarlif instead of ..
$index = file_get_contents($root . 'public/index.php');
$needle = "require FCPATH . '../app/Config/Paths.php';";
if (substr_count($index, $needle) !== 1) {
    exit("public/index.php does not look as expected\n");
}
$zip->addFromString('public_html/index.php', str_replace($needle, "require FCPATH . '../sugarlif/app/Config/Paths.php';", $index));
$addTree($root . 'public/', 'public_html/', ['#^index\.php$#']);
$count += 10;

$zip->close();
echo "package: $count files, " . round(filesize($zipFile) / 1048576, 1) . " MB\n";
echo "written to $out\n";
