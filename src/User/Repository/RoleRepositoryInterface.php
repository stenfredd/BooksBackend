<?php

declare(strict_types=1);

namespace App\User\Repository;

use App\User\Entity\Role;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

interface RoleRepositoryInterface
{
	/**
	 * @param $role
	 * @return Role
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function save($role): Role;

	/**
	 * @param string $roles
	 * @return Role
	 */
	public function getRoleByName(string $roles): Role;

	/**
	 * @param int $id
	 * @return Role
	 */
	public function getById(int $id): Role;

	/**
	 * @param $role
	 */
	public function delete($role): void;

}