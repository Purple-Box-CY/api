<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class OpenAiService
{
    private array $categoryDefinitions = [
        "paper" => "paper-box",
        "glass" => "green-box",
        "plastic" => "blue-box",
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
                    such as plastic, used batteries, or old clothing.
                    - **Paper:** Containers labeled for collecting paper waste, such as newspapers, magazines, or cardboard (USUALLY BROWN COLOR).
                    - **Glass:** Containers designed for collecting glass bottles or jars, often with a distinct label or color (USUALLY GREEN COLOR).
                    - **Plastic:** Containers used for plastic items such as bottles, packaging, or bags. These are often marked with a recycling symbol (USUALLY BLUE COLOR).
                    - **Cloth:** Containers for old clothing, fabrics, or textiles, usually labeled with images of clothes or text indicating textile recycling (USUALLY PURPLE COLOR).
                    - **Electronic:** Special containers for electronic waste, like old phones, laptops, or other gadgets. Often marked with electronic symbols (USUALLY TRANSPARENT OR WHITE COLOR).
                    - **Battery:** Containers for used batteries, typically small and clearly labeled with battery icons (USUALLY TRANSPARENT OR WHITE COLOR).
                    - **Multibox:** A universal container that accepts multiple types of waste, often partitioned or labeled for multiple categories.
                    If the image contains an eco-box, identify its specific category from the following list: ['paper', 'glass', 'plastic', 'cloth', 'electronic', 'battery', 'multibox', 'none'],
                    Select only one category from the list. It must be from the list and no other.
                    If the object is not an eco-box or its category cannot be clearly identified,
                    answer 'none'.",
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
            'response_format' => [
                "type" => "json_schema",
                "json_schema" =>
                    [
                        'name' => 'single_string_type',
                        'schema' => [
                            'type' => 'object',
                            'properties' => [
                                'value' => [
                                    'type' => 'string',
                                    'description' => "This value must be one of the following: {$possibleCategories}, or 'None'. Just one word. Must be one of the predefined values.",
                                    "enum" => [
                                        "paper",
                                        "glass",
                                        "plastic",
                                        "cloth",
                                        "electronic",
                                        "battery",
                                        "multibox",
                                        "none"
                                    ]
                                ],
                            ],
                            'required' => ['value'],
                            'additionalProperties' => false,
                        ],
                        "strict" => true
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

        $content = $responseArray['choices'][0]['message']['content'];

        $decodedContent = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE || !isset($decodedContent['value'])) {
            throw new \Exception("Expected JSON with 'value' field in content: {$content}");
        }

// Извлекаем значение 'value'
        $detectedCategory = $decodedContent['value'];

// Обрабатываем результат
        if ($detectedCategory === 'none') {
            return ['category' => 'None', 'value' => null];
        }

        $mappedValue = $this->categoryDefinitions[strtolower($detectedCategory)] ?? null;

        if ($mappedValue === null) {
            throw new \Exception("Unexpected category detected: {$detectedCategory}");
        }

        return [
            'category' => $detectedCategory,
            'value' => $mappedValue,
        ];

    }


}
