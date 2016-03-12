<?php

namespace Initial\ShippingBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Initial\ShippingBundle\Entity\ElementRules;
use Initial\ShippingBundle\Form\ElementRulesType;

/**
 * ElementRules controller.
 *
 * @Route("/elementrules")
 */
class ElementRulesController extends Controller
{
    /**
     * Lists all ElementRules entities.
     *
     * @Route("/", name="elementrules_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $elementRules = $em->getRepository('InitialShippingBundle:ElementRules')->findAll();

        return $this->render('elementrules/index.html.twig', array(
            'elementRules' => $elementRules,
        ));
    }

    /**
     * Creates a new ElementRules entity.
     *
     * @Route("/new", name="elementrules_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $elementRule = new ElementRules();
        $form = $this->createForm('Initial\ShippingBundle\Form\ElementRulesType', $elementRule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($elementRule);
            $em->flush();

            return $this->redirectToRoute('elementrules_show', array('id' => $elementrules->getId()));
        }

        return $this->render('elementrules/new.html.twig', array(
            'elementRule' => $elementRule,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a ElementRules entity.
     *
     * @Route("/{id}", name="elementrules_show")
     * @Method("GET")
     */
    public function showAction(ElementRules $elementRule)
    {
        $deleteForm = $this->createDeleteForm($elementRule);

        return $this->render('elementrules/show.html.twig', array(
            'elementRule' => $elementRule,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing ElementRules entity.
     *
     * @Route("/{id}/edit", name="elementrules_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, ElementRules $elementRule)
    {
        $deleteForm = $this->createDeleteForm($elementRule);
        $editForm = $this->createForm('Initial\ShippingBundle\Form\ElementRulesType', $elementRule);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($elementRule);
            $em->flush();

            return $this->redirectToRoute('elementrules_edit', array('id' => $elementRule->getId()));
        }

        return $this->render('elementrules/edit.html.twig', array(
            'elementRule' => $elementRule,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a ElementRules entity.
     *
     * @Route("/{id}", name="elementrules_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, ElementRules $elementRule)
    {
        $form = $this->createDeleteForm($elementRule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($elementRule);
            $em->flush();
        }

        return $this->redirectToRoute('elementrules_index');
    }

    /**
     * Creates a form to delete a ElementRules entity.
     *
     * @param ElementRules $elementRule The ElementRules entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(ElementRules $elementRule)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('elementrules_delete', array('id' => $elementRule->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
