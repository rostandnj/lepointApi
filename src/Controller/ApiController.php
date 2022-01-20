<?php


namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\DependencyInjection\ContainerInterface;
use App\Service\RequestService;
use App\Service\ResponseService;
use App\Service\UserService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;


class ApiController extends AbstractController
{
    protected $mycontainer;
    protected $response;
    protected $request;

    public function __construct(ContainerInterface $container)
    {
        $this->mycontainer = $container;
        $this->request = $this->mycontainer->get('request_stack')->getCurrentRequest();
        $this->response = new ResponseService();
    }

    private function renderResponse(array $res){

        if(!array_key_exists('error',$res)){
            return new JsonResponse("internal error",401);
        }
        if($res['error'] === true)
        {
            return new JsonResponse($res["message"],401);
        }

        if(!array_key_exists('data',$res))
        {
            $this->response->setContent($res);
        }
        else
        {
            if(array_key_exists('message',$res)){
                if(count($res['data'])==0){
                    $this->response->setContent(json_encode($res['message']));
                }else{
                    $this->response->setContent($res['data']);
                }
            }
            else{
                $this->response->setContent($res['data']);
            }

        }



        return $this->response->getResponse();

    }

    private function renderException(\Exception $e){

        return new JsonResponse(['error'=>true,'data'=>[],'message'=>$e->getMessage()." ".basename($e->getFile())." ". $e->getLine()],401);
    }

