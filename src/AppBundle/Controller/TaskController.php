<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TaskController extends Controller
{
    public function newAction()
    {
        return $this->render('AppBundle:Task:new.html.twig', array(
            // ...
        ));
    }

}
