<?php

use Illuminate\Filesystem\Filesystem;

use function ScholarMatch\Deployment\packageApplication;

require_once dirname(__DIR__, 2).'/deploy/azure/package.php';

beforeEach(function () {
    $this->packageRoot = sys_get_temp_dir().'/scholarmatch-package-'.bin2hex(random_bytes(8));
    foreach (['app', 'bootstrap/cache', 'config', 'database', 'public/build/assets', 'resources',
        'routes', 'vendor/composer', 'deploy/azure'] as $directory) {
        mkdir($this->packageRoot.'/'.$directory, 0775, true);
    }
    foreach (['artisan', 'composer.json', 'composer.lock', 'bootstrap/app.php', 'public/index.php',
        'vendor/autoload.php', 'deploy/azure/nginx.conf', 'deploy/azure/startup.sh'] as $file) {
        file_put_contents($this->packageRoot.'/'.$file, 'release fixture');
    }
    file_put_contents($this->packageRoot.'/vendor/composer/installed.json', '{"dev":false,"packages":[]}');
    file_put_contents($this->packageRoot.'/public/build/manifest.json', json_encode([
        'resources/css/app.css' => ['file' => 'assets/app.css'],
        'resources/js/app.js' => ['file' => 'assets/app.js'],
    ]));
    file_put_contents($this->packageRoot.'/public/build/assets/app.css', 'body {}');
    file_put_contents($this->packageRoot.'/public/build/assets/app.js', 'console.log("fixture");');
});

afterEach(function () {
    if (! isset($this->packageRoot)) {
        return;
    }

    $root = realpath($this->packageRoot);
    $parent = realpath(sys_get_temp_dir());
    // Delete only this test's directly-created temporary directory; do not follow links.
    if ($root !== false && dirname($root) === $parent
        && preg_match('/^scholarmatch-package-[a-f0-9]{16}$/', basename($root))) {
        (new Filesystem)->deleteDirectory($root);
    }
});

test('Azure release includes built assets but excludes private data and local caches', function () {
    foreach (['.codex', 'storage/app/private/backups', 'storage/logs', 'tests', 'public/storage', 'node_modules'] as $directory) {
        mkdir($this->packageRoot.'/'.$directory, 0775, true);
    }
    foreach (['.env', '.codex/local-secret', 'storage/app/private/backups/live.sql', 'storage/logs/laravel.log',
        'tests/student-data.json', 'public/storage/upload.txt', 'public/hot', 'public/accidental.sql',
        'database/database.sqlite', 'bootstrap/cache/config.php', 'app/.env.backup', 'node_modules/local.js'] as $file) {
        file_put_contents($this->packageRoot.'/'.$file, 'PRIVATE_FIXTURE');
    }

    $output = $this->packageRoot.'/release.zip';
    expect(packageApplication($this->packageRoot, $output))->toBeGreaterThan(0);
    $zip = new ZipArchive;
    expect($zip->open($output))->toBeTrue();
    expect($zip->locateName('public/build/assets/app.css'))->not->toBeFalse();
    expect($zip->locateName('public/build/assets/app.js'))->not->toBeFalse();
    expect($zip->locateName('vendor/autoload.php'))->not->toBeFalse();
    expect($zip->locateName('deploy/azure/startup.sh'))->not->toBeFalse();
    expect($zip->locateName('storage/framework/views/'))->not->toBeFalse();
    for ($i = 0; $i < $zip->numFiles; $i++) {
        expect($zip->getFromIndex($i))->not->toContain('PRIVATE_FIXTURE');
    }
    $zip->close();
});

test('Azure packaging refuses development dependencies', function () {
    file_put_contents($this->packageRoot.'/vendor/composer/installed.json', '{"dev":true,"packages":[]}');

    expect(fn () => packageApplication($this->packageRoot, $this->packageRoot.'/release.zip'))
        ->toThrow(RuntimeException::class, '--no-dev');
    expect(file_exists($this->packageRoot.'/release.zip'))->toBeFalse();
});

test('Azure packaging refuses incomplete Vite builds', function () {
    unlink($this->packageRoot.'/public/build/assets/app.js');

    expect(fn () => packageApplication($this->packageRoot, $this->packageRoot.'/release.zip'))
        ->toThrow(RuntimeException::class, 'Vite asset');
});

test('Azure packaging preserves an existing output file instead of overwriting it', function () {
    $output = $this->packageRoot.'/release.zip';
    file_put_contents($output, 'existing-release');

    expect(fn () => packageApplication($this->packageRoot, $output))
        ->toThrow(RuntimeException::class, 'new file');
    expect(file_get_contents($output))->toBe('existing-release');
});
