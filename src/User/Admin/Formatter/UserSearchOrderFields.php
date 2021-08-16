<?php

namespace App\User\Admin\Formatter;

class UserSearchOrderFields
{
	private $fields = [
		'id' => 'u.id',
		'email' => 'u.email',
		'createdAt' => 'u.createdAt',
		'nickname' => 'd.nickname'
	];

	public function fieldToEntityProp(?string $fieldName): string
	{
		if ($fieldName === null) {
			return $this->fields['id'];
		}
		if (!isset($this->fields[$fieldName])) {
			throw new \InvalidArgumentException(sprintf('Order field "%s" not exists. Use: %s', $fieldName, implode(',', array_keys($this->fields)) ));
		}
		return $this->fields[$fieldName];
	}
}