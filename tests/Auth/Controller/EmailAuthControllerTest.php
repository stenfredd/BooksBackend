<?php

namespace App\Tests\Auth\Controller;

use App\Exception\ValidationException;
use App\User\Authorization\Email\Service\ActivationTokenService;
use App\User\Authorization\Email\Service\PasswordResetTokenService;
use App\User\Service\UserService;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;

class EmailAuthControllerTest extends WebTestCase
{
	protected static $container;

	private $em;
	private $client;

	/**
	 * @var UserService
	 */
	private $userService;
	/**
	 * @var ActivationTokenService
	 */
	private $activationTokenService;
	/**
	 * @var PasswordResetTokenService
	 */
	private $emailPasswordResetTokenService;



	protected function setUp(): void
	{
		self::ensureKernelShutdown();
		$this->client = static::createClient();

		$container = self::$container;

		$this->em = $container->get('doctrine.orm.entity_manager');

		$this->userService = $container->get(UserService::class);
		$this->activationTokenService = $container->get(ActivationTokenService::class);
		$this->emailPasswordResetTokenService = $container->get(PasswordResetTokenService::class);

		$this->em->getConnection()->beginTransaction();
	}


	protected function tearDown(): void
	{
		$this->em->getConnection()->rollBack();
	}

	public function testPermissionFail()
	{
		$this->expectException(HttpException::class);
		$this->client->catchExceptions(false);

		$this->client->request('POST', '/api/auth/email/permission-test', [], [], [
			'HTTP_X-AUTH-TOKEN' => 'admin_auth_token',
		]);

		$this->assertResponseStatusCodeSame(403);
	}


	public function testHasPermission()
	{
		$this->client->catchExceptions(false);

		$this->client->request('POST', '/api/auth/email/permission-test', [], [], [
			'HTTP_X-AUTH-TOKEN' => 'user_auth_token',
		]);

		$this->assertResponseIsSuccessful();
	}

	public function testSignUpEmail()
	{
		$this->client->request('POST', '/api/auth/email/sign-up', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
			'email' => 'signup-email@gmail.com',
			'password' => '111111',
			'nickname' => 'signup-nickname'
		]));

		$this->assertResponseIsSuccessful();
	}

	public function testSignUpEmailValidationFailed()
	{
		$this->expectException(ValidationException::class);
		$this->client->catchExceptions(false);

		$this->client->request('POST', '/api/auth/email/sign-up', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
			'email' => 'signup-emailgmail.com',
			'password' => '111111',
			'nickname' => 'signup-nickname'
		]));

		$this->assertResponseStatusCodeSame(500);
	}

	public function testResendActivationLink()
	{
		$this->client->catchExceptions(false);

		$this->client->request('POST', '/api/auth/email/resend-activation-link', [], [], [
			'CONTENT_TYPE' => 'application/json',
			'HTTP_X-AUTH-TOKEN' => 'user_auth_token'
		]);

		$this->assertResponseIsSuccessful();
	}

	public function testActivateEmail()
	{
		$this->client->catchExceptions(false);

		$user = $this->userService->getByEmail('inactive-user@mail.com');

		$activationToken = $this->activationTokenService->createEmailActivationToken($user);

		$this->client->request('GET', '/api/auth/email/activate-user', ['token' => $activationToken->getToken()]);

		$this->assertResponseRedirects($_ENV['ACTIVATION_LINK_SUCCESS_REDIRECT_TO']);
	}

	public function testActivateEmailFailed()
	{
		$this->client->catchExceptions(false);

		$this->client->request('GET', '/api/auth/email/activate-user', ['token' => 'wrongToken']);

		$this->assertResponseRedirects($_ENV['ACTIVATION_LINK_FAIL_REDIRECT_TO']);
	}

	public function testResetPassword()
	{
		$this->client->catchExceptions(false);

		$user = $this->userService->getByEmail('user@mail.com');

		$this->client->request('POST', '/api/auth/email/reset-password', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
			'email' => 'user@mail.com'
		]));

		$this->assertResponseIsSuccessful();
	}


	public function testResetPasswordFail()
	{
		$this->expectException(HttpException::class);
		$this->client->catchExceptions(false);

		$this->client->request('POST', '/api/auth/email/reset-password', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
			'email' => 'wrongemail@gmail.com'
		]));

		$this->assertResponseStatusCodeSame(500);
	}

	public function testResetPasswordConfirm()
	{
		$this->client->catchExceptions(false);

		$user = $this->userService->getByEmail('user@mail.com');

		$resetToken = $this->emailPasswordResetTokenService->createEmailPasswordResetToken($user);

		$this->client->request('GET', '/api/auth/email/reset-password-confirm', ['token' => $resetToken->getToken()]);

		$this->assertResponseRedirects($_ENV['RESET_PASSWORD_LINK_SUCCESS_REDIRECT_TO']);
	}

	public function testResetPasswordConfirmFailed()
	{
		$this->client->catchExceptions(false);

		$this->client->request('GET', '/api/auth/email/reset-password-confirm', ['token' => 'wrongtoken']);

		$this->assertResponseRedirects($_ENV['RESET_PASSWORD_LINK_FAIL_REDIRECT_TO']);
	}
}
