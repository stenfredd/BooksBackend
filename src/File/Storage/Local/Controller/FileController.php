<?php

declare(strict_types=1);

namespace App\File\Storage\Local\Controller;

use App\File\Exception\FileAccessException;
use App\File\FilesStorageSwitcher;

use League\Flysystem\FileNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Swagger\Annotations as SWG;

/**
 * @Route("/api/file")
 */
class FileController extends AbstractController
{
	/**
	 * @var FilesStorageSwitcher
	 */
	private $filesStorageSwitcher;

	public function __construct(
		FilesStorageSwitcher $filesStorageSwitcher
	)
	{
		$this->filesStorageSwitcher = $filesStorageSwitcher;
	}

	/**
	 * @Route("/download/{path}", name="download_file", methods={"GET"}, requirements={"path"=".+"})
	 * @SWG\Tag(name="Files")
	 * @SWG\Response(
	 *     response=200,
	 *     description="File response",
	 *     @SWG\Schema(
	 *         @SWG\Property(property="file", type="file")
	 *     )
	 * )
	 * @param string $path
	 * @return Response
	 */
	public function downloadFile(string $path): Response
	{
		$fileManager = $this->filesStorageSwitcher->getManager('local');

		$filePath = $fileManager->getFullRelativePath($path);

		$fileManager->checkAccess($filePath);

		$response = new BinaryFileResponse($filePath);
		$response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT);

		return $response;
	}

	/**
	 * @Route("/{path}", name="show_file", methods={"GET"}, requirements={"path"=".+"})
	 * @SWG\Tag(name="Files")
	 * @SWG\Response(
	 *     response=200,
	 *     description="File response",
	 *     @SWG\Schema(
	 *         @SWG\Property(property="file", type="file")
	 *     )
	 * )
	 * @param string $path
	 * @return Response
	 * @throws FileAccessException
	 * @throws FileNotFoundException
	 */
	public function showFile(string $path): Response
	{
		$fileManager = $this->filesStorageSwitcher->getManager('local');

		$filePath = $fileManager->getFullRelativePath($path);

		$fileManager->checkAccess($filePath);

		$mimeTypes = new MimeTypes();
		$mimeType = $mimeTypes->guessMimeType($filePath);

		$response = new BinaryFileResponse($filePath);
		$response->headers->set('Content-Type', $mimeType);
		$response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE);

		return $response;
	}

}
