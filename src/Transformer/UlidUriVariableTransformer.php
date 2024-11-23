<?php

namespace App\Transformer;

use ApiPlatform\Api\UriVariableTransformerInterface;
use ApiPlatform\Exception\InvalidUriVariableException;
use Symfony\Component\Uid\Ulid;

final class UlidUriVariableTransformer implements UriVariableTransformerInterface
{
    /**
     * Transforms a uri variable value.
     *
     * @param mixed $value   The uri variable value to transform
     * @param array $types   The guessed type behind the uri variable
     * @param array $context Options available to the transformer
     *
     * @throws InvalidUriVariableException Occurs when the uriVariable could not be transformed
     */
    public function transform($value, array $types, array $context = []) {
        try {
            return Ulid::fromString($value);
            //return Uuid::fromString($value);
        } catch (\Exception $e) {
            throw new InvalidUriVariableException($e->getMessage());
        }
    }
    /**
     * Checks whether the given uri variable is supported for transformation by this transformer.
     *
     * @param mixed $value   The uri variable value to transform
     * @param array $types   The types to which the data should be transformed
     * @param array $context Options available to the transformer
     */
    public function supportsTransformation($value, array $types, array $context = []): bool
    {
        foreach ($types as $type) {
            if (is_a($type, Ulid::class, true)) {
                return true;
            }
        }

        return false;
    }
}