<?php

declare(strict_types=1);

namespace App\User\Admin\Formatter;

use App\User\Entity\Permission;

class PermissionsListResponse
{
	/**
	 * @param array $permissions
	 * @return array
	 */
	public function format(array $permissions): array
	{
		$result = [];

		if (count($permissions) > 0) {
			/* @var $cPermission Permission */
			foreach ($permissions as $cPermission) {
				$result[] = [
					'id' => $cPermission->getId(),
					'name' => $cPermission->getName(),
					'description' => $cPermission->getDescription(),
					'moduleName' => $cPermission->getModuleName()
				];
			}
		}

		return $result;
	}
}