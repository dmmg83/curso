<?php
namespace AppBundle\Services;

use Firebase\JWT\JWT;

class JwtAuth
{
    public $manager;

    public function __construct($manager){
        $this->manager= $manager;
    }

    public function singup($email, $pass, bool $getHash=false)
    {
        $user = $this->manager->getRepository('BackendBundle:Users')->findOneBy(array(
            'email'=>$email,
            'password'=>$pass
        ));
        $data=null;
        if(is_object($user))
        {
            $token=array(
                'id'=>$user->getId(),
                'email'=>$user->getEmail(),
                'name'=>$user->getName(),
                'iat'=>time(),
                'exp'=>time()+(7*24*60*60)
            );

            if($getHash)
            {
                $data=JWT::encode($token,'prueba','HS256');
            }
            else
            {
                //$jwt=JWT::encode($token,'prueba','HS256');
                //$data=JWT::decode($jwt,'prueba',array('HS256'));
                $data=$token;
            }

        }
        return $data;
    }
}