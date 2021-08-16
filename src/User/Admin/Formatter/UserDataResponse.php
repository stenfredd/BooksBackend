<?php

declare(strict_types=1);

namespace App\User\Admin\Formatter;

use App\User\Entity\Role;
use App\User\Entity\User;

class UserDataResponse
{
	public function format(User $user): array
	{
		$userRoles = [];
		if (count($roles = $user->getRolesCollection()) > 0) {
			/* @var $cRole Role */
			foreach ($roles as $cRole) {
				$userRoles[] = [
					'name' => $cRole->getName(),
					'description' => $cRole->getDescription()
				];
			}
		}

		return [
			'id' => $user->getId(),
			'email' => $user->getEmail(),
			'createdAt' => $user->getCreatedAt()->format('d.m.Y H:i'),
			'updatedAt' => $user->getUpdatedAt()->format('d.m.Y H:i'),
			'active' => $user->getActive(),
			'nickname' => $user->getUserData()->getNickname(),
			'roles' => $userRoles
		];
	}
}