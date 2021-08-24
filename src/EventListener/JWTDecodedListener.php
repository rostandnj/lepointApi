<?php
/**
 * Created by PhpStorm.
 * User: rostandnj
 * Date: 15/3/19
 * Time: 5:16 PM
 */
namespace App\EventListener;

use App\Service\UserService;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTDecodedEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTFailureException;

class JWTDecodedListener
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    private $container;

    /**
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack,ContainerInterface $c)
    {
        $this->requestStack = $requestStack;
        $this->container = $c;
    }

    /**
     * @param JWTDecodedEvent $event
     *
     * @return void
     */
    public function onJWTDecoded(JWTDecodedEvent $event)
    {
        $payload = $event->getPayload();
        $u=$this->container->get(UserService::class)->getUserById($payload['id']);
        if($u === null) $event->markAsInvalid();
        if(is_string($u)) $event->markAsInvalid();
        if ($u !== null && $u->getIsActive()==false) $event->markAsInvalid();
        if ($u !== null && $u->getIsClose()==true) $event->markAsInvalid();



    }
}
