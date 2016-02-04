<?php

namespace Initial\ShippingBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Initial\ShippingBundle\Entity\ReadingKpiValues;
use Initial\ShippingBundle\Form\ReadingKpiValuesType;

/**
 * ReadingKpiValues controller.
 *
 * @Route("/readingkpivalues")
 */
class ReadingKpiValuesController extends Controller
{
    /**
     * Lists all ReadingKpiValues entities.
     *
     * @Route("/", name="readingkpivalues_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $readingKpiValues = $em->getRepository('InitialShippingBundle:ReadingKpiValues')->findAll();

        return $this->render('InitialShippingBundle:readingkpivalues:index.html.twig', array(
            'readingKpiValues' => $readingKpiValues,
        ));
    }

    /**
     * Creates a new ReadingKpiValues entity.
     *
     * @Route("/new", name="readingkpivalues_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $readingKpiValue = new ReadingKpiValues();
        $form = $this->createForm('Initial\ShippingBundle\Form\ReadingKpiValuesType', $readingKpiValue);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($readingKpiValue);
            $em->flush();

            return $this->redirectToRoute('readingkpivalues_show', array('id' => $readingKpiValue->getId()));
        }

        return $this->render('InitialShippingBundle:readingkpivalues:new.html.twig', array(
            'readingKpiValue' => $readingKpiValue,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a ReadingKpiValues entity.
     *
     * @Route("/{id}", name="readingkpivalues_show")
     * @Method("GET")
     */
    public function showAction(ReadingKpiValues $readingKpiValue)
    {
        $deleteForm = $this->createDeleteForm($readingKpiValue);

        return $this->render('InitialShippingBundle:readingkpivalues:show.html.twig', array(
            'readingKpiValue' => $readingKpiValue,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing ReadingKpiValues entity.
     *
     * @Route("/{id}/edit", name="readingkpivalues_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, ReadingKpiValues $readingKpiValue)
    {
        $deleteForm = $this->createDeleteForm($readingKpiValue);
        $editForm = $this->createForm('Initial\ShippingBundle\Form\ReadingKpiValuesType', $readingKpiValue);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($readingKpiValue);
            $em->flush();

            return $this->redirectToRoute('readingkpivalues_edit', array('id' => $readingKpiValue->getId()));
        }

        return $this->render('InitialShippingBundle:readingkpivalues:edit.html.twig', array(
            'readingKpiValue' => $readingKpiValue,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a ReadingKpiValues entity.
     *
     * @Route("/{id}", name="readingkpivalues_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, ReadingKpiValues $readingKpiValue)
    {
        $form = $this->createDeleteForm($readingKpiValue);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($readingKpiValue);
            $em->flush();
        }

        return $this->redirectToRoute('readingkpivalues_index');
    }

    /**
     * Creates a form to delete a ReadingKpiValues entity.
     *
     * @param ReadingKpiValues $readingKpiValue The ReadingKpiValues entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(ReadingKpiValues $readingKpiValue)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('readingkpivalues_delete', array('id' => $readingKpiValue->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
