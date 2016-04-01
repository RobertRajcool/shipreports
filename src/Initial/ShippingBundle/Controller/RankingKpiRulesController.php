<?php

namespace Initial\ShippingBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Initial\ShippingBundle\Entity\RankingKpiRules;
use Initial\ShippingBundle\Form\RankingKpiRulesType;

/**
 * RankingKpiRules controller.
 *
 * @Route("/rankingkpirules")
 */
class RankingKpiRulesController extends Controller
{
    /**
     * Lists all RankingKpiRules entities.
     *
     * @Route("/", name="rankingkpirules_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $rankingKpiRules = $em->getRepository('InitialShippingBundle:RankingKpiRules')->findAll();

        return $this->render('rankingkpirules/index.html.twig', array(
            'rankingKpiRules' => $rankingKpiRules,
        ));
    }

    /**
     * Creates a new RankingKpiRules entity.
     *
     * @Route("/new", name="rankingkpirules_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $rankingKpiRule = new RankingKpiRules();
        $form = $this->createForm('Initial\ShippingBundle\Form\RankingKpiRulesType', $rankingKpiRule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($rankingKpiRule);
            $em->flush();

            return $this->redirectToRoute('rankingkpirules_show', array('id' => $rankingkpirules->getId()));
        }

        return $this->render('rankingkpirules/new.html.twig', array(
            'rankingKpiRule' => $rankingKpiRule,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a RankingKpiRules entity.
     *
     * @Route("/{id}", name="rankingkpirules_show")
     * @Method("GET")
     */
    public function showAction(RankingKpiRules $rankingKpiRule)
    {
        $deleteForm = $this->createDeleteForm($rankingKpiRule);

        return $this->render('rankingkpirules/show.html.twig', array(
            'rankingKpiRule' => $rankingKpiRule,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing RankingKpiRules entity.
     *
     * @Route("/{id}/edit", name="rankingkpirules_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, RankingKpiRules $rankingKpiRule)
    {
        $deleteForm = $this->createDeleteForm($rankingKpiRule);
        $editForm = $this->createForm('Initial\ShippingBundle\Form\RankingKpiRulesType', $rankingKpiRule);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($rankingKpiRule);
            $em->flush();

            return $this->redirectToRoute('rankingkpirules_edit', array('id' => $rankingKpiRule->getId()));
        }

        return $this->render('rankingkpirules/edit.html.twig', array(
            'rankingKpiRule' => $rankingKpiRule,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a RankingKpiRules entity.
     *
     * @Route("/{id}", name="rankingkpirules_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, RankingKpiRules $rankingKpiRule)
    {
        $form = $this->createDeleteForm($rankingKpiRule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($rankingKpiRule);
            $em->flush();
        }

        return $this->redirectToRoute('rankingkpirules_index');
    }

    /**
     * Creates a form to delete a RankingKpiRules entity.
     *
     * @param RankingKpiRules $rankingKpiRule The RankingKpiRules entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(RankingKpiRules $rankingKpiRule)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('rankingkpirules_delete', array('id' => $rankingKpiRule->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
