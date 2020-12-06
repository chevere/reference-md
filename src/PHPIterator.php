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

namespace Chevere\ReferenceMd;

use Chevere\Components\Str\Str;
use Chevere\Components\Writer\StreamWriter;
use Chevere\Interfaces\Filesystem\DirInterface;
use Chevere\Interfaces\Writer\WriterInterface;
use Go\ParserReflection\ReflectionFile;
use RecursiveDirectoryIterator;
use RecursiveFilterIterator;
use RecursiveIteratorIterator;
use Throwable;
use UnexpectedValueException;

use function Chevere\Components\Filesystem\fileForPath;
use function Chevere\Components\Writer\streamFor;

class PHPIterator
{
    private string $title;

    private string $readme;

    private DirInterface $root;

    private RecursiveDirectoryIterator $dirIterator;

    private RecursiveFilterIterator $filterIterator;

    private RecursiveIteratorIterator $recursiveIterator;

    public function __construct(string $title, DirInterface $source, DirInterface $root)
    {
        $this->title = $title;
        $this->readme = $this->titleToPage($title);
        $this->root = $root;
        $this->dirIterator = $this->getRecursiveDirectoryIterator($source->path()->absolute());
        $this->filterIterator = $this->getRecursiveFilterIterator($this->dirIterator);
        $this->recursiveIterator = new RecursiveIteratorIterator($this->filterIterator);
        try {
            $this->recursiveIterator->rewind();
        } catch (UnexpectedValueException $e) {
            echo 'Unable to rewind iterator: '
                . $e->getMessage() . "\n\n"
                . '🤔 Maybe try with user privileges?';
        }
    }

    public function readme(): string
    {
        return $this->readme;
    }

    public function write(string $remote, DirInterface $writeDir, WriterInterface $log): void
    {
        if (!$writeDir->exists()) {
            $writeDir->create();
        }
        $files = [];
        $readmePath = $writeDir->path()->absolute() . $this->readme;
        $readme = new StreamWriter(streamFor($readmePath, 'w'));
        $log->write('📝 Writing ' . $this->title . " readme @ $readmePath\n");
        $readme->write(
            "---\n" .
            "sidebar: false\n" .
            "editLink: false\n" .
            "---\n" .
            "\n# " . $this->title
        );
        while ($this->recursiveIterator->valid()) {
            $key = $this->recursiveIterator->current()->getPathName();
            $files[] = $key;
            $this->recursiveIterator->next();
        }
        asort($files);
        $letters = [];
        $currentLetter = '';
        
        foreach ($files as $file) {
            $target = $file;
            $remoteUrl = $remote . (new Str($target))
                ->withReplaceFirst($this->root->path()->absolute(), '')
                ->toString();
            $reflectionFile = new ReflectionFile($target);
            try {
                $reflection = new ReflectionInterface($reflectionFile);
                $fileName = $reflection->interface()->getName() . '.md';
            } catch (Throwable $e) {
                continue;
            }
            $filePath = $writeDir->path()->absolute() . str_replace('\\', '/', $fileName);
            $file = fileForPath($filePath);
            if (!$file->exists()) {
                $file->create();
            }
            $reference = new Reference($reflection->interface()->getName());
            $explode = explode('/', $reference->path());
            $shortName = $reflection->interface()->getShortName();
            $letter = $explode[2];
            if ($currentLetter !== $letter) {
                $readme->write("\n\n## $letter\n");
                $currentLetter = $letter;
                $letters[] = $currentLetter;
            }
            $readme->write(
                "\n  - [$shortName](./" . $reference->markdownPath() . ')'
            );
            $log->write("- $filePath\n");
            $interfaceWriter = new InterfaceWriter($remoteUrl, $reflection);
            $writer = new StreamWriter(streamFor($filePath, 'w'));
            $interfaceWriter->write($writer);
            continue;
        }
    }

    private function getRecursiveDirectoryIterator(string $path): RecursiveDirectoryIterator
    {
        return new RecursiveDirectoryIterator(
            $path,
            RecursiveDirectoryIterator::SKIP_DOTS
            | RecursiveDirectoryIterator::KEY_AS_PATHNAME
        );
    }

    private function getRecursiveFilterIterator(RecursiveDirectoryIterator $dirIterator): RecursiveFilterIterator
    {
        return new class($dirIterator) extends RecursiveFilterIterator
        {
            public function accept(): bool
            {
                if ($this->hasChildren()) {
                    return true;
                }

                return $this->current()->getExtension() === 'php';
            }
        };
    }

    private function titleToPage(string $title): string
    {
        return strtr(strtolower($title), [
            ' ' => '-',
        ]) . '.md';
    }
}
