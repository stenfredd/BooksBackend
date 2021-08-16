<?php

declare(strict_types=1);

namespace App\User\Admin\Controller;

use App\Exception\ValidationException;
use App\Service\CheckValidation;
use App\Service\JsonDTO;
use App\User\Admin\ValueObject\PermissionToRole;
use App\User\Service\PermissionService;
use App\User\Service\RoleService;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Swagger\Annotations as SWG;

/**
 * @Route("/api/role-permission")
 */
class RolePermissionController extends AbstractController
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
	 * @var RoleService
	 */
	private $roleService;

	/**
	 * @var PermissionService
	 */
	private $permissionService;

	/**
	 * RolesController constructor.
	 * @param CheckValidation $checkValidation
	 * @param JsonDTO $jsonDTO
	 * @param RoleService $roleService
	 * @param PermissionService $permissionService
	 */
	public function __construct
	(
		CheckValidation $checkValidation,
		JsonDTO $jsonDTO,
		RoleService $roleService,
		PermissionService $permissionService
	)
	{
		$this->checkValidation = $checkValidation;
		$this->jsonDTO = $jsonDTO;
		$this->roleService = $roleService;
		$this->permissionService = $permissionService;
	}

	/**
	 * @Route("", name="get_roles_permissions", methods={"GET"})
	 * @Security("is_granted('PERMISSION_ROLES_PERMISSIONS')", statusCode=403, message="Access denied")
	 * @SWG\Tag(name="Roles permissions")
	 * @SWG\Parameter(name="X-AUTH-TOKEN", in="header", required=true, type="string", default="Authorization token", description="Requires permission: 'ROLES_PERMISSIONS'")
	 * @SWG\Response(
	 *     response=200,
	 *     description="Roles permissions list",
	 *     @SWG\Schema(
	 *     	   @SWG\Property(property="data", type="array",
	 *     			@SWG\Items(
	 *         			@SWG\Property(property="roleId", type="integer"),
	 *         			@SWG\Property(property="permissionId", type="string")
	 * 				)
	 *     		)
	 *     )
	 * )
	 * @return Response
	 */
	public function getRolesPermissions(): Response
	{
		$rolesPermissions = $this->roleService->getAllRolesPermissions();

		return $this->json([
			'status' => 'success',
			'data' => $rolesPermissions
		]);
	}

	/**
	 * @Route("", name="add_permission_to_role", methods={"POST"})
	 * @Security("is_granted('PERMISSION_ROLES_PERMISSIONS')", statusCode=403, message="Access denied")
	 * @SWG\Tag(name="Roles permissions")
	 * @SWG\Parameter(name="X-AUTH-TOKEN", in="header", required=true, type="string", default="Authorization token", description="Requires permission: 'ROLES_PERMISSIONS'")
	 * @SWG\Parameter(
	 *     name="roleName",
	 *     in="query",
	 *     type="string",
	 *     description="Role name"
	 * )
	 * @SWG\Parameter(
	 *     name="permissionName",
	 *     in="query",
	 *     type="string",
	 *     description="Permission name"
	 * )
	 * @SWG\Response(
	 *     response=200,
	 *     description="Add permission to role",
	 *     @SWG\Schema(
	 *         @SWG\Property(property="message", type="string"),
	 *     )
	 * )
	 * @return Response
	 * @throws ValidationException
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function addPermissionToRole(): Response
	{
		/* @var PermissionToRole $PermissionToRoleVO */
		$PermissionToRoleVO = $this->jsonDTO->fromRequest(new PermissionToRole);
		$this->checkValidation->validate($PermissionToRoleVO);

		$role = $this->roleService->getRoleByName($PermissionToRoleVO->getRoleName());
		$permission = $this->permissionService->getPermissionByName($PermissionToRoleVO->getPermissionName());

		$this->roleService->setPermissionsByNames($role, [$permission->getName()]);

		return $this->json([
			'status' => 'success',
			'data' => [
				'message' => 'Permission added successfully'
			]
		]);
	}

	/**
	 * @Route("/remove", name="remove_permission_from_role", methods={"POST"})
	 * @Security("is_granted('PERMISSION_ROLES_PERMISSIONS')", statusCode=403, message="Access denied")
	 * @SWG\Tag(name="Roles permissions")
	 * @SWG\Parameter(name="X-AUTH-TOKEN", in="header", required=true, type="string", default="Authorization token", description="Requires permission: 'ROLES_PERMISSIONS'")
	 * @SWG\Parameter(
	 *     name="roleName",
	 *     in="query",
	 *     type="string",
	 *     description="Role name"
	 * )
	 * @SWG\Parameter(
	 *     name="permissionName",
	 *     in="query",
	 *     type="string",
	 *     description="Permission name"
	 * )
	 * @SWG\Response(
	 *     response=200,
	 *     description="Add permission to role",
	 *     @SWG\Schema(
	 *         @SWG\Property(property="message", type="string"),
	 *     )
	 * )
	 * @return Response
	 * @throws ValidationException
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function removePermissionFromRole(): Response
	{
		/* @var PermissionToRole $PermissionToRoleVO */
		$PermissionToRoleVO = $this->jsonDTO->fromRequest(new PermissionToRole);
		$this->checkValidation->validate($PermissionToRoleVO);

		$role = $this->roleService->getRoleByName($PermissionToRoleVO->getRoleName());
		$permission = $this->permissionService->getPermissionByName($PermissionToRoleVO->getPermissionName());

		$this->roleService->removePermissionFromRole($role, $permission);

		return $this->json([
			'status' => 'success',
			'data' => [
				'message' => 'Permission removed successfully'
			]
		]);
	}

}