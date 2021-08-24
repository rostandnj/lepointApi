<?php
/**
 * Created by PhpStorm.
 * User: rostandnj
 * Date: 14/3/19
 * Time: 5:35 PM
 */

namespace App\Service;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


class MailService
{
    private $em;
    private $container;
    private $mailer;
    private $templating;
    private $sender;
    private $manager;



    public function __construct(EntityManagerInterface $em,ContainerInterface $c,\Swift_Mailer $mailer,\Twig\Environment $templating)
    {
        $this->em = $em;
        $this->container = $c;
        $this->mailer = $mailer;
        $this->templating =  $templating;
        $this->sender =  $c->getParameter('email_sender');

    }

    public function testMail()
    {


        $message = (new \Swift_Message('Hello Email'))
            ->setFrom($this->sender )
            ->setTo('njomorostand@gmail.com');


        $img = $message->embed(\Swift_Image::fromPath('assets/logo.png'));

        $message->setBody(
            $this->templating->render(
                'emails/test.html.twig',
                ['name' => "njomo",'logo'=>$img]
            ),
            'text/html'
        )
        ;


        $this->mailer->send($message);
        return "ok";

    }

    public function welcome(int $id,string $name,string $email,string $token)
    {


        $message = (new \Swift_Message($this->container->getParameter('email_welcome')))
            ->setFrom($this->sender)
            ->setTo($email);


        $img = $message->embed(\Swift_Image::fromPath('assets/logo.png'));

        $url =$this->container->getParameter('base_url').'/#/validation/account/'.$token;

        $message->setBody(
            $this->templating->render(
                'emails/welcome.html.twig',
                ['name' => $name,'logo'=>$img,'url'=>$url]
            ),
            'text/html'
        )
        ;


        $this->mailer->send($message);
        //return "ok";

    }
    public function welcomeManager(int $id,string $name,string $email,string $token, string $password)
    {


        $message = (new \Swift_Message($this->container->getParameter('email_welcome')))
            ->setFrom($this->sender)
            ->setTo($email);


        $img = $message->embed(\Swift_Image::fromPath('assets/logo.png'));

        $url =$this->container->getParameter('base_url');

        $message->setBody(
            $this->templating->render(
                'emails/welcome_manager.html.twig',
                ['name' => $name,'logo'=>$img,'url'=>$url,'password'=>$password]
            ),
            'text/html'
        )
        ;


        $this->mailer->send($message);
        //return "ok";

    }
    public function registrationOk(string  $name,string $email)
    {


        $message = (new \Swift_Message($this->container->getParameter('email_validation_ok')))
            ->setFrom($this->sender)
            ->setTo($email);


        $img = $message->embed(\Swift_Image::fromPath('assets/logo.png'));

        $url =$this->container->getParameter('base_url');

        $message->setBody(
            $this->templating->render(
                'emails/registration_ok.html.twig',
                ['name' => $name,'logo'=>$img,'url'=>$url]
            ),
            'text/html'
        )
        ;


        $this->mailer->send($message);
        //return "ok";

    }
    public function validationNo(string  $name,string $email)
    {


        $message = (new \Swift_Message('Account not validated'))
            ->setFrom($this->sender)
            ->setTo($email);


        $img = $message->embed(\Swift_Image::fromPath('assets/logo.png'));

        $url =$this->container->getParameter('base_url');

        $message->setBody(
            $this->templating->render(
                'emails/validation_no.html.twig',
                ['name' => $name,'logo'=>$img,'url'=>$url]
            ),
            'text/html'
        )
        ;


        $this->mailer->send($message);
        //return "ok";

    }
    public function validationOk(string  $name,string $email)
    {


        $message = (new \Swift_Message('Approved account'))
            ->setFrom($this->sender)
            ->setTo($email);


        $img = $message->embed(\Swift_Image::fromPath('assets/logo.png'));

        $url =$this->container->getParameter('base_url');

        $message->setBody(
            $this->templating->render(
                'emails/validation_ok.html.twig',
                ['name' => $name,'logo'=>$img,'url'=>$url]
            ),
            'text/html'
        )
        ;


        $this->mailer->send($message);
        //return "ok";

    }
    public function accountLocked(string  $name,string $email)
    {


        $message = (new \Swift_Message($this->container->getParameter('email_account_lock')))
            ->setFrom($this->sender)
            ->setTo($email);


        $img = $message->embed(\Swift_Image::fromPath('assets/logo.png'));

        $url =$this->container->getParameter('base_url');

        $message->setBody(
            $this->templating->render(
                'emails/account_lock.html.twig',
                ['name' => $name,'logo'=>$img,'url'=>$url]
            ),
            'text/html'
        )
        ;


        $this->mailer->send($message);
        //return "ok";

    }
    public function resetPassword(string  $name,string $email,string $password)
    {
        $message = (new \Swift_Message('Réinitialisation de mot de passe'))
            ->setFrom($this->sender)
            ->setTo($email);


        $img = $message->embed(\Swift_Image::fromPath('assets/logo.png'));

        $url =$this->container->getParameter('base_url');

        $message->setBody(
            $this->templating->render(
                'emails/reset_password.html.twig',
                ['name' => $name,'logo'=>$img,'url'=>$url,'password'=>$password]
            ),
            'text/html'
        )
        ;


        $this->mailer->send($message);
        //return "ok";
    }
    public function welcomeAdmin(int $id,string $name,string $email,string $token,string $password)
    {


        $message = (new \Swift_Message('Welcome to Digitrav'))
            ->setFrom($this->sender)
            ->setTo($email);


        $img = $message->embed(\Swift_Image::fromPath('assets/logo.png'));
        $id = $this->container->get("hash_service")->encrypt($id);

        $url =$this->container->getParameter('base_url').$this->container->get("router")->generate("web_validation",["id"=>$id,"token"=>$token]);

        $message->setBody(
            $this->templating->render(
                'emails/welcome_admin.html.twig',
                ['name' => $name,'logo'=>$img,'url'=>$url,"password"=>$password]
            ),
            'text/html'
        )
        ;


        $this->mailer->send($message);
        //return "ok";

    }

    public function sendMessage(string $name,string $email,string $message1)
    {



        $message = (new \Swift_Message('Nouveau message'))
            ->setFrom($this->sender)
            ->setTo($this->manager);


        $img = $message->embed(\Swift_Image::fromPath('assets/logo.png'));

        $url =$this->container->getParameter('base_url');

        $message->setBody(
            $this->templating->render(
                'emails/message.html.twig',
                ['name' => $name,'logo'=>$img,'url'=>$url,"message"=>$message1,"email"=>$email]
            ),
            'text/html'
        )
        ;


        $this->mailer->send($message);
    }


}
