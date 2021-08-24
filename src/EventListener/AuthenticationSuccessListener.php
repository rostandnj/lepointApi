<?php
/**
 * Created by PhpStorm.
 * User: rostandnj
 * Date: 26/7/19
 * Time: 10:06 PM
 */

namespace App\EventListener;


use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;

class AuthenticationSuccessListener
{
    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event): void
    {
        $event->getResponse()->setStatusCode(201);
        /*  $event->setData([
              'code' => $event->getResponse()->getStatusCode(),
              'payload' => $event->getData(),
          ]);*/
    }
}
