<?php
// check-php-version.php
$composerJson = json_decode(file_get_contents('composer.json'), true);
$phpVersion = phpversion();

if (version_compare($phpVersion, '8.1', '>=')) 
{
    $composerJson['require']['google/analytics-data'] = '^0.18.0';
}
else 
{
    $composerJson['require']['google/analytics-data'] = '^0.15.0';
}

file_put_contents('composer.json', json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

