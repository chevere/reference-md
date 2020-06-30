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

use Chevere\Components\Filesystem\FileFromString;
use Chevere\Components\Writer\StreamWriterFromString;
use Chevere\ReferenceMd\InterfaceWriter;
use Chevere\ReferenceMd\Reference;
use Chevere\ReferenceMd\ReferenceHighlight;
use Chevere\ReferenceMd\ReflectionFileInterface;

require 'vendor/autoload.php';

$remote = 'https://github.com/chevere/chevere/blob/master/';
$target = 'interfaces/Cache/CacheInterface.php';
$remoteUrl = $remote . $target;
$reflection = new ReflectionFileInterface('vendor/chevere/chevere/' . $target);
$saveAs = $reflection->interface()->getName() . '.md';
$saveAs = __DIR__ . '/reference/' . str_replace('\\', '/', $saveAs);
$file = new FileFromString($saveAs);
if (!$file->exists()) {
    $file->create();
}
$interfaceWriter = new InterfaceWriter($remoteUrl, $reflection);
$writer = new StreamWriterFromString($saveAs, 'w');
$interfaceWriter->write($writer);
