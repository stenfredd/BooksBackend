<?php

namespace App\Tests\User\Admin\Controller;

use App\User\Admin\ValueObject\CreateRole;
use App\User\Authorization\Email\Service\AuthService;
use App\User\Authorization\Email\ValueObject\Login;
use App\User\Service\RoleService;
use App\User\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RoleControllerTest extends WebTestCase
{
	protected static $container;

	private $em;

	private $userService;
	private $roleService;
	private $emailAuthService;

	private $client;

	protected function setUp(): void
	{
		self::ensureKernelShutdown();
		$this->client = static::createClient();

		$container = self::$container;

		$this->userService = $container->get(UserService::class);
		$this->roleService = $container->get(RoleService::class);
		$this->emailAuthService = $container->get(AuthService::class);

		$this->em = $container->get('doctrine.orm.entity_manager');

		$this->em->getConnection()->beginTransaction();
	}

	protected function tearDown(): void
	{
		$this->em->getConnection()->rollBack();
	}

	public function testGetRoles()
	{
		$this->client->catchExceptions(false);

		$this->client->request('GET', '/api/role', [], [], [
			'HTTP_X-AUTH-TOKEN' => 'admin_auth_token',
		]);

		$response = $this->client->getResponse();
		$this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
		$this->assertJson($response->getContent());
		$responseData = json_decode($response->getContent(), true);

		$roles = [];
		if(count($responseData['data']) > 0){
			foreach ($responseData['data'] as $cRole) {
				$roles[] = $cRole['name'];
			}
		}

		$this->assertContains('ROLE_TEST', $roles);
		$this->assertContains('ROLE_USER', $roles);
		$this->assertContains('ROLE_ADMIN', $roles);
	}


	public function testCreateRole()
	{
		$this->client->request('POST', '/api/role', [], [], [
			'CONTENT_TYPE' => 'application/json',
			'HTTP_X-AUTH-TOKEN' => 'admin_auth_token'
		], json_encode([
			'name' => 'testrolename',
			'description' => 'Rdescription'
		]));

		$response = $this->client->getResponse();
		$this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
		$this->assertJson($response->getContent());
		$responseData = json_decode($response->getContent(), true);

		$this->assertIsNumeric($responseData['data']['id']);
	}


}
