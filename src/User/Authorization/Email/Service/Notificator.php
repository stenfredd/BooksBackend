<?php

declare(strict_types=1);

namespace App\User\Authorization\Email\Service;

use App\Service\Mail\MailData;
use App\Service\Mail\Mailer;
use App\User\Entity\User;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Notificator
{
	/**
	 * @var Mailer
	 */
	private $mailer;

	/**
	 * @var UrlGeneratorInterface
	 */
	private $router;

	/**
	 * @var string
	 */
	private $siteName;

	/**
	 * UserNotificator constructor.
	 * @param string $siteName
	 * @param Mailer $mailer
	 * @param UrlGeneratorInterface $router
	 */
	public function __construct(string $siteName, Mailer $mailer, UrlGeneratorInterface $router)
	{
		$this->siteName = $siteName;
		$this->mailer = $mailer;
		$this->router = $router;
	}

	/**
	 * @param User $user
	 * @param string $activationToken
	 * @param string $password
	 * @throws TransportExceptionInterface
	 */
	public function signUpEmailNotification(User $user, string $activationToken, string $password): void
	{
		$activationLink = $this->router->generate('activate_email', ['token' => $activationToken], UrlGeneratorInterface::ABSOLUTE_URL);

		$mail = new MailData('email_signup', sprintf('Благодарим за регистрацию на сайте %s!', $this->siteName), $user->getEmail(), [
			'nickname' => $user->getUserData()->getNickname(),
			'siteName' => $this->siteName,
			'activationLink' => $activationLink,
			'userEmail' => $user->getEmail(),
			'userPassword' => $password
		]);
		$this->mailer->send($mail);
	}

	/**
	 * @param User $user
	 * @param string $activationToken
	 * @throws TransportExceptionInterface
	 */
	public function activationLinkEmail(User $user, string $activationToken): void
	{
		$activationLink = $this->router->generate('activate_email', ['token' => $activationToken], UrlGeneratorInterface::ABSOLUTE_URL);

		$mail = new MailData('email_resend_activation_link', sprintf('Активируйте аккаунт на сайте %s!', $this->siteName), $user->getEmail(), [
			'nickname' => $user->getUserData()->getNickname(),
			'siteName' => $this->siteName,
			'activationLink' => $activationLink
		]);
		$this->mailer->send($mail);
	}

	/**
	 * @param User $user
	 * @param string $resetPasswordToken
	 * @throws TransportExceptionInterface
	 */
	public function resetPasswordLink(User $user, string $resetPasswordToken): void
	{
		$resetPasswordLink = $this->router->generate('reset_password_confirm', ['token' => $resetPasswordToken], UrlGeneratorInterface::ABSOLUTE_URL);

		$mail = new MailData('email_password_reset', sprintf('Ваш пароль на сайте %s был изменен', $this->siteName), $user->getEmail(), [
			'resetPasswordLink' => $resetPasswordLink,
			'siteName' => $this->siteName,
			'nickname' => $user->getUserData()->getNickname(),
			'userEmail' => $user->getEmail()
		]);
		$this->mailer->send($mail);
	}

	/**
	 * @param User $user
	 * @param string $password
	 * @throws TransportExceptionInterface
	 */
	public function resetPasswordSuccess(User $user, string $password): void
	{
		$mail = new MailData('email_new_password', sprintf('Ваш пароль на сайте %s изменен', $this->siteName), $user->getEmail(), [
			'nickname' => $user->getUserData()->getNickname(),
			'userEmail' => $user->getEmail(),
			'userPassword' => $password,
			'siteName' => $this->siteName
		]);
		$this->mailer->send($mail);
	}

}