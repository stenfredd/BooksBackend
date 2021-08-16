<?php

declare(strict_types=1);

namespace App\File\Exception;

use Exception;

class FileAccessException extends Exception
{
	public function __construct(string $path)
	{
		$message = sprintf("File %s access denied", $path);
		parent::__construct($message, 403, null);
	}
}