<?php


namespace App\Service;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use App\Entity\User;


class MySerializer
{

    public function singleObjectToArray(object $object, string $group='all'): array
    {
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));

        $metadataAwareNameConverter = new MetadataAwareNameConverter($classMetadataFactory,new CamelCaseToSnakeCaseNameConverter());


        $encoder = new JsonEncoder();
        $dateCallback = function ($innerObject, $outerObject, string $attributeName, string $format = null, array $context = []) {
            return $innerObject instanceof \DateTime ? $innerObject->format(\DateTime::ATOM) : '';
        };

        $dateCallback2 = function ($innerObject, $outerObject, string $attributeName, string $format = null, array $context = []) {
            return $innerObject instanceof \DateTime ? $innerObject->format('d-m-Y H:i:s') : '';
        };

        $dateCallback3 = function ($innerObject, $outerObject, string $attributeName, string $format = null, array $context = []) {
            return $innerObject instanceof \DateTime ? $innerObject->format('d-m-Y') : '';
        };



        $defaultContext = [
            AbstractNormalizer::CALLBACKS => [
                'date' => $dateCallback2,
                'startDate' => $dateCallback,
                'endDate' => $dateCallback3,
                'writeDate' => $dateCallback3,
                'birthday' => $dateCallback2,
                'closeDate' => $dateCallback,
            ],
        ];



        $normalizer = new GetSetMethodNormalizer($classMetadataFactory, $metadataAwareNameConverter, null, null, null, $defaultContext);


        $serializer = new Serializer([$normalizer], [$encoder]);

        try {
            return $serializer->normalize($object,null,  ['groups' => $group]);
        } catch (ExceptionInterface $e) {
            return ['error_msg'=>$e->getMessage()];
        }


    }

    public function multipleObjectToArray($datas, string $group): array
    {
        $res = [];


        if(count($datas) ===0){
            return $res;
        }
        else{
            if(count($datas) === 1){
                if($datas[0] !== null){
                    $res[]=$this->singleObjectToArray($datas[0],$group);
                }

            }
            else{
                foreach ($datas as $data)
                {
                    $res[]=$this->singleObjectToArray($data,$group);
                }
            }
            return $res;
        }

    }


}
