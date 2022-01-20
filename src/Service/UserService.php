<?php


namespace App\Service;

use App\Entity\AcceptedPayMode;
use App\Entity\Advert;
use App\Entity\Article;
use App\Entity\BaseCategory;
use App\Entity\City;
use App\Entity\Comment;
use App\Entity\Country;
use App\Entity\Day;
use App\Entity\File;
use App\Entity\GlobalInfo;
use App\Entity\Location;
use App\Entity\Menu;
use App\Entity\MenuCategory;
use App\Entity\NightPharmacy;
use App\Entity\Notification;
use App\Entity\OpenDay;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\PayMode;
use App\Entity\Entity;
use App\Entity\EntityActivation;
use App\Entity\Product;
use App\Entity\Reaction;
use App\Entity\Status;
use App\Entity\Upload;
use App\Entity\User;
use App\Service\MySerializer;
use chillerlan\QRCode\QROptions;
use DateTime;
use Doctrine\ORM\Query\Expr\Base;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bridge\Twig\Mime\NotificationEmail;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Doctrine\ORM\EntityManagerInterface;
use Intervention\Image\ImageManager;
use chillerlan\QRCode\QRCode;
use TypeError;


class UserService
{
    private $em;
    private $container;
    private $encoder;
    private $jwt;
    private $translator;
    private $locale;
    private $currentUser;

    public function __construct(EntityManagerInterface $em, JWTTokenManagerInterface $jwt,ContainerInterface $c,
                                UserPasswordEncoderInterface $encoder)
    {
        $this->em = $em;
        $this->container = $c;
        $this->encoder = $encoder;
        $this->jwt = $jwt;
        $this->translator = $this->container->get('translator');
        $this->locale= 'fr';

        $lang = $this->container->get('request_stack')->getCurrentRequest()->headers->get('lang');
        if($lang ===null){
            $this->locale = 'fr';
        }
        else{
            if(in_array($lang,['en','fr'])){
                $this->locale= $lang;
            }
            else{
                $this->locale= 'fr';
            }
        }

        $this->translator->setLocale($this->locale);


    }

    public function getUserByEmail(string $email) : Object
    {
        return $this->em->getRepository(User::class)->findOneBy(array('email' =>$email));
    }

    public function uploadImage(array $data) : array{

        if(count($data)>0){

            foreach ($data as $file){
                $uploadedFile=$file;
            }


            $upload = new Upload();
            $upload->save($uploadedFile,'uploads/');
            if($upload->getResult())
            {
                if($upload->getType() === "image")
                {
                    $manager = new ImageManager(array('driver' => 'gd'));
                    $name = $upload->getBaseFolder().'uploads/'.$upload->getName();
                    $image = $manager->make($name)->resize(500, 400);

                    $image2 = $manager->make($name)->resize(200, 150);

                    //finally we save the image as a new file
                    $savedname=$upload->getBaseFolder().'uploads/mini/'.$upload->getName();
                    $savednamep=$upload->getBaseFolder().'uploads/profile/'.$upload->getName();
                    $image->save($savedname);
                    $image2->save($savednamep);


                }

                $tab = $upload->toArray();
                $tab['error']=false;

                return $tab;


            }
            return ['error'=>true,'message'=>$upload->getError()];
        }

        return ['error'=>true,'message'=>'no file'];

    }

    private function getCurrentUser(): ?UserInterface
    {
        $token = $this->container->get('security.token_storage')->getToken();



        if (null === $token) {
            $this->currentUser =null;
        }
        else{
            $user = $token->getUser();
            if (!\is_object($user)) {
                // e.g. anonymous authentication
                $this->currentUser =null;
            }
            else{
                $this->currentUser = $user;
            }
        }

        return $this->currentUser;

    }

    private function passwordStrength (string $password): ?bool {
        $returnVal = true;
        if ( strlen($password) < 6 ) {
            $returnVal = false;
        }

        return $returnVal;
    }

    private function generateRandomString($length = 6) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function getAllUser() : array
    {

        //$users = $this->em->getRepository(User::class)->findClientAndOwner($limit,$offset);
        return ['error'=>false,'data'=>[]];
    }

    public function getAllTopManager(array $data) : array
    {

        $user = $this->getCurrentUser();
        if(!in_array($user->getType(),[User::USER_ADMIN])) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];

        $offset = 0;
        $limit = 10;
        if(array_key_exists('offset',$data)){ $offset = $data['offset'];}
        if(array_key_exists('limit',$data)){ $limit = $data['limit'];}