    public function hello(Request $request):Response
    {
        //return new JsonResponse(['message'=>'Welcome to Restaurant api'],200);

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->getAllUser();
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }
    }

    public function updateToken()
    {
        $reqService = new RequestService($this->request);
        //$data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->updateToken([]);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function login(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);


        try {
            $res = $userService->login($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function api()
    {
        return new Response(sprintf('Logged in as %s', $this->getUser()->getUsername()));
    }

    public function updateProfile(Request $request, $type)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->updateProfile($type, $data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function registerEntity(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->registerEntity( $data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function resetPassword(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();
        if(!array_key_exists('email',$data)){
            $data['email'] = '';
        }

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->resetPassword( $data['email']);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function validateAccount(Request $request, $token)
    {
        /*$reqService = new RequestService($this->request);
        $data = $reqService->getPostData();*/

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->validateAccount( $token);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function activateEntity(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->activateEntity($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function extendActivationEntity(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->extendActivationEntity($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function changeEntityStatus(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->changeEntityStatus($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function getActiveEntity(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->getActiveEntity($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function getAllEntity(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->getAllEntity($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function showOneEntity(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->showOneEntity($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function manageEntityBaseCategories(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->manageEntityBaseCategories($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function addEntityMenu(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->addEntityMenu($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function editEntityMenu(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->editEntityMenu($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function deleteEntityMenu(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->deleteEntityMenu($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function updateEntityDayMenu(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->updateEntityDayMenu($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function makeOrder(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->makeOrder($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function makeOrderConnected(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->makeOrderConnected($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function registerManager(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->registerManager($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function manageUserAccount(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->manageUserAccount($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function getInRunningOrders(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->getInRunningOrders($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function getPaidAndDeliveredOrders(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->getPaidAndDeliveredOrders($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function getInRunningOrdersAdmin(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->getInRunningOrdersAdmin($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function getPaidAndDeliveredOrdersAdmin(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->getPaidAndDeliveredOrdersAdmin($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function updateOrderStatus(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->updateOrderStatus($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function updateOpeningDay(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->updateOpeningDay($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function getAllCities(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->getAllCities($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function getUserDetail(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->getUserDetail($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function registerAccount(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->registerAccount($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function getUserEntities(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->getUserEntities($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function getUserOrders(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->getUserOrders($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function registerOnlyEntity(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->registerOnlyEntity($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function getAllUsers(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->getAllUsers($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function getManagerEntity(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->getManagerEntity($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function getOneEntityDetails(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->getOneEntityDetails($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function getEntityCategories(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->getEntityCategories($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function uploadImage(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getFiles();


        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->uploadImage($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function getAllBaseCategories(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->getAllBaseCategories($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function getAllBaseCategoriesLunch(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->getAllBaseCategoriesLunch($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function getAllBaseCategoriesDrink(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->getAllBaseCategoriesDrink($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function getAllEntityBaseCategories(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->getAllEntityBaseCategories($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function updateEntity(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->updateEntity($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function removeManager(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->removeManager($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function addManager(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->addManager($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function getAllOrders(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->getAllOrders($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function getAllOrdersAdmin(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->getAllOrdersAdmin($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function getUserStatus(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->getUserStatus($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function getUserLastStatus(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->getUserLastStatus($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function markUserStatus(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->markUserStatus($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function changeEntityCanOrder(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->changeEntityCanOrder($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function manageAnnounce(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->manageAnnounce($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function getQrCode(Request $request)
    {
        $reqService = new RequestService($this->request);
        $id = $reqService->getGetStringData('id');

        $userService = $this->mycontainer->get(UserService::class);

        try {
            return $userService->getQrCode(['id'=>$id]);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function registerTopManager(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->registerTopManager($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function getTopManagerEntity(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->getTopManagerEntity($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function removeTopManager(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->removeTopManager($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function addTopManager(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->addTopManager($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function addAdvert(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->addAdvert($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function editAdvert(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->editAdvert($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function deleteAdvert(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->deleteAdvert($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function getEntityAdvert(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->getEntityAdvert($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function addProduct(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->addProduct($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function editProduct(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->editProduct($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function deleteProduct(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->deleteProduct($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function getEntityProduct(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->getEntityProduct($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function searchProduct(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->searchProduct($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function searchEntity(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->searchEntity($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function getAllManager(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->getAllManager($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function getAllTopManager(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->getAllTopManager($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function changeUserStatus(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->changeUserStatus($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function addAcceptedPayMode(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->addAcceptedPayMode($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function editAcceptedPayMode(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->editAcceptedPayMode($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function deleteAcceptedPayMode(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->deleteAcceptedPayMode($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function getAllPayMode(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->getAllPayMode($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function getQrCodeAdvert(Request $request)
    {
        $reqService = new RequestService($this->request);
        $id = $reqService->getGetStringData('slug');

        $userService = $this->mycontainer->get(UserService::class);

        try {
            return $userService->getQrCodeAdvert(['slug'=>$id]);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function searchAdvert(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->searchAdvert($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function listNightPharmacy(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->listNightPharmacy($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function addNightPharmacy(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->addNightPharmacy($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function updateNightPharmacy(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->updateNightPharmacy($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function deleteNightPharmacy(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->deleteNightPharmacy($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function getQrCodePharmacy(Request $request)
    {
        $reqService = new RequestService($this->request);
        $id = $reqService->getGetStringData('slug');

        $userService = $this->mycontainer->get(UserService::class);

        try {
            return $userService->getQrCodePharmacy(['slug'=>$id]);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function showAdvert(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->showAdvert($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function getQrCodeCocan(Request $request)
    {
        $reqService = new RequestService($this->request);
        $id = $reqService->getGetStringData('slug');

        $userService = $this->mycontainer->get(UserService::class);

        try {
            return $userService->getQrCodeCocan([]);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function updateLocation(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->updateLocation($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function createArticle(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->createArticle($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function updateArticle(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->updateArticle($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function deleteArticle(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->deleteArticle($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function showArticle(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->showArticle($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function recentArticles(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->recentArticles($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function otherArticles(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->otherArticles($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function userArticles(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->userArticles($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function getComments(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->getComments($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function commentArticle(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->commentArticle($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function deleteComment(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->deleteComment($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function reactOnComment(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->reactOnComment($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }

    public function reactOnArticle(Request $request)
    {
        $reqService = new RequestService($this->request);
        $data = $reqService->getPostData();

        $userService = $this->mycontainer->get(UserService::class);

        try {
            $res = $userService->reactOnArticle($data);
            return $this->renderResponse($res);
        }catch (\Exception $e){

            return $this->renderException($e);
        }

    }








}
