<?php

declare(strict_types=1);

namespace App\User\Repository;

use App\User\Entity\User;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements UserRepositoryInterface
{
	/**
	 * UserRepository constructor.
	 * @param ManagerRegistry $registry
	 */
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, User::class);
	}

	/**
	 * @param User $user
	 * @return User
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function save(User $user): User
	{
		try {
			$this->_em->persist($user);
			$this->_em->flush();

			return $user;
		} catch (UniqueConstraintViolationException $exception) {
			throw new \LogicException('User with this email is already registered');
		}

	}

	/**
	 * @param string $email
	 * @return User
	 * @throws NotFoundHttpException
	 */
	public function getByEmail(string $email): User
	{
		if(!$user = $this->findOneBy(['email' => $email])){
			throw new NotFoundHttpException('User not found');
		}
		return $user;
	}

	/**
	 * @param int $id
	 * @return User
	 * @throws NotFoundHttpException
	 */
	public function getById(int $id): User
	{
		if(!$user = $this->findOneBy(['id' => $id])){
			throw new NotFoundHttpException('User not found');
		}
		return $user;
	}

	/**
	 * @param $user
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function delete($user)
	{
		try {
			$this->_em->remove($user);
			$this->_em->flush();
		} catch (UniqueConstraintViolationException $exception) {
			throw new NotFoundHttpException('User not found');
		}
	}

	/**
	 * @param string|null $orderBy
	 * @param string|null $orderDirection
	 * @param string|null $stringQuery
	 * @param int|null $limit
	 * @param int|null $offset
	 * @return array
	 */
	public function search(?string $orderBy, ?string $orderDirection, ?string $stringQuery, ?int $limit, ?int $offset): array
	{
		$qb = $this->createSearchQuery($orderBy, $orderDirection, $stringQuery, $limit, $offset);
		$db_query = $qb->getQuery();

		return $db_query->execute();
	}

	/**
	 * @param string|null $orderBy
	 * @param string|null $orderDirection
	 * @param string|null $stringQuery
	 * @return int
	 */
	public function searchTotalRows(?string $orderBy, ?string $orderDirection, ?string $stringQuery): int
	{
		$qb = $this->createSearchQuery($orderBy, $orderDirection, $stringQuery, null, null);
		$db_query = $qb->getQuery();

		$paginator = new Paginator($db_query, $fetchJoinCollection = true);
		return $paginator->count();
	}

	/**
	 * @param string|null $orderBy
	 * @param string|null $orderDirection
	 * @param string|null $stringQuery
	 * @param int|null $limit
	 * @param int|null $offset
	 * @return QueryBuilder
	 */
	private function createSearchQuery(?string $orderBy, ?string $orderDirection, ?string $stringQuery, ?int $limit, ?int $offset): QueryBuilder
	{
		$qb = $this->createQueryBuilder('u');
		$qb->innerJoin('u.userData', 'd');
		$qb->innerJoin('u.roles', 'r');
		$qb->groupBy('u.id', 'd.id');

		$this->setCriteria($qb, $orderBy, $orderDirection, $stringQuery, $limit, $offset);

		return $qb;
	}

	/**
	 * @param QueryBuilder $queryBuilder
	 * @param string|null $orderBy
	 * @param string|null $orderDirection
	 * @param string|null $stringQuery
	 * @param int|null $limit
	 * @param int|null $offset
	 * @return QueryBuilder
	 */
	private function setCriteria(QueryBuilder $queryBuilder, ?string $orderBy, ?string $orderDirection, ?string $stringQuery, ?int $limit, ?int $offset): QueryBuilder
	{
		$orders = ['u.id', 'u.email', 'u.createdAt', 'd.nickname'];
		if ($orderBy !== null && !in_array($orderBy, $orders)) {
			throw new \InvalidArgumentException(sprintf('Invalid value orderBy. Use: %s', implode(', ', $orders)));
		}
		$directions = ['ASC', 'DESC'];
		$orderDirection = $orderDirection === null ? 'ASC' : $orderDirection;
		if ($orderDirection !== null && !in_array($orderDirection, $directions)) {
			throw new \InvalidArgumentException(sprintf('Invalid value directions. Use: %s', implode(', ', $directions)));
		}

		if ($stringQuery) {
			$queryBuilder->orWhere('LOWER(u.email) LIKE :query');
			$queryBuilder->orWhere('LOWER(d.nickname) LIKE :query');
			$queryBuilder->orWhere('LOWER(r.description) LIKE :query');

			$queryBuilder->setParameter(':query',  '%'.strtolower($stringQuery).'%');
		}

		if ($orderBy) {
			$queryBuilder->orderBy($orderBy, $orderDirection);
		}

		if ($limit) {
			$queryBuilder->setMaxResults($limit);
		}

		if ($offset) {
			$queryBuilder->setFirstResult($offset);
		}

		return $queryBuilder;
	}

}