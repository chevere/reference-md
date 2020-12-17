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
use RecursiveDirectoryIterator;
use RecursiveFilterIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
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
        $this->dirIterator = $this->getRecursiveDirectoryIterator($source->path()->toString());
        $this->filterIterator = $this->getRecursiveFilterIterator($this->dirIterator);
        $this->recursiveIterator = new RecursiveIteratorIterator($this->filterIterator);
        try {
            $this->recursiveIterator->rewind();
        } catch (UnexpectedValueException $e) {
            echo 'Unable to rewind iterator: ' . $e->getMessage() .
                "\n\n" .
                '🤔 Try running with user privileges over the directories.';
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
        $readmePath = $writeDir->path()->toString() . $this->readme;
        $readme = new StreamWriter(streamFor($readmePath, 'w'));
        $log->write('📝 Writing ' . $this->title . " readme @ $readmePath\n");
        $readme->write(
            "---" .
            "\nsidebar: false" .
            "\neditLink: false" .
            "\n---" .
            "\n\n# " . $this->title
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
            $remoteUrl = $remote . (new Str($file))
                ->withReplaceFirst($this->root->path()->toString(), '')
                ->toString();
            $namespace = $this->getNamespaceFromFile($file);
            $className = $this->getClassNameFromFile($file);
            try {
                $reflection = new ReflectionInterface(
                    new ReflectionClass("$namespace\\$className")
                );
                $fileName = str_replace('\\', '/', $reflection->reflectionClass()->getName()) . '.md';
            } catch (Throwable $e) {
                xdd($file, "$namespace\\$className");
            }
            $filePath = $writeDir->path()->toString() . $fileName;
            $file = fileForPath($filePath);
            if (!$file->exists()) {
                $file->create();
            }
            $reference = new Reference($reflection->reflectionClass()->getName());
            $explode = explode('/', $reference->path());
            $shortName = $reflection->reflectionClass()->getShortName();
            $letter = $explode[2];
            if ($currentLetter !== $letter) {
                $readme->write("\n\n## $letter\n");
                $currentLetter = $letter;
                $letters[] = $currentLetter;
            }
            $readme->write(
                "\n- [$shortName](./" . $reference->markdownPath() . ')'
            );
            $log->write("- $filePath\n");
            $writer = new StreamWriter(streamFor($filePath, 'w'));
            $interfaceWriter = new InterfaceWriter($remoteUrl, $reflection, $writer);
            $interfaceWriter->write();
            continue;
        }
    }

    private function getNamespaceFromFile(string $file): string
    {
        $src = file_get_contents($file);
        $tokens = token_get_all($src);
        $count = count($tokens);
        for($i = 0, $namespace_ok = false, $namespace = ''; $i < $count; ++$i) {
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
            if(!isset($tokens[$i - 2], $tokens[$i - 1], $tokens[$i])) {
                continue;
            }
            if (
                in_array($tokens[$i - 2][0], [T_INTERFACE, T_CLASS, T_TRAIT])
                && $tokens[$i - 1][0] == T_WHITESPACE
                && $tokens[$i][0] == T_STRING
            ) {
                $classes[] = $tokens[$i][1];
            }
        }

        return $classes[0];
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
