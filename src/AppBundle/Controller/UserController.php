<?php

namespace AppBundle\Controller;

use AppBundle\Services\Helpers;
use AppBundle\Services\JwtAuth;
use BackendBundle\Entity\Users;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints;

class UserController extends Controller
{
 public function newAction(Request $request)
 {
  $helpers = $this->get(Helpers::class);
  $params = $request->get('json', null);
  $json = json_decode($params);
  $excepcion = null;
  $data = array(
   'status' => 'error',
   'mensaje' => 'usuario no creado',
  );

  if ($json != null) {
   $creacion = new \Datetime("now");
   $role = 'user';

   $email = (isset($json->email)) ? $json->email : null;
   $name = (isset($json->name)) ? $json->name : null;
   $surname = (isset($json->surname)) ? $json->surname : null;
   $password = (isset($json->password)) ? $json->password : null;

   $emailConstraint = new Constraints\Email();
   $emailConstraint->message = "Correo no válido";
   $validarEmail = $this->get('validator')->validate($email, $emailConstraint);

   if (count($validarEmail) > 0) {
    $data['excepcion'] = "correo no válido";
   } elseif ($name != null && $surname != null && $password != null) {
    $user = new Users();
    //$user->setCreatedAt($creacion);
    $user->setName($name);
    $user->setEmail($email);
    $user->setPassword($password);
    $user->setRole('admin');
    $user->setSurname($surname);

    $em = $this->getDoctrine()->getManager();
    $existe = $em->getRepository('BackendBundle:Users')->findBy(array(
     'email' => $email,
    ));
    if (count($existe) > 0) {
     $data['excepcion'] = "correo ya existe";
    } else {
     $em->persist($user);
     $em->flush();
     $data['status'] = "ok";
     $data['mensaje'] = "usuario creado";
     $data['user'] = $user;
    }

   }
  }

  return $helpers->json($data);
 }

 public function editAction(Request $request)
 {
  $helpers = $this->get(Helpers::class);
  $jwt_auth = $this->get(JwtAuth::class);
  $token = $request->get('token', null);
  $authCheck = $jwt_auth->checkToken($token);
  $params = $request->get('json', null);
  $json = json_decode($params);
  $excepcion = null;
  $data = array(
   'status' => 'error',
   'mensaje' => 'usuario no creado',
  );

  if ($authCheck) {
   $em = $this->getDoctrine()->getManager();

   $identity = $jwt_auth->checkToken($token, true);

   $user = $em->getRepository('BackendBundle:Users')->findOneBy(array(
    'id' => $identity->id,
   ));

   if ($user == null) {
    $data['mensaje'] = 'usuario no encontrado';
   } elseif ($json != null) {

    $creacion = new \Datetime("now");
    $role = 'user';

    $email = (isset($json->email)) ? $json->email : null;
    $name = (isset($json->name)) ? $json->name : null;
    $surname = (isset($json->surname)) ? $json->surname : null;
    $password = (isset($json->password)) ? $json->password : null;

    $emailConstraint = new Constraints\Email();
    $emailConstraint->message = "Correo no válido";
    $validarEmail = $this->get('validator')->validate($email, $emailConstraint);

    if (count($validarEmail) > 0) {
     $data['excepcion'] = "correo no válido";
    } elseif ($name != null && $surname != null && $password != null) {
     //$user = new Users();
     $user->setCreatedAt($creacion);
     $user->setName($name);
     $user->setEmail($email);
     $user->setPassword($password);
     $user->setRole('admin');
     $user->setSurname($surname);

     $existe = $em->getRepository('BackendBundle:Users')->findBy(array(
      'email' => $email,
     ));
     if (count($existe) > 0) {
      $data['excepcion'] = "correo ya existe";
     } else {
      $em->persist($user);
      $em->flush();
      $data['status'] = "ok";
      $data['mensaje'] = "usuario editado";
      $data['user'] = $user;
     }

    }
   }
  } else {
   $data['mensaje'] = 'token inválido';
  }

  return $helpers->json($data);
 }

 public function buscarAction(Request $request)
 {
  $params = $request->get('json', null); // recibe parámetro json
  $json = json_decode($params); // decodifica
  $busqueda = $json->busqueda; // toma el campo búsqueda del json
  $palabras = explode(' ', $busqueda); // separa las palabras

  $em = $this->getDoctrine()->getManager();

  $qb = $em->createQueryBuilder(); // se crea una instancia de query builder

  $qb->select('u')->from('BackendBundle\Entity\Users', 'u'); //se consulta la tabla

  foreach ($palabras as $palabra) { //recorre las palabras
   if (!empty($palabra)) { //si no es una palabra vacía
    $qb->andWhere("u.name LIKE '%" . \trim($palabra) . "%'"); //se agrega el like con And
   }
  }

  $user = $qb->getQuery()->getResult(); //se obtiene los resultados

  /******************** A CONTINUACIÓN SE MANIPULA LOS RESULTADOS ***************************/
  /******************** EN ESTE CASO SE MUESTRAN... ****************************************/
  foreach ($user as $u) {
   echo '<p>' . $u->getName() . '</p>';
  }
  die();
 }

}
