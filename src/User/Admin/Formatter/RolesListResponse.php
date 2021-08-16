<?php

declare(strict_types=1);

namespace App\User\Admin\Formatter;

use App\User\Entity\Role;

class RolesListResponse
{
	/**
	 * @param array $roles
	 * @return array
	 */
	public function format(array $roles): array
	{
		$result = [];

		if (count($roles) > 0) {
			/* @var $cRole Role */
			foreach ($roles as $cRole) {
				$result[] = [
					'name' => $cRole->getName(),
					'description' => $cRole->getDescription()
				];
			}
		}

		return $result;
	}
}