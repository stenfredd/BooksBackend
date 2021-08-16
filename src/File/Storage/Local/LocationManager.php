<?php

declare(strict_types=1);

namespace App\File\Storage\Local;

class LocationManager
{
	const MAX_SUBDIR_FILES_COUNT = 2000;

	/**
	 * @var string
	 */
	private $rootDir;

	/**
	 * LocationManager constructor.
	 * @param string $rootDir
	 */
	public function __construct(string $rootDir)
	{
		$this->rootDir = $rootDir;
	}

	/**
	 * @param string $location
	 * @return string
	 */
	public function getNewLocation(string $location): string
	{
		$path = pathinfo($location, PATHINFO_DIRNAME);
		$path = $this->applyRootDir($path);
		$path = $this->getNewLocationDirectory($path);

		$filename = pathinfo($location, PATHINFO_BASENAME);
		$filename = $this->getNewLocationFileName($filename, $path);

		$path = $this->removeRootDir($path);

		return $path.'/'.$filename;
	}

	/**
	 * @param $path
	 * @return string
	 */
	private function applyRootDir($path): string
	{
		return $this->rootDir.'/'.$path;
	}

	/**
	 * @param $path
	 * @return string
	 */
	private function removeRootDir($path): string
	{
		return str_replace($this->rootDir.'/', '', $path);
	}

	/**
	 * @param string $fileDir
	 * @return string
	 */
	private function getNewLocationDirectory(string $fileDir): string
	{
		if (!is_dir($fileDir)) {
			mkdir($fileDir, 0777, true);
		}

		$files = scandir($fileDir);
		$maxSubdirNum = 1;
		if (count($files) > 0) {
			foreach ($files as $cFile) {
				if (is_dir($fileDir . '/' . $cFile) && is_numeric($cFile)) {
					if ($maxSubdirNum < (int) $cFile) {
						$maxSubdirNum = (int) $cFile;
					}
				}
			}
		}

		$resultDir = $fileDir.'/'.$maxSubdirNum;

		if (!file_exists($resultDir)) {
			mkdir($resultDir, 0777, true);
		}

		if (count(scandir($resultDir)) >= (static::MAX_SUBDIR_FILES_COUNT + 2)) {
			$resultDir = $fileDir.'/'.($maxSubdirNum + 1);
			mkdir($resultDir, 0777, true);
		}

		return $resultDir;
	}

	/**
	 * @param string $fileName
	 * @param string $fileDir
	 * @return string
	 */
	private function getNewLocationFileName(string $fileName, string $fileDir): string
	{
		$newFileName = $fileName;
		$i=1;
		while (file_exists($fileDir.'/'.$newFileName)) {
			$fileNameParts = pathinfo($fileName);
			$newFileName = $fileNameParts['filename'].'_'.$i;
			if (isset($fileNameParts['extension'])) {
				$newFileName = $newFileName.'.'.$fileNameParts['extension'];
			}
			$i++;
		}

		return $newFileName;
	}

}