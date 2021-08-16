<?php

declare(strict_types=1);

namespace App\User\Admin\Controller;

use App\Service\CheckValidation;
use App\Service\JsonDTO;
use App\User\Admin\Formatter\PermissionsListResponse;
use App\User\Service\PermissionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Swagger\Annotations as SWG;

/**
 * @Route("/api/permission")
 */
class PermissionController extends AbstractController
{
	/**
	 * @var CheckValidation
	 */
	private $checkValidation;

	/**
	 * @var JsonDTO
	 */
	private $jsonDTO;

	/**
	 * @var PermissionService
	 */
	private $permissionService;

	/**
	 * @var PermissionsListResponse
	 */
	private $permissionsListResponse;

	/**
	 * RolesController constructor.
	 * @param CheckValidation $checkValidation
	 * @param JsonDTO $jsonDTO
	 * @param PermissionService $permissionService
	 * @param PermissionsListResponse $permissionsListResponse
	 */
	public function __construct
	(
		CheckValidation $checkValidation,
		JsonDTO $jsonDTO,
		PermissionService $permissionService,
		PermissionsListResponse $permissionsListResponse
	)
	{
		$this->checkValidation = $checkValidation;
		$this->jsonDTO = $jsonDTO;
		$this->permissionService = $permissionService;
		$this->permissionsListResponse = $permissionsListResponse;
	}

	/**
	 * @Route("", name="get_permissions", methods={"GET"})
	 * @SWG\Tag(name="Permissions")
	 * @SWG\Response(
	 *     response=200,
	 *     description="Permissions list",
	 *     @SWG\Schema(
	 *         @SWG\Property(property="id", type="integer"),
	 *         @SWG\Property(property="name", type="string"),
	 *         @SWG\Property(property="description", type="string"),
	 *         @SWG\Property(property="moduleName", type="string")
	 *     )
	 * )
	 * @return Response
	 */
	public function getPermissions(): Response
	{
		$permissions = $this->permissionService->getAllPermissions();

		return $this->json([
			'status' => 'success',
			'data' => $this->permissionsListResponse->format($permissions)
		]);
	}

}
