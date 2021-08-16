<?php

declare(strict_types=1);

namespace App\Service;

class RandomString
{
	/**
	 * @var string
	 */
	private $characters = '0123456789abcdefghijklmnopqrstuvwxyz';

	/**
	 * @param int $length
	 * @return string
	 */
	public function generate($length = 16): string
	{
		$charactersLength = strlen($this->characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $this->characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}

	/**
	 * @param string $characters
	 */
	public function setCharacters(string $characters): void
	{
		$this->characters = $characters;
	}

}