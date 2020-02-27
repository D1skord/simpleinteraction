<?php

namespace App\Repository;

use App\Entity\Room;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;



class RoomRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Room::class);
    }


    public function findByUserId($userId)
    {
        $query = $this->createQueryBuilder("r")
            ->where("r.user = :user")
            ->setParameter('user', $userId);
        return $query->getQuery()->getResult();
    }


}
