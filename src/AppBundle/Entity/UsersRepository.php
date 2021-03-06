<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * UsersRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class UsersRepository extends EntityRepository
{
	function getLimitedUsers($offset = 0,$search = null,$limit)
	{
		$query = $this->createQueryBuilder('u');

		$query->addOrderBy('u.nom','ASC')
		        ->setFirstResult( $offset )
		        ->setMaxResults( $limit );

		if($search != null)
		{
			$query->where('u.nom LIKE :search')
				->setParameter(':search','%'.$search.'%');
		}

		return $query->getQuery()->getResult();
	}

	function countAllWithSearch($search = null)
	{
		$query = $this->createQueryBuilder('u')
						->select('COUNT(u)');

		if($search != null)
		{
			$query->where('u.nom LIKE :search')
			      ->setParameter(':search','%'.$search.'%');
		}

		return $query->getQuery()->getSingleScalarResult();
	}
}
