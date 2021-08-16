<?php

declare(strict_types=1);

namespace App\User\Personal\Controller;

use App\User\Entity\User;
use App\User\Personal\Formatter\UserDataResponse;
use App\User\Service\UserService;
use Swagger\Annotations as SWG;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/api/user")
 */
class PersonalController extends AbstractController
{
	/**
	 * @var UserDataResponse
	 */
	private $userDataResponse;

	public function __construct
	(
		UserDataResponse $userDataResponse
	)
	{
		$this->userDataResponse = $userDataResponse;
	}

	/**
	 * @Route("/personal-data", name="get_user_personal_data", methods={"GET"})
	 * @Security("is_granted('IS_AUTHENTICATED_FULLY')", statusCode=403, message="Access denied")
	 * @SWG\Tag(name="Personal")
	 * @SWG\Response(
	 *     response=200,
	 *     description="Returns user perosnal data",
	 *     @SWG\Schema(
	 *         @SWG\Property(property="id", type="integer"),
	 *         @SWG\Property(property="active", type="integer"),
	 *         @SWG\Property(property="email", type="string"),
	 *         @SWG\Property(property="nickname", type="string"),
	 *         @SWG\Property(property="permissions", type="array", @SWG\Items(type="string")),
	 *         @SWG\Property(property="createdAt", type="string"),
	 *     )
	 * )
	 * @param Request $request
	 * @return Response
	 */
	public function personalData(Request $request): Response
	{
		/** @var $user User */
		$user = $this->getUser();

		return $this->json([
			'status' => 'success',
			'data' => $this->userDataResponse->format($user)
		]);
	}

}
