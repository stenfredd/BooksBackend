<?php

declare(strict_types=1);

namespace App\User\Authorization\System\Repository;

use App\User\Authorization\System\Entity\AuthToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @method AuthToken|null find($id, $lockMode = null, $lockVersion = null)
 * @method AuthToken|null findOneBy(array $criteria, array $orderBy = null)
 * @method AuthToken[]    findAll()
 * @method AuthToken[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AuthTokenRepository extends ServiceEntityRepository implements AuthTokenRepositoryInterface
{
	/**
	 * AuthTokenRepository constructor.
	 * @param ManagerRegistry $registry
	 */
	public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AuthToken::class);
    }

	/**
	 * @param AuthToken $authToken
	 * @return AuthToken
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function save(AuthToken $authToken): AuthToken
	{
		$this->_em->persist($authToken);
		$this->_em->flush();

		return $authToken;
	}

	/**
	 * @param string $token
	 * @return AuthToken
	 */
	public function getByTokenValue(string $token): AuthToken
	{
		if(!$token = $this->findOneBy(['value' => $token])){
			throw new NotFoundHttpException('Auth Token not found');
		}
		return $token;
	}

	/**
	 * @param AuthToken $authToken
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function delete(AuthToken $authToken): void
	{
		$this->_em->remove($authToken);
		$this->_em->flush();
	}

}
