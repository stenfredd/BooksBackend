<?php

namespace App\DataFixtures\Base;

use App\User\Service\RoleService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PermissionsToRolesFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
	/**
	 * @var RoleService
	 */
	private $roleService;

	public function __construct(RoleService $roleService)
	{
		$this->roleService = $roleService;
	}

	public static function getGroups(): array
	{
		return ['test', 'app_start'];
	}


	public function getDependencies()
	{
		return [
			RolesFixtures::class,
			PermissionsFixtures::class
		];
	}

	public function load(ObjectManager $manager)
    {
		$role = $this->roleService->getRoleByName('ROLE_ADMIN');
		$this->roleService->setPermissionsByNames($role, ['ADMIN_PANEL']);
		$this->roleService->setPermissionsByNames($role, ['VIEW_USER']);
		$this->roleService->setPermissionsByNames($role, ['ROLES_PERMISSIONS']);
		$this->roleService->setPermissionsByNames($role, ['EDIT_USER']);

		$role = $this->roleService->getRoleByName('ROLE_USER');
		$this->roleService->setPermissionsByNames($role, ['TEST']);
    }
}
