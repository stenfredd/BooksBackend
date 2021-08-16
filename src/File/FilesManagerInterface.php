<?php

declare(strict_types=1);

namespace App\File;

use League\Flysystem\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface FilesManagerInterface
{
	/**
	 * @param UploadedFile $file
	 * @param string $baseLocation
	 * @return string
	 */
	public function upload(UploadedFile $file, string $baseLocation): string;

	/**
	 * @param string $location
	 * @return File
	 */
	public function get(string $location): File;

	/**
	 * @param string $location
	 */
	public function delete(string $location): void;

	/**
	 * @param string $location
	 */
	public function checkAccess(string $location): void;

	/**
	 * @param string $location
	 * @return string
	 */
	public function getDownloadLink(string $location): string;

	/**
	 * @param string $location
	 * @return string
	 */
	public function getShowLink(string $location): string;

}