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

use function Chevere\Components\Filesystem\fileForPath;
use Chevere\Components\Str\Str;
use function Chevere\Components\Writer\streamFor;
use Chevere\Components\Writer\StreamWriter;
use Chevere\Interfaces\Filesystem\DirInterface;
use Chevere\Interfaces\Writer\WriterInterface;
use Ds\Set;
use Generator;
use RecursiveDirectoryIterator;
use RecursiveFilterIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use Throwable;
use UnexpectedValueException;

class PHPIterator
{
    private string $title;

    private DirInterface $sourceDir;

    private DirInterface $outputDir;

    private WriterInterface $logWriter;

    private WriterInterface $readme;

    private string $readmeFilename;

    private RecursiveDirectoryIterator $dirIterator;

    private RecursiveFilterIterator $filterIterator;

    private RecursiveIteratorIterator $recursiveIterator;

    private Set $letters;

    private string $currentLetter = '';

    private string $urlBase = '';

    public function __construct(
        string $title,
        DirInterface $sourceDir,
        DirInterface $outputDir,
        WriterInterface $logWriter
    ) {
        $this->title = $title;
        $this->sourceDir = $sourceDir;
        $this->outputDir = $outputDir;
        $this->logWriter = $logWriter;
        $this->readmeFilename = $this->titleToDocument($title);
        $this->dirIterator = $this->getRecursiveDirectoryIterator($this->sourceDir);
        $this->filterIterator = $this->getRecursiveFilterIterator($this->dirIterator);
        $this->recursiveIterator = new RecursiveIteratorIterator($this->filterIterator);
        $this->letters = new Set();

        try {
            $this->recursiveIterator->rewind();
        } catch (UnexpectedValueException $e) {
            $this->logWriter->write(
                'Unable to rewind iterator: ' . $e->getMessage() .
                "\n\n" .
                '🤔 Try running with user privileges.'
            );
        }
    }

    public function withUrlBase(string $urlBase): self
    {
        $new = clone $this;
        $new->urlBase = $urlBase;

        return $new;
    }

    public function readmeFilename(): string
    {
        return $this->readmeFilename;
    }

    public function getFiles(): Generator
    {
        $files = [];
        while ($this->recursiveIterator->valid()) {
            $key = $this->recursiveIterator->current()->getPathName();
            $files[] = $key;
            $this->recursiveIterator->next();
        }
        asort($files);

        foreach ($files as $file) {
            yield $file;
        }
    }

    public function write(): void
    {
        if (! $this->outputDir->exists()) {
            $this->outputDir->create();
        }
        $readmePath = $this->outputDir->path()->toString() . $this->readmeFilename;
        $this->readme = new StreamWriter(streamFor($readmePath, 'w'));
        $this->logWriter->write('📝 Writing ' . $this->title . " readme at ${readmePath}\n");
        $this->readme->write(
            '---' .
            "\nsidebar: false" .
            "\neditLink: false" .
            "\n---" .
            "\n\n# " . $this->title
        );
        foreach ($this->getFiles() as $file) {
            $this->writeFile($file);
        }
    }

    public function writeFile(string $file): void
    {
        $remoteLink = $this->urlBase . (new Str($file))
            ->withReplaceFirst($this->sourceDir->path()->toString(), '')
            ->toString();
        $namespace = $this->getNamespaceFromFile($file);
        $className = $this->getClassNameFromFile($file);
        if ($className === '') {
            return;
        }

        try {
            $reflection = new ReflectionInterface(
                new ReflectionClass("${namespace}\\${className}")
            );
            $fileName = str_replace('\\', '/', $reflection->reflectionClass()->getName()) . '.md';
        } catch (Throwable $e) {
        }
        $filePath = $this->outputDir->path()->toString() . $fileName;
        $file = fileForPath($filePath);
        if (! $file->exists()) {
            $file->create();
        }
        $reference = new Reference($reflection->reflectionClass()->getName());
        $explode = explode('/', $reference->path());
        $shortName = $reflection->reflectionClass()->getShortName();
        $letter = $explode[2];
        if ($this->currentLetter !== $letter) {
            $this->readme->write("\n\n## ${letter}\n");
            $this->currentLetter = $letter;
            $this->letters->add($this->currentLetter);
        }
        $this->readme->write(
            "\n- [${shortName}](./" . $reference->markdownPath() . ')'
        );
        $this->logWriter->write("* ${filePath}\n");
        $writer = new StreamWriter(streamFor($filePath, 'w'));
        $interfaceWriter = new InterfaceWriter($remoteLink, $reflection, $writer);
        $interfaceWriter->write();
    }

    private function getNamespaceFromFile(string $file): string
    {
        $src = file_get_contents($file);
        $tokens = token_get_all($src);
        $count = count($tokens);
        for ($i = 0, $namespace_ok = false, $namespace = ''; $i < $count; ++$i) {
            $token = $tokens[$i];
            if (is_array($token) && $token[0] === T_NAMESPACE) {
                while (++$i < $count) {
                    if ($tokens[$i] === ';') {
                        $namespace_ok = true;
                        $namespace = trim($namespace);

                        return $namespace_ok ? $namespace : '';
                    }
                    $namespace .= is_array($tokens[$i]) ? $tokens[$i][1] : $tokens[$i];
                }
            }
        }
    }

    private function getClassNameFromFile($file): string
    {
        $php_code = file_get_contents($file);
        $classes = [];
        $tokens = token_get_all($php_code);
        $count = count($tokens);
        for ($i = 0; $i < $count; $i++) {
            if (! isset($tokens[$i - 2], $tokens[$i - 1], $tokens[$i])) {
                continue;
            }
            if (
                in_array($tokens[$i - 2][0], [T_INTERFACE, T_CLASS, T_TRAIT], true)
                && $tokens[$i - 1][0] === T_WHITESPACE
                && $tokens[$i][0] === T_STRING
            ) {
                $classes[] = $tokens[$i][1];
            }
        }

        return $classes[0] ?? '';
    }

    private function getRecursiveDirectoryIterator(DirInterface $dir): RecursiveDirectoryIterator
    {
        return new RecursiveDirectoryIterator(
            $dir->path()->toString(),
            RecursiveDirectoryIterator::SKIP_DOTS
            | RecursiveDirectoryIterator::KEY_AS_PATHNAME
        );
    }

    private function getRecursiveFilterIterator(RecursiveDirectoryIterator $dirIterator): RecursiveFilterIterator
    {
        return new class($dirIterator) extends RecursiveFilterIterator {
            public function accept(): bool
            {
                if ($this->hasChildren()) {
                    return true;
                }

                return $this->current()->getExtension() === 'php';
            }
        };
    }

    private function titleToDocument(string $title): string
    {
        return strtr(strtolower($title), [
            ' ' => '-',
        ]) . '.md';
    }
}
