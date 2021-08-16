<?php

declare(strict_types=1);

namespace App\User\Admin\ValueObject;

use App\Interfaces\RequestValueObjectInterface;
use Symfony\Component\Validator\Constraints as Assert;

class PermissionToRole implements RequestValueObjectInterface
{
	/**
	 * @Assert\Regex("/^[a-zA-Z\_]+$/u")
	 * @Assert\NotNull
	 */
	private $roleName;

	/**
	 * @Assert\Regex("/^[a-zA-Z\_]+$/u")
	 * @Assert\NotNull
	 */
	private $permissionName;

	/**
	 * @return mixed
	 */
	public function getRoleName()
	{
		return $this->roleName;
	}

	/**
	 * @param mixed $roleName
	 */
	public function setRoleName($roleName): void
	{
		$this->roleName = $roleName;
	}

	/**
	 * @return mixed
	 */
	public function getPermissionName()
	{
		return $this->permissionName;
	}

	/**
	 * @param mixed $permissionName
	 */
	public function setPermissionName($permissionName): void
	{
		$this->permissionName = $permissionName;
	}

}
