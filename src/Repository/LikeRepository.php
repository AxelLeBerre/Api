<?php

namespace App\Repository;

use App\Entity\Likes;
use App\Entity\User;
use App\Entity\Movies;
use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LikeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Likes::class);
    }

    /**
     * Find an existing like for a movie by a specific user.
     *
     * @param User $user The user who liked the movie
     * @param Movies $movie The movie that was liked
     *
     * @return Likes|null The like object or null if not found
     */
    public function findExistingLikeForMovie(User $user, Movies $movie): ?Likes
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.user = :user')
            ->andWhere('l.movie = :movie')
            ->setParameter('user', $user)
            ->setParameter('movie', $movie)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find an existing like for a comment by a specific user.
     *
     * @param User $user The user who liked the comment
     * @param Comment $comment The comment that was liked
     *
     * @return Likes|null The like object or null if not found
     */
    public function findExistingLikeForComment(User $user, Comment $comment): ?Likes
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.user = :user')
            ->andWhere('l.comment = :comment')
            ->setParameter('user', $user)
            ->setParameter('comment', $comment)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
