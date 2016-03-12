<?php

namespace Initial\ShippingBundle\Controller;

use  Initial\ShippingBundle\Form\RegistrationFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Initial\ShippingBundle\Entity\User;
use Ob\HighchartsBundle\Highcharts\Highchart;

class UserActivitiyController extends Controller
{
    public function index1Action()
    {
        $em = $this->getDoctrine()->getManager();

        $shipDetails = $em->getRepository('InitialShippingBundle:CompanyDetails')->findAll();

        return $this->render('InitialShippingBundle:CompanyUsers:index1.html.twig', array(
            'shipDetails' => $shipDetails,
        ));
    }



    public function edit1Action(Request $request, User $user, $id)
    {

        //$user=new User();
        $em = $this->getDoctrine()->getManager();
        //$userDetails = $em->getRepository('InitialShippingBundle:User')->findById($id);

        $editForm = $this->createForm(new RegistrationFormType($id), $user);
        $editForm->handleRequest($request);

        /* if ($editForm->isSubmitted() && $editForm->isValid()) {
             $em = $this->getDoctrine()->getManager();
             $em->persist($user);
             $em->flush();

              return $this->redirectToRoute('mycontroller', array('id' => $user->getId()));
         }
        */

        return $this->render('InitialShippingBundle:CompanyUsers:edit.html .twig', array(
            'userdetails' => $user,
            'edit_form' => $editForm->createView(),

        ));
    }


    public function updateAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $params = $request->request->get('fos_user');
        $id = $params['id'];
        $entity = $em->getRepository('InitialShippingBundle:User')->find($id);
        $newrole = $request->request->get('registration_form');
        $role_name=$newrole['roles'];
        $newrole1 = $request->request->get('registration_form.roles');
        $newmail = $params['email'];
        $newname = $params['username'];


        $uservalue = new User();
        $entity->setroles($role_name);
        $entity->setemail($newmail);
        $entity->setusername($newname);

        $em->flush();

        return $this->redirectToRoute('fos_user_registration_show');

    }

    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();


        $user = $this->getDoctrine()->getRepository('InitialShippingBundle:User')->find($id);

        $em->remove($user);
        $em->flush();

        return $this->redirectToRoute("fos_user_registration_show");
    }
}
