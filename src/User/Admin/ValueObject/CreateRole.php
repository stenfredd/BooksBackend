<?php

declare(strict_types=1);

namespace App\User\Admin\ValueObject;

use App\Interfaces\RequestValueObjectInterface;
use Symfony\Component\Validator\Constraints as Assert;

class CreateRole implements RequestValueObjectInterface
{

	/**
	 * @Assert\Regex("/^[a-zA-Z\_]+$/u")
	 * @Assert\NotNull
	 */
	private $name;

	/**
	 * @Assert\Regex("/^[a-zA-Z\_]+$/u")
	 * @Assert\NotNull
	 */
	private $description;

	/**
	 * @return mixed
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param mixed $name
	 */
	public function setName($name): void
	{
		$name = mb_strtoupper($name);
		if (!str_starts_with($name, "ROLE_")) {
			$name = "ROLE_".$name;
		}

		$this->name = $name;
	}

	/**
	 * @return mixed
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * @param mixed $description
	 */
	public function setDescription($description): void
	{
		$this->description = $description;
	}


}
