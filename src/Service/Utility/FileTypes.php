<?php

namespace App\Service\Utility;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileTypes
{
    public const TYPE_VIDEO = 'video';
    public const TYPE_IMAGE = 'image';
    public const TYPE_AUDIO = 'audio';
    public const TYPE_OTHER = 'other';
    public const EXT_WEBP   = 'webp';
    public const EXT_JPG   = 'jpg';

    public const VIDEO_MIME_TYPES = [
        'video/mp4', //.mp4
        'video/x-msvideo', //.avi
        'video/msvideo', //.avi
        'video/avs-video', //.avs
        'video/webm', //.webm
        'video/mpeg', //.mpeg
        'video/quicktime', //mov
        'video/ogg', //.ogv
        'video/mp2t', //.ts
        'video/3gpp', //.3gp
        'video/3gpp2', //.3g2
        'video/x-m4v', //.m4v
    ];

    public const IMAGES_MIME_TYPES = [
        'image/gif',
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/heic',
    ];

    public const AUDIO_MIME_TYPES = [
        'audio/mpeg',
        'audio/wav',
        'audio/x-wav',
        'audio/wave',
        'audio/ogg',
        'audio/mp4',
        'audio/x-m4a',
    ];

    public const AVAILABLE_VIDEO_EXTENSIONS = [
        'mp4', //.mp4
        'avi', //.avi
        'avs', //.avs
        'webm', //.webm
        'mpeg', //.mpeg
        'mov', //mov
        'ogv', //.ogv
        'ts', //.ts
        '3gp', //.3gp
        '3g2', //.3g2
        'm4v', //.m4v
    ];

    public static function getFileType(string $mimeType): string
    {
        if (in_array($mimeType, self::VIDEO_MIME_TYPES)) {
            return self::TYPE_VIDEO;
        }

        if (in_array($mimeType, self::IMAGES_MIME_TYPES)) {
            return self::TYPE_IMAGE;
        }

        if (in_array($mimeType, self::AUDIO_MIME_TYPES)) {
            return self::TYPE_AUDIO;
        }

        return self::TYPE_OTHER;
    }

    public static function isVideo(UploadedFile $file): bool
    {
        return in_array($file->getClientMimeType(), self::VIDEO_MIME_TYPES);
    }
}