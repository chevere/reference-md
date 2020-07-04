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
use Chevere\Components\Str\StrBool;
use Chevere\ReferenceMd\Reference;

final class ReferenceHighlight
{
    const PHP_CLASSES = [
        'AppendIterator',
        'ArgumentCountError',
        'ArithmeticError',
        'ArrayIterator',
        'ArrayObject',
        'AssertionError',
        'BadFunctionCallException',
        'BadMethodCallException',
        'CachingIterator',
        'CallbackFilterIterator',
        'Closure',
        'CompileError',
        'Countable',
        'DateInterval',
        'DatePeriod',
        'DateTime',
        'DateTimeImmutable',
        'DateTimeZone',
        'Directory',
        'DirectoryIterator',
        'DivisionByZeroError',
        'DomainException',
        'EmptyIterator',
        'Error',
        'ErrorException',
        'Exception',
        'FilesystemIterator',
        'FilterIterator',
        'Generator',
        'GlobIterator',
        'HashContext',
        'InfiniteIterator',
        'InvalidArgumentException',
        'IteratorIterator',
        'LengthException',
        'LibXMLError',
        'LimitIterator',
        'LogicException',
        'MultipleIterator',
        'NoRewindIterator',
        'OuterIterator',
        'OutOfBoundsException',
        'OutOfRangeException',
        'OverflowException',
        'ParentIterator',
        'ParseError',
        'php_user_filter',
        'RangeException',
        'RecursiveArrayIterator',
        'RecursiveCachingIterator',
        'RecursiveCallbackFilterIterator',
        'RecursiveDirectoryIterator',
        'RecursiveFilterIterator',
        'RecursiveIterator',
        'RecursiveIteratorIterator',
        'RecursiveRegexIterator',
        'RecursiveTreeIterator',
        'Reflection',
        'ReflectionClass',
        'ReflectionClassConstant',
        'ReflectionException',
        'ReflectionExtension',
        'ReflectionFunction',
        'ReflectionFunctionAbstract',
        'ReflectionGenerator',
        'ReflectionMethod',
        'ReflectionNamedType',
        'ReflectionObject',
        'ReflectionParameter',
        'ReflectionProperty',
        'ReflectionReference',
        'ReflectionType',
        'ReflectionZendExtension',
        'RegexIterator',
        'RuntimeException',
        'SeekableIterator',
        'SessionHandler',
        'SplDoublyLinkedList',
        'SplFileInfo',
        'SplFileObject',
        'SplFixedArray',
        'SplHeap',
        'SplMaxHeap',
        'SplMinHeap',
        'SplObjectStorage',
        'SplPriorityQueue',
        'SplQueue',
        'SplStack',
        'SplTempFileObject',
        'Throwable',
        'TypeError',
        'UnderflowException',
        'UnexpectedValueException',
        'WeakReference',
    ];

    const PHP_URL_MANUAL = 'https://www.php.net/manual/';

    private Reference $reference;

    public function __construct(Reference $reference)
    {
        $this->reference = $reference;
        $this->explode = explode('/', $reference->path());
        if (count($this->explode) > 1) {
            array_pop($this->explode);
        }
    }

    public function getLinkTo(Reference $targetReference): string
    {
        if (
            $this->reference->name() === $targetReference->name()
            || $targetReference->isLinked() === false
        ) {
            return $targetReference->shortName();
        }
        if ($this->reference->base() === $targetReference->base()) {
            return './' . $targetReference->markdownName();
        }
        $return = $targetReference->name();
        $pops = $this->explode;
        $levels = 0;
        $toStr = new Str($targetReference->path());
        $toStrBol = new StrBool($targetReference->path());
        foreach ($pops as $chop) {
            $try = implode('/', $pops) . '/';
            if (!$toStrBol->startsWith($try)) {
                array_pop($pops);
                $levels++;
            } else {
                $return = $toStr
                    ->withReplaceFirst($try, str_repeat('../', $levels))
                    ->withReplaceLast(
                        $targetReference->shortName(),
                        $targetReference->markdownName()
                    )
                    ->toString();
                break;
            }
        }

        return $return;
    }

    public function getHighlightTo(Reference $targetReference): string
    {
        $link = $this->getLinkTo($targetReference);
        if ($targetReference->isLinked() === false) {
            if (in_array($targetReference->name(), self::PHP_CLASSES)) {
                $link = $this->getPHPManualPage($targetReference->name());
            }
        }
        $linkBool = new StrBool($link);
        if ($linkBool->startsWith('.') || $linkBool->startsWith('http')) {
            return '[' . $targetReference->shortName() . ']'
            . '(' . $link . ')';
        }

        return $link;
    }

    private function getPHPManualPage(string $className): string
    {
        $className = strtolower($className);
        $className = str_replace('_', '-', $className);

        return self::PHP_URL_MANUAL . 'class.' . $className;
    }
}
