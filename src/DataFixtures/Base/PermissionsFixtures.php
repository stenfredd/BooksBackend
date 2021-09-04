<?php

namespace App\DataFixtures\Base;

use App\User\Service\PermissionService;
use App\User\Service\RoleService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class PermissionsFixtures extends Fixture implements FixtureGroupInterface
{
	/**
	 * @var PermissionService
	 */
	private $permissionService;

	public function __construct(PermissionService $permissionService)
	{
		$this->permissionService = $permissionService;
	}

	public static function getGroups(): array
	{
		return ['test', 'app_start'];
	}

	public function load(ObjectManager $manager)
    {
		$this->permissionService->createPermission('ADMIN_PANEL', 'Admin panel', 'user');
		$this->permissionService->createPermission('VIEW_USER', 'View users', 'user');
		$this->permissionService->createPermission('EDIT_USER', 'Edit users', 'user');
		$this->permissionService->createPermission('TEST', 'Test', 'system');
		$this->permissionService->createPermission('PERSONAL_CABINET', 'Personal cabinet', 'user');
		$this->permissionService->createPermission('ROLES_PERMISSIONS', 'Edit roles', 'user');
    }
}
