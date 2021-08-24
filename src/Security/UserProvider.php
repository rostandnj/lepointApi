<?php


namespace App\Security;

use App\Entity\User;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface
{

    private $container;

    public function __construct(ContainerInterface $c)
    {
        $this->container = $c;
    }

    public function loadUserByUsername($playload)
    {

       return$this->container->get('doctrine.orm.entity_manager')->getRepository(User::class)->findOneBy(array('email' =>$playload['email']));

    }


    public function refreshUser(UserInterface $user)
    {

        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        $repository = $this->container->get('doctrine.orm.entity_manager')->getRepository(User::class);
        if ($repository instanceof UserProviderInterface) {
            $refreshedUser = $repository->refreshUser($user);
        } else {
            // The user must be reloaded via the primary key as all other data
            // might have changed without proper persistence in the database.
            // That's the case when the user has been changed by a form with
            // validation errors.
            if (!$id = $this->container->get('doctrine.orm.entity_manager')->getClassMetadata(User::class)->getIdentifierValues($user)) {
                throw new \InvalidArgumentException('You cannot refresh a user '.
                    'from the EntityUserProvider that does not contain an identifier. '.
                    'The user object has to be serialized with its own identifier '.
                    'mapped by Doctrine.'
                );
            }

            $refreshedUser = $repository->find($id);
            if (null === $refreshedUser) {
                throw new UsernameNotFoundException(sprintf('User with id %s not found', json_encode($id)));
            }
        }

        return $refreshedUser;
    }


    public function supportsClass($class)
    {
        return User::class === $class;
    }

}
