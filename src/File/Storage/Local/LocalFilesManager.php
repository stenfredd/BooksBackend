<?php

declare(strict_types=1);

namespace App\File\Storage\Local;

use App\File\Exception\FileAccessException;
use App\File\FileAccessCheckerInterface;
use App\File\FilesManagerInterface;
use League\Flysystem\Adapter\Local;
use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;
use League\Flysystem\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Yaml\Yaml;

class LocalFilesManager implements FilesManagerInterface
{
	/**
	 * @var Filesystem
	 */
	private $filesystem;

	/**
	 * @var string
	 */
	private $rootDir;

	/**
	 * @var array
	 */
	private $config;

	/**
	 * @var UrlGeneratorInterface
	 */
	private $router;

	/**
	 * LocalFilesManager constructor.
	 * @param string $rootDir
	 * @param UrlGeneratorInterface $router
	 */
	public function __construct(string $rootDir, UrlGeneratorInterface $router)
	{
		$this->rootDir = $rootDir;
		$adapter = new Local($rootDir);
		$this->router = $router;
		$this->filesystem = new Filesystem($adapter);
		$this->config = Yaml::parseFile('../config/packages/file_manager.yaml');
	}

	/**
	 * @param UploadedFile $file
	 * @param string $baseLocation
	 * @return string
	 * @throws FileExistsException
	 */
	public function upload(UploadedFile $file, string $baseLocation): string
	{
		$locationManager = new LocationManager($this->rootDir);
		$location = $locationManager->getNewLocation($baseLocation);

		$this->filesystem->write($location, $file->getContent(), ['visibility' => 'public']);

		return $location;
	}

	/**
	 * @param string $location
	 * @throws FileNotFoundException
	 */
	public function delete(string $location): void
	{
		$this->filesystem->delete($location);
	}

	/**
	 * @param string $location
	 * @return File
	 */
	public function get(string $location): File
	{
		return $this->filesystem->get($location);
	}

	/**
	 * @param string $location
	 * @return string
	 * @throws FileNotFoundException
	 */
	public function getFullRelativePath(string $location): string
	{
		$path = $this->rootDir.'/'.$location;
		$this->checkFileExists($path);

		return $path;
	}

	/**
	 * @param string $location
	 * @throws FileAccessException
	 * @throws FileNotFoundException
	 */
	public function checkAccess(string $location): void
	{
		$fullPath = $this->getFullRelativePath($location);

		if (!$this->inPermittedDirectory($fullPath)) {
			throw new FileAccessException($location);
		}

		$topDirectory = $this->getTopFileDir($location);
		$accessChecker = $this->getAccessChecker($topDirectory);

		if(!$accessChecker->allowed($location)){
			throw new FileAccessException($location);
		}
	}

	/**
	 * @param string $location
	 * @return string
	 */
	public function getDownloadLink(string $location): string
	{
		return $this->router->generate('download_file', ['path' => $location], UrlGeneratorInterface::ABSOLUTE_URL);
	}

	/**
	 * @param string $location
	 * @return string
	 */
	public function getShowLink(string $location): string
	{
		return $this->router->generate('show_file', ['path' => $location], UrlGeneratorInterface::ABSOLUTE_URL);
	}

	/**
	 * @param string $directory
	 * @return FileAccessCheckerInterface
	 */
	private function getAccessChecker(string $directory): FileAccessCheckerInterface
	{
		if(!isset($this->config['parameters']['access_checker']['local'][$directory])){
			throw new \LogicException(sprintf('Access checker for %s dir not found. Check file_manager.yaml configuration file', $directory));
		}

		$accessCheckerClassName = $this->config['parameters']['access_checker']['local'][$directory];
		$accessChecker = new $accessCheckerClassName;

		if(!$accessChecker instanceof FileAccessCheckerInterface){
			throw new \LogicException(sprintf('%s class must implement FileAccessCheckerInterface', $accessCheckerClassName));
		}

		return $accessChecker;
	}

	/**
	 * Return top file directory inside base upload directory
	 * @param string $location
	 * @return string
	 */
	private function getTopFileDir(string $location): string
	{
		$root = realpath($this->rootDir);
		$filePath = realpath($location);

		$fileDir = preg_replace('/^'.preg_quote($root.'/', '/').'/', '', $filePath); // Delete base dir from file real path
		$topDir = explode('/', $fileDir);

		return $topDir[0];
	}

	/**
	 * @param string $path
	 * @throws FileNotFoundException
	 */
	private function checkFileExists(string $path): void
	{
		if(!file_exists($path)){
			throw new FileNotFoundException($path);
		}
	}

	/**
	 * @param string $location
	 * @return bool
	 */
	private function inPermittedDirectory(string $location): bool
	{
		$root = realpath($this->rootDir);
		$filePath = realpath($location);

		if (str_starts_with($filePath, $root)) {
			return true;
		}

		return false;
	}

}