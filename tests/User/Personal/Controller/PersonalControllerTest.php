<?php

namespace App\Tests\User\Personal\Controller;

use App\User\Authorization\Email\Service\AuthService;
use App\User\Authorization\Email\ValueObject\Login;
use App\User\Entity\UserData;
use App\User\Personal\Controller\PersonalController;
use App\User\Repository\UserRepository;
use App\User\Service\PermissionService;
use App\User\Service\RoleService;
use App\User\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PersonalControllerTest extends WebTestCase
{
	protected static $container;

	private $em;

	private $userService;
	private $roleService;
	private $permissionService;
	private $userRepository;
	private $emailAuthService;

	private $client;

	protected function setUp(): void
	{
		self::ensureKernelShutdown();
		$this->client = static::createClient();

		$container = self::$container;

		$this->userService = $container->get(UserService::class);
		$this->roleService = $container->get(RoleService::class);
		$this->permissionService = $container->get(PermissionService::class);
		$this->userRepository = $container->get(UserRepository::class);
		$this->emailAuthService = $container->get(AuthService::class);

		$this->em = $container->get('doctrine.orm.entity_manager');

		$this->em->getConnection()->beginTransaction();
	}

	protected function tearDown(): void
	{
		$this->em->getConnection()->rollBack();
	}

	public function testGetPersonalData()
	{
		$this->client->request('GET', '/api/user/personal-data', [], [], [
			'HTTP_X-AUTH-TOKEN' => 'user_auth_token'
		]);

		$response = $this->client->getResponse();
		$this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
		$this->assertJson($response->getContent());
		$responseData = json_decode($response->getContent(), true);

		$this->assertEquals($responseData['data']['email'], 'user@mail.com');
		$this->assertEquals($responseData['data']['nickname'], 'user');
		$this->assertContains('TEST', $responseData['data']['permissions']);

		$this->assertResponseIsSuccessful();
	}

}
