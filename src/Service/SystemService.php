<?php
/**
 * Created by PhpStorm.
 * User: rostandnj
 * Date: 13/3/19
 * Time: 2:02 PM
 */
namespace App\Service;



use App\Entity\BaseCategory;
use App\Entity\Country;
use App\Entity\Day;
use App\Entity\PayMode;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use App\Entity\User;

class SystemService
{
    private $em;
    private $container;

    public function __construct(EntityManagerInterface $em,ContainerInterface $c)
    {
        $this->em = $em;
        $this->container = $c;
    }

    public function getStatut()
    {
        $sys= $this->em->getRepository(User::class)->findAll();
        $nb = count($sys);
        if($nb === 0)
        {
            return ["message"=>"system empty","statut"=>0];
        }

        return ["message"=>"system already initialised","statut"=>1];

    }

    public function initSystem()
    {
        $country = new Country();
        $country->setCode('CM');
        $country->setNameEn('Cameroon');
        $country->setNameFr('Cameroun');

        $this->em->persist($country);

        $payMode1 = new PayMode();
        $payMode1->setName('OrangeMoney');
        $payMode1->setImage('orange-money.png');

        $payMode2 = new PayMode();
        $payMode2->setName('MTN Mobile Money');
        $payMode2->setImage('momo.png');

        $payMode3 = new PayMode();
        $payMode3->setName('PayPal');
        $payMode3->setImage('paypal.png');

        $payMode4 = new PayMode();
        $payMode4->setName('Cash');
        $payMode4->setImage('cash.png');

        $this->em->persist($payMode1);
        $this->em->persist($payMode2);
        $this->em->persist($payMode3);
        $this->em->persist($payMode4);

        $day1 = new Day();
        $day1->setNameEn('Monday');
        $day1->setNameFr('Lundi');
        $day1->setValue(1);

        $day2 = new Day();
        $day2->setNameEn('Tuesday');
        $day2->setNameFr('Mardi');
        $day2->setValue(2);

        $day3 = new Day();
        $day3->setNameEn('Wednesday');
        $day3->setNameFr('Mercredi');
        $day3->setValue(3);

        $day4 = new Day();
        $day4->setNameEn('Thursday');
        $day4->setNameFr('Jeudi');
        $day4->setValue(4);

        $day5 = new Day();
        $day5->setNameEn('Friday');
        $day5->setNameFr('Vendredi');
        $day5->setValue(5);

        $day6 = new Day();
        $day6->setNameEn('Saturday');
        $day6->setNameFr('Samedi');
        $day6->setValue(6);

        $day7 = new Day();
        $day7->setNameEn('Sunday');
        $day7->setNameFr('Dimanche');
        $day7->setValue(7);

        $this->em->persist($day1);
        $this->em->persist($day2);
        $this->em->persist($day3);
        $this->em->persist($day4);
        $this->em->persist($day5);
        $this->em->persist($day6);
        $this->em->persist($day7);

        $bc1 = new BaseCategory();
        $bc1->setNameEn('Entrée');
        $bc1->setNameFr('Starter dishes');
        $bc1->setImage('starter_dishes.png');

        $bc2 = new BaseCategory();
        $bc2->setNameEn('Dessert');
        $bc2->setNameFr('Dessert');
        $bc2->setImage('dessert.png');

        $bc3 = new BaseCategory();
        $bc3->setNameEn('Breakfast');
        $bc3->setNameFr('Petit déjeuner');
        $bc3->setImage('breakfast.png');

        $bc4 = new BaseCategory();
        $bc4->setNameEn('Fastfood');
        $bc4->setNameFr('Burger-shawarma');
        $bc4->setImage('fastfood.png');

        $bc5 = new BaseCategory();
        $bc5->setNameEn('Fruits');
        $bc5->setNameFr('Fruits');
        $bc5->setImage('fruits.png');

        $bc6 = new BaseCategory();
        $bc6->setNameEn('Salads');
        $bc6->setNameFr('Salades');
        $bc6->setImage('salads.png');

        $bc7 = new BaseCategory();
        $bc7->setNameEn('Soft drinks');
        $bc7->setNameFr('Boissons gazeuses');
        $bc7->setImage('soft_drinks.png');

        $bc8 = new BaseCategory();
        $bc8->setNameEn('Alcoholic beverages');
        $bc8->setNameFr('Boissons alcoolisées');
        $bc8->setImage('alcoholic_beverages.png');

        $bc9 = new BaseCategory();
        $bc9->setNameEn("Chef's meal");
        $bc9->setNameFr("Plats du chef");
        $bc9->setImage('chef_meal.png');

        $bc10 = new BaseCategory();
        $bc10->setNameEn('Appetizers');
        $bc10->setNameFr('Appéritifs');
        $bc10->setImage('appetizers.png');

        $bc11 = new BaseCategory();
        $bc11->setNameEn('Champagne-Wines');
        $bc11->setNameFr('Champagne-Vins');
        $bc11->setImage('wine.png');

        $bc12 = new BaseCategory();
        $bc12->setNameEn('Natural cocktail');
        $bc12->setNameFr('Cocktail naturel ');
        $bc12->setImage('natural_cocktail.png');

        $bc13 = new BaseCategory();
        $bc13->setNameEn('Alcoholic cocktail');
        $bc13->setNameFr('Cocktail alcoolisé');
        $bc13->setImage('alcoholic_cocktail.png');

        $bc14 = new BaseCategory();
        $bc14->setNameEn('Seafood');
        $bc14->setNameFr('Fruits de mers');
        $bc14->setImage('seafood.png');

        $bc15 = new BaseCategory();
        $bc15->setNameEn(' Ice cream');
        $bc15->setNameFr('Glace - Crême');
        $bc15->setImage('ice_cream.png');

        $bc16 = new BaseCategory();
        $bc16->setNameEn('Drinks');
        $bc16->setNameFr('Boissons');
        $bc16->setImage('drink.png');

        $bc17 = new BaseCategory();
        $bc17->setNameEn('Locals meals');
        $bc17->setNameFr('Mets traditionnels');
        $bc17->setImage('local_meal.png');

        $bc18 = new BaseCategory();
        $bc18->setNameEn('Roast meat / fish');
        $bc18->setNameFr('Roti viande  / poisson');
        $bc18->setImage('roast.png');

        $bc19 = new BaseCategory();
        $bc19->setNameEn('Main course');
        $bc19->setNameFr('Plat principal');
        $bc19->setImage('main_course.png');

        $bc20 = new BaseCategory();
        $bc20->setNameEn('Hot beverage');
        $bc20->setNameFr('Boissons chaudes');
        $bc20->setImage('hot_beverage.png');

        $bc21 = new BaseCategory();
        $bc21->setNameEn('Side dish');
        $bc21->setNameFr('Accompagnement');
        $bc21->setImage('side_dish.png');

        $this->em->persist($bc1);
        $this->em->persist($bc2);
        $this->em->persist($bc3);
        $this->em->persist($bc4);
        $this->em->persist($bc5);
        $this->em->persist($bc6);
        $this->em->persist($bc7);
        $this->em->persist($bc8);
        $this->em->persist($bc9);
        $this->em->persist($bc10);
        $this->em->persist($bc11);
        $this->em->persist($bc12);
        $this->em->persist($bc13);
        $this->em->persist($bc14);
        $this->em->persist($bc15);
        $this->em->persist($bc16);
        $this->em->persist($bc17);
        $this->em->persist($bc18);
        $this->em->persist($bc19);
        $this->em->persist($bc20);
        $this->em->persist($bc21);





        $user = new User();
        $user->setName("admin");
        $user->setSurname("admin");
        $user->setEmail("admin@admin.cm");
        $user->setGender(1);
        $user->setType(User::USER_ADMIN);
        $user->setPhone("00237694864251");
        $user->setIsClose(false);
        $user->setIsActive(true);
        $user->setPassword($this->container->get("security.password_encoder")->encodePassword($user,'123456' ));



        $this->em->persist($user);


        $this->em->flush();


        return ["message"=>"system initialised","statut"=>1];




    }
}
