<?php

namespace AppBundle\Controller;

use AppBundle\Services\Helpers;
use AppBundle\Services\JwtAuth;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TasksController extends Controller
{
    public function newAction(Request $request)
    {
        $helper = $this->get(Helpers::class);
        $jwt =$this->get(JwtAuth::class);

        $token =$request->get('token',null);
        $rta = array(
            'status'=>'error'
        );
        $auth=$jwt->checkToken($token);
        if ($auth->valido) {
            $rta['status']='ok';
            //$rta['user']=\json_encode( $auth->usuario);
            
        }
        else
        {
            $rta['mensaje']='token incorrecto';
        }
        
        return $helper->json($rta);
    }

}
