<?php
/**
 * Created by PhpStorm.
 * User: rostandnj
 * Date: 19/3/19
 * Time: 11:35 AM
 */

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTNotFoundEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTInvalidEvent;

class JWTInvalidListener
{
    /**
     * @param JWTNotFoundEvent $event
     */
    public function onJWTNotFound(JWTNotFoundEvent $event): void
    {
        $data = [
            'message'=>'Missing token', 'code' =>'token_not_found','status'  => '403 Forbidden'
        ];

        $response = new JsonResponse($data, 403);

        $event->setResponse($response);
    }

    /**
     * @param JWTInvalidEvent $event
     */
    public function onJWTInvalid(JWTInvalidEvent $event): void
    {
        $data = [
            'message' => 'Invalid JWT Token', 'code' => 'invalid_jwt_token','status'  => '403 Forbidden'
        ];

        $response = new JsonResponse($data, 403);

        $event->setResponse($response);
    }

}
