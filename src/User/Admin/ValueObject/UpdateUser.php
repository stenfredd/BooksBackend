<?php

declare(strict_types=1);

namespace App\User\Admin\ValueObject;

use App\Interfaces\RequestValueObjectInterface;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateUser implements RequestValueObjectInterface
{
	/**
	 * @Assert\Email
	 */
	private $email;

	/**
	 * @Assert\Regex("/^[a-zA-Z0-9\!\@\#\$\%\^\&\*\(\)\-\_\=\+]+$/")
	 * @Assert\Length(
	 *      min = 6,
	 *      max = 512
	 * )
	 */
	private $password;

	/**
	 * @Assert\Regex("/^[a-zA-Zа-яА-Я\-\_]+$/u")
	 */
	private $nickname;

	/**
	 * @Assert\Regex("/^ROLE_[A-Z\_]+$/")
	 */
	private $role;

	/**
	 * @Assert\Choice({0, 1})
	 */
	private $active;


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

	/**
	 * @return mixed
	 */
	public function getPassword()
	{
		return $this->password;
	}

	/**
	 * @param mixed $password
	 */
	public function setPassword($password): void
	{
		$this->password = $password;
	}

	/**
	 * @return mixed
	 */
	public function getNickname()
	{
		return $this->nickname;
	}

	/**
	 * @param mixed $nickname
	 */
	public function setNickname($nickname): void
	{
		$this->nickname = $nickname;
	}

	/**
	 * @return mixed
	 */
	public function getActive()
	{
		return $this->active;
	}

	/**
	 * @param mixed $active
	 */
	public function setActive($active): void
	{
		$this->active = (int) $active;
	}

	/**
	 * @return mixed
	 */
	public function getRole()
	{
		return $this->role;
	}

	/**
	 * @param mixed $role
	 */
	public function setRole($role): void
	{
		$this->role = $role;
	}

}
