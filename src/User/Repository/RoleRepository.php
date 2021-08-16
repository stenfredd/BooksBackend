<?php

declare(strict_types=1);

namespace App\User\Repository;

use App\User\Entity\Role;

use App\User\Entity\User;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @method Role|null find($id, $lockMode = null, $lockVersion = null)
 * @method Role|null findOneBy(array $criteria, array $orderBy = null)
 * @method Role[]    findAll()
 * @method Role[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RoleRepository extends ServiceEntityRepository implements RoleRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Role::class);
    }

	/**
	 * @param $role
	 * @return Role
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function save($role): Role
	{
		try {
			$this->_em->persist($role);
			$this->_em->flush();

			return $role;
		} catch (UniqueConstraintViolationException $exception) {
			throw new \LogicException(sprintf('Role "%s" already exists', $role->getName()));
		}
	}

	/**
	 * @param int $id
	 * @return Role
	 */
	public function getById(int $id): Role
	{
		if(!$role = $this->findOneBy(['id' => $id])){
			throw new NotFoundHttpException('Role not found');
		}
		return $role;
	}

	/**
	 * @param string $name
	 * @return Role
	 * @throws NotFoundHttpException
	 */
	public function getRoleByName(string $name): Role
	{
		if(!$role = $this->findOneBy(['name' => $name])){
			throw new NotFoundHttpException(sprintf('Role "%s" not found', $name));
		}

		return $role;
	}

	/**
	 * @return Role[]|array
	 */
	public function getAllRoles(): array
	{
		return $this->findAll();
	}

	/**
	 * @param $role
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function delete($role): void
	{
		try {
			$this->_em->remove($role);
			$this->_em->flush();
		} catch (UniqueConstraintViolationException $exception) {
			throw new NotFoundHttpException('Role not found');
		}
	}

	/**
	 * @return array
	 */
	public function getAllRolesPermissions(): array
	{
		$entityManager = $this->getEntityManager();

		$rsm = new ResultSetMappingBuilder($entityManager);
		$rsm->addScalarResult('role_name', 'role_name', 'string');
		$rsm->addScalarResult('permission_name', 'permission_name', 'string');

		$sql = 'select 
					roles."name" as role_name, 
					"permission"."name" as permission_name 
				from role_permission 
					inner join roles on (roles.id = role_permission.role_id) 
					inner join "permission" on ("permission".id = role_permission.permission_id)';

		$query = $entityManager->createNativeQuery($sql, $rsm);

		return $query->getArrayResult();
	}

}
