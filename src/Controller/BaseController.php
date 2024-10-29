<?php

namespace App\Controller;

use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\SerializerInterface;

abstract class BaseController
{
    public function __construct(
        protected readonly SerializerInterface $serializer,
        protected readonly int $jsonEncodeOptions
    ) {}

    /**
     * @param string|string[] $groupNames
     */
    protected function jsonSerialize(
        $data,
        array|string $groupNames,
    ): string {
        return $this->serializer->serialize(
            $data,
            'json',
            (new ObjectNormalizerContextBuilder())
                ->withGroups($groupNames)
                ->withContext(['json_encode_options' => $this->jsonEncodeOptions])
                ->toArray()
        );
    }
}
