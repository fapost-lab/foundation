<?php

declare(strict_types=1);

namespace FAPost\Foundation\Media\Enums;

/**
 * Coarse classification of a media file used by send_message and input nodes.
 *
 * Mapped from a mime type at upload time; channel adapters route to the right
 * provider endpoint (sendPhoto vs sendDocument vs sendVoice etc.) using this value.
 */
enum MediaKind: string
{
    case Image    = 'image';
    case Video    = 'video';
    case Audio    = 'audio';
    case Document = 'document';
    case Sticker  = 'sticker';
    case Other    = 'other';

    /**
     * Resolve the kind from an IANA mime type. Unknown types fall back to Document.
     */
    public static function fromMimeType(string $mimeType): self
    {
        $normalized = mb_strtolower(trim($mimeType));

        return match (true) {
            '' === $normalized                     => self::Other,
            str_starts_with($normalized, 'image/') => self::Image,
            str_starts_with($normalized, 'video/') => self::Video,
            str_starts_with($normalized, 'audio/') => self::Audio,
            default                                => self::Document,
        };
    }
}
