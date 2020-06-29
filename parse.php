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

use Chevere\Components\Writer\StreamWriterFromString;
use Chevere\ReferenceMd\InterfaceWriter;
use Chevere\ReferenceMd\MethodWriter;
use Chevere\ReferenceMd\Reference;
use Chevere\ReferenceMd\ReflectionFileInterface;
use Go\ParserReflection\ReflectionClassConstant;
use Go\ParserReflection\ReflectionMethod;
use phpDocumentor\Reflection\DocBlockFactory;

require 'vendor/autoload.php';

$remote = 'https://github.com/chevere/chevere/blob/master/';
$target = 'interfaces/Message/MessageInterface.php';
$remoteUrl = $remote . $target;
$reflection = new ReflectionFileInterface('vendor/chevere/chevere/' . $target);
$interfaceWriter = new InterfaceWriter($remoteUrl, $reflection);
$writer = new StreamWriterFromString(__DIR__ . '/md.md', 'w');
$interfaceWriter->write($writer);