        $users = $this->em->getRepository(User::class)->findTopManager($limit,$offset);
        return ['error'=>false,'data'=>$this->container->get(MySerializer::class)->multipleObjectToArray($users,'user_all')];
    }

    public function getAllManager(array $data) : array
    {

        $user = $this->getCurrentUser();
        if(!in_array($user->getType(),[User::USER_ADMIN])) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];

        $offset = 0;
        $limit = 10;
        if(array_key_exists('offset',$data)){ $offset = $data['offset'];}
        if(array_key_exists('limit',$data)){ $limit = $data['limit'];}

        $users = $this->em->getRepository(User::class)->findManager($limit,$offset);
        return ['error'=>false,'data'=>$this->container->get(MySerializer::class)->multipleObjectToArray($users,'user_all')];
    }

    private function getRandom(int $n) :?string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers='0123456789';
        $maj='ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';

        for ($i = 0; $i < $n; $i++) {

            if($i ==$n-1)
            {
                $index = random_int(0, strlen($numbers) - 1);
                $randomString .= $numbers[$index];
            }
            else if($i==$n)
            {
                $index = random_int(0, strlen($maj) - 1);
                $randomString .= $maj[$index];
            }
            else
            {
                $index = random_int(0, strlen($characters) - 1);
                $randomString .= $characters[$index];
            }

        }

        return $randomString;
    }

    public function login(array $data1): ?array
    {

        $required = ['login','password'];

        foreach ($required as $el)
        {
            if(!array_key_exists($el,$data1))
            {
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : ' .$el];
            }

            if(trim($data1[$el])===''){
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : ' .$el];
            }

        }

        $sendData=$data1;
        $user = $this->em->getRepository(User::class)->findOneBy(array('email' =>$sendData['login']));

        if($user === null)  return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('login or password incorrect')];

        if($user->getIsClose())  return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('account locked')];
        if(!$user->getIsActive())  return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('account not activated')];

        if(!$this->encoder->isPasswordValid($user,$sendData['password'])) {
            if($user->getRpassword() !== null)
            {
                $user1 = $user;
                $user1->setPassword($user->getRpassword());

                if (!$this->encoder->isPasswordValid($user1,$user->getRpassword()))
                {
                    return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('login or password incorrect')];
                }
                $user->setPassword($user->getRpassword());
                $user->setRpassword(null);
                $this->em->persist($user);
                $this->em->flush();


            }

            return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('login or password incorrect')];

        }

        if(array_key_exists('admin',$data1)){
            if($user->getType() != User::USER_ADMIN){
                return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('unauthorized access')];
            }
        }

        $token = $this->jwt->create($user);



        return ['error'=>false,'token'=>$token,'usertype'=>$user->getType(),
            'user'=>$this->container->get(MySerializer::class)->singleObjectToArray($user,'user_all')];

    }

    public function updateToken(array $data): ?array{

        $user = $this->getCurrentUser();


        if($user === null) return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('please login')];


        $u = $this->em->getRepository(User::class)->findOneBy(['id'=>$user->getId()]);

        if($u === null ) return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('user_not_found')];

        $token = $this->jwt->create($u);


        return ['error'=>false,'token'=>$token,'usertype'=>$user->getType(),'user'=>$this->container->get(MySerializer::class)->singleObjectToArray($user,'all')];

    }

    public function getUserById(int $id):?Object
    {
        return $this->em->getRepository(User::class)->findOneBy(['id' =>$id]);
    }

    public function updateProfile(string $type,array $data) :?array
    {
        $user = $this->getCurrentUser();

        switch ($type)
        {
            case 'picture':

                if(!array_key_exists('file',$data)) return ['message' => 'file field is required', 'error' =>true, 'data' => []];
                $required =['name', 'size', 'extension', 'type', 'path'];

                foreach ($required as $key=>$el)
                {
                    if(!array_key_exists($el,$data['file']))
                    {
                        return ['message' =>$el. ' field is required', 'error' =>true, 'data' => []];
                    }
                }

                $user->setPicture($data['file']['path']);

                $this->em->persist($user);
                $this->em->flush();

                return ['message' => 'picture updated', 'error' =>false, 'data' => $this->container->get(MySerializer::class)->singleObjectToArray($user,'user_all')];

                break;
            case 'phone_birthday':
                if(!array_key_exists('phone',$data)) return ['message' =>'phone field is required', 'error' =>true, 'data' => []];
                if(strlen($data['phone'])< 9) return ['message' => $this->translator->trans('phone length error'), 'error' =>true, 'data' => []];
                if(!array_key_exists('birthday',$data)) return ['message' =>'birthday field is required', 'error' =>true, 'data' => []];
                if(strlen($data['birthday'])> 1){
                    try {
                        $user->setBirthday(\DateTime::createFromFormat('Y-m-d',$data['birthday']));
                    }
                    catch (TypeError $e){
                        return ['message' => 'birthday format error / required format is d/m/Y', 'error' =>true, 'data' => []];
                    }
                }


                if($user->getPhone() !== $data['phone']){
                    $oldUser = $this->em->getRepository(User::class)->findOneBy(['phone'=>$data['phone']]);
                    if ($oldUser !== null) return ['message' => $this->translator->trans('phone already used'), 'error' =>true, 'data' => []];
                    $user->setPhone($data['phone']);
                }
                $this->em->persist($user);
                $this->em->flush();

                return ['message' => 'profile updated', 'error' =>false, 'data' => $this->container->get(MySerializer::class)->singleObjectToArray($user,'user_all')];


                break;
            case 'birthday':
                if(!array_key_exists('birthday',$data)) return ['message' =>'birthday field is required', 'error' =>true, 'data' => []];

                if(strlen($data['birthday'])< 0) return ['message' => 'empty birthday', 'error' =>true, 'data' => []];

                try {
                    $user->setBirthday(\DateTime::createFromFormat('d/m/Y',$data['birthday']));
                }
                catch (TypeError $e){
                    return ['message' => 'birthday format error / required format is d/m/Y', 'error' =>true, 'data' => []];
                }


                $this->em->persist($user);
                $this->em->flush();
                return ['message' => 'birthday updated', 'error' =>false, 'data' => $this->container->get(MySerializer::class)->singleObjectToArray($user,'user_all')];


                break;
            case 'password':
                if(!array_key_exists('c-password',$data)) return ['message' => 'c-password field is required','error' =>true, 'data' =>[]];
                if(!array_key_exists('n-password',$data)) return ['message' => 'n-password field is required','error' =>true, 'data' =>[]];

                $res=$this->encoder->isPasswordValid($user,$data['c-password']);

                if($res === false) return ['message' => $this->translator->trans('current_password_invalid'), 'error' =>true, 'data' =>[]];

                $res = $this->passwordStrength($data['n-password']);

                if($res === false) return ['message' => $this->translator->trans('new_password_invalid'), 'error' =>true, 'data' =>[]];

                $user->setPassword($this->container->get('security.password_encoder')->encodePassword($user,$data['n-password'] ));

                $this->em->persist($user);

                $this->em->flush();

                return ['message' => 'birthday updated', 'error' =>false, 'data' => $this->container->get(MySerializer::class)->singleObjectToArray($user,'user_all')];

                break;
            case 'location':
                if(!array_key_exists($type,$data)) return ['message' =>$type. ' field is required', 'error' =>true, 'data' => []];
                $required = ['country_id','city', 'street', 'street_detail'];
                foreach ($required as $key=>$el)
                {
                    if(!array_key_exists($el,$data['location']))
                    {
                        return ['message' =>'location '.$el. ' field is required', 'error' =>true, 'data' => []];
                    }

                }

                $country = $this->em->getRepository(Country::class)->findOneBy(['id'=>$data['location']['country_id']]);
                if( $country === null) return ['message' =>'country not found', 'error' =>true, 'data' => []];

                $loc = $user->getLocation();
                if( $loc === null){
                    $loc = new Location();
                    $loc->setCountry($country);
                }

                $loc->setCity($data['location']['city']);
                $loc->setStreet($data['location']['street']);
                $loc->setStreetDetail($data['location']['street_detail']);

                $user->setLocation($loc);
                $this->em->persist($user);

                $this->em->flush();

                return ['message' => 'birthday updated', 'error' =>false, 'data' => $this->container->get(MySerializer::class)->singleObjectToArray($user,'user_all')];



                break;

            default:
                return ['message' => 'no type added', 'error' =>true, 'data' => []];
                break;
        }
    }

    public function resetPassword(string $email):?array
    {
        $user = $this->em->getRepository(User::class)->findOneBy(array('email' =>$email));

        if($user === null) return ['message' => $this->translator->trans('user not found'), 'error' =>true, 'data' => []];

        $pass = $this->getRandom(6);

        $user2 =new User();

        $user2->setPassword($this->container->get('security.password_encoder')->encodePassword($user2,$pass ));

        $user->setRpassword($user2->getPassword());

        $this->em->persist($user);
        $this->em->flush();

        $this->container->get('mail_manager')->resetPassword($user->getName().' '.$user->getSurname(),$user->getEmail(),$pass);

        return ['message' => $this->translator->trans('new password send'), 'error' =>false,  'data' =>[]];

    }

    public function validateAccount(string $token):?array {

        $user = $this->em->getRepository(User::class)->findOneBy(['token'=>$token]);
        if($user === null) {
            return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('account not found')];
        }
        if($user->getActivationDate() !== null) {
            return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('account already activated')];
        }
        $user->setActivationDate(new \DateTime());
        $user->setIsActive(true);

        $this->em->persist($user);
        $this->em->flush();

        return ['message' => $this->translator->trans('account activated'), 'error' =>false, 'data' => []];



    }

    // start edit here for all entities

    private function testIfUserIsAdminOrManager(UserInterface $user, Entity $entity):?array{
        if($user->getType() !== User::USER_ADMIN){
            if($user->getType() === User::USER_TOP_MANAGER){
                if($entity->getTopManager() !== $user){
                    return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];
                }
            }
            else
            {
                if($user->getType() === User::USER_MANAGER){
                    $managers = $entity->getManagers();
                    if(!$managers->contains($user)){
                        return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];
                    }
                }
                else{
                    if($entity->getOwner() !== $user){
                        return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];
                    }
                }


            }

        }

        return ['error'=>false];
    }

    public function registerEntity(array $data):?array{

        $required = ['name','description','website', 'facebook_page','whatsapp_phone','phone1','phone2','location','image','entity_type',
            'user_name','user_surname','user_email','user_phone','user_password','user_gender'];

        foreach ($required as $el)
        {
            if(!array_key_exists($el,$data))
            {
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : ' .$el];
            }

        }

        $oldUser = $this->em->getRepository(User::class)->findOneBy(['email'=>$data['user_email']]);
        if($oldUser !== null) return ['message' => $this->translator->trans('email already used'), 'error' =>true, 'data' => []];


        if(strlen($data['name']) < 2) return ['message' => $this->translator->trans('entity name must content at least 2 characters'), 'error' =>true, 'data' => []];
        if(strlen($data['description']) < 100) return ['message' => $this->translator->trans('entity description must content at least 100 characters'), 'error' =>true, 'data' => []];
        if(strlen($data['phone1'])< 9) return ['message' => $this->translator->trans('phone length error'), 'error' =>true, 'data' => []];
        if(strlen($data['user_phone'])< 9) return ['message' => $this->translator->trans('phone length error'), 'error' =>true, 'data' => []];
        $res = $this->passwordStrength($data['user_password']);

        if($res === false) return ['message' => $this->translator->trans('password_invalid'), 'error' =>true, 'data' =>[]];


        $requiredLocation = ['country_id','city', 'street', 'street_detail'];
        foreach ($requiredLocation as $key=>$el)
        {
            if(!array_key_exists($el,$data['location']))
            {
                return ['message' =>'location '.$el. ' field is required', 'error' =>true, 'data' => []];
            }

        }

        $country = $this->em->getRepository(Country::class)->findOneBy(['id'=>$data['location']['country_id']]);
        if( $country === null) return ['message' =>'country not found', 'error' =>true, 'data' => []];

        $entity = new Entity();
        $info = new GlobalInfo();

        $info->setName($data['name']);
        $info->setDescription($data['description']);
        $info->setImage($data['image']);
        $info->setFacebookPage($data['facebook_page']);
        $info->setWhatsappPhone($data['whatsapp_phone']);
        $info->setPhone1($data['phone1']);
        $info->setPhone2($data['phone2']);


        $loc = new Location();
        $loc->setCountry($country);
        $loc->setCity($data['location']['city']);
        $loc->setStreet($data['location']['street']);
        $loc->setStreetDetail($data['location']['street_detail']);
        $loc2 = clone $loc;

        $entity->setLocation($loc);
        $entity->setGlobalInfo($info);


        $user = new User();
        $user->setType(User::USER_OWNER);
        $user->setName($data['user_name']);
        $user->setSurname($data['user_surname']);
        $user->setEmail($data['user_email']);
        $user->setPhone($data['user_phone']);
        $user->setGender($data['user_gender']);
        $user->setPassword($this->container->get('security.password_encoder')->encodePassword($user,$data['user_password'] ));
        $user->setLocation($loc2);
        $entity->setOwner($user);
        $user->setToken(uniqid('',true));
        $this->em->persist($entity);
        $this->em->flush();

        $date = new \DateTime();
        $act = new EntityActivation();
        $act->setAmount(0);
        $act->setYear($date->format('Y'));
        $act->setEntityId($entity->getId());
        $act->setStartDate($date);
        $endDate = $date->add(new \DateInterval('P3D'));
        $act->setEndDate($endDate);

        $entity->setEntityActivation($act);
        $entity->setStatus(Entity::STATUS_PAID);
        $entity->setIsLock(false);


        $payCash = $this->em->getRepository(PayMode::class)->findOneBy(['name'=>'Cash']);
        if($payCash === null) return ['message' => $this->translator->trans('no pay mode cash registered'), 'error' =>true, 'data' => []];
        $acceptPay1 = new AcceptedPayMode();
        $acceptPay1->setEntity($entity);
        $acceptPay1->setDetailOne("cash");
        $acceptPay1->setPayMode($payCash);
        $entity->addAcceptedPayMode($acceptPay1);

        $this->em->persist($entity);
        $this->em->flush();



        $this->container->get("mail_manager")->welcome($user->getId(),$user->getName(),$user->getEmail(),$user->getToken());

        return ['error' => false, 'data' => [], 'message' =>$this->translator->trans('account_created')];

    }

    public function activateEntity(array $data):?array {
        $user = $this->getCurrentUser();

        if(!in_array($user->getType(),[User::USER_ADMIN, User::USER_TOP_MANAGER])) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];

        $required = ['id','start_date','end_date','amount','year'];

        foreach ($required as $el)
        {
            if(!array_key_exists($el,$data))
            {
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : ' .$el];
            }
        }

        $entity = $this->em->getRepository(Entity::class)->findOneBy(['id'=>$data['id']]);
        if($entity === null) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('not found')];
        $oldAct = $entity->getEntityActivation();

        if($oldAct !== null){
            $date = new DateTime();
            if($oldAct->getEndDate()> $date) {
                return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('extend enddate')];
            }
        }

        $act = new EntityActivation();
        $act->setAmount($data['amount']);
        $act->setYear($data['year']);
        $act->setEntityId($entity->getId());
        try {
            $act->setStartDate(\DateTime::createFromFormat('d/m/Y',$data['start_date']));
            $act->setEndDate(\DateTime::createFromFormat('d/m/Y',$data['end_date']));
            if($act->getEndDate()<$act->getStartDate()) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('endate_startdate_error')];
        }
        catch (TypeError $e){
            return ['message' => 'date format error / required format is d/m/Y', 'error' =>true, 'data' => []];
        }

        $entity->setStatus(Entity::STATUS_PAID);
        $entity->setIsLock(false);
        $entity->setEntityActivation($act);
        $this->em->persist($entity);
        $this->em->flush();

        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->singleObjectToArray($entity,'entity_all')];

    }

    public function extendActivationEntity(array $data):?array {
        $user = $this->getCurrentUser();

        if(!in_array($user->getType(),[User::USER_ADMIN, User::USER_TOP_MANAGER])) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];

        $required = ['id','days','amount'];

        foreach ($required as $el)
        {
            if(!array_key_exists($el,$data))
            {
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : ' .$el];
            }
        }

        $entity = $this->em->getRepository(Entity::class)->findOneBy(['id'=>$data['id']]);
        if($entity === null) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('not found')];
        $oldAct = $entity->getEntityActivation();

        if($oldAct === null)  return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('activate entity')];
        $date = new DateTime();
        $oldEnd = $oldAct->getEndDate();
        /*if($oldEnd< $date) {
            return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('activate restaurant')];
        }*/


        $act = $oldAct;
        $act->setAmount($act->getAmount()+ (int)$data['amount']);
        $act->setYear($date->format('Y'));
        try {
            if($oldEnd< $date){
                $add = $date->add(new \DateInterval('P'.$data['days'].'D'));

            }else{
                $add = $oldEnd->add(new \DateInterval('P'.$data['days'].'D'));
            }
            $act->setEndDate($add);
        }
        catch (TypeError $e){
            return ['message' => 'date format error', 'error' =>true, 'data' => []];
        }

        $entity->setStatus(Entity::STATUS_PAID);
        $entity->setIsLock(false);
        $entity->setEntityActivation($act);
        $this->em->persist($entity);
        $this->em->flush();

        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->singleObjectToArray($entity,'entity_all'), 'message' =>''];

    }

    public function changeEntityStatus(array $data) :?array{

        $user = $this->getCurrentUser();

        if(!in_array($user->getType(),[User::USER_ADMIN, User::USER_TOP_MANAGER])) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];


        $required = ['id','status'];

        foreach ($required as $el)
        {
            if(!array_key_exists($el,$data))
            {
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : ' .$el];
            }
        }

        $entity = $this->em->getRepository(Entity::class)->findOneBy(['id'=>$data['id']]);
        if($entity === null) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('not found')];

        /*if($data['status'] === 1){
            $entity->setIsLock(true);
        }
        else{
            $oldAct = $entity->getEntityActivation();
            if($oldAct !== null){
                $date = new DateTime();
                if($oldAct->getEndDate()> $date) {
                    $entity->setIsLock(false);
                }
                else{
                    return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('extend enddate')];
                }
            }
            else{
                return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('activate entity')];
            }


        }*/

        if($data['status'] === 1){
            $entity->setStatus(true);
        }
        else{
            $entity->setStatus(false);

        }


        $this->em->persist($entity);
        $this->em->flush();

        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->singleObjectToArray($entity,'entity_all')];
    }

    // ajout du type
    public function getActiveEntity(array  $data):?array{
        $required = ['type'];

        foreach ($required as $el)
        {
            if(!array_key_exists($el,$data))
            {
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : ' .$el];
            }
        }

        $offset = 0;
        $limit = 10;
        if(array_key_exists('offset',$data)){ $offset = $data['offset'];}
        if(array_key_exists('limit',$data)){ $limit = $data['limit'];}



        $entities= $this->em->getRepository(Entity::class)->findBy(['isActive'=>true,'isLock'=>false,'type'=>(int)$data['type']],['isPromoted'=>'DESC'],$limit,$offset);

        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->multipleObjectToArray($entities,'entity_all')];


    }

    // ajout du type
    public function getAllEntity(array  $data):?array{
        $user = $this->getCurrentUser();

         if(!in_array($user->getType(),[User::USER_ADMIN, User::USER_TOP_MANAGER])) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];
        $required = ['type'];

        foreach ($required as $el)
        {
            if(!array_key_exists($el,$data))
            {
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : ' .$el];
            }
        }
        $offset = 0;
        $limit = 10;
        if(array_key_exists('offset',$data)){ $offset = $data['offset'];}
        if(array_key_exists('limit',$data)){ $limit = $data['limit'];}

        $entities= $this->em->getRepository(Entity::class)->findBy(['isActive'=>true,'type'=>$data['type']],['name'=>'ASC'],$limit,$offset);

        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->multipleObjectToArray($entities,'entity_all')];


    }

    public function showOneEntity(array $data):?array {
        $required = ['id'];

        foreach ($required as $el)
        {
            if(!array_key_exists($el,$data))
            {
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : ' .$el];
            }
        }

        $entity = $this->em->getRepository(Entity::class)->findOneBy(['id'=>$data['id']]);
        if($entity === null) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('not found')];



        if(in_array($entity->getType(),[Entity::ENTITY_RESTAURANT, Entity::ENTITY_LAUNCH, Entity::ENTITY_DISCO])){
            $menuCategories = $this->em->getRepository(MenuCategory::class)->findBy(['entity'=>$entity,'isActive'=>true]);
            $menus = $this->em->getRepository(Menu::class)->findBy(['entity'=>$entity,'isActive'=>true]);
            $images = $this->em->getRepository(File::class)->findBy(['entity'=>$entity,'isActive'=>true]);

            return ['error' => false,
                'data' => ['entity'=>$this->container->get(MySerializer::class)->singleObjectToArray($entity,'entity_all'),
                'categories'=>$this->container->get(MySerializer::class)->multipleObjectToArray($menuCategories,'MC_all'),
                'menus'=>$this->container->get(MySerializer::class)->multipleObjectToArray($menus,'menu_all'),
                'images'=>$this->container->get(MySerializer::class)->multipleObjectToArray($images,'file_all')]
            ];


        }
        elseif(in_array($entity->getType(),[Entity::ENTITY_HOSTEL, Entity::ENTITY_MUSEUM, Entity::ENTITY_OFFICE])){

            $adverts = $this->em->getRepository(Advert::class)->findBy(['entity'=>$entity,'isActive'=>true],['date'=>'DESC'],10, 0);
            $images = $this->em->getRepository(File::class)->findBy(['entity'=>$entity,'isActive'=>true]);

            return ['error' => false,
                'data' => ['entity'=>$this->container->get(MySerializer::class)->singleObjectToArray($entity,'entity_all'),
                    'adverts'=>$this->container->get(MySerializer::class)->multipleObjectToArray($adverts,'adv_all'),
                    'images'=>$this->container->get(MySerializer::class)->multipleObjectToArray($images,'file_all')]
            ];

        }
        else{
            // pharmacy
            $adverts = $this->em->getRepository(Advert::class)->findBy(['entity'=>$entity,'isActive'=>true],['date'=>'DESC'],10, 0);
            $products = $this->em->getRepository(Product::class)->findBy(['entity'=>$entity,'isActive'=>true],['date'=>'DESC']);

            return ['error' => false,
                'data' => ['entity'=>$this->container->get(MySerializer::class)->singleObjectToArray($entity,'entity_all'),
                    'adverts'=>$this->container->get(MySerializer::class)->multipleObjectToArray($adverts,'adv_all'),
                    'products'=>$this->container->get(MySerializer::class)->multipleObjectToArray($products,'product_all')]
            ];

        }


    }

    public function manageEntityBaseCategories(array $data):?array {

        $user = $this->getCurrentUser();

        $required = ['id','categories','action'];

        foreach ($required as $el)
        {
            if(!array_key_exists($el,$data))
            {
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : ' .$el];
            }
        }

        $entity = $this->em->getRepository(Entity::class)->findOneBy(['id'=>$data['id']]);
        if($entity === null) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('not found')];

        if(!in_array($entity->getType(),Entity::ENTITY_USE_MENU)){
            return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];
        }


        $test = $this->testIfUserIsAdminOrManager($user,$entity);
        if($test['error'] === true) return $test;


        foreach ($data['categories'] as $catId){
            $cat = $this->em->getRepository(MenuCategory::class)->findOneBy(['baseCategory'=>$catId,'entity'=>$entity->getId()]);

            if($data['action'] === 'add'){
                if($cat === null){
                    $c = $this->em->getRepository(BaseCategory::class)->findOneBy(['id'=>$catId]);
                    if($c !== null){
                        $catMenu = new MenuCategory();
                        $catMenu->setEntity($entity);
                        $catMenu->setBaseCategory($c);
                        $entity->addMenuCategory($catMenu);
                    }

                }
                else{
                    if($cat->getIsActive() === false){
                        $cat->setIsActive(true);
                    }
                }
            }
            else{
                if($cat !== null){
                    $cat->setIsActive(false);
                    $entity->addMenuCategory($cat);
                    $entity->addMenuCategory($cat);
                }

            }

        }

        $this->em->persist($entity);
        $this->em->flush();

        $result= [];
        foreach ($entity->getMenuCategories() as $m){
            if($m->getIsActive()){
                $result[]=$m;
            }
        }



        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->multipleObjectToArray($result,'MC_all')];

    }

    public function addEntityMenu(array $data):?array {

        $user = $this->getCurrentUser();

        $required = ['id','name','description','price','is_day_menu','estimate_min_time','estimate_max_time','image','categories'];

        foreach ($required as $el)
        {
            if(!array_key_exists($el,$data))
            {
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : ' .$el];
            }
        }

        $entity = $this->em->getRepository(Entity::class)->findOneBy(['id'=>$data['id']]);
        if($entity === null) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('not found')];

        if(!in_array($entity->getType(),Entity::ENTITY_USE_MENU)){
            return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];
        }


        $test = $this->testIfUserIsAdminOrManager($user,$entity);
        if($test['error'] === true) return $test;

        if(count($data['categories']) === 0) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('choose at list one category')];
        if(strlen($data['name'])=== 0) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('empty field'). ' name'];
        if(strlen($data['description'])=== 0) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('empty field'). ' description'];
        if(strlen($data['price'])=== 0) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('empty field'). ' price'];

        $cats = [];
        foreach ($data['categories'] as $idCat){
            $cat = $this->em->getRepository(MenuCategory::class)->findOneBy(['id'=>$idCat]);
            if($cat !== null){
                $cats[] = $cat;
            }
        }

        if(count($cats) === 0) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('choose at list one category')];

        $menu = new Menu();
        $menu->setPrice($data['price']);
        $menu->setName($data['name']);
        $menu->setDescription($data['description']);
        $menu->setIsDayMenu((bool)$data['is_day_menu']);
        $menu->setEstimateMinTime($data['estimate_min_time']);
        $menu->setEstimateMaxTime($data['estimate_max_time']);
        $menu->setImage($data['image']);

        $menu->setEntity($entity);

        foreach ($cats as $c){
            $menu->addMenuCategory($c);
        }

        $this->em->persist($menu);
        $this->em->flush();

        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->singleObjectToArray($menu,'menu_all')];



    }

    public function editEntityMenu(array $data):?array {

        $user = $this->getCurrentUser();

        $required = ['id','name','description','price','is_day_menu','estimate_min_time','estimate_max_time','categories'];

        foreach ($required as $el)
        {
            if(!array_key_exists($el,$data))
            {
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : ' .$el];
            }
        }

        $menu = $this->em->getRepository(Menu::class)->findOneBy(['id'=>$data['id']]);
        if($menu === null) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('not found'). ' : entity menu'];

        $entity = $menu->getEntity();


        $test = $this->testIfUserIsAdminOrManager($user,$entity);
        if($test['error'] === true) return $test;

        if(count($data['categories']) === 0) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('choose at list one category')];
        if(strlen($data['name'])=== 0) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('empty field'). ' name'];
        if(strlen($data['description'])=== 0) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('empty field'). ' description'];
        if(strlen($data['price'])=== 0) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('empty field'). ' price'];

        $cats = [];
        foreach ($data['categories'] as $idCat){
            $cat = $this->em->getRepository(MenuCategory::class)->findOneBy(['id'=>$idCat]);
            if($cat !== null){
                $cats[] = $cat;
            }
        }

        if(count($cats) === 0) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('choose at list one category')];

        $oldCat = $menu->getMenuCategories();

        foreach ($oldCat as $o){
            $menu->removeMenuCategory($o);
        }

        $menu->setPrice($data['price']);
        $menu->setName($data['name']);
        $menu->setDescription($data['description']);
        $menu->setIsDayMenu((bool)$data['is_day_menu']);
        $menu->setEstimateMinTime($data['estimate_min_time']);
        $menu->setEstimateMaxTime($data['estimate_max_time']);
        if(array_key_exists('image',$data) && strlen($data['image'])>0){
           $menu->setImage($data['image']);
        }
        $menu->setEntity($entity);

        foreach ($cats as $c){
            $menu->addMenuCategory($c);
        }

        $this->em->persist($menu);
        $this->em->flush();

        $oldCat = $menu->getMenuCategories();

        $d = $this->container->get(MySerializer::class)->singleObjectToArray($menu,'menu_all');
        $d['menu_categories'] = [];

        foreach ($oldCat as $o){
            $d['menu_categories'][]=$this->container->get(MySerializer::class)->singleObjectToArray($o,'menu_all');
        }

        return ['error' => false, 'data' => $d];



    }

    public function deleteEntityMenu(array $data):?array {

        $user = $this->getCurrentUser();

        $required = ['id'];

        foreach ($required as $el)
        {
            if(!array_key_exists($el,$data))
            {
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : ' .$el];
            }
        }

        $menu = $this->em->getRepository(Menu::class)->findOneBy(['id'=>$data['id']]);
        if($menu === null) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('not found'). ' : entity menu'];

        $entity = $menu->getEntity();


        $test = $this->testIfUserIsAdminOrManager($user,$entity);
        if($test['error'] === true) return $test;


        $menu->setIsActive(false);

        $this->em->persist($menu);
        $this->em->flush();

        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->singleObjectToArray($menu,'menu_all')];



    }

    public function updateEntityDayMenu(array $data):?array {

        $user = $this->getCurrentUser();

        $required = ['id','menus'];

        foreach ($required as $el)
        {
            if(!array_key_exists($el,$data))
            {
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : ' .$el];
            }
        }

        $entity = $this->em->getRepository(Entity::class)->findOneBy(['id'=>$data['id']]);
        if($entity === null) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('not found')];

        $test = $this->testIfUserIsAdminOrManager($user,$entity);
        if($test['error'] === true) return $test;

        $allMenu = $this->em->getRepository(Menu::class)->findBy(['restaurant'=>$entity,'isActive'=>true]);

        $list = [];

        foreach ($allMenu as $menu){
            if(in_array($menu->getId(),$data['menus'])){
                $menu->setIsDayMenu(true);
                $list[] = $menu;
            }
            else{
                $menu->setIsDayMenu(false);
            }
            $this->em->persist($menu);
        }


        $this->em->flush();

        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->multipleObjectToArray($list,'menu_all')];



    }

    public function makeOrder(array $data):?array{

        $required = ['entity_id','menus','location','place','pay_mode'];

        foreach ($required as $el)
        {
            if(!array_key_exists($el,$data))
            {
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : ' .$el];
            }
        }

        if(!array_key_exists('customer_name',$data)){
            return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : customer_name'];
        }
        else{
            if(strlen($data['customer_name'])<3)  return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('customer name should not be empty')];
        }

        if(!array_key_exists('customer_phone',$data)){
            return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : customer_phone'];
        }
        else{
            if(strlen($data['customer_phone'])<9)  return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('customer phone length error')];
        }

        $entity = $this->em->getRepository(Entity::class)->findOneBy(['id'=>$data['entity_id']]);
        if($entity === null) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('not found')];

        if(!in_array($entity->getType(),Entity::ENTITY_USE_MENU)){
            return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];
        }



        $order = new Order();
        $order->setEntity($entity);

        $notif = new Notification();
        $notif->setOrderMain($order);

        if(strlen($data['place']) === 0){

            $notif->setCode(Notification::ORDER_EXTERNAL_WITHOUT_ACCOUNT);

            $requiredLocation = ['country_id','city', 'street', 'street_detail'];
            foreach ($requiredLocation as $key=>$el)
            {
                if(!array_key_exists($el,$data['location']))
                {
                    return ['message' =>'location '.$el. ' field is required', 'error' =>true, 'data' => []];
                }

            }

            $country = $this->em->getRepository(Country::class)->findOneBy(['code'=>$data['location']['country_id']]);
            if( $country === null) return ['message' =>'country not found', 'error' =>true, 'data' => []];
            $location =  new Location();
            $location->setCountry($country);
            $location->setCity($data['location']['city']);
            $location->setStreet($data['location']['street']);
            $location->setStreetDetail($data['location']['street_detail']);

            $order->setLocation($location);
        }
        else{
            $order->setPlace($data['place']);
            $notif->setCode(Notification::ORDER_INTERNAL_WITHOUT_ACCOUNT);

        }

        $payMode = $this->em->getRepository(AcceptedPayMode::class)->findOneBy(['id'=>$data['pay_mode'],'entity'=>$entity]);
        if( $payMode === null) return ['message' =>'pay mode not found', 'error' =>true, 'data' => []];

        $order->setPayMode($payMode->getPayMode());

        $order->setCustomerName($data['customer_name']);
        $order->setCustomerPhone($data['customer_phone']);

        $notif->setCustomerName($data['customer_name']);
        $notif->setCustomerPhone($data['customer_phone']);

        $totalAmount = 0;
        foreach ($data['menus'] as $menuElt){

            if(!array_key_exists('id',$menuElt['menu'])){
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' id in menus : '];
            }
            if(!array_key_exists('quantity',$menuElt)){
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' quantity in menus : '];
            }

            $menu = $this->em->getRepository(Menu::class)->findOneBy(['entity'=>$entity,'id'=>$menuElt['menu']['id'],'isActive'=>true]);
            if($menu === null){
                return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('not found'). ' : entity menu '.$menuElt['id']];
            }
            $orderItem = new OrderItem();
            $orderItem->setQuantity((int)$menuElt['quantity']);
            $orderItem->setMenu($menu);
            $orderItem->setOrderMain($order);
            $orderItem->setAmount($orderItem->getQuantity()*$menu->getPrice());
            $order->addOrderItem($orderItem);
            $totalAmount+=$orderItem->getAmount();

        }

        $order->setAmount($totalAmount);


        $uNotif = [];
        if($entity->getTopManager() !== null){
            $uNotif[] = $entity->getTopManager();
        }
        if($entity->getOwner() !== null){
            $uNotif[] = $entity->getOwner();
        }

        if(count($entity->getManagers())>0){
            foreach ($entity->getManagers() as $en){
                $uNotif[]=$en;
            }
        }


        foreach ($uNotif as $u){
            $statut = new Status();
            $statut->setUser($u);
            $statut->setNotification($notif);
            $notif->addStatus($statut);

        }



        $this->em->persist($notif);
        $this->em->flush();


        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->singleObjectToArray($order,'order_all')];


    }

    public function makeOrderConnected(array $data):?array{

        $user = $this->getCurrentUser();

        $required = ['entity_id','menus','location','place','pay_mode'];

        foreach ($required as $el)
        {
            if(!array_key_exists($el,$data))
            {
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : ' .$el];
            }
        }

        $entity = $this->em->getRepository(Entity::class)->findOneBy(['id'=>$data['entity_id']]);
        if($entity === null) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('not found')];

        if(!in_array($entity->getType(),Entity::ENTITY_USE_MENU)){
            return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];
        }

        $order = new Order();
        $order->setEntity($entity);

        $notif = new Notification();
        $notif->setOrderMain($order);

        if(strlen($data['place']) === 0){

            $notif->setCode(Notification::ORDER_EXTERNAL_WITH_ACCOUNT);


            $requiredLocation = ['country_id','city', 'street', 'street_detail'];
            foreach ($requiredLocation as $key=>$el)
            {
                if(!array_key_exists($el,$data['location']))
                {
                    return ['message' =>'location '.$el. ' field is required', 'error' =>true, 'data' => []];
                }

            }

            $country = $this->em->getRepository(Country::class)->findOneBy(['code'=>$data['location']['country_id']]);
            if( $country === null) return ['message' =>'country not found', 'error' =>true, 'data' => []];
            $location =  new Location();
            $location->setCountry($country);
            $location->setCity($data['location']['city']);
            $location->setStreet($data['location']['street']);
            $location->setStreetDetail($data['location']['street_detail']);

            $order->setLocation($location);
        }
        else{
            $order->setPlace($data['place']);
            $notif->setCode(Notification::ORDER_INTERNAL_WITH_ACCOUNT);

        }

        $payMode = $this->em->getRepository(AcceptedPayMode::class)->findOneBy(['id'=>$data['pay_mode'],'entity'=>$entity]);
        if( $payMode === null) return ['message' =>'pay mode not found', 'error' =>true, 'data' => []];

        $order->setPayMode($payMode->getPayMode());


        $order->setClient($user);
        $notif->setUser($user);

        $totalAmount = 0;
        foreach ($data['menus'] as $menuElt){

            if(!array_key_exists('id',$menuElt['menu'])){
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' id in menus : '];
            }
            if(!array_key_exists('quantity',$menuElt)){
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' quantity in menus : '];
            }

            $menu = $this->em->getRepository(Menu::class)->findOneBy(['entity'=>$entity,'id'=>$menuElt['menu']['id'],'isActive'=>true]);
            if($menu === null){
                return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('not found'). ' : restaurant menu '.$menuElt['id']];
            }
            $orderItem = new OrderItem();
            $orderItem->setQuantity((int)$menuElt['quantity']);
            $orderItem->setMenu($menu);
            $orderItem->setOrderMain($order);
            $orderItem->setAmount($orderItem->getQuantity()*$menu->getPrice());
            $order->addOrderItem($orderItem);
            $totalAmount+=$orderItem->getAmount();

        }

        $order->setAmount($totalAmount);

        $uNotif = [];
        if($entity->getTopManager() !== null){
            $uNotif[] = $entity->getTopManager();
        }
        if($entity->getOwner() !== null){
            $uNotif[] = $entity->getOwner();
        }

        if(count($entity->getManagers())>0){
            foreach ($entity->getManagers() as $en){
                $uNotif[]=$en;
            }
        }


        foreach ($uNotif as $u){
            $statut = new Status();
            $statut->setUser($u);
            $statut->setNotification($notif);
            $notif->addStatus($statut);

        }




        $this->em->persist($notif);
        $this->em->flush();


        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->singleObjectToArray($order,'order_all')];


    }

    public function registerManager(array $data):?array{

        $cuUser = $this->getCurrentUser();
        if(!in_array($cuUser->getType(),[User::USER_ADMIN,User::USER_TOP_MANAGER])){
            return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];
        }

        $required = ['user_name','user_surname','user_email','user_phone','user_gender'];

        foreach ($required as $el)
        {
            if(!array_key_exists($el,$data))
            {
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : ' .$el];
            }

        }

        $oldUser = $this->em->getRepository(User::class)->findOneBy(['email'=>$data['user_email']]);
        if($oldUser !== null) return ['message' => $this->translator->trans('email already used'), 'error' =>true, 'data' => []];

        if(strlen($data['user_phone'])< 9) return ['message' => $this->translator->trans('phone length error'), 'error' =>true, 'data' => []];


        $user = new User();
        $user->setIsActive(true);
        $user->setIsClose(false);
        $pass = $this->generateRandomString(7);
        $user->setType(User::USER_MANAGER);
        $user->setName($data['user_name']);
        $user->setSurname($data['user_surname']);
        $user->setEmail($data['user_email']);
        $user->setPhone($data['user_phone']);
        $user->setGender($data['user_gender']);
        $user->setPassword($this->container->get('security.password_encoder')->encodePassword($user,$pass));
        $user->setToken(uniqid('',true));


        $this->em->persist($user);
        $this->em->flush();

        $this->container->get("mail_manager")->welcomeManager($user->getId(),$user->getName(),$user->getEmail(),$user->getToken(),$pass);

        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->singleObjectToArray($user,'user_all'), 'message' =>$this->translator->trans('account_created')];



    }

    public function registerAccount(array $data):?array{

        $required = ['name','surname','email','phone','gender','password','type'];

        foreach ($required as $el)
        {
            if(!array_key_exists($el,$data))
            {
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : ' .$el];
            }

        }


        $oldUser = $this->em->getRepository(User::class)->findOneBy(['email'=>$data['email']]);
        if($oldUser !== null) return ['message' => $this->translator->trans('email already used'), 'error' =>true, 'data' => []];

        if(strlen($data['phone'])< 9) return ['message' => $this->translator->trans('phone length error'), 'error' =>true, 'data' => []];
        if(strlen($data['password'])< 6) return ['message' => $this->translator->trans('password should contains at least 6 characters'), 'error' =>true, 'data' => []];


        $user = new User();
        $user->setIsActive(false);
        $user->setIsClose(false);
        $pass = $data['password'];
        switch ($data['type']){
            case User::USER_CLIENT:
                $user->setType(User::USER_CLIENT);
                break;
            case User::USER_OWNER:
                $user->setType(User::USER_OWNER);
                break;
            case User::USER_MANAGER:
                $user->setType(User::USER_MANAGER);
                break;
            default:
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('bad user type'). ' : ' .$el];
                break;
        }

        $user->setName($data['name']);
        $user->setSurname($data['surname']);
        $user->setEmail($data['email']);
        $user->setPhone($data['phone']);
        $user->setGender((int)$data['gender']);
        if($user->getGender() == 1){
            $user->setPicture('man.png');
        }
        else{
            $user->setPicture('woman.png');
        }
        $user->setPassword($this->container->get('security.password_encoder')->encodePassword($user,$pass));
        $user->setToken(uniqid('',true));

        $this->em->persist($user);
        $this->em->flush();

        $this->container->get("mail_manager")->welcome($user->getId(),$user->getName(),$user->getEmail(),$user->getToken());

        return ['error' => false, 'data' => [], 'message' =>$this->translator->trans('account_created')];


    }

    public function manageUserAccount(array $data):?array{
        $required =['id'];

        foreach ($required as $el)
        {
            if(!array_key_exists($el,$data))
            {
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : ' .$el];
            }

        }

        $cuUser = $this->getCurrentUser();
        $uType = $cuUser->getType();

        if(!in_array($uType,[User::USER_ADMIN, User::USER_TOP_MANAGER])){
            return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];
        }

        $user = $this->em->getRepository(User::class)->findOneBy(['id'=>$data['id']]);
        if($user === null) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('account not found')];
        if($user->getType() === User::USER_ADMIN) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];

        if($user->getType() === User::USER_TOP_MANAGER && $uType === User::USER_TOP_MANAGER){
            return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];
        }

        if($user !== $cuUser){
            $user->setIsClose(!$user->getIsClose());
        }

        $this->em->persist($user);
        $this->em->flush();

        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->singleObjectToArray($user,'user_all')];


    }

    public function getInRunningOrders(array $data):?array{

        $offset = 0;
        $limit = 20;
        if(array_key_exists('offset',$data)){ $offset = $data['offset'];}
        if(array_key_exists('limit',$data)){ $limit = $data['limit'];}

        if(!array_key_exists('id',$data)){
            return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : id'];
        }

        $cuUser = $this->getCurrentUser();
        $uType = $cuUser->getType();
        if(!in_array($uType,[User::USER_OWNER,User::USER_MANAGER, User::USER_TOP_MANAGER, User::USER_ADMIN])){
            return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];
        }
        if($uType === User::USER_TOP_MANAGER){

            $entity = $this->em->getRepository(Entity::class)->findOneBy(['topManager'=>$cuUser, 'id'=>$data['id']]);
        }
        elseif($uType === User::USER_MANAGER){
            $entity = $this->em->getRepository(Entity::class)->findEntityManageByUser($data['id'],$cuUser->getId());
        }
        else{
            $entity = $this->em->getRepository(Entity::class)->findOneBy(['id'=>$data['id'],'owner'=>$cuUser]);
        }

        if($uType !== User::USER_ADMIN){
            if($entity === null) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];
            $orders = $this->em->getRepository(Order::class)->findInRunningEntity($entity->getId(),$limit,$offset);
        }
        else{
            $orders = $this->em->getRepository(Order::class)->findInRunningAll($limit,$offset);
        }

        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->multipleObjectToArray($orders,'order_all')];

    }

    public function getAllOrders(array $data):?array{

        $cuUser = $this->getCurrentUser();
        $uType = $cuUser->getType();

        $offset = 0;
        $limit = 20;
        if(array_key_exists('offset',$data)){ $offset = $data['offset'];}
        if(array_key_exists('limit',$data)){ $limit = $data['limit'];}

        if(!array_key_exists('id',$data)){
            return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : id'];
        }

        if(!in_array($uType,[User::USER_OWNER,User::USER_MANAGER, User::USER_TOP_MANAGER, User::USER_ADMIN])){
            return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];
        }
        if($uType === User::USER_TOP_MANAGER){

            $entity = $this->em->getRepository(Entity::class)->findOneBy(['topManager'=>$cuUser, 'id'=>$data['id']]);
        }
        elseif($uType === User::USER_MANAGER){
            $entity = $this->em->getRepository(Entity::class)->findEntityManageByUser($data['id'],$cuUser->getId());
        }
        else{
            $entity = $this->em->getRepository(Entity::class)->findOneBy(['id'=>$data['id'],'owner'=>$cuUser]);
        }

        if($uType !== User::USER_ADMIN){
            if($entity === null) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];
            $orders = $this->em->getRepository(Order::class)->findAllEntity($entity->getId(),$limit,$offset);
        }
        else{
            $orders = $this->em->getRepository(Order::class)->findAllAdmin($limit,$offset);
        }



        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->multipleObjectToArray($orders,'order_all')];


    }

    public function getInRunningOrdersAdmin(array $data):?array{

        $offset = 0;
        $limit = 20;
        if(array_key_exists('offset',$data)){ $offset = $data['offset'];}
        if(array_key_exists('limit',$data)){ $limit = $data['limit'];}

        $cuUser = $this->getCurrentUser();
        $uType = $cuUser->getType();

        if($uType!==User::USER_ADMIN){
            return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];
        }

        $orders = $this->em->getRepository(Order::class)->findInRunningRestaurantAdmin($limit,$offset);

        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->multipleObjectToArray($orders,'order_all')];


    }

    public function getAllOrdersAdmin(array $data):?array{

        $offset = 0;
        $limit = 20;
        if(array_key_exists('offset',$data)){ $offset = $data['offset'];}
        if(array_key_exists('limit',$data)){ $limit = $data['limit'];}
        $cuUser = $this->getCurrentUser();
        $uType = $cuUser->getType();

        if($uType!==User::USER_ADMIN){
            return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];
        }

        $orders = $this->em->getRepository(Order::class)->findAllAdmin($limit,$offset);

        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->multipleObjectToArray($orders,'order_all')];


    }

    public function getPaidAndDeliveredOrders(array $data):?array{

        $offset = 0;
        $limit = 20;
        if(array_key_exists('offset',$data)){ $offset = $data['offset'];}
        if(array_key_exists('limit',$data)){ $limit = $data['limit'];}

        $cuUser = $this->getCurrentUser();
        $uType = $cuUser->getType();

        if(!in_array($uType,[User::USER_OWNER,User::USER_MANAGER, User::USER_ADMIN, User::USER_TOP_MANAGER])){
            return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];
        }

        if(!array_key_exists('id',$data)){
            return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : id'];
        }

        if(!in_array($uType,[User::USER_OWNER,User::USER_MANAGER, User::USER_TOP_MANAGER, User::USER_ADMIN])){
            return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];
        }
        if($uType === User::USER_TOP_MANAGER){

            $entity = $this->em->getRepository(Entity::class)->findOneBy(['topManager'=>$cuUser, 'id'=>$data['id']]);
        }
        elseif($uType === User::USER_MANAGER){
            $entity = $this->em->getRepository(Entity::class)->findEntityManageByUser($data['id'],$cuUser->getId());
        }
        else{
            $entity = $this->em->getRepository(Entity::class)->findOneBy(['id'=>$data['id'],'owner'=>$cuUser]);
        }

        if($uType !== User::USER_ADMIN){
            if($entity === null) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];

            $orders = $this->em->getRepository(Order::class)->findPaidEntity($entity->getId(),$limit,$offset);
        }else{
            $orders = $this->em->getRepository(Order::class)->findPaidEntityAdmin($limit,$offset);
        }



        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->multipleObjectToArray($orders,'order_all')];


    }

    public function getPaidAndDeliveredOrdersAdmin(array $data):?array{

        $offset = 0;
        $limit = 20;
        if(array_key_exists('offset',$data)){ $offset = $data['offset'];}
        if(array_key_exists('limit',$data)){ $limit = $data['limit'];}

        $cuUser = $this->getCurrentUser();
        $uType = $cuUser->getType();

        if(!in_array($uType,[User::USER_ADMIN, User::USER_TOP_MANAGER])){
            return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];
        }

        if($uType===User::USER_ADMIN){
            $orders = $this->em->getRepository(Order::class)->findPaidEntityAdmin($limit,$offset);

        }
        else{
            $orders = $this->em->getRepository(Order::class)->findPaidEntityTopManager($cuUser->getId(), $limit,$offset);
        }

        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->multipleObjectToArray($orders,'order_all')];


    }

    public function updateOrderStatus (array $data):?array{
        $required =['id','status'];

        foreach ($required as $el)
        {
            if(!array_key_exists($el,$data))
            {
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : ' .$el];
            }

        }

        $cuUser = $this->getCurrentUser();
        $uType = $cuUser->getType();


        $order = $this->em->getRepository(Order::class)->findOneBy(['id'=>$data['id']]);

        if($order === null ) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('not found'). ' order'];
        $entity = $order->getEntity();

        if(!in_array($uType,[User::USER_OWNER,User::USER_MANAGER, User::USER_TOP_MANAGER])){
            return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];
        }
        else{
            if(in_array($uType,[User::USER_OWNER,User::USER_MANAGER])){
                $test = false;
                if($entity->getOwner() === $cuUser){
                    $test = true;
                }
                if($test===false){
                    $en = $this->em->getRepository(Entity::class)->findEntityManageByUser($entity->getId(),$cuUser->getId());
                    if($en !== null){
                        $test = true;
                    }
                }
                if($test === false) {
                    return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];
                }

            }
            else{
                if($entity->getTopManager() !== $cuUser)
                {
                    return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];
                }

            }


        }


        switch ($data['status']){
            case Order::STATUS_PAID_ONLY:
                $order->setStatus(Order::STATUS_PAID_ONLY);
                break;
            case Order::STATUS_PAID_AND_DELIVERED:
                $order->setStatus(Order::STATUS_PAID_AND_DELIVERED);
                break;
            case Order::STATUS_CANCELLED:
                $order->setStatus(Order::STATUS_CANCELLED);
                break;
            default:
                return ['error'=>true,'data'=>[],'message'=>' bad status value'];
        }

        $this->em->persist($order);
        $this->em->flush();

        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->singleObjectToArray($order,'order_all')];


    }

    public function updateOpeningDay (array $data):?array{

        $required =['days'];
        $required2 =['id','start_hour_one','end_hour_one','start_hour_two','end_hour_two'];

        foreach ($required as $el)
        {
            if(!array_key_exists($el,$data))
            {
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : ' .$el];
            }

        }
        foreach ($data['days'] as $d){
            foreach ($required2 as $r){
                if(!array_key_exists($r,$d))
                {
                    return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' in data array : ' .$r];
                }
            }
        }

        $cuUser = $this->getCurrentUser();
        $uType = $cuUser->getType();

        if(!in_array($uType,[User::USER_OWNER,User::USER_MANAGER, User::USER_TOP_MANAGER, User::USER_ADMIN])){
            return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];
        }
        if($uType === User::USER_TOP_MANAGER){

            $entity = $this->em->getRepository(Entity::class)->findOneBy(['topManager'=>$cuUser, 'id'=>$data['id']]);
        }
        elseif($uType === User::USER_MANAGER){
            $entity = $this->em->getRepository(Entity::class)->findEntityManageByUser($data['id'],$cuUser->getId());
        }
        else{
            $entity = $this->em->getRepository(Entity::class)->findOneBy(['id'=>$data['id'],'owner'=>$cuUser]);
        }

        if($entity === null) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];


        $oldDays = $entity->getOpenDays();
        $days = [];
        foreach ($oldDays as $o){
            $days[$o->getDay()->getId()] = $o;

        }

        foreach ($data['days'] as $d){

            $day = $this->em->getRepository(Day::class)->findOneBy(['id'=>$d['id']]);
            if($day !== null){
                if(array_key_exists($day->getId(),$days)){
                    $days[$day->getId()]->setStartHourOne($d['start_hour_one']);
                    $days[$day->getId()]->setStartHourTwo($d['start_hour_two']);
                    $days[$day->getId()]->setEndHourOne($d['end_hour_one']);
                    $days[$day->getId()]->setEndHourTwo($d['end_hour_two']);

                    $entity->addOpenDay($days[$day->getId()]);
                }
                else{
                    $n = new OpenDay();
                    $n->setDay($day);
                    $n->setStartHourOne($d['start_hour_one']);
                    $n->setStartHourTwo($d['start_hour_two']);
                    $n->setEndHourOne($d['end_hour_one']);
                    $n->setEndHourTwo($d['end_hour_two']);
                    $entity->addOpenDay($n);
                }

            }
        }

        $this->em->persist($entity);
        $this->em->flush();

        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->singleObjectToArray($entity,'entity_all')];

    }

    public function getAllCities(array $data):?array {
        $cities = $this->em->getRepository(City::class)->findBy([],['id'=>'ASC']);
        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->multipleObjectToArray($cities,'city_all')];

    }

    public function getUserDetail(array $data):?array {
        $user = $this->getCurrentUser();
        if(array_key_exists('id',$data)){
            $user = $this->em->getRepository(User::class)->findOneBy(['id'=>$data['id']]);
            if($user === null) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('not found'). ' : user'];
        }
        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->singleObjectToArray($user,'user_all')];

    }

    public function getUserEntities(array $data):?array{
        if(!array_key_exists('type',$data))
        {
            return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : type'];
        }

        $offset = 0;
        $limit = 20;
        if(array_key_exists('offset',$data)){ $offset = $data['offset'];}
        if(array_key_exists('limit',$data)){ $limit = $data['limit'];}

        $user = $this->getCurrentUser();
        $entities = [];

        switch ($user->getType()){
            case User::USER_TOP_MANAGER:
                $entities = $this->em->getRepository(Entity::class)->findBy(['topManager'=>$user,'isActive'=>true,'type'=>$data['type']],['id'=>'DESC'],$limit,$offset);
                break;
            case User::USER_OWNER:
                $entities = $this->em->getRepository(Entity::class)->findBy(['owner'=>$user,'isActive'=>true,'type'=>$data['type']],['id'=>'DESC'],$limit,$offset);
                break;
            case User::USER_MANAGER:
                $entities = $this->em->getRepository(Entity::class)->findOnlyEntityManageByUser($user->getId(),$data['type'],$limit,$offset);
                break;
            case User::USER_ADMIN:
                $entities = $this->em->getRepository(Entity::class)->findByAdminAll($data['type'],$limit,$offset);
                //$entities = $this->em->getRepository(Entity::class)->findBy(['isActive'=>true,'isLock'=>false,'type'=>(int)$data['type']],['id'=>'DESC'],$limit,$offset);
                break;
            default:
                break;

        }



        $res = $this->container->get(MySerializer::class)->multipleObjectToArray($entities,'entity_all');

        return ['error' => false, 'data' => $res];



    }

    public function getUserOrders(array  $data):?array{
        $user = $this->getCurrentUser();

        $offset = 0;
        $limit = 10;
        if(array_key_exists('offset',$data)){ $offset = $data['offset'];}
        if(array_key_exists('limit',$data)){ $limit = $data['limit'];}

        $orders = $this->em->getRepository(Order::class)->findBy(['client'=>$user,'isActive'=>true],['date'=>'DESC']);

        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->multipleObjectToArray($orders,'order_all')];


    }

    public function registerOnlyEntity(array $data):?array{

        $user = $this->getCurrentUser();

        if(!in_array($user->getType(), [User::USER_ADMIN, User::USER_OWNER, User::USER_TOP_MANAGER])) return ['message' => $this->translator->trans('operation denied'), 'error' =>true, 'data' =>[]];

        $required = ['name','description','website', 'facebook_page','whatsapp_phone','phone1','phone2','image','entity_type','country_id','city', 'street', 'street_detail', 'base_categories'];



        foreach ($required as $el)
        {
            if(!array_key_exists($el,$data))
            {
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : ' .$el];
            }

        }

        if(strlen($data['name']) < 2) return ['message' => $this->translator->trans('entity name must content at least 2 characters'), 'error' =>true, 'data' => []];
        if(strlen($data['description']) < 100) return ['message' => $this->translator->trans('entity description must content at least 100 characters'), 'error' =>true, 'data' => []];
        if(strlen($data['phone1'])< 9) return ['message' => $this->translator->trans('phone length error'), 'error' =>true, 'data' => []];


        $country = $this->em->getRepository(Country::class)->findOneBy(['code'=>$data['country_id']]);
        if( $country === null) return ['message' =>'country not found', 'error' =>true, 'data' => []];



        $entity = new Entity();
        $entity->setType($data['entity_type']);


        if(in_array($entity->getType(),  Entity::ENTITY_USE_MENU)){
            $entity->setCanOrder(true);
        }
        $entity->setCreatedBy($user);
        $info = new GlobalInfo();

        $info->setName($data['name']);
        $info->setDescription($data['description']);
        $info->setImage($data['image']);
        $info->setFacebookPage($data['facebook_page']);
        $info->setWhatsappPhone($data['whatsapp_phone']);
        $info->setPhone1($data['phone1']);
        $info->setPhone2($data['phone2']);
        $info->setWebsite($data['website']);


        $loc = new Location();
        $loc->setCountry($country);
        $loc->setCity($data['city']);
        $loc->setStreet($data['street']);
        $loc->setStreetDetail($data['street_detail']);

        $entity->setLocation($loc);
        $entity->setGlobalInfo($info);




        if($user->getType()=== User::USER_OWNER)
        {
            $entity->setOwner($user);
        }

        if($user->getType()=== User::USER_TOP_MANAGER)
        {
            $entity->setTopManager($user);
        }


        $this->em->persist($entity);

        if(is_array($data['base_categories'])){
            foreach ($data['base_categories'] as $b){
                $base = $this->em->getRepository(BaseCategory::class)->findOneBy(['id'=>$b,'isActive'=>true]);
                $menuCat = new MenuCategory();
                $menuCat->setEntity($entity);
                $menuCat->setBaseCategory($base);
                $this->em->persist($menuCat);
            }
        }





        $this->em->flush();

        $date = new \DateTime();
        $act = new EntityActivation();
        $act->setAmount(0);
        $act->setYear($date->format('Y'));
        $act->setEntityId($entity->getId());
        $act->setStartDate($date);
        $endDate = $date->add(new \DateInterval('P3D'));
        $act->setEndDate($endDate);

        $entity->setEntityActivation($act);
        $entity->setStatus(Entity::STATUS_PAID);
        $entity->setIsLock(false);


        $payCash = $this->em->getRepository(PayMode::class)->findOneBy(['name'=>'Cash']);
        if($payCash === null) return ['message' => $this->translator->trans('no pay mode cash registered'), 'error' =>true, 'data' => []];
        $acceptPay1 = new AcceptedPayMode();
        $acceptPay1->setEntity($entity);
        $acceptPay1->setDetailOne("cash");
        $acceptPay1->setPayMode($payCash);
        $entity->addAcceptedPayMode($acceptPay1);

        $this->em->persist($entity);
        $this->em->flush();



        return ['error' => false, 'data' =>  $this->container->get(MySerializer::class)->singleObjectToArray($entity,'entity_all'), 'message' =>''];

    }

    public function updateEntity(array $data):?array{

        $user = $this->getCurrentUser();

        if( !in_array($user->getType(), [User::USER_ADMIN,User::USER_TOP_MANAGER , User::USER_OWNER])) return ['message' => $this->translator->trans('operation denied'), 'error' =>true, 'data' =>[]];

        $required = ['id','name','description','website', 'facebook_page','whatsapp_phone','phone1','phone2',
            'country_id','city', 'street', 'street_detail','base_categories'];

        foreach ($required as $el)
        {
            if(!array_key_exists($el,$data))
            {
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : ' .$el];
            }

        }

        $entity = $this->em->getRepository(Entity::class)->findOneBy(['id'=>$data['id']]);
        if( $entity === null) return ['message' =>'entity not found', 'error' =>true, 'data' => []];
        if($user->getType() === User::USER_OWNER){
            $entity = $this->em->getRepository(Entity::class)->findOneBy(['owner'=>$user,'id'=>$data['id']]);
            if($entity === null) return ['message' => $this->translator->trans('operation denied'), 'error' =>true, 'data' =>[]];
        }
        if($user->getType() === User::USER_TOP_MANAGER){
            $entity = $this->em->getRepository(Entity::class)->findOneBy(['topManager'=>$user,'id'=>$data['id']]);
            if($entity === null) return ['message' => $this->translator->trans('operation denied'), 'error' =>true, 'data' =>[]];
        }

        if(strlen($data['name']) < 2) return ['message' => $this->translator->trans('entity name must content at least 2 characters'), 'error' =>true, 'data' => []];
        if(strlen($data['description']) < 100) return ['message' => $this->translator->trans('entity description must content at least 100 characters'), 'error' =>true, 'data' => []];
        if(strlen($data['phone1'])< 9) return ['message' => $this->translator->trans('phone length error'), 'error' =>true, 'data' => []];

        $country = $this->em->getRepository(Country::class)->findOneBy(['code'=>$data['country_id']]);
        if( $country === null) return ['message' =>'country not found', 'error' =>true, 'data' => []];

        $globalInfo = $entity->getGlobalInfo();
        $globalInfo->setName($data['name']);
        $globalInfo->setDescription($data['description']);


        if(array_key_exists('image',$data) && strlen($data['image'])>0){
            $globalInfo->setImage($data['image']);
        }
        $globalInfo->setWhatsappPhone($data['whatsapp_phone'] === null?'':$data['whatsapp_phone']);
        $globalInfo->setFacebookPage($data['facebook_page'] === null ? '':$data['facebook_page']);
        $globalInfo->setPhone1($data['phone1']);
        $globalInfo->setWebsite($data['website']);
        $globalInfo->setPhone2($data['phone2'] === null?'':$data['phone2']);

        if(in_array($entity->getType(),Entity::ENTITY_USE_MENU)){
            $mc = $this->em->getRepository(MenuCategory::class)->findBy(['entity'=>$entity]);
            $bcIds = [];

            foreach ($mc as $m){
                $m->setIsActive(false);
                $bcIds[$m->getBaseCategory()->getId()] = $m;
            }

            if(is_array($data['base_categories'])){
                foreach ($data['base_categories'] as $b){
                    if(!array_key_exists((int)$b, $bcIds)){
                        $base = $this->em->getRepository(BaseCategory::class)->findOneBy(['id'=>$b,'isActive'=>true]);
                        $menuCat = new MenuCategory();
                        $menuCat->setEntity($entity);
                        $menuCat->setBaseCategory($base);
                        $this->em->persist($menuCat);
                    }
                    else{
                        $bcIds[(int)$b]->setIsActive(true);
                    }
                }
            }




            foreach ($bcIds as $el){
                $this->em->persist($el);
            }

        }




        $loc = $entity->getLocation();
        $loc->setCountry($country);
        $loc->setCity($data['city']);
        $loc->setStreet($data['street']);
        $loc->setStreetDetail($data['street_detail'] === null?'':$data['street_detail']);
        $entity->setLocation($loc);
        $this->em->persist($entity);
        $this->em->flush();


        if(in_array($entity->getType(),[Entity::ENTITY_RESTAURANT, Entity::ENTITY_LAUNCH, Entity::ENTITY_DISCO])){
            $menuCategories = $this->em->getRepository(MenuCategory::class)->findBy(['entity'=>$entity,'isActive'=>true]);
            $menus = $this->em->getRepository(Menu::class)->findBy(['entity'=>$entity,'isActive'=>true]);
            $images = $this->em->getRepository(File::class)->findBy(['entity'=>$entity,'isActive'=>true]);

            return ['error' => false,
                'data' => ['entity'=>$this->container->get(MySerializer::class)->singleObjectToArray($entity,'entity_all'),
                    'categories'=>$this->container->get(MySerializer::class)->multipleObjectToArray($menuCategories,'MC_all'),]
            ];


        }
        elseif(in_array($entity->getType(),[Entity::ENTITY_HOSTEL, Entity::ENTITY_MUSEUM, Entity::ENTITY_OFFICE])){

            $adverts = $this->em->getRepository(Advert::class)->findBy(['entity'=>$entity,'isActive'=>true],['date'=>'DESC'],10, 0);
            $images = $this->em->getRepository(File::class)->findBy(['entity'=>$entity,'isActive'=>true]);

            return ['error' => false,
                'data' => ['entity'=>$this->container->get(MySerializer::class)->singleObjectToArray($entity,'entity_all'),
                    'categories'=>[],]
            ];

        }
        else{
            // pharmacy
            $adverts = $this->em->getRepository(Advert::class)->findBy(['entity'=>$entity,'isActive'=>true],['date'=>'DESC'],10, 0);
            $products = $this->em->getRepository(Product::class)->findBy(['entity'=>$entity,'isActive'=>true],['date'=>'DESC']);

            return ['error' => false,
                'data' => ['entity'=>$this->container->get(MySerializer::class)->singleObjectToArray($entity,'entity_all'),
                    'categories'=>[],]
            ];

        }
        //return ['error' => false, 'data' =>  $this->container->get(MySerializer::class)->singleObjectToArray($entity,'entity_all'), 'message' =>''];

    }

    public function getAllUsers(array  $data):?array{
        $user = $this->getCurrentUser();

         if(!in_array($user->getType(),[User::USER_ADMIN, User::USER_TOP_MANAGER])) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];
        $offset = 0;
        $limit = 10;
        if(array_key_exists('offset',$data)){ $offset = $data['offset'];}
        if(array_key_exists('limit',$data)){ $limit = $data['limit'];}

        $users= $this->em->getRepository(User::class)->findAllUser($limit,$offset);

        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->multipleObjectToArray($users,'user_all')];


    }

    public function getManagerEntity(array $data):?array {

        if(!array_key_exists('id',$data)){
            return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : id'];
        }

        $user =$this->getCurrentUser();

        $entity = $this->em->getRepository(Entity::class)->findOneBy(['id'=>$data['id']]);

        if($entity === null) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('not found')];

        if($user->getType() !== User::USER_ADMIN){
            if($user->getType() === User::USER_TOP_MANAGER){
                if($user !== $entity->getTopManager()){
                    return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];
                }
            }
            elseif($user->getType() === User::USER_OWNER){
                if($user !== $entity->getOwner()){
                    return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];
                }
            }
            elseif($user->getType() === User::USER_MANAGER){
                $entity = $this->em->getRepository(Entity::class)->findEntityManageByUser($entity->getId(),$user->getId());
                if($entity === null){
                    return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];
                }
            }
            else{
                return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];
            }
        }

        $users = $entity->getManagers();

        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->multipleObjectToArray($users,'user_all')];
    }

    public function getOneEntityDetails(array $data):?array {
        $required = ['id'];

        foreach ($required as $el)
        {
            if(!array_key_exists($el,$data))
            {
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : ' .$el];
            }
        }

        $entity = $this->em->getRepository(Entity::class)->findOneBy(['id'=>$data['id']]);
        if($entity === null) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('not found')];



        if(in_array($entity->getType(),[Entity::ENTITY_RESTAURANT, Entity::ENTITY_LAUNCH, Entity::ENTITY_DISCO])){
            $menuCategories = $this->em->getRepository(MenuCategory::class)->findBy(['entity'=>$entity,'isActive'=>true]);
            $menus = $this->em->getRepository(Menu::class)->findBy(['entity'=>$entity,'isActive'=>true]);
            $images = $this->em->getRepository(File::class)->findBy(['entity'=>$entity,'isActive'=>true]);
            $adverts = $this->em->getRepository(Advert::class)->findBy(['entity'=>$entity,'isActive'=>true],['date'=>'DESC'],20, 0);

            return ['error' => false,
                'data' => ['entity'=>$this->container->get(MySerializer::class)->singleObjectToArray($entity,'entity_all'),
                    'categories'=>$this->container->get(MySerializer::class)->multipleObjectToArray($menuCategories,'MC_all'),
                    'menus'=>$this->container->get(MySerializer::class)->multipleObjectToArray($menus,'menu_all'),
                    'adverts'=>$this->container->get(MySerializer::class)->multipleObjectToArray($adverts,'adv_all'),
                    'products'=> [],
                    'images'=>$this->container->get(MySerializer::class)->multipleObjectToArray($images,'file_all')]
            ];


        }
        elseif(in_array($entity->getType(),[Entity::ENTITY_HOSTEL, Entity::ENTITY_MUSEUM, Entity::ENTITY_OFFICE])){

            $adverts = $this->em->getRepository(Advert::class)->findBy(['entity'=>$entity,'isActive'=>true],['date'=>'DESC'],20, 0);
            $images = $this->em->getRepository(File::class)->findBy(['entity'=>$entity,'isActive'=>true]);

            return ['error' => false,
                'data' => ['entity'=>$this->container->get(MySerializer::class)->singleObjectToArray($entity,'entity_all'),
                    'adverts'=>$this->container->get(MySerializer::class)->multipleObjectToArray($adverts,'adv_all'),
                    'categories'=>[],
                    'menus'=>[],
                    'products'=> [],
                    'images'=>$this->container->get(MySerializer::class)->multipleObjectToArray($images,'file_all')]
            ];

        }
        else{
            // pharmacy
            $adverts = $this->em->getRepository(Advert::class)->findBy(['entity'=>$entity,'isActive'=>true],['date'=>'DESC'],20, 0);
            $products = $this->em->getRepository(Product::class)->findBy(['entity'=>$entity,'isActive'=>true],['name'=>'ASC']);

            return ['error' => false,
                'data' => ['entity'=>$this->container->get(MySerializer::class)->singleObjectToArray($entity,'entity_all'),
                    'adverts'=>$this->container->get(MySerializer::class)->multipleObjectToArray($adverts,'adv_all'),
                    'categories'=>[],
                    'menus'=>[],
                    'images'=>[],
                    'products'=>$this->container->get(MySerializer::class)->multipleObjectToArray($products,'product_all')]
            ];

        }
    }

    public function getEntityCategories(array $data):?array {
        $required = ['id','entity'];

        foreach ($required as $el)
        {
            if(!array_key_exists($el,$data))
            {
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : ' .$el];
            }
        }

        $entity = $this->em->getRepository(Entity::class)->findOneBy(['id'=>$data['entity']]);
        if($entity === null) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('not found')];


        $cat = $this->em->getRepository(MenuCategory::class)->findOneBy(['id'=>$data['id']]);
        if($cat === null) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('not found'). ' : category'];

        $menus = $this->em->getRepository(Menu::class)->findMenuByCategory($entity->getId(),$cat->getId());

        return ['error' => false, 'data' =>$this->container->get(MySerializer::class)->multipleObjectToArray($menus,'menu_all')];
    }

    public function getAllBaseCategories(array $data):?array {
        $bc = $this->em->getRepository(BaseCategory::class)->findBy(['isActive'=>true],['id'=>'ASC']);
        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->multipleObjectToArray($bc,'BC_all')];

    }

    public function getAllBaseCategoriesLunch(array $data):?array {
        $bc = $this->em->getRepository(BaseCategory::class)->findBy(['isActive'=>true,'isFastFood'=>1,'isDrink'=>1],['id'=>'ASC']);
        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->multipleObjectToArray($bc,'BC_all')];

    }

    public function getAllBaseCategoriesDrink(array $data):?array {
        $bc = $this->em->getRepository(BaseCategory::class)->findBy(['isActive'=>true,'isDrink'=>1],['id'=>'ASC']);
        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->multipleObjectToArray($bc,'BC_all')];

    }

    public function getAllEntityBaseCategories(array $data):?array {

        if(!array_key_exists('id',$data)){
            return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : id'];
        }
        $mc = $this->em->getRepository(MenuCategory::class)->findBy(['isActive'=>true,'entity'=>$data['id']]);
        $bc = [];
        foreach ($mc as $m){
            $bc[] = $m->getBaseCategory();
        }

        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->multipleObjectToArray($bc,'BC_all')];

    }

    public function removeManager(array $data):?array {

        $user =$this->getCurrentUser();

        if(!array_key_exists('id',$data)){
            return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : id'];
        }
        if(!array_key_exists('entity_id',$data)){
            return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : entity_id'];
        }



        $u = $this->em->getRepository(User::class)->findOneBy(['id'=>$data['id']]);
        if($u === null) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('not found'). ' : user'];

        $entity = $this->em->getRepository(Entity::class)->findOneBy(['id'=>$data['entity_id']]);
        if($entity === null) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('not found'). ' : entity'];

        if(!$entity->getManagers()->contains($u)){
            return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];
        }



        if($user->getType() === User::USER_OWNER){
            if($entity->getOwner() !== $user){
                return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];
            }
        }
        elseif($user->getType() === User::USER_TOP_MANAGER){
            if($entity->getTopManager() !== $user){
                return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];
            }

        }
        else{
            if($user->getType() !== User::USER_ADMIN){
                return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];
            }
        }

        $entity->removeManager($u);

        $this->em->persist($entity);
        $this->em->flush();

        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->singleObjectToArray($u,'user_all')];
    }

    public function addManager(array $data):?array {

        $user =$this->getCurrentUser();


        if(!array_key_exists('email',$data)){
            return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : email'];
        }

        if(!array_key_exists('entity_id',$data)){
            return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : entity_id'];
        }


        $u = $this->em->getRepository(User::class)->findOneBy(['email'=>$data['email']]);
        if($u === null) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('not found'). ' : user'];

        $entity = $this->em->getRepository(Entity::class)->findOneBy(['id'=>$data['entity_id']]);
        if($entity === null) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('not found'). ' : entity'];

        if($entity->getManagers()->contains($u)){
            return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];
        }


        if($u->getType() === User::USER_MANAGER){
            $entity->addManager($u);
        }
        else{
            return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];
        }

        $this->em->persist($entity);
        $this->em->flush();

        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->singleObjectToArray($u,'user_all')];
    }

    public function showOneOrder(array $data):?array {

        if(!array_key_exists('id',$data)){
            return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : id'];
        }


        $order = $this->em->getRepository(Order::class)->findOneBy(['id'=>$data['id']]);

        if($order === null) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('not found'). ' : order'];


        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->singleObjectToArray($order,'order_all')];
    }

    public function getUserStatus(array $data):?array {

        $user =$this->getCurrentUser();



        $status = $this->em->getRepository(Status::class)->findBy(['user'=>$user,'isActive'=>true],['date'=>'DESC','status'=>'ASC']);

        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->multipleObjectToArray($status,'status_all')];


    }

    public function getUserLastStatus(array $data):?array {

        if(!array_key_exists('id',$data)){
            return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : id'];
        }

        $user =$this->getCurrentUser();

        $status = $this->em->getRepository(Status::class)->findLastForUSer($user->getId(),$data['id']);

        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->multipleObjectToArray($status,'status_all')];


    }

    public function markUserStatus(array $data):?array {

        $user =$this->getCurrentUser();

        $status = $this->em->getRepository(Status::class)->findBy(['user'=>$user,'isActive'=>true,'status'=>false]);

        $nb = count($status);

        foreach ($status as $st){
            $st->setStatus(true);
            $this->em->persist($st);
        }

        if($nb>0){
            $this->em->flush();
        }



        return ['error' => false, 'data' => [], 'message'=>'ok'];


    }

    public function getQrCode(array $data):?BinaryFileResponse {

        if(!array_key_exists('id',$data)){
            return new BinaryFileResponse(__DIR__.'/../../public/assets/logo.png');
        }

        $res = $this->em->getRepository(Entity::class)->findOneBy(['id'=>$data['id']]);

        if($res === null)  return new BinaryFileResponse(__DIR__.'/../../public/assets/logo.png');


        $filesystem = new Filesystem();

        if(!$filesystem->exists(__DIR__.'/../../public/qr_code')){

            try {
                $filesystem->mkdir(__DIR__.'/../../public/qr_code',0777);
            } catch (IOExceptionInterface $exception) {
                return new BinaryFileResponse(__DIR__.'/../../public/assets/logo.png');

                       }

        }


        if(!file_exists(__DIR__.'/../../public/qr_code/'.$res->getGLobalInfo()->getSlug().'.png')){


            switch($res->getType()){
                case 1:
                    $link = $this->container->getParameter('base_url').'/#/restaurant/'.$res->getId().'/'.$res->getGLobalInfo()->getSlug();
                    break;
                case 2:
                    $link = $this->container->getParameter('base_url').'/#/lunch/'.$res->getId().'/'.$res->getGLobalInfo()->getSlug();
                    break;
                case 3:
                    $link = $this->container->getParameter('base_url').'/#/snack-disco-club/'.$res->getId().'/'.$res->getGLobalInfo()->getSlug();
                    break;
                case 4:
                    $link = $this->container->getParameter('base_url').'/#/hostel/'.$res->getId().'/'.$res->getGLobalInfo()->getSlug();
                    break;
                case 5:
                    $link = $this->container->getParameter('base_url').'/#/art-culture/'.$res->getId().'/'.$res->getGLobalInfo()->getSlug();
                    break;
                case 6:
                    $link = $this->container->getParameter('base_url').'/#/office-institution/'.$res->getId().'/'.$res->getGLobalInfo()->getSlug();
                    break;
                case 7:
                    $link = $this->container->getParameter('base_url').'/#/pharmacy/'.$res->getId().'/'.$res->getGLobalInfo()->getSlug();
                    break;
                default:
                    $link = $this->container->getParameter('base_url');
                    break;
            }



            $options = new QROptions([
                'version'      => 7,
                'outputType'   => QRCode::OUTPUT_MARKUP_SVG,
                'imageBase64'  => false,
                'eccLevel'     => QRCode::ECC_L,
                'svgViewBoxSize' => 530,
                'addQuietzone' => true,
                'cssClass'     => 'my-css-class',
                'svgOpacity'   => 1.0,
                'svgDefs'      => '
		<linearGradient id="g2">
			<stop offset="0%" stop-color="#39F" />
			<stop offset="100%" stop-color="#F3F" />
		</linearGradient>
		<linearGradient id="g1">
			<stop offset="0%" stop-color="#F3F" />
			<stop offset="100%" stop-color="#39F" />
		</linearGradient>
		<style>rect{shape-rendering:crispEdges}</style>',
                'moduleValues' => [
                    // finder
                    1536 => 'url(#g1)', // dark (true)
                    6    => '#fff', // light (false)
                    // alignment
                    2560 => 'url(#g1)',
                    10   => '#fff',
                    // timing
                    3072 => 'url(#g1)',
                    12   => '#fff',
                    // format
                    3584 => 'url(#g1)',
                    14   => '#fff',
                    // version
                    4096 => 'url(#g1)',
                    16   => '#fff',
                    // data
                    1024 => 'url(#g2)',
                    4    => '#fff',
                    // darkmodule
                    512  => 'url(#g1)',
                    // separator
                    8    => '#fff',
                    // quietzone
                    18   => '#fff',
                ],
            ]);

            $qrOutputInterface = new QRImageWithText($options, (new QRCode($options))->getMatrix($link));
            $img = $qrOutputInterface->dump(__DIR__.'/../../public/qr_code/'.$res->getGLobalInfo()->getSlug().'.png', $this->container->getParameter('base_url'));

        }




        return new BinaryFileResponse(__DIR__.'/../../public/qr_code/'.$res->getGLobalInfo()->getSlug().'.png');


        //return ['error' => false, 'data' =>[], 'message'=>$name];


    }

    public function changeEntityCanOrder(array $data):?array {
        if(!array_key_exists('id',$data)){
            return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : id'];
        }

        $user =$this->getCurrentUser();

        $entity = $this->em->getRepository(Entity::class)->findOneBy(['id'=>$data['id']]);
        if($entity === null) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('not found')];

        if($user->getType() === User::USER_OWNER){
            if($user !== $entity->getOwner()){
                return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];
            }

        }

        if($user->getType() === User::USER_TOP_MANAGER){
            if($entity->getTopManager() !== $user){
                return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];
            }

        }

        if(!in_array($user->getType(),[User::USER_OWNER,User::USER_TOP_MANAGER,User::USER_ADMIN] )){
            return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];
        }

        $entity->updateCanOrder();

        $this->em->persist($entity);
        $this->em->flush();

        return ['error' => false, 'data' =>  $this->container->get(MySerializer::class)->singleObjectToArray($entity,'entity_all'), 'message' =>''];


    }

    public function manageAnnounce(array $data):?array{

        $user = $this->getCurrentUser();

        if( !in_array($user->getType(), [User::USER_ADMIN, User::USER_OWNER, User::USER_TOP_MANAGER, User::USER_MANAGER])) return ['message' => $this->translator->trans('operation denied'), 'error' =>true, 'data' =>[]];

        $required = ['id','message'];

        foreach ($required as $el)
        {
            if(!array_key_exists($el,$data))
            {
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : ' .$el];
            }

        }

        $entity = $this->em->getRepository(Entity::class)->findOneBy(['id'=>$data['id']]);
        if( $entity === null) return ['message' =>'entity not found', 'error' =>true, 'data' => []];

        if($user->getType() === User::USER_OWNER){
            if($user !== $entity->getOwner()) return ['message' => $this->translator->trans('operation denied'), 'error' =>true, 'data' =>[]];
        }

        if($user->getType() === User::USER_TOP_MANAGER){
            if($user !== $entity->getTopManager()) return ['message' => $this->translator->trans('operation denied'), 'error' =>true, 'data' =>[]];
        }

        if($user->getType() === User::USER_MANAGER){
            if(!$entity->getManagers()->contains($user)) return ['message' => $this->translator->trans('operation denied'), 'error' =>true, 'data' =>[]];
        }

        $entity->setFlashMessage($data['message']);

        $this->em->persist($entity);
        $this->em->flush();
        return ['error' => false, 'data' =>  $this->container->get(MySerializer::class)->singleObjectToArray($entity,'entity_all'), 'message' =>''];


    }

    public function registerTopManager(array $data):?array{

        $cuUser = $this->getCurrentUser();

        if($cuUser->getType() !== User::USER_ADMIN){
            return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];
        }
        $required = ['user_name','user_surname','user_email','user_phone','user_gender'];

        foreach ($required as $el)
        {
            if(!array_key_exists($el,$data))
            {
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : ' .$el];
            }

        }

        $oldUser = $this->em->getRepository(User::class)->findOneBy(['email'=>$data['user_email']]);
        if($oldUser !== null) return ['message' => $this->translator->trans('email already used'), 'error' =>true, 'data' => []];

        if(strlen($data['user_phone'])< 9) return ['message' => $this->translator->trans('phone length error'), 'error' =>true, 'data' => []];


        $user = new User();
        $user->setIsActive(true);
        $user->setIsClose(false);
        $pass = $this->generateRandomString(7);
        $user->setType(User::USER_TOP_MANAGER);
        $user->setName($data['user_name']);
        $user->setSurname($data['user_surname']);
        $user->setEmail($data['user_email']);
        $user->setPhone($data['user_phone']);
        $user->setGender($data['user_gender']);
        $user->setPassword($this->container->get('security.password_encoder')->encodePassword($user,$pass));
        $user->setToken(uniqid('',true));

        $this->em->persist($user);
        $this->em->flush();

        $this->container->get("mail_manager")->welcomeManager($user->getId(),$user->getName(),$user->getEmail(),$user->getToken(),$pass);

        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->singleObjectToArray($user,'user_all'), 'message' =>$this->translator->trans('account_created')];


    }

    public function getTopManagerEntity(array $data):?array {


        $user =$this->getCurrentUser();

        if($user->getType() !== User::USER_TOP_MANAGER){
            return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];
        }

        $entities = $this->em->getRepository(Entity::class)->findBy(['isActive'=>true,'topManager'=>$user]);


        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->multipleObjectToArray($entities,'entity_all')];
    }

    public function removeTopManager(array $data):?array {
        $user =$this->getCurrentUser();

        if($user->getType() !== User::USER_ADMIN){
            return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];
        }

        $required = ['id','entity_id'];

        foreach ($required as $el)
        {
            if(!array_key_exists($el,$data))
            {
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : ' .$el];
            }

        }

        $u = $this->em->getRepository(User::class)->findOneBy(['id'=>$data['id']]);
        if($u === null) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('not found'). ' : user'];
        if($u->getType() !== User::USER_TOP_MANAGER){
            return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];
        }

        $entity = $this->em->getRepository(Entity::class)->findOneBy(['id'=>$data['entity_id']]);
        if($entity === null) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('not found')];

        if($entity->getTopManager() !== $u)  return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];

        $entity->setTopManager(null);

        $this->em->persist($entity);
        $this->em->flush();

        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->singleObjectToArray($u,'user_all')];
    }

    public function addTopManager(array $data):?array {

        $user =$this->getCurrentUser();

        if($user->getType() !== User::USER_ADMIN){
            return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];
        }

        $required = ['email','entity_id'];

        foreach ($required as $el)
        {
            if(!array_key_exists($el,$data))
            {
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : ' .$el];
            }

        }


        $u = $this->em->getRepository(User::class)->findOneBy(['email'=>$data['email']]);

        if($u === null) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('not found'). ' : user'];

        if($u->getType() !== User::USER_TOP_MANAGER){
            return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];
        }

        $entity = $this->em->getRepository(Entity::class)->findOneBy(['id'=>$data['entity_id']]);
        if($entity === null) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('not found')];

        if($entity->getTopManager() === $u)  return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];

        $entity->setTopManager($u);


        $this->em->persist($entity);
        $this->em->flush();

        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->singleObjectToArray($u,'user_all')];
    }

    // gestion des annonces
    public function addAdvert(array $data):?array{

        $user =$this->getCurrentUser();

        if(!in_array($user->getType(),[User::USER_TOP_MANAGER,User::USER_ADMIN,User::USER_MANAGER, User::USER_OWNER])){
            return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('operation denied')];
        }


        $required = ['entity_id','title','content','file'];
        foreach ($required as $el)
        {
            if(!array_key_exists($el,$data))
            {
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : ' .$el];
            }

        }

        if(strlen($data['title']) < 2) return ['message' => $this->translator->trans('title should not be empty'), 'error' =>true, 'data' => []];

        $entity = $this->em->getRepository(Entity::class)->findOneBy(['id'=>$data['entity_id']]);
        if($entity === null) return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('not found')];

        if($user->getType() === User::USER_OWNER){
            if($entity->getOwner() !== $user) return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('operation denied')];
        }

        if($user->getType() === User::USER_TOP_MANAGER){
            if($entity->getTopManager() !== $user) return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('operation denied')];
        }

        if($user->getType() === User::USER_MANAGER){

            if(!$entity->getManagers()->contains($user) ) return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('operation denied')];
        }

        $advert = new Advert();
        $advert->setTitle($data['title']);
        $advert->setContent($data['content']);
        $advert->setEntity($entity);

        if(is_array($data['file'])){
            $advert->setFile($data['file']);
        }
        else{
            $advert->setFile(null);
        }

        $this->em->persist($advert);
        $this->em->flush();

        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->singleObjectToArray($advert,'adv_all')];

    }

    public function editAdvert(array $data):?array{

        $user =$this->getCurrentUser();

        if(!in_array($user->getType(),[User::USER_TOP_MANAGER,User::USER_ADMIN,User::USER_MANAGER, User::USER_OWNER])){
            return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('operation denied')];
        }


        $required = ['id','title','content','file'];
        foreach ($required as $el)
        {
            if(!array_key_exists($el,$data))
            {
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : ' .$el];
            }

        }

        if(strlen($data['title']) < 2) return ['message' => $this->translator->trans('title should not be empty'), 'error' =>true, 'data' => []];

        $advert = $this->em->getRepository(Advert::class)->findOneBy(['id'=>$data['id']]);
        if($advert === null) return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('not found')];

        $entity = $advert->getEntity();

        if($user->getType() === User::USER_OWNER){
            if($entity->getOwner() !== $user) return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('operation denied')];
        }

        if($user->getType() === User::USER_TOP_MANAGER){
            if($entity->getTopManager() !== $user) return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('operation denied')];
        }

        if($user->getType() === User::USER_MANAGER){

            if(!$entity->getManagers()->contains($user) ) return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('operation denied')];
        }


        $advert->setTitle($data['title']);
        $advert->setContent($data['content']);
        $advert->setEntity($entity);

        if(is_array($data['file'])){
            $advert->setFile($data['file']);
        }
        else{
            $advert->setFile(null);
        }


        $this->em->persist($advert);
        $this->em->flush();

        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->singleObjectToArray($advert,'adv_all')];

    }

    public function deleteAdvert(array $data):?array{

        $user =$this->getCurrentUser();

        if(!in_array($user->getType(),[User::USER_TOP_MANAGER,User::USER_ADMIN,User::USER_MANAGER, User::USER_OWNER])){
            return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('operation denied')];
        }


        $required = ['id'];
        foreach ($required as $el)
        {
            if(!array_key_exists($el,$data))
            {
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : ' .$el];
            }

        }

        $advert = $this->em->getRepository(Advert::class)->findOneBy(['id'=>$data['id']]);
        if($advert === null) return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('not found')];

        $entity = $advert->getEntity();

        if($user->getType() === User::USER_OWNER){
            if($entity->getOwner() !== $user) return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('operation denied')];
        }

        if($user->getType() === User::USER_TOP_MANAGER){
            if($entity->getTopManager() !== $user) return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('operation denied')];
        }

        if($user->getType() === User::USER_MANAGER){

            if(!$entity->getManagers()->contains($user) ) return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('operation denied')];
        }


        $advert->setIsActive(false);
        $advert->setStatus(false);


        $this->em->persist($advert);
        $this->em->flush();

        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->singleObjectToArray($advert,'adv_all')];

    }

    public function getEntityAdvert(array $data):?array{


        $required = ['id'];
        foreach ($required as $el)
        {
            if(!array_key_exists($el,$data))
            {
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : ' .$el];
            }

        }

        $offset = 0;
        $limit = 20;
        if(array_key_exists('offset',$data)){ $offset = $data['offset'];}
        if(array_key_exists('limit',$data)){ $limit = $data['limit'];}

        $adverts = $this->em->getRepository(Advert::class)->findBy(['entity'=>$data['id']],['date'=>'DESC'],$limit,$offset);

        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->multipleObjectToArray($adverts,'adv_all')];

    }

    public function showAdvert(array $data):?array{


        $required = ['slug'];
        foreach ($required as $el)
        {
            if(!array_key_exists($el,$data))
            {
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : ' .$el];
            }

        }

        $advert = $this->em->getRepository(Advert::class)->findOneBy(['slug'=>$data['slug']]);
        if($advert === null) return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('not found')];

        $entity = $advert->getEntity();


        return ['error' => false, 'data' => ['advert'=>$this->container->get(MySerializer::class)->singleObjectToArray($advert,'adv_all'),
            'entity'=>$this->container->get(MySerializer::class)->singleObjectToArray($entity,'entity_all')]];

    }


    // gestion des produits
    public function addProduct(array $data):?array{

        $user =$this->getCurrentUser();

        if(!in_array($user->getType(),[User::USER_TOP_MANAGER,User::USER_ADMIN,User::USER_MANAGER, User::USER_OWNER])){
            return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('operation denied')];
        }


        $required = ['entity_id','name','description','image','availability','price'];
        foreach ($required as $el)
        {
            if(!array_key_exists($el,$data))
            {
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : ' .$el];
            }

        }

        if(strlen($data['name']) < 2) return ['message' => $this->translator->trans('name should not be empty'), 'error' =>true, 'data' => []];

        $entity = $this->em->getRepository(Entity::class)->findOneBy(['id'=>$data['entity_id']]);
        if($entity === null) return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('not found')];

        if(in_array($entity->getType(),Entity::ENTITY_USE_MENU)) return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('operation denied')];

        if($user->getType() === User::USER_OWNER){
            if($entity->getOwner() !== $user) return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('operation denied')];
        }

        if($user->getType() === User::USER_TOP_MANAGER){
            if($entity->getTopManager() !== $user) return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('operation denied')];
        }

        if($user->getType() === User::USER_MANAGER){

            if(!$entity->getManagers()->contains($user) ) return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('operation denied')];
        }

        $product = new Product();
        $product->setName($data['name']);
        $product->setDescription($data['description']);
        $product->setPrice($data['price']);
        $product->setEntity($entity);
        $product->setIsAvailable((bool)$data['availability']);

        if($data['image'] !== null){
            $product->setImage($data['image']);
        }

        $this->em->persist($product);
        $this->em->flush();

        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->singleObjectToArray($product,'product_all')];

    }

    public function editProduct(array $data):?array{

        $user =$this->getCurrentUser();

        if(!in_array($user->getType(),[User::USER_TOP_MANAGER,User::USER_ADMIN,User::USER_MANAGER, User::USER_OWNER])){
            return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('operation denied')];
        }


        $required = ['id','name','description','image','availability','price'];
        foreach ($required as $el)
        {
            if(!array_key_exists($el,$data))
            {
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : ' .$el];
            }

        }

        if(strlen($data['name']) < 2) return ['message' => $this->translator->trans('name should not be empty'), 'error' =>true, 'data' => []];

        $product = $this->em->getRepository(Product::class)->findOneBy(['id'=>$data['id']]);
        if($product === null) return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('not found')];

        $entity = $product->getEntity();

        if(in_array($entity->getType(),Entity::ENTITY_USE_MENU)) return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('operation denied')];

        if($user->getType() === User::USER_OWNER){
            if($entity->getOwner() !== $user) return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('operation denied')];
        }

        if($user->getType() === User::USER_TOP_MANAGER){
            if($entity->getTopManager() !== $user) return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('operation denied')];
        }

        if($user->getType() === User::USER_MANAGER){

            if(!$entity->getManagers()->contains($user) ) return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('operation denied')];
        }


        $product->setName($data['name']);
        $product->setDescription($data['description']);
        $product->setPrice($data['price']);
        $product->setEntity($entity);
        $product->setIsAvailable((bool)$data['availability']);



        if($data['image'] !== null && $data['image'] !== '' ){
            $product->setImage($data['image']);
        }

        $this->em->persist($product);
        $this->em->flush();

        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->singleObjectToArray($product,'product_all')];

    }

    public function deleteProduct(array $data):?array{

        $user =$this->getCurrentUser();

        if(!in_array($user->getType(),[User::USER_TOP_MANAGER,User::USER_ADMIN,User::USER_MANAGER, User::USER_OWNER])){
            return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('operation denied')];
        }


        $required = ['id'];
        foreach ($required as $el)
        {
            if(!array_key_exists($el,$data))
            {
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : ' .$el];
            }

        }


        $product = $this->em->getRepository(Product::class)->findOneBy(['id'=>$data['id']]);
        if($product === null) return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('not found')];

        $entity = $product->getEntity();

        if(in_array($entity->getType(),Entity::ENTITY_USE_MENU)) return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('operation denied')];

        if($user->getType() === User::USER_OWNER){
            if($entity->getOwner() !== $user) return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('operation denied')];
        }

        if($user->getType() === User::USER_TOP_MANAGER){
            if($entity->getTopManager() !== $user) return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('operation denied')];
        }

        if($user->getType() === User::USER_MANAGER){

            if(!$entity->getManagers()->contains($user) ) return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('operation denied')];
        }


        $product->setIsAvailable(false);
        $product->setIsActive(false);

        $this->em->persist($product);
        $this->em->flush();

        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->singleObjectToArray($product,'product_all')];

    }

    public function getEntityProduct(array $data):?array{


        $required = ['id'];
        foreach ($required as $el)
        {
            if(!array_key_exists($el,$data))
            {
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : ' .$el];
            }

        }

        $offset = 0;
        $limit = 10;
        if(array_key_exists('offset',$data)){ $offset = $data['offset'];}
        if(array_key_exists('limit',$data)){ $limit = $data['limit'];}

        $products = $this->em->getRepository(Product::class)->findBy(['entity'=>$data['id']],['date'=>'DESC'],$limit,$offset);

        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->multipleObjectToArray($products,'product_all')];

    }

    public function searchProduct(array $data):?array{

        $required = ['entity_id','city','name','type'];
        foreach ($required as $el)
        {
            if(!array_key_exists($el,$data))
            {
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : ' .$el];
            }

        }

        $entity = $data['entity_id'];
        $city = $data['city'];
        $name = $data['name'];
        $type = $data['type'];
        $offset = 0;
        $limit = 10;
        if(array_key_exists('offset',$data)){ $offset = $data['offset'];}
        if(array_key_exists('limit',$data)){ $limit = $data['limit'];}

        $products = $this->em->getRepository(Product::class)->search($entity,$type,$city,$name,$limit,$offset);
        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->multipleObjectToArray($products,'product_all')];

    }

    public function searchEntity(array $data):?array{


        $required = ['type','city','name'];
        foreach ($required as $el)
        {
            if(!array_key_exists($el,$data))
            {
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : ' .$el];
            }

        }

        $type = $data['type'];
        $city = $data['city'];
        $name = $data['name'];

        $offset = 0;
        $limit = 10;
        if(array_key_exists('offset',$data)){ $offset = $data['offset'];}
        if(array_key_exists('limit',$data)){ $limit = $data['limit'];}

        $entities = $this->em->getRepository(Entity::class)->searchEntities($type,$city,$name,$limit,$offset);

        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->multipleObjectToArray($entities,'entity_all')];

    }

    public function changeUserStatus(array $data){
        $user =$this->getCurrentUser();

        if($user->getType() !== User::USER_ADMIN){
            return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];
        }

        $required = ['id'];

        foreach ($required as $el)
        {
            if(!array_key_exists($el,$data))
            {
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : ' .$el];
            }

        }

        $u = $this->em->getRepository(User::class)->findOneBy(['id'=>$data['id']]);
        if($u === null) return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('not found')];

        if($u === $user) return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('operation denied')];

        $u->setIsClose(!$u->getIsClose());
        $this->em->persist($u);
        $this->em->flush();

        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->singleObjectToArray($u,'user_all')];


    }

    // pay mode

    public function addAcceptedPayMode(array $data):?array{

        $user =$this->getCurrentUser();

        if(!in_array($user->getType(),[User::USER_TOP_MANAGER,User::USER_ADMIN,User::USER_MANAGER, User::USER_OWNER])){
            return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('operation denied')];
        }


        $required = ['entity_id','pay_mode','detail_one','detail_two'];
        foreach ($required as $el)
        {
            if(!array_key_exists($el,$data))
            {
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : ' .$el];
            }

        }

        $entity = $this->em->getRepository(Entity::class)->findOneBy(['id'=>$data['entity_id']]);
        if($entity === null) return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('not found')];

        $payMode = $this->em->getRepository(PayMode::class)->findOneBy(['id'=>$data['pay_mode']]);
        if($payMode === null) return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('not found')];

        if(!in_array($entity->getType(),Entity::ENTITY_USE_MENU)) return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('operation denied')];

        if($user->getType() === User::USER_OWNER){
            if($entity->getOwner() !== $user) return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('operation denied')];
        }

        if($user->getType() === User::USER_TOP_MANAGER){
            if($entity->getTopManager() !== $user) return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('operation denied')];
        }

        if($user->getType() === User::USER_MANAGER){

            if(!$entity->getManagers()->contains($user) ) return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('operation denied')];
        }

        $old = $this->em->getRepository(AcceptedPayMode::class)->findOneBy(['entity'=>$entity,'payMode'=>$payMode]);

        if($old === null){
            $acp = new AcceptedPayMode();
            $acp->setEntity($entity);
            $acp->setPayMode($payMode);
            $acp->setDetailOne($data['detail_one']);
            $acp->setDetailTwo($data['detail_two']);
        }
        else{
            if($old->getIsActive()){
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('already used')];
            }
           $acp = $old;
            $acp->setDetailOne($data['detail_one']);
            $acp->setDetailTwo($data['detail_two']);

        }

        $this->em->persist($acp);
        $this->em->flush();

        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->singleObjectToArray($acp,'APM_all')];

    }

    public function editAcceptedPayMode(array $data):?array{

        $user =$this->getCurrentUser();

        if(!in_array($user->getType(),[User::USER_TOP_MANAGER,User::USER_ADMIN,User::USER_MANAGER, User::USER_OWNER])){
            return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('operation denied')];
        }


        $required = ['id','detail_one','detail_two'];
        foreach ($required as $el)
        {
            if(!array_key_exists($el,$data))
            {
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : ' .$el];
            }

        }

        $acp = $this->em->getRepository(AcceptedPayMode::class)->findOneBy(['id'=>$data['id']]);
        if($acp === null) return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('not found')];

        $entity = $acp->getEntity();
        if($entity === null) return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('not found')];

        if(!in_array($entity->getType(),Entity::ENTITY_USE_MENU)) return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('operation denied')];

        if($user->getType() === User::USER_OWNER){
            if($entity->getOwner() !== $user) return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('operation denied')];
        }

        if($user->getType() === User::USER_TOP_MANAGER){
            if($entity->getTopManager() !== $user) return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('operation denied')];
        }

        if($user->getType() === User::USER_MANAGER){

            if(!$entity->getManagers()->contains($user) ) return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('operation denied')];
        }

        $acp->setDetailOne($data['detail_one']);
        $acp->setDetailTwo($data['detail_two']);

        $this->em->persist($acp);
        $this->em->flush();

        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->singleObjectToArray($acp,'APM_all')];

    }

    public function deleteAcceptedPayMode(array $data):?array{

        $user =$this->getCurrentUser();

        if(!in_array($user->getType(),[User::USER_TOP_MANAGER,User::USER_ADMIN,User::USER_MANAGER, User::USER_OWNER])){
            return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('operation denied')];
        }


        $required = ['id'];
        foreach ($required as $el)
        {
            if(!array_key_exists($el,$data))
            {
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : ' .$el];
            }

        }

        $acp = $this->em->getRepository(AcceptedPayMode::class)->findOneBy(['id'=>$data['id']]);
        if($acp === null) return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('not found')];

        $entity = $acp->getEntity();
        if($entity === null) return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('not found')];

        if(!in_array($entity->getType(),Entity::ENTITY_USE_MENU)) return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('operation denied')];

        if($user->getType() === User::USER_OWNER){
            if($entity->getOwner() !== $user) return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('operation denied')];
        }

        if($user->getType() === User::USER_TOP_MANAGER){
            if($entity->getTopManager() !== $user) return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('operation denied')];
        }

        if($user->getType() === User::USER_MANAGER){

            if(!$entity->getManagers()->contains($user) ) return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('operation denied')];
        }


        $this->em->remove($acp);
        $this->em->flush();

        return ['error' => false, 'data' => []];

    }

    public function getAllPayMode(array $data){
        $pay = $this->em->getRepository(PayMode::class)->findBy(['isActive'=>true]);

        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->multipleObjectToArray($pay,'APM_all')];
    }

    public function getQrCodeAdvert(array $data):?BinaryFileResponse {

        if(!array_key_exists('slug',$data)){
            return new BinaryFileResponse(__DIR__.'/../../public/assets/logo.png');
        }

        $res = $this->em->getRepository(Advert::class)->findOneBy(['slug'=>$data['slug']]);

        if($res === null)  return new BinaryFileResponse(__DIR__.'/../../public/assets/logo.png');


        $filesystem = new Filesystem();

        if(!$filesystem->exists(__DIR__.'/../../public/qr_code')){

            try {
                $filesystem->mkdir(__DIR__.'/../../public/qr_code',0777);
            } catch (IOExceptionInterface $exception) {
                return new BinaryFileResponse(__DIR__.'/../../public/assets/logo.png');

            }

        }


        if(!file_exists(__DIR__.'/../../public/qr_code/advert_'.$res->getSlug().'.png')){

            $link = $this->container->getParameter('base_url').'/#/advert/'.$res->getSlug();

            $options = new QROptions([
                'version'      => 7,
                'outputType'   => QRCode::OUTPUT_MARKUP_SVG,
                'imageBase64'  => false,
                'eccLevel'     => QRCode::ECC_L,
                'svgViewBoxSize' => 530,
                'addQuietzone' => true,
                'cssClass'     => 'my-css-class',
                'svgOpacity'   => 1.0,
                'svgDefs'      => '
		<linearGradient id="g2">
			<stop offset="0%" stop-color="#39F" />
			<stop offset="100%" stop-color="#F3F" />
		</linearGradient>
		<linearGradient id="g1">
			<stop offset="0%" stop-color="#F3F" />
			<stop offset="100%" stop-color="#39F" />
		</linearGradient>
		<style>rect{shape-rendering:crispEdges}</style>',
                'moduleValues' => [
                    // finder
                    1536 => 'url(#g1)', // dark (true)
                    6    => '#fff', // light (false)
                    // alignment
                    2560 => 'url(#g1)',
                    10   => '#fff',
                    // timing
                    3072 => 'url(#g1)',
                    12   => '#fff',
                    // format
                    3584 => 'url(#g1)',
                    14   => '#fff',
                    // version
                    4096 => 'url(#g1)',
                    16   => '#fff',
                    // data
                    1024 => 'url(#g2)',
                    4    => '#fff',
                    // darkmodule
                    512  => 'url(#g1)',
                    // separator
                    8    => '#fff',
                    // quietzone
                    18   => '#fff',
                ],
            ]);

            $qrOutputInterface = new QRImageWithText($options, (new QRCode($options))->getMatrix($link));
            $img = $qrOutputInterface->dump(__DIR__.'/../../public/qr_code/advert_'.$res->getSlug().'.png', $this->container->getParameter('base_url'));

        }




        return new BinaryFileResponse(__DIR__.'/../../public/qr_code/advert_'.$res->getSlug().'.png');


        //return ['error' => false, 'data' =>[], 'message'=>$name];


    }

    public function searchAdvert(array $data):?array{

        $required = ['entity_id','city','name','type'];
        foreach ($required as $el)
        {
            if(!array_key_exists($el,$data))
            {
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : ' .$el];
            }

        }

        $entity = $data['entity_id'];
        $city = $data['city'];
        $name = $data['name'];
        $type = $data['type'];
        $offset = 0;
        $limit = 10;
        if(array_key_exists('offset',$data)){ $offset = $data['offset'];}
        if(array_key_exists('limit',$data)){ $limit = $data['limit'];}

        $products = $this->em->getRepository(Advert::class)->search($entity,$type,$city,$name,$limit,$offset);
        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->multipleObjectToArray($products,'adv_all')];

    }


    public function addNightPharmacy(array $data):?array{

        $user =$this->getCurrentUser();

        if(!in_array($user->getType(),[User::USER_TOP_MANAGER,User::USER_ADMIN])){
            return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('operation denied')];
        }


        $required = ['town'];
        foreach ($required as $el)
        {
            if(!array_key_exists($el,$data))
            {
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : ' .$el];
            }

        }

        if(strlen($data['town']) < 2) return ['message' => $this->translator->trans('name should not be empty'), 'error' =>true, 'data' => []];

        $entity = $this->em->getRepository(NightPharmacy::class)->findOneBy(['name'=>$data['town']]);
        if($entity !== null) return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('already used')];


        $p = new NightPharmacy();
        $p->setName($data['town']);
        $p->setImage(null);


        $this->em->persist($p);
        $this->em->flush();

        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->singleObjectToArray($p,'phar_all')];

    }

    public function updateNightPharmacy(array $data):?array{

        $user =$this->getCurrentUser();

        if(!in_array($user->getType(),[User::USER_TOP_MANAGER,User::USER_ADMIN])){
            return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('operation denied')];
        }


        $required = ['id','content','write_date'];
        foreach ($required as $el)
        {
            if(!array_key_exists($el,$data))
            {
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : ' .$el];
            }

        }


        $p = $this->em->getRepository(NightPharmacy::class)->findOneBy(['id'=>$data['id']]);
        if($p === null) return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('not found')];



        $p->setContent($data['content']);

        try{

            $p->setWriteDate(\DateTime::createFromFormat('Y-m-d',$data["write_date"]));

        }
        catch(\Exception $e){
            return ['error' => true, 'data' => [], 'message' =>$e->getMessage()];
        }







        $this->em->persist($p);
        $this->em->flush();

        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->singleObjectToArray($p,'phar_all')];

    }

    public function deleteNightPharmacy(array $data):?array{

        $user =$this->getCurrentUser();

        if(!in_array($user->getType(),[User::USER_TOP_MANAGER,User::USER_ADMIN])){
            return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('operation denied')];
        }


        $required = ['id'];
        foreach ($required as $el)
        {
            if(!array_key_exists($el,$data))
            {
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : ' .$el];
            }

        }


        $p = $this->em->getRepository(NightPharmacy::class)->findOneBy(['id'=>$data['id']]);
        if($p === null) return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('not found')];




        $this->em->remove($p);
        $this->em->flush();

        return ['error' => false, 'data' => [], 'message'=>''];

    }

    public function listNightPharmacy(array $data):?array{

        $p = $this->em->getRepository(NightPharmacy::class)->findBy(['isActive'=>true],['name'=>'ASC']);

        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->multipleObjectToArray($p,'phar_all')];

    }

    public function getQrCodePharmacy(array $data):?BinaryFileResponse {

        $filesystem = new Filesystem();


        if(!$filesystem->exists(__DIR__.'/../../public/qr_code')){

            try {
                $filesystem->mkdir(__DIR__.'/../../public/qr_code',0777);
            } catch (IOExceptionInterface $exception) {
                return new BinaryFileResponse(__DIR__.'/../../public/assets/logo.png');

            }

        }


        if(!file_exists(__DIR__.'/../../public/qr_code/pharmacy_code.png')){

            $link = $this->container->getParameter('base_url').'/#/all-night/drugstore';

            $options = new QROptions([
                'version'      => 7,
                'outputType'   => QRCode::OUTPUT_MARKUP_SVG,
                'imageBase64'  => false,
                'eccLevel'     => QRCode::ECC_L,
                'svgViewBoxSize' => 530,
                'addQuietzone' => true,
                'cssClass'     => 'my-css-class',
                'svgOpacity'   => 1.0,
                'svgDefs'      => '
		<linearGradient id="g2">
			<stop offset="0%" stop-color="#39F" />
			<stop offset="100%" stop-color="#F3F" />
		</linearGradient>
		<linearGradient id="g1">
			<stop offset="0%" stop-color="#F3F" />
			<stop offset="100%" stop-color="#39F" />
		</linearGradient>
		<style>rect{shape-rendering:crispEdges}</style>',
                'moduleValues' => [
                    // finder
                    1536 => 'url(#g1)', // dark (true)
                    6    => '#fff', // light (false)
                    // alignment
                    2560 => 'url(#g1)',
                    10   => '#fff',
                    // timing
                    3072 => 'url(#g1)',
                    12   => '#fff',
                    // format
                    3584 => 'url(#g1)',
                    14   => '#fff',
                    // version
                    4096 => 'url(#g1)',
                    16   => '#fff',
                    // data
                    1024 => 'url(#g2)',
                    4    => '#fff',
                    // darkmodule
                    512  => 'url(#g1)',
                    // separator
                    8    => '#fff',
                    // quietzone
                    18   => '#fff',
                ],
            ]);

            $qrOutputInterface = new QRImageWithText($options, (new QRCode($options))->getMatrix($link));
            $img = $qrOutputInterface->dump(__DIR__.'/../../public/qr_code/pharmacy_code.png', $this->container->getParameter('base_url'));

        }




        return new BinaryFileResponse(__DIR__.'/../../public/qr_code/pharmacy_code.png');
        //return ['error' => false, 'data' =>[], 'message'=>$name];


    }

    public function getQrCodeCocan(array $data):?BinaryFileResponse {

        $filesystem = new Filesystem();


        if(!$filesystem->exists(__DIR__.'/../../public/qr_code')){

            try {
                $filesystem->mkdir(__DIR__.'/../../public/qr_code',0777);
            } catch (IOExceptionInterface $exception) {
                return new BinaryFileResponse(__DIR__.'/../../public/assets/logo.png');

            }

        }


        if(!file_exists(__DIR__.'/../../public/qr_code/cocan_code.png')){

            $link = $this->container->getParameter('base_url').'/#/cocan';

            $options = new QROptions([
                'version'      => 7,
                'outputType'   => QRCode::OUTPUT_MARKUP_SVG,
                'imageBase64'  => false,
                'eccLevel'     => QRCode::ECC_L,
                'svgViewBoxSize' => 530,
                'addQuietzone' => true,
                'cssClass'     => 'my-css-class',
                'svgOpacity'   => 1.0,
                'svgDefs'      => '
		<linearGradient id="g2">
			<stop offset="0%" stop-color="#39F" />
			<stop offset="100%" stop-color="#F3F" />
		</linearGradient>
		<linearGradient id="g1">
			<stop offset="0%" stop-color="#F3F" />
			<stop offset="100%" stop-color="#39F" />
		</linearGradient>
		<style>rect{shape-rendering:crispEdges}</style>',
                'moduleValues' => [
                    // finder
                    1536 => 'url(#g1)', // dark (true)
                    6    => '#fff', // light (false)
                    // alignment
                    2560 => 'url(#g1)',
                    10   => '#fff',
                    // timing
                    3072 => 'url(#g1)',
                    12   => '#fff',
                    // format
                    3584 => 'url(#g1)',
                    14   => '#fff',
                    // version
                    4096 => 'url(#g1)',
                    16   => '#fff',
                    // data
                    1024 => 'url(#g2)',
                    4    => '#fff',
                    // darkmodule
                    512  => 'url(#g1)',
                    // separator
                    8    => '#fff',
                    // quietzone
                    18   => '#fff',
                ],
            ]);

            $qrOutputInterface = new QRImageWithText($options, (new QRCode($options))->getMatrix($link));
            $img = $qrOutputInterface->dump(__DIR__.'/../../public/qr_code/cocan_code.png', $this->container->getParameter('base_url'));

        }




        return new BinaryFileResponse(__DIR__.'/../../public/qr_code/cocan_code.png');
        //return ['error' => false, 'data' =>[], 'message'=>$name];


    }

    public function updateLocation(array $data):?array{
        if(!array_key_exists('id',$data)){
            return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : id'];
        }
        if(!array_key_exists('longitude',$data)){
            return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : longitude'];
        }

        if(!array_key_exists('latitude',$data)){
            return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : latitude'];
        }

        $user =$this->getCurrentUser();

        $entity = $this->em->getRepository(Entity::class)->findOneBy(['id'=>$data['id']]);
        if($entity === null) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('not found')];

        if($user->getType() === User::USER_OWNER){
            if($user !== $entity->getOwner()){
                return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];
            }

        }

        if($user->getType() === User::USER_TOP_MANAGER){
            if($entity->getTopManager() !== $user){
                return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];
            }

        }

        if(!in_array($user->getType(),[User::USER_OWNER,User::USER_TOP_MANAGER,User::USER_ADMIN] )){
            return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];
        }

        $location = $entity->getLocation();
        $location->setLongitude($data['longitude']);
        $location->setLatitude($data['latitude']);
        $entity->setLocation($location);

        $this->em->persist($entity);
        $this->em->flush();

        return ['error' => false, 'data' =>  $this->container->get(MySerializer::class)->singleObjectToArray($entity,'entity_all'), 'message' =>''];

    }

    // blog
    public function createArticle(array $data):?array{

        $user =$this->getCurrentUser();

        if(!in_array($user->getType(),[User::USER_TOP_MANAGER,User::USER_ADMIN])){
            return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('operation denied')];
        }


        $required = ['title','content','cover_image','type'];
        foreach ($required as $el)
        {
            if(!array_key_exists($el,$data))
            {
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : ' .$el];
            }

        }

        if(strlen($data['title']) < 1) return ['message' => $this->translator->trans('title should not be empty'), 'error' =>true, 'data' => []];
        if(strlen($data['content']) < 1) return ['message' => $this->translator->trans('content should not be empty'), 'error' =>true, 'data' => []];


        $article = new Article();
        $article->setTitle($data['title']);
        $article->setContent($data['content']);
        $article->setType((int)$data['type']);
        //$article->setTags($data['tags']);
        if($data['cover_image'] !== ''){
            $article->setImageCover($data['cover_image']);
        }
        $article->setUser($user);

        if($data['cover_image'] !== null){
            $article->setImageCover($data['cover_image']);
        }

        $this->em->persist($article);
        $this->em->flush();

        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->singleObjectToArray($article,'article_all')];

    }

    public function updateArticle(array $data):?array{

        $user =$this->getCurrentUser();

        if(!in_array($user->getType(),[User::USER_TOP_MANAGER,User::USER_ADMIN])){
            return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('operation denied')];
        }


        $required = ['id','title','content','cover_image','type'];
        foreach ($required as $el)
        {
            if(!array_key_exists($el,$data))
            {
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : ' .$el];
            }

        }

        if(strlen($data['title']) < 1) return ['message' => $this->translator->trans('title should not be empty'), 'error' =>true, 'data' => []];
        if(strlen($data['content']) < 1) return ['message' => $this->translator->trans('content should not be empty'), 'error' =>true, 'data' => []];


        $article = $this->em->getRepository(Article::class)->findOneBy(['id'=>$data['id']]);
        if($article === null) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('not found')];

        if($article->getUser() !== $user) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];


        $article->setTitle($data['title']);
        $article->setType((int)$data['type']);
        $article->setContent($data['content']);
        //$article->setTags($data['tags']);
        if($data['cover_image'] !== ''){
            $article->setImageCover($data['cover_image']);
        }

        if($data['cover_image'] !== null){
            $article->setImageCover($data['cover_image']);
        }

        $this->em->persist($article);
        $this->em->flush();

        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->singleObjectToArray($article,'article_all')];

    }

    public function deleteArticle(array $data):?array{

        $user =$this->getCurrentUser();

        if(!in_array($user->getType(),[User::USER_TOP_MANAGER,User::USER_ADMIN])){
            return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('operation denied')];
        }


        $required = ['id'];
        foreach ($required as $el)
        {
            if(!array_key_exists($el,$data))
            {
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : ' .$el];
            }

        }

        $article = $this->em->getRepository(Article::class)->findOneBy(['id'=>$data['id']]);
        if($article === null) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('not found')];

        if($article->getUser() !== $user) {
            if($user->getType() !== User::USER_ADMIN){
                return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];
            }
        }

        $article->setIsActive(false);

        $this->em->persist($article);
        $this->em->flush();

        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->singleObjectToArray($article,'article_all')];

    }

    public function showArticle(array $data):?array{

       // $user =$this->getCurrentUser();

        $required = ['slug'];
        foreach ($required as $el)
        {
            if(!array_key_exists($el,$data))
            {
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : ' .$el];
            }

        }

        $article = $this->em->getRepository(Article::class)->findOneBy(['slug'=>$data['slug']]);
        if($article === null) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('not found')];

        $offset = 0;
        $limit = 10;
        if(array_key_exists('offset',$data)){ $offset = $data['offset'];}
        if(array_key_exists('limit',$data)){ $limit = $data['limit'];}

        $comments = $this->em->getRepository(Comment::class)->findBy(['article'=>$article,'isActive'=>true],['id'=>'ASC'],$limit,$offset);


        $article->incNbView();

        $this->em->persist($article);
        $this->em->flush();

        return ['error' => false, 'data' => ['article'=>$this->container->get(MySerializer::class)->singleObjectToArray($article,'article_all'),'comments'=>$this->container->get(MySerializer::class)->multipleObjectToArray($comments,'comment_all')]];

    }

    public function recentArticles(array $data):?array{

       // $user =$this->getCurrentUser();
        $offset = 0;
        $limit = 10;
        if(array_key_exists('offset',$data)){ $offset = $data['offset'];}
        if(array_key_exists('limit',$data)){ $limit = $data['limit'];}

        $articles = $this->em->getRepository(Article::class)->findBy(['isActive'=>true],['id'=>'DESC'],$limit,$offset);

        return ['error' => false, 'data' =>$this->container->get(MySerializer::class)->multipleObjectToArray($articles,'article_all')];

    }

    public function userArticles(array $data):?array{

        $user =$this->getCurrentUser();
        $offset = 0;
        $limit = 10;
        if(array_key_exists('offset',$data)){ $offset = $data['offset'];}
        if(array_key_exists('limit',$data)){ $limit = $data['limit'];}

        if($user->getType() === User::USER_ADMIN){
            $articles = $this->em->getRepository(Article::class)->findBy(['isActive'=>true],['id'=>'DESC'],$limit,$offset);

        }
        else{
            $articles = $this->em->getRepository(Article::class)->findBy(['user'=>$user,'isActive'=>true],['id'=>'DESC'],$limit,$offset);

        }


        return ['error' => false, 'data' =>$this->container->get(MySerializer::class)->multipleObjectToArray($articles,'article_all')];

    }

    public function otherArticles(array $data):?array{

        $required = ['id'];
        foreach ($required as $el)
        {
            if(!array_key_exists($el,$data))
            {
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : ' .$el];
            }

        }
        $offset = 0;
        $limit = 10;
        if(array_key_exists('offset',$data)){ $offset = $data['offset'];}
        if(array_key_exists('limit',$data)){ $limit = $data['limit'];}

        $articles = $this->em->getRepository(Article::class)->findOtherThan($data['id'],$limit,$offset);

        return ['error' => false, 'data' =>$this->container->get(MySerializer::class)->multipleObjectToArray($articles,'article_all')];

    }

    public function commentArticle(array $data):?array{

        $user =$this->getCurrentUser();

        if($user === null){
            return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('operation denied')];
        }


        $required = ['message','id'];
        foreach ($required as $el)
        {
            if(!array_key_exists($el,$data))
            {
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : ' .$el];
            }

        }

        if(strlen($data['message']) < 1) return ['message' => $this->translator->trans('comment should not be empty'), 'error' =>true, 'data' => []];

        $article = $this->em->getRepository(Article::class)->findOneBy(['id'=>$data['id']]);

        if($article === null) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('not found')];

        $comment =  new Comment();
        $comment->setUser($user);
        $comment->setMessage($data['message']);
        $comment->setArticle($article);
        $article->incNbComment();
        $article->addComment($comment);

        $this->em->persist($article);
        $this->em->flush();

        return ['error' => false, 'data' =>['article'=>$this->container->get(MySerializer::class)->singleObjectToArray($article,'article_all'),'comment'=>$this->container->get(MySerializer::class)->singleObjectToArray($comment,'comment_all')]];

    }

    public function getComments(array $data):?array{

        // $user =$this->getCurrentUser();

        $required = ['id'];
        foreach ($required as $el)
        {
            if(!array_key_exists($el,$data))
            {
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : ' .$el];
            }

        }

        $article = $this->em->getRepository(Article::class)->findOneBy(['id'=>$data['id']]);
        if($article === null) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('not found')];

        $offset = 0;
        $limit = 10;
        if(array_key_exists('offset',$data)){ $offset = $data['offset'];}
        if(array_key_exists('limit',$data)){ $limit = $data['limit'];}

        $comments = $this->em->getRepository(Comment::class)->findBy(['article'=>$article,'isActive'=>true],['id'=>'ASC'],$limit,$offset);


        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->multipleObjectToArray($comments,'comment_all')];

    }

    public function deleteComment(array $data):?array{

        $user =$this->getCurrentUser();

        if(!in_array($user->getType(),[User::USER_TOP_MANAGER,User::USER_ADMIN])){
            return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('operation denied')];
        }


        $required = ['id'];
        foreach ($required as $el)
        {
            if(!array_key_exists($el,$data))
            {
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : ' .$el];
            }

        }

        $comment = $this->em->getRepository(Comment::class)->findOneBy(['id'=>$data['id']]);
        if($comment === null) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('not found')];

        if($comment->getUser() !== $user) {
            if($user->getType() !== User::USER_ADMIN){
                return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('operation denied')];
            }
        }

        $comment->setIsActive(false);

        $this->em->persist($comment);
        $this->em->flush();

        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->singleObjectToArray($comment,'comment_all')];

    }


    public function reactOnComment(array $data):?array{

        $user =$this->getCurrentUser();


        $required = ['id','action'];
        foreach ($required as $el)
        {
            if(!array_key_exists($el,$data))
            {
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : ' .$el];
            }

        }

        $comment = $this->em->getRepository(Comment::class)->findOneBy(['id'=>$data['id']]);
        if($comment === null) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('not found')];

        $reaction = $this->em->getRepository(Reaction::class)->findOneBy(['comment'=>$comment,'user'=>$user]);
        if($reaction === null){
            $reaction = new Reaction();
            $reaction->setUser($user);
            $reaction->setComment($comment);
            $reaction->setType(Reaction::REACTION_COMMENT);
            $reaction->setAction(1);
            $comment->incNbLike();
        }
        else{
            if($reaction->getAction() == 1){

                    $comment->decNblike();
                    $reaction->setAction(0);
            }
            else{
                  $reaction->setAction(1);
                  $comment->incNbLike();
            }
        }

        $comment->addReaction($reaction);
        $this->em->persist($comment);
        $this->em->flush();

        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->singleObjectToArray($comment,'comment_all')];

    }

    public function reactOnArticle(array $data):?array{

        $user =$this->getCurrentUser();


        $required = ['id','action'];
        foreach ($required as $el)
        {
            if(!array_key_exists($el,$data))
            {
                return ['error' => true, 'data' => [], 'message' =>$this->translator->trans('required_field'). ' : ' .$el];
            }

        }

        $article = $this->em->getRepository(Article::class)->findOneBy(['id'=>$data['id']]);
        if($article === null) return ['error'=>true,'data'=>[],'message'=>$this->translator->trans('not found')];

        $reaction = $this->em->getRepository(Reaction::class)->findOneBy(['article'=>$article,'user'=>$user]);
        if($reaction === null){
            $reaction = new Reaction();
            $reaction->setUser($user);
            $reaction->setArticle($article);
            $reaction->setType(Reaction::REACTION_ARTICLE);
            if((int)$data['action'] >=1 && (int)$data['action'] <=5){
                $reaction->setAction((int)$data['action']);
            }
            else{
                $reaction->setAction(5);
            }

        }
        else{
            if((int)$data['action'] >=1 && (int)$data['action'] <=5){
                $reaction->setAction((int)$data['action']);
            }
            else{
                $reaction->setAction(5);
            }
        }

        $this->em->persist($reaction);
        $this->em->flush();
        $val = $this->em->getRepository(Reaction::class)->AvgOfReaction($article->getId());

        $article->setRate($val[0]['total']);
        $this->em->persist($article);
        $this->em->flush();

        return ['error' => false, 'data' => $this->container->get(MySerializer::class)->singleObjectToArray($article,'article_all')];

    }
































}
