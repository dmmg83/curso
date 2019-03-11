<?php

namespace AppBundle\Controller;

use AppBundle\Services\Helpers;
use AppBundle\Services\JwtAuth;
use BackendBundle\Entity\Tasks;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TasksController extends Controller
{
 public function newAction(Request $request)
 {
  $helper = $this->get(Helpers::class);
  $jwt = $this->get(JwtAuth::class);

  $token = $request->get('token', null);
  $json = $request->get('json', null);
  $json = \json_decode($json);
  $rta = array(
   'status' => 'error',
  );
  $auth = $jwt->checkToken($token);
  if ($auth->valido) {

   if (is_object($json) && 
        (isset($json->title) && isset($json->description))) {

    //$rta['user']=\json_encode( $auth->usuario);
    $em = $this->getDoctrine()->getManager();
   
    $task = new Tasks();
    $task->autoSet($json, $em);
        
    $em->persist($task);
    $em->flush();

    $rta['status'] = 'ok';
    $rta['mensaje'] = 'task creada';
   } else {
    $rta['mensaje'] = 'el objeto json no es correcto';

   }
  } else {
   $rta['mensaje'] = 'token incorrecto';
  }

  return $helper->json($rta);
 }

 public function editAction(Request $request)
 {
  $helper = $this->get(Helpers::class);
  $jwt = $this->get(JwtAuth::class);

  $token = $request->get('token', null);
  $auth = $jwt->checkToken($token);

  $json = $request->get('json', null);

  $rta = array('status' => 'error');

  if ($auth->valido) {
   $json = json_decode($json);
   if (is_object($json) && isset($json->id) && isset($json->title) && isset($json->description)) {

   
    
    $em = $this->getDoctrine()->getManager();
    //  $task = $em->getRepository('BackendBundle:Tasks')->findOneBy(array(
    //      'id'=>$json->id
    //  ));
    
    $task = $em->getRepository('BackendBundle:Tasks')->find($json->id);

    // echo '<p>';
    // echo $helper->json($task);
    // echo '</p>';

    $task->autoSet($json);
    

    // echo '<p>';
    // echo $helper->json($task);
    // echo '</p>';
    // die();
    // $task->setTitle($json->title);
    // $task->setDescription($json->description);
    $em->flush();
    $rta['status'] = 'ok';
    $rta['mensje'] = 'task editado';
   } else {
    $rta['mensje'] = 'error en json';
   }
  } else {
   $rta['mensje'] = 'token invalido';
  }

  return $helper->json($rta);
 }

 public function removeAction(Request $request)
 {
  $helper = $this->get(Helpers::class);
  $jwt = $this->get(JwtAuth::class);

  $token = $request->get('token', null);
  $auth = $jwt->checkToken($token);

  $json = $request->get('json', null);

  $rta = array('status' => 'error');

  if ($auth->valido) {
   $json = json_decode($json);
   if (is_object($json) && isset($json->id)) {
    $em = $this->getDoctrine()->getManager();
    //  $task = $em->getRepository('BackendBundle:Tasks')->findOneBy(array(
    //      'id'=>$json->id
    //  ));
    $task = $em->getRepository('BackendBundle:Tasks')->find($json->id);
    $em->remove($task);
    $em->flush();
    $rta['status'] = 'ok';
    $rta['mensje'] = 'task eliminado';
   } else {
    $rta['mensje'] = 'error en json';
   }
  } else {
   $rta['mensje'] = 'token invalido';
  }

  return $helper->json($rta);
 }

 public function listarAction(Request $request)
 {
  $helper = $this->get(Helpers::class);
  $jwt = $this->get(JwtAuth::class);

  $token = $request->get('token', null);
  $json = $request->get('json', null);
  $json = \json_decode($json);
  $rta = array(
   'status' => 'error',
  );
  $auth = $jwt->checkToken($token);
  if ($auth->valido) {

   if (is_object($json) && (isset($json->idUsuario))) {

    //$rta['user']=\json_encode( $auth->usuario);
    $em = $this->getDoctrine()->getManager();
    // $user = $em->getRepository('BackendBundle:Users')->findOneBy(array(
    //  'id' => $auth->usuario->id,
    // ));
    $dql='select t from BackendBundle:Tasks t';
    //$result = $em->createQuery($dql)->getResult();
    $query = $em->createQuery($dql);
    $page=$request->query->getInt('page',1);
    $paginator=$this->get('knp_paginator');
    $items_per_page=10;
    $pagination=$paginator->paginate($query,$page,$items_per_page);
    $total_items_count=$pagination->getTotalItemCount();
    $rta['total_items_count']=$total_items_count;
    $rta['page_actual']=$page;
    $rta['items_per_page']=$items_per_page;
    $rta['total_pages']= ceil($total_items_count/$items_per_page);
    $rta['data']=$pagination;
    $rta['status'] = 'ok';
    
    //$rta['data']=$result;
   } else {
    $rta['mensaje'] = 'el objeto json no es correcto';

   }
  } else {
   $rta['mensaje'] = 'token incorrecto';
  }

  return $helper->json($rta);
 }
}
