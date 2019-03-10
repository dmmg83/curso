<?php
namespace AppBundle\Services;

class  Helpers
{
    public $manager;

    public function __construct($manager){
        $this->manager= $manager;
    }
    
    public function json($data){
        
        $normalizers = array(new \Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer());
        $encodres = array("json"=> new \Symfony\Component\Serializer\Encoder\JsonEncoder());
        $serializer = new \Symfony\Component\Serializer\Serializer($normalizers, $encodres);
        $json = $serializer->serialize($data, 'json');

        $response = new \Symfony\Component\HttpFoundation\Response();
        $response->setContent($json);
        $response->setCharset('UTF-8');
        $response->headers->set('Content-Type','application/json');        

        return $response;
    }
    
}
