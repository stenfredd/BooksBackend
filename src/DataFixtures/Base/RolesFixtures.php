<?php

namespace App\DataFixtures\Base;

use App\User\Service\PermissionService;
use App\User\Service\RoleService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class RolesFixtures extends Fixture implements FixtureGroupInterface
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

	public function load(ObjectManager $manager)
    {
		$this->roleService->createRole('ROLE_TEST', 'Test role');
		$this->roleService->createRole('ROLE_USER', 'User role');
		$this->roleService->createRole('ROLE_ADMIN', 'Admin role');
    }
}
