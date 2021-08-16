<?php

declare(strict_types=1);

namespace App\User\Admin\Controller;

use App\Exception\ValidationException;
use App\Service\CheckValidation;
use App\Service\JsonDTO;
use App\User\Admin\Formatter\RolesListResponse;
use App\User\Admin\ValueObject\CreateRole;
use App\User\Service\RoleService;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Swagger\Annotations as SWG;

/**
 * @Route("/api/role")
 */
class RoleController extends AbstractController
{
	/**
	 * @var RoleService
	 */
	private $roleService;

	/**
	 * @var RolesListResponse
	 */
	private $rolesListResponseFormatter;

	/**
	 * @var CheckValidation
	 */
	private $checkValidation;

	/**
	 * @var JsonDTO
	 */
	private $jsonDTO;

	/**
	 * RolesController constructor.
	 * @param CheckValidation $checkValidation
	 * @param JsonDTO $jsonDTO
	 * @param RoleService $roleService
	 * @param RolesListResponse $rolesListResponseFormatter
	 */
	public function __construct
	(
		CheckValidation $checkValidation,
		JsonDTO $jsonDTO,
		RoleService $roleService,
		RolesListResponse $rolesListResponseFormatter
	)
	{
		$this->checkValidation = $checkValidation;
		$this->jsonDTO = $jsonDTO;
		$this->roleService = $roleService;
		$this->rolesListResponseFormatter = $rolesListResponseFormatter;
	}

	/**
	 * @Route("", name="get_roles", methods={"GET"})
	 * @Security("is_granted('PERMISSION_ROLES_PERMISSIONS')", statusCode=403, message="Access denied")
	 * @SWG\Tag(name="Roles")
	 * @SWG\Parameter(name="X-AUTH-TOKEN", in="header", required=true, type="string", default="Authorization token", description="Requires permission: 'ROLES_PERMISSIONS'")
	 * @SWG\Response(
	 *     response=200,
	 *     description="Roles list",
	 *     @SWG\Schema(
	 *         @SWG\Property(property="id", type="integer"),
	 *         @SWG\Property(property="name", type="string"),
	 *         @SWG\Property(property="description", type="string"),
	 *     )
	 * )
	 * @return Response
	 */
	public function getRoles(): Response
	{
		$roles = $this->roleService->getAllRoles();

		return $this->json([
			'status' => 'success',
			'data' => $this->rolesListResponseFormatter->format($roles)
		]);
	}

	/**
	 * @Route("", name="create_role", methods={"POST"})
	 * @Security("is_granted('PERMISSION_ROLES_PERMISSIONS')", statusCode=403, message="Access denied")
	 * @SWG\Tag(name="Roles")
	 * @SWG\Parameter(name="X-AUTH-TOKEN", in="header", required=true, type="string", default="Authorization token", description="Requires permission: 'ROLES_PERMISSIONS'")
	 * @SWG\Parameter(
	 *     name="name",
	 *     required=true,
	 *     in="query",
	 *     type="string",
	 *     description="Role name"
	 * )
	 * @SWG\Parameter(
	 *     name="description",
	 *     required=true,
	 *     in="query",
	 *     type="string",
	 *     description="Role description"
	 * )
	 * @SWG\Response(
	 *     response=200,
	 *     description="Create role",
	 *     @SWG\Schema(
	 *         @SWG\Property(property="id", type="integer")
	 *     )
	 * )
	 * @return Response
	 * @throws ValidationException
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function createRole(): Response
	{
		/* @var CreateRole $createRoleVO */
		$createRoleVO = $this->jsonDTO->fromRequest(new CreateRole);
		$this->checkValidation->validate($createRoleVO);

		$role = $this->roleService->createRole($createRoleVO->getName(), $createRoleVO->getDescription());

		return $this->json([
			'status' => 'success',
			'data' => [
				'id' => $role->getId()
			]
		]);
	}

	/**
	 * @Route("/{name}", name="delete_role", methods={"DELETE"}, requirements={"name"="[a-zA-Z\_]+"})
	 * @Security("is_granted('PERMISSION_ROLES_PERMISSIONS')", statusCode=403, message="Access denied")
	 * @SWG\Tag(name="Roles")
	 * @SWG\Parameter(name="X-AUTH-TOKEN", in="header", required=true, type="string", default="Authorization token", description="Requires permission: 'ROLES_PERMISSIONS'")
	 * @SWG\Response(
	 *     response=200,
	 *     description="Delete role",
	 *     @SWG\Schema(
	 *         @SWG\Property(property="message", type="string")
	 *     )
	 * )
	 * @param string $name
	 * @return Response
	 */
	public function deleteRole(string $name): Response
	{
		$role = $this->roleService->getRoleByName($name);
		$this->roleService->deleteRole($role);

		return $this->json([
			'status' => 'success',
			'data' => [
				'message' => 'Role deleted successfully'
			]
		]);
	}

}