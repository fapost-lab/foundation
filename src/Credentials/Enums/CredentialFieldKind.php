<?php

declare(strict_types=1);

namespace Fapost\Foundation\Credentials\Enums;

/**
 * How a credential field is entered, and so the input that edits it.
 *
 * New kinds may be added in a minor release: callers matching on it keep a default arm.
 *
 * @experimental
 */
enum CredentialFieldKind: string
{
    /** A line of text. The only kind a secret field may have. */
    case Text = 'text';

    /** An absolute URL, such as an OAuth2 token endpoint. */
    case Url = 'url';

    /** One of a fixed list of strings. */
    case Select = 'select';
}
