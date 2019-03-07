<?php

namespace AppBundle\Controller;

use AppBundle\Services\Helpers;
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
                $user->setCreatedAt($creacion);
                $user->setName($name);
                $user->setEmail($name);
                $user->setPassword($password);
                $user->setRole('admin');
                $user->setSurname($surname);

                $em = $this->getDoctrine()->getManager();
                $existe = $em->getRepository('BackendBundle:Users')->findBy(array(
                    'email' => $email,
                ));
                if (count($existe) > 0) {
                    $data['excepcion'] = "correo no válido";
                } else {
                    $em->persist($user);
                    $em->flush();
                    $data['status'] = "ok";
                    $data['mensaje'] = "usuario creado";
                }

            }
        }

        return $helpers->json($data);
    }

}
