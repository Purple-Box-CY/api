<?php

namespace App\ApiDTO\Response\Auth;

use ApiPlatform\Metadata\ApiProperty;

class TokenResponse
{
    public const FIELD_TOKEN = 'token';
    public const FIELD_REFRESH_TOKEN = 'refreshToken';
    public const FIELD_STREAM_TOKEN = 'streamToken';
    public const FIELD_SOURCE = 'source';
    public const FIELD_USER_UID = 'userUid';
    public const FIELD_CURRENT_USER_UID = 'uid';
    public const FIELD_IS_JUST_REGISTERED = 'isJustRegistered';

    public function __construct(
        #[ApiProperty(example: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE2OTkyNTg5MjcsImV4cCI6MTY5OTM0NTMyNywicm9sZXMiOlsiUk9MRV9VU0VSIl0sImVtYWlsIjoiQ2FkZW4uQ3VtbWVyYXRhQHlhaG9vLmNvbSJ9.NAwu3bWorLcV1jetu52kdqoqQgj88sh6VLS3S2v0wBBF0DfVrTs7_OHo8jlfRVIoasNonEjH6vZuFJH9bC-Lc3rRBRDI2tUcwpCuRbzPVwOhwEGhbAvWWQ1e6Iu7xPBAYzWhkL97s4hsdr9n_uMGM6m5y0GByJH-3coN_vF8Al5DJ_Td7Mr3354DDMDM7pBXKerg1Lo3IveVhm9ectNMfDkRIuLxiJsbgp0546hySyERuMDBmYEGvE6h15MnnFs01usPsJ6yvO-6cRd2XUBSZtOyeZqDRhKZFSzmF_8g7sVYWpoBXrxE20rg9XRP2rYbne-MBDF9AxNofHWCY7culw')]
        public string $token,
        #[ApiProperty(example: '735965128bb926004694528e45c618db9a706d3ea2f3a8278f7d21c33f3d2cc0425dff6858229ceffc2ff755b5efeb2908fb88d8a63e45b622fd9d59039cc4e5')]
        public string $refreshToken,
        #[ApiProperty(example: 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjoiMDFIRFFaR1FYWTNZNVRHMUtBR0UwTlYxRDkifQ.AZ9JC4PdMkI4hAxPEiKdAK2Cy6O5232W8ugdgsUlHmA')]
        public string $streamToken,
        #[ApiProperty(description: 'Action auth. registration/login', example: 'authorisation')]
        public ?string $action = null,
        #[ApiProperty(description: 'Source auth. facebook/google', example: 'facebook')]
        public ?string $source = null,
        #[ApiProperty(description: 'User uid', example: '01HEJ5JZ52Z449R6HM5J985S9N')]
        public ?string $userUid = null,
        #[ApiProperty(description: 'User type (user, anonym)', example: 'user')]
        public ?string $userType = 'user',
        #[ApiProperty(description: 'User uid', example: '01HRRVHM15ZGN4C3MJPFTF5ZAT')]
        public ?string $uid = null,
    ) {
    }
}
