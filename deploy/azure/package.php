<?php

namespace ScholarMatch\Deployment;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use Throwable;
use ZipArchive;

/** Build an extracted-ZIP deployment without copying any local runtime storage. */
function packageApplication(string $root, string $output): int
{
    if (! class_exists(ZipArchive::class)) {
        throw new RuntimeException('The CLI ZIP extension is required to package the release.');
    }

    $root = realpath($root) ?: throw new RuntimeException('Application root does not exist.');
    $required = [
        'artisan', 'composer.json', 'composer.lock', 'bootstrap/app.php',
        'public/index.php', 'public/build/manifest.json', 'vendor/autoload.php',
        'vendor/composer/installed.json', 'deploy/azure/nginx.conf', 'deploy/azure/startup.sh',
    ];
    foreach ($required as $file) {
        if (! is_file($root.'/'.$file) || is_link($root.'/'.$file)) {
            throw new RuntimeException('Missing release file or unexpected symlink: '.$file);
        }
    }

    $installed = json_decode(file_get_contents($root.'/vendor/composer/installed.json'), true, flags: JSON_THROW_ON_ERROR);
    if (($installed['dev'] ?? true) !== false) {
        throw new RuntimeException('Install Composer dependencies with --no-dev before packaging.');
    }

    $manifest = json_decode(file_get_contents($root.'/public/build/manifest.json'), true, flags: JSON_THROW_ON_ERROR);
    if (! isset($manifest['resources/css/app.css'], $manifest['resources/js/app.js'])) {
        throw new RuntimeException('Build the application CSS and JavaScript before packaging.');
    }
    foreach ($manifest as $entry) {
        foreach (array_merge([$entry['file'] ?? ''], $entry['css'] ?? [], $entry['assets'] ?? []) as $asset) {
            if ($asset === '' || str_contains($asset, '..') || ! is_file($root.'/public/build/'.$asset)) {
                throw new RuntimeException('Missing or invalid Vite asset: '.$asset);
            }
        }
    }

    // Allow only application sources, production dependencies and built assets.
    // The whole storage tree, docs, test data, toolchains and Git metadata stay out.
    $files = [];
    foreach (['app', 'bootstrap', 'config', 'database', 'public', 'resources', 'routes', 'vendor'] as $directory) {
        if (! is_dir($root.'/'.$directory) || is_link($root.'/'.$directory)) {
            throw new RuntimeException('Missing source directory or unexpected symlink: '.$directory);
        }
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root.'/'.$directory, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST,
        );
        foreach ($iterator as $file) {
            $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
            if ($relative === 'public/storage' || str_starts_with($relative, 'public/storage/')
                || str_starts_with($relative, 'bootstrap/cache/')
                || $relative === 'public/hot'
                || preg_match('~(^|/)(\.env[^/]*|\.git[^/]*|\.codex|node_modules)(/|$)~i', $relative)
                || preg_match('~\.(sql|sqlite|sqlite3|db|key|pfx|p12)$~i', $relative)) {
                continue;
            }
            if ($file->isLink()) {
                throw new RuntimeException('Unexpected release symlink: '.$relative);
            }
            if ($file->isFile()) {
                $files[$relative] = $file->getPathname();
            }
        }
    }
    foreach (['artisan', 'composer.json', 'composer.lock', 'deploy/azure/nginx.conf', 'deploy/azure/startup.sh'] as $file) {
        $files[$file] = $root.'/'.$file;
    }
    ksort($files);

    if (! is_dir(dirname($output)) || file_exists($output)) {
        throw new RuntimeException('Output directory must exist and the ZIP must be a new file.');
    }
    $resolvedOutput = str_replace('\\', '/', realpath(dirname($output)).'/'.basename($output));
    if (in_array($resolvedOutput, array_map(fn ($file) => str_replace('\\', '/', $file), $files), true)) {
        throw new RuntimeException('The ZIP cannot replace a source file.');
    }

    $zip = new ZipArchive;
    if ($zip->open($output, ZipArchive::CREATE | ZipArchive::EXCL) !== true) {
        throw new RuntimeException('Could not create the release ZIP.');
    }
    try {
        foreach ($files as $relative => $file) {
            if (! $zip->addFile($file, $relative)) {
                throw new RuntimeException('Could not package: '.$relative);
            }
            $zip->setExternalAttributesName($relative, ZipArchive::OPSYS_UNIX, 0100644 << 16);
        }
        foreach (['bootstrap/cache', 'storage/app/private', 'storage/app/public', 'storage/framework/cache/data',
            'storage/framework/sessions', 'storage/framework/views', 'storage/logs'] as $directory) {
            $zip->addEmptyDir($directory);
            $zip->setExternalAttributesName($directory.'/', ZipArchive::OPSYS_UNIX, 0040775 << 16);
        }
    } finally {
        if (! $zip->close()) {
            throw new RuntimeException('Could not finish the release ZIP.');
        }
    }

    return count($files);
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    try {
        $options = getopt('', ['root:', 'output:']);
        if (! isset($options['output'])) {
            throw new RuntimeException('Usage: php deploy/azure/package.php --output=/absolute/path/release.zip [--root=/source]');
        }
        $count = packageApplication($options['root'] ?? dirname(__DIR__, 2), $options['output']);
        fwrite(STDOUT, 'Packaged '.$count.' application files into '.$options['output'].PHP_EOL);
    } catch (Throwable $exception) {
        fwrite(STDERR, $exception->getMessage().PHP_EOL);
        exit(1);
    }
}
