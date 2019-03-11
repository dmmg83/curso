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
        // $str=$helpers->normalizarCadena('María Gómez');
        // // echo 'María Gómez'."\n";
        // // echo $str."\n";
        // echo str_replace(array('`','´',"'"),'', $str);
        // die();
        $params = $request->get('json', null);
        $json = json_decode($params);
        $excepcion = null;
        $data = array(
            'status' => 'error',
            'mensaje' => 'usuario no creado',
        );

        if ($json != null) {
            //$creacion = new \Datetime("now");
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
                $user->setPassword(\hash('sha256', $password));
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
        $busqueda = $json->busqueda;
        $palabras = explode(' ', $busqueda);
        $em = $this->getDoctrine()->getManager();
        $dql = 'select u FROM BackendBundle\Entity\Users u Where 1=1 ';
        $dqlpalabras = '';
        $i = 1;
        foreach ($palabras as $palabra) {
            if (!empty($palabra)) {
            $dql .= "and u.name LIKE ?$i "; //u.name LIKE ?1 and u.name LIKE ?2
            $i++;
            }
        }
        // echo $dql;
        // die();

        $params = array();
        $i = 1;
        foreach ($palabras as $palabra) {
            if (!empty($palabra)) {
                $params[$i] = "%" . \trim($palabra) . "%";
                $i++;
            }
        }
        // var_dump($params);
        // die();
        $query = $em->createQuery($dql);

        $query->setParameters($params);
        
        $user = $query->getResult();

        foreach ($user as $u) {
            echo '<p>' . $u->getName() . '</p>';
        }

        die();
        if ($authCheck) {

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

}
