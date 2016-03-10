<?php

namespace Initial\ShippingBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Initial\ShippingBundle\Entity\KpiRules;
use Initial\ShippingBundle\Form\KpiRulesType;

/**
 * KpiRules controller.
 *
 * @Route("/kpirules")
 */
class KpiRulesController extends Controller
{
    /**
     * Lists all KpiRules entities.
     *
     * @Route("/", name="kpirules_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $kpiRules = $em->getRepository('InitialShippingBundle:KpiRules')->findAll();

        return $this->render('kpirules/index.html.twig', array(
            'kpiRules' => $kpiRules,
        ));
    }

    /**
     * Creates a new KpiRules entity.
     *
     * @Route("/new", name="kpirules_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $kpiRule = new KpiRules();
        $form = $this->createForm('Initial\ShippingBundle\Form\KpiRulesType', $kpiRule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($kpiRule);
            $em->flush();

            return $this->redirectToRoute('kpirules_show', array('id' => $kpirules->getId()));
        }

        return $this->render('kpirules/new.html.twig', array(
            'kpiRule' => $kpiRule,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a KpiRules entity.
     *
     * @Route("/{id}", name="kpirules_show")
     * @Method("GET")
     */
    public function showAction(KpiRules $kpiRule)
    {
        $deleteForm = $this->createDeleteForm($kpiRule);

        return $this->render('kpirules/show.html.twig', array(
            'kpiRule' => $kpiRule,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing KpiRules entity.
     *
     * @Route("/{id}/edit", name="kpirules_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, KpiRules $kpiRule)
    {
        $deleteForm = $this->createDeleteForm($kpiRule);
        $editForm = $this->createForm('Initial\ShippingBundle\Form\KpiRulesType', $kpiRule);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($kpiRule);
            $em->flush();

            return $this->redirectToRoute('kpirules_edit', array('id' => $kpiRule->getId()));
        }

        return $this->render('kpirules/edit.html.twig', array(
            'kpiRule' => $kpiRule,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a KpiRules entity.
     *
     * @Route("/{id}", name="kpirules_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, KpiRules $kpiRule)
    {
        $form = $this->createDeleteForm($kpiRule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($kpiRule);
            $em->flush();
        }

        return $this->redirectToRoute('kpirules_index');
    }

    /**
     * Creates a form to delete a KpiRules entity.
     *
     * @param KpiRules $kpiRule The KpiRules entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(KpiRules $kpiRule)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('kpirules_delete', array('id' => $kpiRule->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
