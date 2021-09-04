<?php

namespace App\DataFixtures\Base;

use App\User\Entity\UserData;
use App\User\Service\UserService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class UsersFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
	/**
	 * @var UserService
	 */
	private $userService;

	public function __construct(UserService $userService)
	{
		$this->userService = $userService;
	}

	public static function getGroups(): array
	{
		return ['app_start'];
	}

	public function getDependencies()
	{
		return [
			RolesFixtures::class,
		];
	}

	public function load(ObjectManager $manager)
    {
		$user = $this->userService->createUser('admin@mail.com', '111111', ['ROLE_ADMIN']);

		$userData = new UserData();
		$userData->setNickname('admin');
		$this->userService->setUserData($user, $userData);
		$this->userService->activateUser($user);
    }
}
