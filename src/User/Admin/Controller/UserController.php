<?php

declare(strict_types=1);

namespace App\User\Admin\Controller;

use App\Exception\ValidationException;
use App\User\Admin\Formatter\UserDataResponse;
use App\User\Admin\Formatter\UsersSearchResponse;
use App\User\Admin\Service\AdminUserService;
use App\User\Admin\ValueObject\CreateUser;
use App\User\Admin\ValueObject\UpdateUser;
use App\User\Service\UserService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Swagger\Annotations as SWG;

use App\Service\CheckValidation;
use App\Service\JsonDTO;

/**
 * @Route("/api/user")
 */
class UserController extends AbstractController
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
	 * @var AdminUserService
	 */
	private $adminUserService;

	/**
	 * @var UsersSearchResponse
	 */
	private $usersSearchResponseFormatter;

	/**
	 * @var UserService
	 */
	private $userService;

	/**
	 * @var UserDataResponse
	 */
	private $userDataResponseFormatter;


	/**
	 * AuthController constructor.
	 * @param CheckValidation $checkValidation
	 * @param JsonDTO $jsonDTO
	 * @param AdminUserService $adminUserService
	 * @param UserService $userService
	 * @param UsersSearchResponse $usersSearchResponseFormatter
	 * @param UserDataResponse $userDataResponseFormatter
	 */
	public function __construct(
		CheckValidation $checkValidation,
		JsonDTO $jsonDTO,
		AdminUserService $adminUserService,
		UserService $userService,
		UsersSearchResponse $usersSearchResponseFormatter,
		UserDataResponse $userDataResponseFormatter
	)
	{
		$this->checkValidation = $checkValidation;
		$this->jsonDTO = $jsonDTO;
		$this->adminUserService = $adminUserService;
		$this->userService = $userService;
		$this->usersSearchResponseFormatter = $usersSearchResponseFormatter;
		$this->userDataResponseFormatter = $userDataResponseFormatter;
	}

	/**
	 * @Route("", name="search_user", methods={"GET"})
	 * @Security("is_granted('PERMISSION_VIEW_USER')", statusCode=403, message="Access denied")
	 * @SWG\Tag(name="Users")
	 * @SWG\Parameter(name="X-AUTH-TOKEN", in="header", required=true, type="string", default="Authorization token", description="Requires permission: 'VIEW_USER'")
	 * @SWG\Parameter(
	 *     name="query",
	 *     in="query",
	 *     type="string",
	 *     description="Query string. Example: 'example@mail.com'"
	 * )
	 * @SWG\Parameter(
	 *     name="orderDirection",
	 *     in="query",
	 *     type="string",
	 *     description="Order direction. Example: 'ASC', 'DESC'"
	 * )
	 * @SWG\Parameter(
	 *     name="orderBy",
	 *     in="query",
	 *     type="string",
	 *     description="Order by field. Example: 'id', 'email', 'createdAt', 'nickname', 'description'"
	 * )
	 * @SWG\Parameter(
	 *     name="page",
	 *     in="query",
	 *     type="integer",
	 *     description="Pagination number."
	 * )
	 * @SWG\Parameter(
	 *     name="rows",
	 *     in="query",
	 *     type="integer",
	 *     description="Rows on page"
	 * )
	 * @SWG\Response(
	 *     response=200,
	 *     description="Users list",
	 *     @SWG\Schema(
	 *         @SWG\Property(property="totalRows", type="string"),
	 *         @SWG\Property(property="users", type="array",
	 *     			@SWG\Items(
	 *     				@SWG\Property(property="id", type="integer"),
	 *     				@SWG\Property(property="email", type="string"),
	 *     				@SWG\Property(property="createdAt", type="string"),
	 *     				@SWG\Property(property="nickname", type="string"),
	 *     				@SWG\Property(property="roles", type="array",
	 * 						@SWG\Items(
	 *     						@SWG\Property(property="name", type="string"),
	 *     						@SWG\Property(property="description", type="string")
	 * 						)
	 * 					)
	 * 				),
	 * 		   ),
	 *     )
	 * )
	 * @param Request $request
	 * @return Response
	 */
	public function userSearch(Request $request): Response
	{
		$orderBy = $request->get('orderBy');
		$orderDirection = $request->get('orderDirection');
		$query = $request->get('query');
		$page = (int) $request->get('page');
		$rows = (int) $request->get('rows');


		$users = $this->adminUserService->usersSearch($orderBy, $orderDirection, $query, $rows, $page);
		$totalRows = $this->adminUserService->usersSearchTotalRows($orderBy, $orderDirection, $query);

		return $this->json([
			'status' => 'success',
			'data' => $this->usersSearchResponseFormatter->format($users, $totalRows)
		]);
	}

	/**
	 * @Route("/{id}", name="get_user", methods={"GET"}, requirements={"id"="\d+"})
	 * @Security("is_granted('PERMISSION_VIEW_USER')", statusCode=403, message="Access denied")
	 * @SWG\Tag(name="Users")
	 * @SWG\Parameter(name="X-AUTH-TOKEN", in="header", required=true, type="string", default="Authorization token", description="Requires permission: 'VIEW_USER'")
	 * @SWG\Parameter(
	 *     name="id",
	 *     in="query",
	 *     type="integer",
	 *     description="User id"
	 * )
	 * @SWG\Response(
	 *     response=200,
	 *     description="User data",
	 *     @SWG\Schema(
	 *         @SWG\Property(property="userId", type="integer"),
	 *     )
	 * )
	 * @param int $id
	 * @return Response
	 */
	public function getUsers(int $id): Response
	{
		$user = $this->userService->getById($id);

		return $this->json([
			'status' => 'success',
			'data' => $this->userDataResponseFormatter->format($user)
		]);
	}

	/**
	 * @Route("", name="create_user", methods={"POST"})
	 * @Security("is_granted('PERMISSION_EDIT_USER')", statusCode=403, message="Access denied")
	 * @SWG\Tag(name="Users")
	 * @SWG\Parameter(name="X-AUTH-TOKEN", in="header", required=true, type="string", default="Authorization token", description="Requires permission: 'EDIT_USER'")
	 * @SWG\Parameter(
	 *     name="email",
	 *     required=true,
	 *     in="query",
	 *     type="string",
	 *     description="User email"
	 * )
	 * @SWG\Parameter(
	 *     name="password",
	 *     required=true,
	 *     in="query",
	 *     type="string",
	 *     description="User password"
	 * )
	 * @SWG\Parameter(
	 *     name="nickname",
	 *     required=true,
	 *     in="query",
	 *     type="string",
	 *     description="User nickname"
	 * )
	 * @SWG\Parameter(
	 *     name="roasteryName",
	 *     required=true,
	 *     in="query",
	 *     type="string",
	 *     description="User roastery"
	 * )
	 * @SWG\Parameter(
	 *     name="role",
	 *     required=true,
	 *     in="query",
	 *     type="string",
	 *     description="User role. Example: 'ROLE_ADMIN', 'ROLE_USER'"
	 * )
	 * @SWG\Parameter(
	 *     name="active",
	 *     required=true,
	 *     in="query",
	 *     type="string",
	 *     description="User active. Example: '1', '0'"
	 * )
	 * @SWG\Response(
	 *     response=201,
	 *     description="Create user",
	 *     @SWG\Schema(
	 *         @SWG\Property(property="userId", type="integer"),
	 *     )
	 * )
	 * @param Request $request
	 * @return Response
	 * @throws ValidationException
	 */
	public function createUser(Request $request): Response
	{
		/* @var CreateUser $createUserVO */
		$createUserVO = $this->jsonDTO->fromRequest(new CreateUser);
		$this->checkValidation->validate($createUserVO);

		$user = $this->adminUserService->createUser($createUserVO);

		return $this->json([
			'status' => 'success',
			'data' => [
				'userId' => $user->getId()
			]
		], 201);
	}

	/**
	 * @Route("/{id}", name="update_user", methods={"PUT"}, requirements={"id"="\d+"})
	 * @Security("is_granted('PERMISSION_EDIT_USER')", statusCode=403, message="Access denied")
	 * @SWG\Tag(name="Users")
	 * @SWG\Parameter(name="X-AUTH-TOKEN", in="header", required=true, type="string", default="Authorization token", description="Requires permission: 'EDIT_USER'")
	 * @SWG\Parameter(
	 *     name="email",
	 *     in="query",
	 *     type="string",
	 *     description="User email"
	 * )
	 * @SWG\Parameter(
	 *     name="password",
	 *     in="query",
	 *     type="string",
	 *     description="User password"
	 * )
	 * @SWG\Parameter(
	 *     name="nickname",
	 *     in="query",
	 *     type="string",
	 *     description="User nickname"
	 * )
	 * @SWG\Parameter(
	 *     name="role",
	 *     in="query",
	 *     type="string",
	 *     description="User role. Example: 'ROLE_ADMIN', 'ROLE_USER'"
	 * )
	 * @SWG\Parameter(
	 *     name="active",
	 *     in="query",
	 *     type="string",
	 *     description="User active. Example: '1', '0'"
	 * )
	 * @SWG\Response(
	 *     response=200,
	 *     description="Create user",
	 *     @SWG\Schema(
	 *         @SWG\Property(property="userId", type="integer"),
	 *     )
	 * )
	 * @param Request $request
	 * @param int $id
	 * @return Response
	 * @throws ValidationException
	 */
	public function updateUser(Request $request, int $id): Response
	{
		/* @var UpdateUser $updateUserVO */
		$updateUserVO = $this->jsonDTO->fromRequest(new UpdateUser);
		$this->checkValidation->validate($updateUserVO);

		$user = $this->adminUserService->updateUser($id, $updateUserVO);

		return $this->json([
			'status' => 'success',
			'data' => [
				'userId' => $user->getId()
			]
		]);
	}

	/**
	 * @Route("/{id}", name="delete_user", methods={"DELETE"}, requirements={"id"="\d+"})
	 * @Security("is_granted('PERMISSION_EDIT_USER')", statusCode=403, message="Access denied")
	 * @SWG\Tag(name="Users")
	 * @SWG\Parameter(name="X-AUTH-TOKEN", in="header", required=true, type="string", default="Authorization token", description="Requires permission: 'EDIT_USER'")
	 * @SWG\Response(
	 *     response=204,
	 *     description="Delete user",
	 *     @SWG\Schema(
	 *         @SWG\Property(property="message", type="string"),
	 *     )
	 * )
	 * @param int $id
	 * @return Response
	 */
	public function deleteUser(int $id): Response
	{
		$this->adminUserService->deleteUser($id);

		return $this->json([
			'status' => 'success',
			'data' => [
				'message' => 'User deleted successfully'
			]
		], 204);
	}

}
