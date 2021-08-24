<?php


namespace App\Security;


use Lexik\Bundle\JWTAuthenticationBundle\Security\Guard\JWTTokenAuthenticator;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * Exception that should be thrown from a {@link JWTTokenAuthenticator} implementation during
 * an authentication process.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class AccessDeniedException extends AuthenticationException
{

    /**
     * {@inheritdoc}
     */
    public function getMessageKey()
    {
        return 'Account Locked';
    }
}
