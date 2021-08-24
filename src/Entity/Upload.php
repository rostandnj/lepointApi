<?php

namespace App\Entity;


use Symfony\Component\HttpFoundation\File\UploadedFile;

class Upload
{

    private $name;

    private $extension;

    private $type;

    private $size;

    private $result;

    private $error;

    private $path;

    private $baseFolder;

    public function __construct()
    {
        $this->result =false;
        $this->baseFolder = __DIR__."/../../public/";
    }


    private function setExtension($mimetype)
    {
        $types = array('image/jpg','image/jpeg','image/png','image/gif','image/pjpeg', 'application/pdf');
        $ext = array('.jpg','.jpeg','.png','.gif','.jpg','.pdf');

        foreach ($types as $key => $value)
        {
            if($value == $mimetype)
            {
                return $ext[$key];
            }
        }

        return null;
    }

    public function getSize()
    {
        return $this->size;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getExtension()
    {
        return $this->extension;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getError()
    {
        return $this->error;
    }

    public function save (UploadedFile $uploadedFile,$directory)
    {
        if($uploadedFile === null) return null;

        $this->extension = $this->setExtension($uploadedFile->getMimeType());

        if($this->extension === null)
        {
            $this->error = $uploadedFile->getMimeType()." extension not supported";
            $this->result = false;
            return $this;

        }

        $this->size = $uploadedFile->getSize();

        if($this->size > 2097152)
        {
            $ta = $uploadedFile->getSize()/ 2097152;
            $this->error = "size (".$ta.") must be < 2 Mo";
            $this->result = false;
            return $this;
        }

        $this->type = "image";
        if(strpos($uploadedFile->getMimeType(), "pdf")) $this->type = "pdf";

        $this->name = uniqid('', true).$this->extension;
        $this->path = $this->getName();



        try
        {

            $file = $uploadedFile->move($this->baseFolder.$directory, $this->name);
        }
        catch(\Exception $e)
        {
            $this->error = $e->getMessage()." à la ligne ".$e->getLine()." du fichier ".$e->getFile();
            $this->result = false;

            return $this;
        }

        $this->result = true;





        return $this;



    }

    /**
     * @return string
     */
    public function getBaseFolder(): string
    {
        return $this->baseFolder;
    }



    public function toArray()
    {
        return [

            'name'=>$this->getName(),
            'extension'=>$this->getExtension(),
            'type'=>$this->getType(),
            'size'=>$this->getSize(),
            'path'=>$this->getPath()
            // ... and so on ...
        ];
    }

}
