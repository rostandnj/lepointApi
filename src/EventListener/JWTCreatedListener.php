<?php
/**
 * Created by PhpStorm.
 * User: rostandnj
 * Date: 15/3/19
 * Time: 5:16 PM
 */
namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;

class JWTCreatedListener
{

    public function __construct()
    {

    }

    /**
     * @param JWTCreatedEvent $event
     *
     * @return void
     */
    public function onJWTCreated(JWTCreatedEvent $event): void
    {


        $user = $event->getUser();

        $payload = $event->getData();
        $payload['id']=$user->getId();
        $payload['email']=$user->getEmail();
        $payload['name']=$user->getName();
        $payload['surname']=$user->getSurname();
        $payload['type']=$user->getType();
        $payload['picture']=$user->getPicture();
        //$payload["is_active"]=$user->getIsActive();
        //$payload["is_close"]=$user->getIsClose();
        //$payload["is_valid"]=$user->getIsValid();


        $event->setData($payload);

        $header        = $event->getHeader();
        $header['cty'] = 'JWT';

        $event->setHeader($header);


    }
}
