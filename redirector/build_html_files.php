<?php

$htaccess = file_get_contents('htacess.txt');
$htaccess = array_filter(explode(PHP_EOL, $htaccess));

$template = file_get_contents('../docs/index.html');

foreach ($htaccess as $rule) {
    [$redirect, $permanent, $source, $target] = explode(' ', $rule);

    $targetFile = '../docs' . $source;
    $targetFolder = dirname($targetFile);
    $targetRepo = basename($targetFile, '.html');

    $content = str_replace([
        "'https://github.com/z-index-net'",
        'github organisation',
        'href="https://github.com/z-index-net"',
    ], [
        sprintf("'%s'", $target),
        'github repository',
        sprintf('href="%s"', $target),
    ],
        $template);

    if (!is_dir($targetFolder)) {
        mkdir($targetFolder, 0777, true);
    }

    file_put_contents($targetFile, $content);

    echo $source . PHP_EOL;
}
