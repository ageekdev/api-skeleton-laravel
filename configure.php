#!/usr/bin/env php
<?php

function ask(string $question, string $default = ''): string
{
    $answer = readline($question.($default ? " ({$default})" : null).': ');

    if (! $answer) {
        return $default;
    }

    return $answer;
}

function confirm(string $question, bool $default = false): bool
{
    $answer = ask($question.' ('.($default ? 'Y/n' : 'y/N').')');

    if (! $answer) {
        return $default;
    }

    return strtolower($answer) === 'y';
}

function writeln(string $line): void
{
    echo $line.PHP_EOL;
}

function run(string $command): string
{
    return trim((string) shell_exec($command));
}

function str_after(string $subject, string $search): string
{
    $pos = strrpos($subject, $search);

    if ($pos === false) {
        return $subject;
    }

    return substr($subject, $pos + strlen($search));
}

function slugify(string $subject): string
{
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $subject), '-'));
}

function namespace_case(string $subject): string
{
    return str_replace(' ', '', title_case($subject));
}

function title_case(string $subject): string
{
    return ucwords(str_replace(['-', '_'], ' ', $subject));
}

function title_snake(string $subject, string $replace = '_'): string
{
    return str_replace(['-', '_'], $replace, $subject);
}

function replace_in_file(?string $file, array $replacements): void
{
    if (! $file) {
        return;
    }

    $contents = file_get_contents($file);

    file_put_contents(
        $file,
        str_replace(
            array_keys($replacements),
            array_values($replacements),
            $contents
        )
    );
}

function remove_prefix(string $prefix, string $content): string
{
    if (str_starts_with($content, $prefix)) {
        return substr($content, strlen($prefix));
    }

    return $content;
}

function remove_composer_deps(array $names): void
{
    $data = json_decode(file_get_contents(__DIR__.'/composer.json'), true);

    foreach ($data['require-dev'] as $name => $version) {
        if (in_array($name, $names, true)) {
            unset($data['require-dev'][$name]);
        }
    }

    file_put_contents(__DIR__.'/composer.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
}

function remove_composer_script($scriptName): void
{
    $data = json_decode(file_get_contents(__DIR__.'/composer.json'), true);

    foreach ($data['scripts'] as $name => $script) {
        if ($scriptName === $name) {
            unset($data['scripts'][$name]);
            break;
        }
    }

    file_put_contents(__DIR__.'/composer.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
}

function remove_readme_paragraphs(string $file): void
{
    $contents = file_get_contents($file);

    file_put_contents(
        $file,
        preg_replace('/<!--delete-->.*<!--\/delete-->/s', '', $contents) ?: $contents
    );
}

function safeUnlink(string $filename)
{
    if (file_exists($filename) && is_file($filename)) {
        unlink($filename);
    }
}

function determineSeparator(string $path): string
{
    return str_replace('/', DIRECTORY_SEPARATOR, $path);
}

function replaceForWindows(): array
{
    return preg_split('/\\r\\n|\\r|\\n/', run('dir /S /B * | findstr /v /i .git\ | findstr /v /i vendor | findstr /v /i '.basename(__FILE__).' | findstr /r /i /M /F:/ ":vendor :project :project_slug VendorName vendor_name vendor_slug"'));
}

function replaceForAllOtherOSes(): array
{
    return explode(PHP_EOL, run('grep -E -r -l -i ":vendor|:project|:project_slug|VendorName|vendor_name|vendor_slug" --exclude-dir=vendor ./* ./.github/* | grep -v '.basename(__FILE__)));
}

$vendorName = ask('Vendor name', 'ageekdev');
$vendorSlug = slugify($vendorName);
$vendorNamespace = namespace_case($vendorName);
$vendorNamespace = ask('Vendor namespace', $vendorNamespace);

$currentDirectory = getcwd();
$folderName = basename($currentDirectory);

$projectName = ask('Project name', $folderName);
$projectSlug = slugify($projectName);
$projectTitle = title_case($projectName);
$projectSlugWithoutPrefix = remove_prefix('laravel-', $projectSlug);

$description = ask('Project description', "This is my project {$projectSlug}");

writeln('------');
writeln("Vendor     : {$vendorName} ({$vendorSlug})");
writeln("Project    : {$projectSlug} <{$description}>");
writeln("ProjectTitle  : {$projectTitle}");
writeln('------');

writeln('This script will replace the above values in all relevant files in the project directory.');

if (! confirm('Modify files?', true)) {
    exit(1);
}

$files = (str_starts_with(strtoupper(PHP_OS), 'WIN') ? replaceForWindows() : replaceForAllOtherOSes());

foreach ($files as $file) {
    replace_in_file($file, [
        ':vendor_name' => $vendorName,
        ':vendor_slug' => $vendorSlug,
        ':project_name' => $projectName,
        ':project_slug' => $projectSlug,
        ':project_title' => $projectTitle,
        ':project_slug_without_prefix' => $projectSlugWithoutPrefix,
        ':project_description' => $description,
    ]);

    match (true) {
        str_contains($file, 'README.md') => remove_readme_paragraphs($file),
        default => [],
    };
}

$envFiles = ['.env.example', '.env.sail.example'];

foreach ($envFiles as $envFile) {
    replace_in_file($envFile, [
        ':vendor_name' => $vendorName,
        ':vendor_slug' => $vendorSlug,
        ':project_name' => $projectName,
        ':project_slug' => $projectSlug,
        ':project_title' => $projectTitle,
        ':project_slug_without_prefix' => $projectSlugWithoutPrefix,
        ':project_description' => $description,
    ]);
}

confirm('Execute `composer install` and key generate?') && run('composer install && cp .env.example .env && php artisan key:generate');

confirm('Let this script delete itself?', true) && unlink(__FILE__);
