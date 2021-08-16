<?php

namespace App\Tests\Auth\Service;



use App\User\Authorization\Email\ValueObject\Login;
use App\User\Authorization\Email\Service\AuthService as EmailAuthService;
use App\User\Authorization\System\Service\AuthService;
use App\User\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AuthServiceTest extends KernelTestCase
{
	protected static $container;

	private $em;

	private $emailAuthService;
	private $systemAuthService;
	private $userService;

	private $usersData = [
		['testmail@gmail.com', 'testpassword', ['ROLE_USER']],
		['testmail1@gmail.com', 'testpassword1', ['ROLE_USER']]
	];

	private $noExistsUsersData = [
		['nottestmail@gmail.com', 'nottestpassword']
	];

	private $invalidUsersData = [
		['incorrecttestmail@gmail.com', 'testpassword'],
		['testmail@gmail.com', 'incorrecttestpassword']
	];


	protected function setUp(): void
	{
		self::bootKernel();

		$container = self::$container;

		$this->systemAuthService = $container->get(AuthService::class);
		$this->emailAuthService = $container->get(EmailAuthService::class);
		$this->userService = $container->get(UserService::class);

		$this->em = $container->get('doctrine.orm.entity_manager');

		$this->em->getConnection()->beginTransaction();

		$this->addUsers();

	}

	protected function tearDown(): void
	{
		$this->em->getConnection()->rollBack();
	}


	public function usersDataProvider()
	{
		return $this->usersData;
	}

	public function notExistsUsersDataProvider()
	{
		return $this->noExistsUsersData;
	}

	public function invalidUsersDataProvider()
	{
		return $this->invalidUsersData;
	}

	private function addUsers()
	{
		foreach ($this->usersData as $user) {
			$this->userService->createUser($user[0], $user[1], $user[2]);
		}
	}

	/**
	 * @dataProvider usersDataProvider
	 */
	public function testLoginSuccess($email, $pass)
	{
		$loginVO = new Login();
		$loginVO->setEmail($email);
		$loginVO->setPassword($pass);

		$token = $this->emailAuthService->login($loginVO);
		$this->assertIsString($token);
	}

	/**
	 * @dataProvider notExistsUsersDataProvider
	 */
	public function testLoginNotExistsUser($email, $pass)
	{
		$loginVO = new Login();
		$loginVO->setEmail($email);
		$loginVO->setPassword($pass);

		try {
			$this->emailAuthService->login($loginVO);

			$this->fail();
		} catch (HttpException $exception) {
			$this->assertEquals('Invalid username or password', $exception->getMessage());
		}

	}

	/**
	 * @dataProvider invalidUsersDataProvider
	 */
	public function testLoginInvalidCredentials($email, $pass)
	{
		try {
			$loginVO = new Login();
			$loginVO->setEmail($email);
			$loginVO->setPassword($pass);

			$this->emailAuthService->login($loginVO);

			$this->fail();
		} catch (HttpException $exception) {
			$this->assertEquals('Invalid username or password', $exception->getMessage());
		}
	}


	public function testVerifyPasswordSuccess()
	{
		$email = 'testemail@gmail.com';
		$password = '111111';

		$user = $this->userService->getNewUser($email, $password);

		$this->systemAuthService->verifyPassword($user, $password);

		$this->assertTrue( true );
	}

	public function testVerifyPasswordFailed()
	{
		$this->expectException(AuthenticationException::class);

		$email = 'testemail@gmail.com';
		$password = '111111';

		$user = $this->userService->getNewUser($email, $password);

		$password = 'wrong';

		$this->systemAuthService->verifyPassword($user, $password);
	}

	public function testManyLoginFailAttempts()
	{
		$email = 'manyattemptsfails@gmail.com';
		$pass = '111111';

		$user = $this->userService->createUser($email, $pass, []);

		for ($i=1;$i<=$_ENV['MAX_LOGIN_FAIL_COUNT'];$i++) {
			try {
				$loginVO = new Login();
				$loginVO->setEmail($email);
				$loginVO->setPassword('wrong');

				$this->emailAuthService->login($loginVO);
			} catch (HttpException $e) {
				$this->assertEquals('Invalid username or password', $e->getMessage());
			}
		}

		try {
			$loginVO = new Login();
			$loginVO->setEmail($email);
			$loginVO->setPassword('wrong');

			$this->emailAuthService->login($loginVO);
		} catch (HttpException $e) {
			$this->assertEquals('Too many attempts, try again later', $e->getMessage());
		}

	}

}
