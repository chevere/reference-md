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

use Chevere\Components\Writer\StreamWriter;
use Chevere\ReferenceMd\PHPIterator;
use function Chevere\Components\Filesystem\dirFromString;
use function Chevere\Components\Filesystem\fileFromString;
use function Chevere\Components\Writer\streamFor;
use function Chevere\Components\Writer\writerForFile;
use function Chevere\Components\Writer\writerForStream;

require 'vendor/autoload.php';

$hrTime = (int) hrtime(true);

set_error_handler('Chevere\Components\ThrowableHandler\errorsAsExceptions');
set_exception_handler('Chevere\Components\ThrowableHandler\consoleHandler');

$remote = 'https://github.com/chevere/chevere/blob/master/';
$source = '/home/rodolfo/git/chevere/chevere/';
$root = '/home/rodolfo/git/chevere/chevere/';
$target = '/home/rodolfo/git/chevere/docs/reference/';
$targetDir = dirFromString($target);
$rootDir = dirFromString($root);
if (!$targetDir->exists()) {
    $targetDir->create();
}
$readmeFilename = $targetDir->path()->getChild('README.md')->absolute();
$readme = writerForFile(fileFromString($readmeFilename), 'w');
$log = new StreamWriter(streamFor('php://stdout', 'w'));
$log->write("📝 Writing reference readme @ $readmeFilename\n");
$readme->write(
    "---\n" .
    "sidebar: false\n" .
    "editLink: false\n" .
    "---\n" .
    "\n# Reference\n" .
    "\nThis is the public reference for exceptions and interfaces.\n"
);
foreach ([
    'exceptions/' => 'Exceptions',
    'interfaces/' => 'Interfaces',
] as $path => $title) {
    $log->write("\n✨ Process started for $path ($title)\n");
    $sourceDir = $rootDir->getChild($path);
    $iterator = new PHPIterator($title, $sourceDir, $rootDir);
    $iterator->write($remote, $targetDir, $log);
    $readme->write("\n- [$title](./" . $iterator->readme() . ')');
}
$timeTook = number_format(((int) hrtime(true) - $hrTime) / 1e+6, 0) . ' ms';
$log->write("\n🎉 Done in $timeTook!\n");
die();
