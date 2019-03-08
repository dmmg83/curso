<?php

namespace AppBundle\Controller;

use AppBundle\Services\Helpers;
use AppBundle\Services\JwtAuth;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class DefaultController extends Controller
{
    
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);
    }

    public function pruebasAction(){
        $em=$this->getDoctrine()->getManager();
        $userRepo = $em->getRepository('BackendBundle:Users');
        $users = $userRepo->findAll();
        // $query=$em->createQuery('Select u from BackendBundle\Entity\Users u');
        // $users = $query->getResult(); 
        //$aux = $users->result();
        
        echo '<table><tr><th>Nombre</th><th>Apellido</th></tr>';
        foreach ($users as $usu) {
            //echo "<tr><td>$usu->getName()</td><td>$usu->getSurname()</td></tr>";
            echo "<tr><td>".$usu->getName()."</td><td>".$usu->getSurname()."</td></tr>";
            //echo var_dump($usu) + "\n\n";
        }
        echo '</table>';

        //var_dump($users);

        die();
    }

    public function pruebasJsonAction(){
        $em=$this->getDoctrine()->getManager();
        $userRepo = $em->getRepository('BackendBundle:Users');
        $users = $userRepo->findAll();
        
        $helper = $this->get(Helpers::class);
        return $helper->json(array(
            'status'=>'ok',
            'users' => $users
        ));

        die();
        return $this->json(array(
            'status'=>'ok',
            'users' => $users
        ));

        
    }


    public function loginAction(Request $request){
        $helpers = $this->get(Helpers::class);
        // recibir json por post
        $json = $request->get('json',null);
        
        $data = array(
            'status'=>'error',
            'data'=>'falta json'
        );

        if(isset($json))
        {
            // convierte json a objeto
            $params = json_decode($json);

            $email = isset($params->email)?$params->email:null;
            $pass = isset($params->pass)?$params->pass:null;
            $getHash = isset($params->getHash)?$params->getHash:null;

            $emailConstraint = new Assert\Email();
            $emailConstraint->message = "mail no válido";
            $validate_email = $this->get('validator')->validate($email,$emailConstraint);

            if(count($validate_email)==0 && isset($pass))
            {
                
                $jwt_auth = $this->get(JwtAuth::class);
                $singup = $jwt_auth->singup($email,$pass,$getHash);
                $data = array(
                    'status'=>'ok',
                    'data'=>$singup
                );
            }
            else {
                // $data = array(
                //     'status'=>'error',
                //     'data'=>$validate_email[0]->getMessage()
                // );

                 $data = array(
                    'status'=>'error',
                    'data'=>'Usuario o contraseña inválidos'
                );
        
            }

          
        }
        //return $this->json($data);
        return $helpers->json($data);
    }
}
