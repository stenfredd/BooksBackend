<?php

declare(strict_types=1);

namespace App\User\Personal\Formatter;

use App\User\Entity\User;
use App\User\Service\UserService;

class UserDataResponse
{
	/**
	 * @var UserService
	 */
	private $userService;

	/**
	 * UserDataResponse constructor.
	 * @param UserService $userService
	 */
	public function __construct(UserService $userService)
	{
		$this->userService = $userService;
	}

	/**
	 * @param User $user
	 * @return array
	 */
	public function format(User $user): array
	{
		$permissions = $this->userService->getPermissions($user);

		$permissions_strings = [];
		if ($permissions > 0) {
			foreach ($permissions as $cPermission) {
				$permissions_strings[] = $cPermission->getName();
			}
		}

		return [
			'id' => $user->getId(),
			'active' => $user->getActive(),
			'email' => $user->getEmail(),
			'nickname' => $user->getUserData()->getNickname(),
			'permissions' => $permissions_strings,
			'createdAt' => $user->getCreatedAt()->format('d.m.Y H:i')
		];
	}
}