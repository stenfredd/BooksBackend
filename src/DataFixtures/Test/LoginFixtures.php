<?php

namespace App\DataFixtures\Test;

use App\User\Authorization\Email\ValueObject\Login;
use App\User\Authorization\System\Entity\AuthToken;
use App\User\Authorization\System\Repository\AuthTokenRepository;
use App\User\Authorization\System\Repository\AuthTokenRepositoryInterface;
use App\User\Authorization\System\Service\TokenService;
use App\User\Entity\User;
use App\User\Service\UserService;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoginFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
	/**
	 * @var TokenService
	 */
	private $tokenService;

	/**
	 * @var UserService
	 */
	private $userService;

	/**
	 * @var AuthTokenRepositoryInterface
	 */
	private $authTokenRepository;

	private $tokenExpiredAt;

	public function __construct(TokenService $tokenService, UserService $userService, AuthTokenRepositoryInterface $authTokenRepository)
	{
		$this->tokenService = $tokenService;
		$this->userService = $userService;
		$this->authTokenRepository = $authTokenRepository;

		$this->tokenExpiredAt = new DateTime(sprintf('+%d second', 946080000));
	}

	public static function getGroups(): array
	{
		return ['test'];
	}

	public function getDependencies()
	{
		return [
			UsersFixtures::class,
		];
	}

	public function load(ObjectManager $manager)
    {
		$user = $this->userService->getByEmail('admin@mail.com');
		$this->saveToken($user, 'admin_auth_token');

		$user = $this->userService->getByEmail('user@mail.com');
		$this->saveToken($user, 'user_auth_token');

		$user = $this->userService->getByEmail('without-role@mail.com');
		$this->saveToken($user, 'without_role_auth_token');

		$user = $this->userService->getByEmail('inactive-user@mail.com');
		$this->saveToken($user, 'inactive_user_auth_token');
    }

	private function saveToken(User $user, string $stringToken): void
	{
		$token = new AuthToken();
		$token->setValue($stringToken);
		$token->setHolder($user);
		$token->setExpiredAt($this->tokenExpiredAt);

		$this->authTokenRepository->save($token);
	}
}
