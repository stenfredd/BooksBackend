<?php

declare(strict_types=1);

namespace App\User\Admin\Formatter;

use App\User\Entity\Role;
use App\User\Entity\User;

class UsersSearchResponse
{
	public function format(array $users, int $totalRows): array
	{
		$result = [
			'totalRows' => $totalRows,
			'users' => []
		];

		/* @var $cUser User */
		foreach ($users as $cUser) {
			$userRoles = [];
			if (count($roles = $cUser->getRolesCollection()) > 0) {
				/* @var $cRole Role */
				foreach ($roles as $cRole) {
					$userRoles[] = [
						'name' => $cRole->getName(),
						'description' => $cRole->getDescription()
					];
				}
			}
			$result['users'][] = [
				'id' => $cUser->getId(),
				'email' => $cUser->getEmail(),
				'createdAt' => $cUser->getCreatedAt()->format(\DateTime::ISO8601),
				'nickname' => $cUser->getUserData()->getNickname(),
				'roles' => $userRoles
			];
		}

		return $result;
	}
}