<?php

declare(strict_types=1);

namespace App\File\Storage\Local\Controller;

use App\File\Exception\FileAccessException;
use App\File\FilesStorageSwitcher;

use FOS\ElasticaBundle\Finder\PaginatedFinderInterface;
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
	/**
	 * @var PaginatedFinderInterface
	 */
	private $finder;

	public function __construct(
		PaginatedFinderInterface $finder,
		FilesStorageSwitcher $filesStorageSwitcher
	)
	{
		$this->finder = $finder;
		$this->filesStorageSwitcher = $filesStorageSwitcher;
	}

	/**
	 * @Route("/elastic", name="elastic", methods={"GET"})
	 */
	public function elastic()
	{
		//$results = $this->finder->find('admin');


		$fieldQuery = new \Elastica\Query\MatchQuery();
		$fieldQuery->setFieldQuery('userData.nickname', 'odmen');
		$fieldQuery->setFieldParam('userData.nickname', 'fuzziness', '6');


		$data = $this->finder->find($fieldQuery);

		echo 123;
		exit;
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
