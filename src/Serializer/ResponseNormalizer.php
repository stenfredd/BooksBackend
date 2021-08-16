<?php

declare(strict_types=1);

namespace App\Serializer;

use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ResponseNormalizer implements NormalizerInterface
{
	/**
	 * @param mixed $object
	 * @param string|null $format
	 * @param array $context
	 * @return array|\ArrayObject|bool|float|int|string|null
	 */
	public function normalize($object, string $format = null, array $context = []): array
	{
		return [
			'data' => $object['data']
		];
	}

	/**
	 * @param mixed $data
	 * @param string|null $format
	 * @return bool
	 */
	public function supportsNormalization($data, string $format = null): bool
	{
		return !($data instanceof FlattenException);
	}
}
