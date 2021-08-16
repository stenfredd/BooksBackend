<?php

declare(strict_types=1);

namespace App\User\Authorization\Email\ValueObject;

use App\Interfaces\RequestValueObjectInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ResetPassword implements RequestValueObjectInterface
{
	/**
	 * @Assert\Email
	 * @Assert\NotNull
	 */
	private $email;

	/**
	 * @return mixed
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * @param mixed $email
	 */
	public function setEmail($email): void
	{
		$this->email = strtolower($email);
	}
}