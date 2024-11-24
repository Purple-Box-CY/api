<?php

namespace App\Service\Infrastructure;

class RedisKeys
{
    public const string PREFIX_MAIN               = 'main';
    public const string PREFIX_AUTH               = 'auth';
    public const string PREFIX_JWT                = 'jwt';
    public const string PREFIX_EMAIL_CONFIRMATION = 'email-confirmation';
    public const string PREFIX_USER               = 'user';
    public const string PREFIX_CONFIG             = 'config';

    public const string KEY_CONTENT_MEDIA_ITEM = '%s';                   //id

    public const string KEY_ARTICLE_ITEM = 'article:%s'; //alias
    public const string KEY_MARKERS = 'markers:%s';
    public const string KEY_MARKER = 'marker:%s';

    public const string KEY_JWT_ITEM     = '%s';         //email
    public const string KEY_USER_ITEM    = '%s:profile'; //uid|username|id
    public const string KEY_USER_INFO    = '%s:info';
    public const string KEY_USER_COUNTRY = 'user:%s:country';

    public const string QUEUE_MAIN                           = 'main';
    public const string QUEUE_EVENTS                         = 'events';
    public const string QUEUE_MAIL                           = 'mail_to_send';

    public const array AVAILABLE_QUEUES = [
        self::QUEUE_MAIL,
        self::QUEUE_MAIN,
        self::QUEUE_EVENTS,
    ];
}
