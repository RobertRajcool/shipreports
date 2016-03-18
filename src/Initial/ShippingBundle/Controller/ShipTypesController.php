<?php

namespace Initial\ShippingBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Initial\ShippingBundle\Entity\ShipTypes;
use Initial\ShippingBundle\Form\ShipTypesType;

/**
 * ShipTypes controller.
 *
 * @Route("/shiptypes")
 */
class ShipTypesController extends Controller
{
    /**
     * Lists all ShipTypes entities.
     *
     * @Route("/", name="shiptypes_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $shipTypes = $em->getRepository('InitialShippingBundle:ShipTypes')->findAll();

        return $this->render('shiptypes/index.html.twig', array(
            'shipTypes' => $shipTypes,
        ));
    }

    /**
     * Creates a new ShipTypes entity.
     *
     * @Route("/new", name="shiptypes_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $shipType = new ShipTypes();
        $form = $this->createForm('Initial\ShippingBundle\Form\ShipTypesType', $shipType);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($shipType);
            $em->flush();

            return $this->redirectToRoute('shiptypes_show', array('id' => $shiptypes->getId()));
        }

        return $this->render('shiptypes/new.html.twig', array(
            'shipType' => $shipType,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a ShipTypes entity.
     *
     * @Route("/{id}", name="shiptypes_show")
     * @Method("GET")
     */
    public function showAction(ShipTypes $shipType)
    {
        $deleteForm = $this->createDeleteForm($shipType);

        return $this->render('shiptypes/show.html.twig', array(
            'shipType' => $shipType,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing ShipTypes entity.
     *
     * @Route("/{id}/edit", name="shiptypes_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, ShipTypes $shipType)
    {
        $deleteForm = $this->createDeleteForm($shipType);
        $editForm = $this->createForm('Initial\ShippingBundle\Form\ShipTypesType', $shipType);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($shipType);
            $em->flush();

            return $this->redirectToRoute('shiptypes_edit', array('id' => $shipType->getId()));
        }

        return $this->render('shiptypes/edit.html.twig', array(
            'shipType' => $shipType,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a ShipTypes entity.
     *
     * @Route("/{id}", name="shiptypes_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, ShipTypes $shipType)
    {
        $form = $this->createDeleteForm($shipType);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($shipType);
            $em->flush();
        }

        return $this->redirectToRoute('shiptypes_index');
    }

    /**
     * Creates a form to delete a ShipTypes entity.
     *
     * @param ShipTypes $shipType The ShipTypes entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(ShipTypes $shipType)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('shiptypes_delete', array('id' => $shipType->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
