<?php

namespace Initial\ShippingBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Initial\ShippingBundle\Entity\UserRole;
use Initial\ShippingBundle\Form\UserRoleType;

/**
 * UserRole controller.
 *
 * @Route("/userrole")
 */
class UserRoleController extends Controller
{
    /**
     * Lists all UserRole entities.
     *
     * @Route("/", name="userrole_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $userRoles = $em->getRepository('InitialShippingBundle:UserRole')->findAll();

        return $this->render('userrole/index.html.twig', array(
            'userRoles' => $userRoles,
        ));
    }

    /**
     * Creates a new UserRole entity.
     *
     * @Route("/new", name="userrole_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $userRole = new UserRole();
        $form = $this->createForm('Initial\ShippingBundle\Form\UserRoleType', $userRole);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($userRole);
            $em->flush();

            return $this->redirectToRoute('userrole_show', array('id' => $userRole->getId()));
        }

        return $this->render('userrole/new.html.twig', array(
            'userRole' => $userRole,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a UserRole entity.
     *
     * @Route("/{id}", name="userrole_show")
     * @Method("GET")
     */
    public function showAction(UserRole $userRole)
    {
        $deleteForm = $this->createDeleteForm($userRole);

        return $this->render('userrole/show.html.twig', array(
            'userRole' => $userRole,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing UserRole entity.
     *
     * @Route("/{id}/edit", name="userrole_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, UserRole $userRole)
    {
        $deleteForm = $this->createDeleteForm($userRole);
        $editForm = $this->createForm('Initial\ShippingBundle\Form\UserRoleType', $userRole);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($userRole);
            $em->flush();

            return $this->redirectToRoute('userrole_edit', array('id' => $userRole->getId()));
        }

        return $this->render('userrole/edit.html.twig', array(
            'userRole' => $userRole,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a UserRole entity.
     *
     * @Route("/{id}", name="userrole_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, UserRole $userRole)
    {
        $form = $this->createDeleteForm($userRole);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($userRole);
            $em->flush();
        }

        return $this->redirectToRoute('userrole_index');
    }

    /**
     * Creates a form to delete a UserRole entity.
     *
     * @param UserRole $userRole The UserRole entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(UserRole $userRole)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('userrole_delete', array('id' => $userRole->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
