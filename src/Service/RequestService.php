<?php


namespace App\Service;

use phpDocumentor\Reflection\Types\Integer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class RequestService
{

    protected $request;

    protected $content;

    protected $method;

    protected $lang;

    protected $token;

    public function __construct(Request $request)
    {
        $this->request = $request;

        if($this->request->getContentType() === 'json')
        {
            $res = json_decode($this->request->getContent(), true);
            $this->content = $res ?? [];
            foreach($this->content as $key => $value){
                if($value === null){
                    $this->content[$key]='';
                }
            }

        }

        $this->method = $this->request->getMethod();

        $this->lang = $this->request->headers->get('lang');


    }

    public function getPostData(): array
    {

        if($this->method === 'POST'){
            return $this->content;
        }
        return [];

    }

    public function getPostFormData(): array
    {
        if($this->method === 'POST'){
            return $this->request->request->all();
        }
        return [];

    }

    public function getFiles(): array
    {

        if($this->method === 'POST'){
            return $this->request->files->all();
        }
        return [];

    }

    public function getGetStringData(string $key): ?string
    {
        $r = $this->request->query->get($key);
        if(is_string($r))
        {
            return $r;
        }

        return null;
    }

    public function getGetBoolData(string $key) : bool
    {
        $r = $this->request->query->getBoolean($key);
        if(is_bool($r))
        {
            return $r;
        }

        return 0;
    }

    public function getGetIntData(string $key) : int
    {
        $r = $this->request->query->getInt($key);
        if(is_int($r))
        {
            return $r;
        }

        return 0;
    }

    public function getMethod():string{
        return $this->method;
    }

    public function getLang(): string{
        return $this->lang;
    }
}
