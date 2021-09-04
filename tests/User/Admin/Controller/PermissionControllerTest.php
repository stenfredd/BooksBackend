<?php

namespace App\Tests\User\Admin\Controller;

use App\User\Authorization\Email\Service\AuthService;
use App\User\Repository\UserRepository;
use App\User\Service\PermissionService;
use App\User\Service\RoleService;
use App\User\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PermissionControllerTest extends WebTestCase
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

	public function testGetPermissions()
	{
		$this->client->request('GET', '/api/permission');

		$response = $this->client->getResponse();
		$this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
		$this->assertJson($response->getContent());
		$responseData = json_decode($response->getContent(), true);

		$permissions = [];
		if(count($responseData['data']) > 0){
			foreach ($responseData['data'] as $cPermission) {
				$permissions[] = $cPermission['name'];
			}
		}

		$this->assertContains('ADMIN_PANEL', $permissions);
		$this->assertContains('VIEW_USER', $permissions);
		$this->assertContains('EDIT_USER', $permissions);
	}
}
