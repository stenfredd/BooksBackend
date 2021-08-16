<?php

declare(strict_types=1);

namespace App\User\Service;

use App\User\Entity\Permission;
use App\User\Entity\Role;
use App\User\Repository\RoleRepositoryInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RoleService
{

	/**
	 * @var RoleRepositoryInterface
	 */
	private $roleRepository;

	/**
	 * @var PermissionService
	 */
	private $permissionService;

	/**
	 * RoleService constructor.
	 * @param RoleRepositoryInterface $roleRepository
	 * @param PermissionService $permissionService
	 */
	public function __construct(RoleRepositoryInterface $roleRepository, PermissionService $permissionService)
	{
		$this->roleRepository = $roleRepository;
		$this->permissionService = $permissionService;
	}

	/**
	 * @param string $name
	 * @param string $description
	 * @return Role
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function createRole(string $name, string $description): Role
	{
		$role = new Role();

		$role->setName($name);
		$role->setDescription($description);

		$this->roleRepository->save($role);

		return $role;
	}

	/**
	 * @param $name
	 * @return Role
	 */
	public function getRoleByName($name): Role
	{
		return $this->roleRepository->getRoleByName($name);
	}

	/**
	 * @param $names
	 * @return array
	 * @throws NotFoundHttpException
	 */
	public function getRolesByNames(array $names): array
	{
		$roles = [];
		if(count($names) > 0){
			foreach ($names as $cName) {
				$roles[] = $this->getRoleByName($cName);
			}
		}

		return $roles;
	}

	/**
	 * @param Role $role
	 * @param array $permissionsNames
	 * @return Role
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function setPermissionsByNames(Role $role, array $permissionsNames): Role
	{
		$permissions = $this->permissionService->getPermissionsByNames($permissionsNames);

		if(count($permissions) > 0){
			foreach ($permissions as $cPermission) {
				$role->addPermission($cPermission);
			}
		}

		$this->roleRepository->save($role);

		return $role;
	}

	/**
	 * @return array
	 */
	public function getAllRoles(): array
	{
		return $this->roleRepository->getAllRoles();
	}

	/**
	 * @param Role $role
	 */
	public function deleteRole(Role $role): void
	{
		$this->roleRepository->delete($role);
	}

	/**
	 * @return array
	 */
	public function getAllRolesPermissions(): array
	{
		return $this->roleRepository->getAllRolesPermissions();
	}

	/**
	 * @param Role $role
	 * @param Permission $permission
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function removePermissionFromRole(Role $role, Permission $permission): void
	{
		$role->removePermission($permission);
		$this->roleRepository->save($role);
	}
}