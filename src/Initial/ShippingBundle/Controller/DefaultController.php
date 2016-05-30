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

            $role = $user->getRoles();
            if($role[0] == 'ROLE_KPI_INFO_PROVIDER')
            {
                return $this->redirectToRoute('adddata_scorecard');

            }
            else
            {
                return $this->redirectToRoute('dashboardhome');
            }

        }
    }
}
