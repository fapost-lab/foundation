<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Architecture;

use FilesystemIterator;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionProperty;

/**
 * Every own property and class constant under src/ must be typed.
 *
 * Exception: a member that overrides an untyped member of a vendor parent must stay untyped
 * (PHP fatals otherwise) and must carry a PHPDoc `@var`.
 */
final class TypedMembersTest extends TestCase
{
    private const string NAMESPACE_PREFIX = 'Fapost\\Foundation\\';

    /**
     * Members that are intentionally left untyped because typing them is a backwards-incompatible
     * change for subclasses in Solutions. Keyed by "Class::$property" or "Class::CONSTANT"; the
     * value is the reason. Keep empty unless a reason is written down.
     *
     * @var array<string, string>
     */
    private const array ALLOWED_UNTYPED = [];

    public function test_all_own_properties_and_constants_are_typed(): void
    {
        foreach (self::ALLOWED_UNTYPED as $member => $reason) {
            $this->assertNotSame('', mb_trim($reason), "Allow-list entry {$member} needs a reason.");
        }

        $violations = [];

        foreach ($this->classes() as $class) {
            $reflection = new ReflectionClass($class);

            foreach ($reflection->getProperties() as $property) {
                if ($property->getDeclaringClass()->getName() !== $class || $property->hasType()) {
                    continue;
                }

                $key = $class . '::$' . $property->getName();

                if (isset(self::ALLOWED_UNTYPED[$key]) || $this->isDocumentedUntypedOverride($reflection, $property)) {
                    continue;
                }

                $violations[] = $key;
            }

            foreach ($reflection->getReflectionConstants() as $constant) {
                if ($constant->getDeclaringClass()->getName() !== $class || $constant->isEnumCase() || $constant->hasType()) {
                    continue;
                }

                $key = $class . '::' . $constant->getName();

                if (isset(self::ALLOWED_UNTYPED[$key]) || $this->overridesUntypedConstant($reflection, $constant)) {
                    continue;
                }

                $violations[] = $key;
            }
        }

        $this->assertSame([], $violations, "Untyped own members:\n" . implode("\n", $violations));
    }

    /**
     * @param ReflectionClass<object> $class
     */
    private function isDocumentedUntypedOverride(ReflectionClass $class, ReflectionProperty $property): bool
    {
        $parent = $class->getParentClass();

        if (false === $parent || !$parent->hasProperty($property->getName())) {
            return false;
        }

        $parentProperty = $parent->getProperty($property->getName());

        if ($parentProperty->hasType() || $parentProperty->isPrivate()) {
            return false;
        }

        $doc = $property->getDocComment();

        return false !== $doc && str_contains($doc, '@var');
    }

    /**
     * @param ReflectionClass<object> $class
     */
    private function overridesUntypedConstant(ReflectionClass $class, ReflectionClassConstant $constant): bool
    {
        $ancestors = array_merge(
            false === $class->getParentClass() ? [] : [$class->getParentClass()],
            array_map(static fn (string $name): ReflectionClass => new ReflectionClass($name), $class->getInterfaceNames()),
        );

        foreach ($ancestors as $ancestor) {
            $parentConstant = $ancestor->getReflectionConstant($constant->getName());

            if (false !== $parentConstant && !$parentConstant->isPrivate() && !$parentConstant->hasType()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<class-string>
     */
    private function classes(): array
    {
        $root    = dirname(__DIR__, 3) . '/src';
        $classes = [];

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
        );

        foreach ($files as $file) {
            if ('php' !== $file->getExtension()) {
                continue;
            }

            $relative = mb_substr($file->getPathname(), mb_strlen($root) + 1, -4);
            $name     = self::NAMESPACE_PREFIX . str_replace('/', '\\', $relative);

            if (class_exists($name) || interface_exists($name) || trait_exists($name) || enum_exists($name)) {
                $classes[] = $name;
            }
        }

        sort($classes);

        $this->assertNotEmpty($classes, 'PSR-4 scan found no classes under src/.');

        return $classes;
    }
}
