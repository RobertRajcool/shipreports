<?php

namespace Initial\ShippingBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if($user==null)
        {

            return $this->redirectToRoute('fos_user_security_login');
        }
        else
        {

        return $this->render('InitialShippingBundle:Default:index.html.twig');
        }
    }
}
