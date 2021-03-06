<?php

declare(strict_types=1);

namespace App\User\Authorization\Email\Repository;

use App\User\Authorization\Email\Entity\ActivationToken;
use App\User\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @method ActivationToken|null find($id, $lockMode = null, $lockVersion = null)
 * @method ActivationToken|null findOneBy(array $criteria, array $orderBy = null)
 * @method ActivationToken[]    findAll()
 * @method ActivationToken[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActivationTokenRepository extends ServiceEntityRepository
{
	/**
	 * ActivationTokenRepository constructor.
	 * @param ManagerRegistry $registry
	 */
	public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ActivationToken::class);
    }

	/**
	 * @param ActivationToken $token
	 * @return ActivationToken
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function save(ActivationToken $token): ActivationToken
	{
		$this->_em->persist($token);
		$this->_em->flush();

		return $token;
	}

	/**
	 * @param string $token
	 * @return ActivationToken
	 */
	public function getTokenByValue(string $token): ActivationToken
	{
		if(!$token = $this->findOneBy(['token' => $token])){
			throw new NotFoundHttpException('Token not found');
		}
		return $token;
	}

	/**
	 * @param User $user
	 */
	public function deleteUserTokens(User $user): void
	{
		$this->createQueryBuilder('t')
			->delete()
			->where('t.holder = :holder_id')
			->setParameter('holder_id', $user->getId())
			->getQuery()
			->execute();
	}
}
