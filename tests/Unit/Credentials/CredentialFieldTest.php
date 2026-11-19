<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Credentials;

use Fapost\Foundation\Credentials\DTO\CredentialField;
use Fapost\Foundation\Credentials\DTO\CredentialReference;
use Fapost\Foundation\Credentials\Enums\CredentialFieldKind;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CredentialFieldTest extends TestCase
{
    /**
     * @return iterable<string, array{0: callable(): CredentialField}>
     */
    public static function invalidFields(): iterable
    {
        yield 'a key that is not snake_case' => [static fn (): CredentialField => new CredentialField('ClientSecret', 'Client secret')];
        yield 'an empty label' => [static fn (): CredentialField => new CredentialField('token', ' ')];
        yield 'a secret url' => [static fn (): CredentialField => new CredentialField('token', 'Token', secret: true, kind: CredentialFieldKind::Url)];
        yield 'a secret select' => [static fn (): CredentialField => new CredentialField('token', 'Token', secret: true, kind: CredentialFieldKind::Select, choices: ['a'])];
        yield 'a select without choices' => [static fn (): CredentialField => new CredentialField('mode', 'Mode', kind: CredentialFieldKind::Select)];
        yield 'choices on a text' => [static fn (): CredentialField => new CredentialField('mode', 'Mode', choices: ['a'])];
        /** @phpstan-ignore argument.type */
        yield 'a non-string choice' => [static fn (): CredentialField => new CredentialField('mode', 'Mode', kind: CredentialFieldKind::Select, choices: [1])];
    }
    public function test_a_field_is_a_required_non_secret_text_by_default(): void
    {
        $field = new CredentialField('username', 'Username');

        $this->assertFalse($field->secret);
        $this->assertTrue($field->required);
        $this->assertSame(CredentialFieldKind::Text, $field->kind);
        $this->assertSame([], $field->choices);
    }

    public function test_a_select_keeps_its_choices(): void
    {
        $field = new CredentialField('client_auth', 'Client authentication', kind: CredentialFieldKind::Select, choices: ['basic', 'body']);

        $this->assertSame(['basic', 'body'], $field->choices);
    }

    #[DataProvider('invalidFields')]
    public function test_it_refuses_a_field_that_does_not_fit(callable $make): void
    {
        $this->expectException(InvalidArgumentException::class);

        $make();
    }

    public function test_field_kind_values_are_stable(): void
    {
        $this->assertSame(
            ['text', 'url', 'select'],
            array_map(static fn (CredentialFieldKind $kind): string => $kind->value, CredentialFieldKind::cases()),
        );
    }

    public function test_a_reference_needs_an_id_a_name_and_a_type(): void
    {
        $reference = new CredentialReference('cred-1', 'Stripe', 'bearer');

        $this->assertSame(['cred-1', 'Stripe', 'bearer'], [$reference->id, $reference->name, $reference->type]);

        $this->expectException(InvalidArgumentException::class);

        new CredentialReference('cred-1', '', 'bearer');
    }
}
