<?php

declare(strict_types=1);

namespace App\Service\DTO;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class ValueObjectToEntity
{
	/**
	 * @param object $from
	 * @param string $to
	 * @param object|null $contextObject
	 * @param bool|null $ignoreNull
	 * @return object
	 */
	public function get(object $from, string $to, ?object $contextObject = null, ?bool $ignoreNull = false): object
	{
		$serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
		$jsonContent = $serializer->serialize($from, 'json');

		if($ignoreNull){
			$jsonContent = $this->removeNullProps($jsonContent);
		}

		$context = [];
		if($contextObject !== null){
			$context = [AbstractNormalizer::OBJECT_TO_POPULATE => $contextObject];
		}

		return $serializer->deserialize($jsonContent, $to, 'json', $context);
	}

	/**
	 * @param string $jsonContent
	 * @return string
	 */
	private function removeNullProps(string $jsonContent): string
	{
		$data = json_decode($jsonContent, true);

		$data = array_filter($data, function ($value) {
			return null !== $value;
		});

		return json_encode($data);
	}
}