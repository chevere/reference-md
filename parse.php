<?php

/*
 * This file is part of Cheveress.
 *
 * (c) Rodolfo Berrios <rodolfo@chevere.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

use Chevere\Components\Filesystem\FilePhpReturn;
use Chevere\Components\VarStorable\VarStorable;
use Chevere\Components\Writer\StreamWriter;
use Chevere\ReferenceMd\PHPIterator;
use function Chevere\Components\Filesystem\dirForPath;
use function Chevere\Components\Filesystem\filePhpForPath;
use function Chevere\Components\Writer\streamFor;

require 'vendor/autoload.php';

$hrTime = (int) hrtime(true);
set_error_handler('Chevere\Components\ThrowableHandler\errorsAsExceptions');
set_exception_handler('Chevere\Components\ThrowableHandler\consoleHandler');
$remote = 'https://github.com/chevere/chevere/blob/master/';
$source = '/home/rodolfo/git/chevere/chevere/';
require $source . 'vendor/autoload.php';
$root = $source;
$target = '/home/rodolfo/git/chevere/docs/reference/';
$targetDir = dirForPath($target);
$rootDir = dirForPath($root);
if (!$targetDir->exists()) {
    $targetDir->create();
} else {
    $targetDir->removeContents();
}
$sidebar = filePhpForPath($target . 'sidebar.php');
$sidebar->file()->create();
(new FilePhpReturn($sidebar))->put(new VarStorable('auto'));
$readmeFilename = $targetDir->path()->getChild('README.md')->toString();
$readme = new StreamWriter(streamFor($readmeFilename, 'w'));
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
    'src/Chevere/Exceptions/' => 'Exceptions',
    'src/Chevere/Interfaces/' => 'Interfaces',
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
