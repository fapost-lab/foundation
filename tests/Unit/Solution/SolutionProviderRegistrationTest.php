<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Solution;

use Fapost\Foundation\Action\ActionHandlerInterface;
use Fapost\Foundation\Contracts\CoreRegistrarInterface;
use Fapost\Foundation\Lifecycle\AbstractSolutionServiceProvider;
use Illuminate\Contracts\Foundation\Application;
use LogicException;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

final class SolutionProviderRegistrationTest extends TestCase
{
    public function test_the_registrar_contract_registers_action_handlers_by_class(): void
    {
        $method = new ReflectionMethod(CoreRegistrarInterface::class, 'registerActionHandler');

        $this->assertSame(['handlerClass'], array_map(static fn ($parameter): string => $parameter->getName(), $method->getParameters()));
        $this->assertSame('string', (string) $method->getParameters()[0]->getType());
        $this->assertSame('void', (string) $method->getReturnType());
        $this->assertStringContainsString('class-string<ActionHandlerInterface>', (string) $method->getDocComment());
        $this->assertTrue(interface_exists(ActionHandlerInterface::class));
    }

    public function test_registering_extensions_is_core_entry_point_and_final(): void
    {
        $this->assertTrue((new ReflectionMethod(AbstractSolutionServiceProvider::class, 'registerExtensionsWith'))->isFinal());
    }

    public function test_registrar_given_by_core_reaches_register_extensions(): void
    {
        $registrar = $this->createMock(CoreRegistrarInterface::class);
        $registrar->expects($this->once())->method('registerActionHandler')->with('Acme\\Feedback\\SubmitAction');

        $provider = new class ($this->createStub(Application::class)) extends AbstractSolutionServiceProvider {
            protected function registerExtensions(CoreRegistrarInterface $registrar): void
            {
                $registrar->registerActionHandler('Acme\\Feedback\\SubmitAction');
            }
        };

        $provider->registerExtensionsWith($registrar);
    }

    public function test_boot_runs_the_solution_without_asking_the_container_for_a_registrar(): void
    {
        $app = $this->createMock(Application::class);
        $app->expects($this->never())->method('bound');
        $app->expects($this->never())->method('make');

        $provider = new class ($app) extends AbstractSolutionServiceProvider {
            public bool $booted = false;

            protected function registerExtensions(CoreRegistrarInterface $registrar): void
            {
                throw new LogicException('boot() must not register extensions');
            }

            protected function bootSolution(): void
            {
                $this->booted = true;
            }
        };

        $provider->boot();

        $this->assertTrue($provider->booted);
    }
}
