<?php

declare(strict_types=1);

namespace App\File;

interface FileAccessCheckerInterface
{
	/**
	 * @param string $path
	 * @return bool
	 */
	public function allowed(string $path): bool;
}