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
use function Chevere\Components\Filesystem\fileForPath;
use function Chevere\Components\Filesystem\filePhpForPath;
use Chevere\Components\Filesystem\FilePhpReturn;
use Chevere\Components\VarSupport\VarStorable;
use function Chevere\Components\Writer\streamFor;
use Chevere\Components\Writer\StreamWriter;
use Chevere\ReferenceMd\PHPIterator;
use samejack\PHP\ArgvParser;

require 'vendor/autoload.php';

$hrTime = (int) hrtime(true);
$log = new StreamWriter(streamFor('php://stdout', 'w'));
set_error_handler('Chevere\Components\ThrowableHandler\errorsAsExceptions');
set_exception_handler('Chevere\Components\ThrowableHandler\consoleHandler');
$options = (new ArgvParser())->parseConfigs();
$baseUrl = $options['b'] ?? '';
$sourceDir = dirForPath(realpath($options['s']));
$sourceDir->assertExists();
$sourceDirToScan = $sourceDir;
if($options['p'] ?? false) {
    $sourceDirToScan = $sourceDir->getChild($options['p']);
}
$sourceDirToScan->assertExists();
$vendorDir = $sourceDir->path()->getChild('vendor/autoload.php')->toString();
$sourceAutoLoader = fileForPath($vendorDir);
$sourceAutoLoader->assertExists();
require $sourceAutoLoader->path()->toString();
$outputDir = dirForPath(realpath($options['o']));
if (! $outputDir->exists()) {
    $outputDir->create();
} else {
    $outputDir->removeContents();
}
// $sidebar = filePhpForPath($outputDir->path()->getChild('sidebar.php')->toString());
// $sidebar->file()->create();
// (new FilePhpReturn($sidebar))
//     ->put(new VarStorable('auto'));

$dirIteratorSource = new DirectoryIterator($sourceDirToScan->path()->toString());
foreach($dirIteratorSource as $fileinfo) {
    if (!$fileinfo->isDir() || $fileinfo->isDot()) {
        continue;
    }
    $workingPath = $fileinfo->getRealPath();
    $component = $fileinfo->getBasename();
    $log->write("\n🆕 Process started for ./${component}\n--\n");
    $scanDir = dirForPath($workingPath);
    $iterator = (new PHPIterator($component, $scanDir, $outputDir, $log))
        ->withUrlBase("${baseUrl}${component}/");
    $iterator->write();
}

$timeTook = number_format(((int) hrtime(true) - $hrTime) / 1e+6, 0) . ' ms';
$log->write("\n🎉 Done in ${timeTook}!\n");
die();
