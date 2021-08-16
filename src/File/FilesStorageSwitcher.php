<?php

declare(strict_types=1);

namespace App\File;

use App\File\Storage\Local\LocalFilesManager;

class FilesStorageSwitcher
{
	/**
	 * @var LocalFilesManager
	 */
	private $local;

	/**
	 * FilesStorageSwitcher constructor.
	 * @param LocalFilesManager $localFilesManager
	 */
	public function __construct(LocalFilesManager $localFilesManager)
	{
		$this->local = $localFilesManager;
	}

	/**
	 * @param string $name
	 * @return FilesManagerInterface
	 */
	public function getManager(string $name): FilesManagerInterface
	{
		switch ($name) {
			case 'local':
				return $this->local;
				break;
			default:
				throw new \LogicException(sprintf('%s file manager class not found', $name));
				break;
		}
	}
}