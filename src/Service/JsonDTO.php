<?php

declare(strict_types=1);

namespace App\Service;

use App\Interfaces\RequestValueObjectInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class JsonDTO
{
	/**
	 * @var Request
	 */
	private $request;

	/**
	 * JsonDTO constructor.
	 * @param RequestStack $requestStack
	 */
	public function __construct(RequestStack $requestStack)
	{
		$this->request = $requestStack->getCurrentRequest();
	}

	/**
	 * @param RequestValueObjectInterface $valueObject
	 * @param string|null $field
	 * @return object
	 */
	public function fromRequest(RequestValueObjectInterface $valueObject, string $field = null): object
	{
		if ($field === null) {
			$json = $this->request->getContent();
		} else {
			$json = $this->request->request->get($field);
		}

		return $this->deserialize($json, get_class($valueObject));
	}

	/**
	 * @param string $json
	 * @param string $className
	 * @return object
	 */
	private function deserialize(string $json, string $className): object
	{
		$serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);

		return $serializer->deserialize($json, $className,'json');
	}
}