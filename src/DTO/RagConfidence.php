<?php

declare(strict_types=1);

namespace Fapost\Foundation\DTO;

enum RagConfidence: string
{
    case Low    = 'low';
    case Medium = 'medium';
    case High   = 'high';
}
