<?php
/**
 * Created by PhpStorm.
 * User: rostandnj
 * Date: 19/3/19
 * Time: 11:24 AM
 */

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTExpiredEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationFailureResponse;

class JWTExpiredListener
{
    /**
     * @param JWTExpiredEvent $event
     */
    public function onJWTExpired(JWTExpiredEvent $event): void
    {
        /** @var JWTAuthenticationFailureResponse */
        $response = $event->getResponse();
        $response->setStatusCode(400);

        $response->setMessage('Your token is expired, please renew it.');
    }

}
