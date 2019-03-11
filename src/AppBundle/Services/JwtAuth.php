<?php
namespace AppBundle\Services;

use Firebase\JWT\JWT;
use Psr\Log\LoggerInterface;

class JwtAuth
{
 private $manager;
 private $key = 'aux_prueba213';
 private $logger;

 public function __construct($manager, LoggerInterface $logger)
 {
  $this->manager = $manager;
  $this->logger = $logger;
 }

 public function singup($email, $pass, bool $getHash = true)
 {
  $user = $this->manager->getRepository('BackendBundle:Users')->findOneBy(array(
   'email' => $email,
   'password' => $pass,
  ));
  $data = null;
  if (is_object($user)) {
   $token = array(
    'id' => $user->getId(),
    'email' => $user->getEmail(),
    'name' => $user->getName(),
    'iat' => time(),
    'exp' => time() + (7 * 24 * 60 * 60),
    'geth' => $getHash
   );
   
   if ($getHash) {
    $data = JWT::encode($token, $this->key, 'HS256');
   } else {    
    $data = $token;
   }

  }
  return $data;
 }

 /**
  * Valida el token
  *
  * @param $jwt
  * @return objeto que contiene bandera de valido y el usuario
  */
 public function checkToken($jwt)
 {

  //se crea un objeto para  que contendrá si es válido y el usuario.
  $auth = new class

  {
   public $valido = false; 
   public $usuario = null;
  };

  try {
   $decode = JWT::decode($jwt, $this->key, array('HS256'));
   $auth->usuario =$decode;

   $auth->valido = (is_object($decode) && isset($decode->id));

  } catch (\UnexpectedValueException $th) {
   $this->logger->error('ERROR AUTHENTICACION: ' . $th->getMessage());
  } catch (\DomainException $th) {   
   $this->logger->error('ERROR AUTHENTICACION: ' . $th->getMessage());
  } catch (\Throwable $th) {   
   $this->logger->error('CheckToken Error: ' . $th->getMessage());
  }
  return $auth;
 }
}
