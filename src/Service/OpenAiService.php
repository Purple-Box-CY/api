<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class OpenAiService
{
    private array $categoryDefinitions = [
        "paper" => "Paper",
        "glass" => "Glass",
        "plastic" => "Plastic",
        "cloth" => "Cloth)",
        "electronic" => "Electronic devices",
        "battery" => "Batteries",

    ];

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $openaiApiKey
    ) {}

    public function processImage(string $imageBase64): array|string
    {
        $possibleCategories = implode(', ', array_keys($this->categoryDefinitions));

        $requestBody = [
            'model' => 'gpt-4o-mini',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => "Analyze the provided image and determine if it contains an eco-box.
                            Eco-boxes are containers designed for collecting specific types of waste,
                            such as plastic, used batteries, or old clothing. If the image contains an eco-box,
                            identify its specific category from the following list: {$possibleCategories}.
                            If the object is not an eco-box or its category cannot be clearly identified,
                            answer 'None'",
                        ],
                        [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => "data:image/jpeg;base64,{$imageBase64}",
                            ],
                        ],
                    ],
                ],
            ],
            'functions' => [
                [
                    'name' => 'single_string_type',
                    'schema' => [
                        'type' => 'object',
                        'properties' => [
                            'value' => [
                                'type' => 'string',
                                'description' => "some option from this list {$possibleCategories} or None",
                            ],
                        ],
                        'required' => ['value'],
                        'additionalProperties' => false,
                    ],
                ],
            ],
        ];

        // Отправляем запрос к OpenAI
        $ch = curl_init('https://api.openai.com/v1/chat/completions');

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: ' . 'Bearer ' . $this->openaiApiKey,
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestBody));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($httpCode !== 200) {
            throw new \Exception("OpenAI API request failed with HTTP code {$httpCode}: {$response}");
        }

        $responseArray = json_decode($response, true);

        if (!isset($responseArray['choices'][0])) {
            throw new \Exception("Invalid response structure from OpenAI: {$response}");
        }

        return $responseArray['choices'][0]['message']['content'];
    }
}
