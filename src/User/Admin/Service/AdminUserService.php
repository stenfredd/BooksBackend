<?php

declare(strict_types=1);

namespace App\User\Admin\Service;

use App\Service\DTO\ValueObjectToEntity;
use App\User\Admin\Formatter\UserSearchOrderFields;
use App\User\Admin\ValueObject\CreateUser;
use App\User\Admin\ValueObject\UpdateUser;
use App\User\Entity\User;
use App\User\Entity\UserData;
use App\User\Repository\UserRepositoryInterface;
use App\User\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;

class AdminUserService
{
	/**
	 * @var UserRepositoryInterface
	 */
	private $userRepository;

	/**
	 * @var EntityManagerInterface
	 */
	private $entityManager;

	/**
	 * @var UserSearchOrderFields
	 */
	private $userSearchOrderFields;

	/**
	 * @var UserService
	 */
	private $userService;

	/**
	 * @var ValueObjectToEntity
	 */
	private $valueObjectToEntity;

	/**
	 * AdminUserService constructor.
	 * @param UserRepositoryInterface $userRepository
	 * @param EntityManagerInterface $entityManager
	 * @param UserService $userService
	 * @param UserSearchOrderFields $userSearchOrderFields
	 * @param ValueObjectToEntity $valueObjectToEntity
	 */
	public function __construct(
		UserRepositoryInterface $userRepository,
		EntityManagerInterface $entityManager,
		UserService $userService,
		UserSearchOrderFields $userSearchOrderFields,
		ValueObjectToEntity $valueObjectToEntity
	)
	{
		$this->userRepository = $userRepository;
		$this->entityManager = $entityManager;
		$this->userService = $userService;
		$this->userSearchOrderFields = $userSearchOrderFields;
		$this->valueObjectToEntity = $valueObjectToEntity;
	}

	/**
	 * @param string|null $orderBy
	 * @param string|null $orderDirection
	 * @param string|null $query
	 * @return int
	 */
	public function usersSearchTotalRows(?string $orderBy, ?string $orderDirection, ?string $query): int
	{
		$orderBy = $this->userSearchOrderFields->fieldToEntityProp($orderBy);
		return $this->userRepository->searchTotalRows($orderBy, $orderDirection, $query);
	}

	/**
	 * @param string|null $orderBy
	 * @param string|null $orderDirection
	 * @param string|null $query
	 * @param int|null $limit
	 * @param int|null $page
	 * @return array
	 */
	public function usersSearch(?string $orderBy, ?string $orderDirection, ?string $query, ?int $limit, ?int $page): array
	{
		$page = ($page <= 1) ? null : $page - 1;
		$offset = ($page !== null && $limit !== null) ? $page*$limit : null;

		$orderBy = $this->userSearchOrderFields->fieldToEntityProp($orderBy);
		return $this->userRepository->search($orderBy, $orderDirection, $query, $limit, $offset);
	}

	/**
	 * @param CreateUser $createUserVO
	 * @return User
	 */
	public function createUser(CreateUser $createUserVO): User
	{
		return $this->entityManager->transactional(function() use ($createUserVO) {
			$user = $this->userService->createUser($createUserVO->getEmail(), $createUserVO->getPassword(), [$createUserVO->getRole()]);

			if ($createUserVO->getActive() === '1') {
				$this->userService->activateUser($user);
			}

			/* @var UserData $userData */
			$userData = $this->valueObjectToEntity->get($createUserVO, UserData::class);

			$this->userService->setUserData($user, $userData);

			return $user;
		});
	}

	/**
	 * @param int $userId
	 * @param UpdateUser $updateUserVO
	 * @return User
	 */
	public function updateUser(int $userId, UpdateUser $updateUserVO): User
	{
		return $this->entityManager->transactional(function() use ($userId, $updateUserVO) {
			$user = $this->userService->getById($userId);

			if($updateUserVO->getEmail() !== null){
				$user->setEmail($updateUserVO->getEmail());
			}

			if($updateUserVO->getActive() !== null){
				$user->setActive($updateUserVO->getActive());
			}

			if($updateUserVO->getPassword() !== null){
				$user->setPassword($this->userService->getEncodedPassword($user, $updateUserVO->getPassword()));
			}

			/* @var UserData $userData */
			$userData = $this->valueObjectToEntity->get($updateUserVO, UserData::class, $user->getUserData(), true);
			$user->setUserData($userData);

			if($updateUserVO->getRole()){
				$this->userService->removeAllRoles($user);
				$this->userService->setRolesByNames($user, [$updateUserVO->getRole()]);
			}

			return $this->userRepository->save($user);
		});

	}

	/**
	 * @param int $userId
	 */
	public function deleteUser(int $userId): void
	{
		$this->userService->deleteUser($userId);
	}

}
