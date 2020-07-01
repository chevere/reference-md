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

use Chevere\Components\Filesystem\DirFromString;
use Chevere\Components\ThrowableHandler\Documents\ConsoleDocument;
use Chevere\Components\ThrowableHandler\ThrowableHandler;
use Chevere\Components\ThrowableHandler\ThrowableRead;
use Chevere\Components\Writer\StreamWriterFromString;
use Chevere\ReferenceMd\PHPIterator;

require 'vendor/autoload.php';

$remote = 'https://github.com/chevere/chevere/blob/master/';
$source = '/home/rodolfo/git/chevere/chevere/';
$root = '/home/rodolfo/git/chevere/chevere/';
$target = '/home/rodolfo/git/chevere/docs/reference/';
$targetDir = new DirFromString($target);
$rootDir = new DirFromString($root);
$README = new StreamWriterFromString(
    $targetDir->path()->getChild('README.md')->absolute(),
    'w'
);
$README->write(
    "---\n" .
    "sidebar: false\n" .
    "editLink: false\n" .
    "---\n" .
    "\n# Reference\n" .
    "\nThis is the public reference for exceptions and interfaces.\n"
);
try {
    foreach ([
        'exceptions/' => 'Exceptions',
        'interfaces/' => 'Interfaces',
    ] as $path => $title) {
        $sourceDir = $rootDir->getChild($path);
        $iterator = new PHPIterator($title, $sourceDir, $rootDir);
        $iterator->write($remote, $targetDir);
        $README->write("\n- [$title](./" . $iterator->readme() . ')');
    }
} catch (Exception $e) {
    $handler = new ThrowableHandler(new ThrowableRead($e));
    $document = new ConsoleDocument($handler);
    echo $document->toString() . "\n";
}
