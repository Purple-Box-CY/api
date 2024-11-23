<?php

namespace App\User\Repository;

use App\User\Entity\User;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @implements PasswordUpgraderInterface<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(
        ManagerRegistry $registry,
    ) {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setHashedPassword($newHashedPassword);
        $this->save($user);
    }

    public function modifyBalance(int $userId, float $value): int
    {
        $sql = <<<SQL
UPDATE users
SET balance = balance + :value
WHERE id = :userId
AND balance > :negative;
SQL;
        $params = [
            'value' => $value,
            'negative' => -$value,
            'userId' => $userId,
        ];
        $stmt = $this->_em->getConnection()->prepare($sql);

        return $stmt->executeQuery($params)->rowCount();
    }

    public function save(User $user): User
    {
        $roles = $user->getRoles();
        if (in_array(User::ROLE_AUTH_TYPE_CMS, $roles)) {
            $roles = array_diff($roles, [User::ROLE_AUTH_TYPE_CMS]);
        }
        $user->setRoles($roles);

        $this->_em->persist($user);
        $this->_em->flush();

        return $user;
    }
}
