<?php

declare(strict_types=1);

namespace FAPost\Foundation\DTO;

enum RagConfidence: string
{
    case Low    = 'low';
    case Medium = 'medium';
    case High   = 'high';
}
