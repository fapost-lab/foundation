<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Console;

use FilesystemIterator;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionMethod;

/**
 * The console extension contract is experimental: every public type and public method in it says so, so an
 * author's IDE and static analysis warn before they build on it. Internal helpers are exempt.
 */
final class ExperimentalSurfaceTest extends TestCase
{
    public function test_every_public_type_and_method_of_the_console_contract_is_marked_experimental(): void
    {
        $root    = dirname(__DIR__, 3) . '/src/Console';
        $missing = [];
        $checked = 0;

        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)) as $file) {
            if ('php' !== $file->getExtension()) {
                continue;
            }

            $class      = 'Fapost\\Foundation\\Console\\' . str_replace('/', '\\', mb_substr($file->getPathname(), mb_strlen($root) + 1, -4));
            $reflection = new ReflectionClass($class);

            if (str_contains((string) $reflection->getDocComment(), '@internal')) {
                continue;
            }

            $checked++;

            if (!str_contains((string) $reflection->getDocComment(), '@experimental')) {
                $missing[] = $class;
            }

            foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                if ($method->getDeclaringClass()->getName() !== $class || $method->isConstructor() || $reflection->isEnum()) {
                    continue;
                }

                if (!str_contains((string) $method->getDocComment(), '@experimental')) {
                    $missing[] = $class . '::' . $method->getName() . '()';
                }
            }
        }

        $this->assertGreaterThan(20, $checked);
        $this->assertSame([], $missing, "Not marked @experimental:\n" . implode("\n", $missing));
    }
}
