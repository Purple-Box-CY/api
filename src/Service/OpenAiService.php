<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class OpenAiService
{
    private array $categoryDefinitions = [
        "paper" => "paper-box",
        "glass" => "blue-box",
        "plastic" => "green-box",
        "cloth" => "purple-box",
        "electronic" => "purple-box",
        "battery" => "batteries-box",
        "multibox" => "multi-boxes"

    ];

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string              $openaiApiKey
    )
    {
    }

    public function processImage(string $imageBase64): array
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
                    Select only one category from the list. It must be from the list and no other.
                    If the object is not an eco-box or its category cannot be clearly identified,
                    answer 'None'.",
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
                                'description' => "This value must be one of the following: {$possibleCategories}, or 'None'. Just one word.",
                                'enum' => array_merge(explode(', ', $possibleCategories), ['None']),
                            ],
                        ],
                        'required' => ['value'],
                        'additionalProperties' => false,
                    ],
                ],
            ],
        ];

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

        if (!isset($responseArray['choices'][0]['message']['content'])) {
            throw new \Exception("Invalid response structure from OpenAI: {$response}");
        }

        $detectedCategory = $responseArray['choices'][0]['message']['content'];

        if ($detectedCategory === 'None') {
            return ['category' => 'None', 'value' => null];
        }

//        if (!array_key_exists($detectedCategory, $this->categoryDefinitions)) {
//            throw new \Exception("Unexpected category detected: {$detectedCategory}");
//        }
        if ($detectedCategory !== 'None') {
            $mappedValue = $this->categoryDefinitions[strtolower($detectedCategory)] ?? null;
        } else {
            $mappedValue = null;
        }


        return [
            'category' => $detectedCategory,
            'value' => $mappedValue,
        ];
    }
}
