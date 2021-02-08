<?php

/*
 * This file is part of Chevere.
 *
 * (c) Rodolfo Berrios <rodolfo@chevere.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

use function Chevere\Components\Filesystem\dirForPath;
use function Chevere\Components\Filesystem\filePhpForPath;
use Chevere\Components\Filesystem\FilePhpReturn;
use Chevere\Components\VarStorable\VarStorable;
use function Chevere\Components\Writer\streamFor;
use Chevere\Components\Writer\StreamWriter;
use Chevere\ReferenceMd\PHPIterator;

require 'vendor/autoload.php';

$hrTime = (int) hrtime(true);
set_error_handler('Chevere\Components\ThrowableHandler\errorsAsExceptions');
set_exception_handler('Chevere\Components\ThrowableHandler\consoleHandler');
$urlBase = 'https://github.com/chevere/chevere/blob/main/src/Chevere/';
$source = '/home/rodolfo/git/chevere/chevere/';
require $source . 'vendor/autoload.php';
$root = $source;
$target = '/home/rodolfo/git/chevere/docs/reference/';
$outputDir = dirForPath($target);
$rootDir = dirForPath($root);
if (! $outputDir->exists()) {
    $outputDir->create();
} else {
    $outputDir->removeContents();
}
$sidebar = filePhpForPath($target . 'sidebar.php');
$sidebar->file()->create();
(new FilePhpReturn($sidebar))->put(new VarStorable('auto'));
$readmeFilename = $outputDir->path()->getChild('README.md')->toString();
$log = new StreamWriter(streamFor('php://stdout', 'w'));
foreach ([
    'src/Chevere/Components/' => 'Components',
    'src/Chevere/Exceptions/' => 'Exceptions',
    'src/Chevere/Interfaces/' => 'Interfaces',
] as $path => $title) {
    $log->write("\n✨ Process started for ${path} (${title})\n");
    $sourceDir = $rootDir->getChild($path);
    $iterator = (new PHPIterator($title, $sourceDir, $outputDir, $log))
        ->withUrlBase("${urlBase}${title}/");
    $iterator->write();
}
$timeTook = number_format(((int) hrtime(true) - $hrTime) / 1e+6, 0) . ' ms';
$log->write("\n🎉 Done in ${timeTook}!\n");
die();
