<?php

namespace Initial\ShippingBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Initial\ShippingBundle\Entity\RankingElementRules;
use Initial\ShippingBundle\Form\RankingElementRulesType;

/**
 * RankingElementRules controller.
 *
 * @Route("/rankingelementrules")
 */
class RankingElementRulesController extends Controller
{
    /**
     * Lists all RankingElementRules entities.
     *
     * @Route("/", name="rankingelementrules_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $rankingElementRules = $em->getRepository('InitialShippingBundle:RankingElementRules')->findAll();

        return $this->render('rankingelementrules/index.html.twig', array(
            'rankingElementRules' => $rankingElementRules,
        ));
    }

    /**
     * Creates a new RankingElementRules entity.
     *
     * @Route("/new", name="rankingelementrules_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $rankingElementRule = new RankingElementRules();
        $form = $this->createForm('Initial\ShippingBundle\Form\RankingElementRulesType', $rankingElementRule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($rankingElementRule);
            $em->flush();

            return $this->redirectToRoute('rankingelementrules_show', array('id' => $rankingElementRule->getId()));
        }

        return $this->render('rankingelementrules/new.html.twig', array(
            'rankingElementRule' => $rankingElementRule,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a RankingElementRules entity.
     *
     * @Route("/{id}", name="rankingelementrules_show")
     * @Method("GET")
     */
    public function showAction(RankingElementRules $rankingElementRule)
    {
        $deleteForm = $this->createDeleteForm($rankingElementRule);

        return $this->render('rankingelementrules/show.html.twig', array(
            'rankingElementRule' => $rankingElementRule,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing RankingElementRules entity.
     *
     * @Route("/{id}/edit", name="rankingelementrules_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, RankingElementRules $rankingElementRule)
    {
        $deleteForm = $this->createDeleteForm($rankingElementRule);
        $editForm = $this->createForm('Initial\ShippingBundle\Form\RankingElementRulesType', $rankingElementRule);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($rankingElementRule);
            $em->flush();

            return $this->redirectToRoute('rankingelementrules_edit', array('id' => $rankingElementRule->getId()));
        }

        return $this->render('rankingelementrules/edit.html.twig', array(
            'rankingElementRule' => $rankingElementRule,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a RankingElementRules entity.
     *
     * @Route("/{id}", name="rankingelementrules_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, RankingElementRules $rankingElementRule)
    {
        $form = $this->createDeleteForm($rankingElementRule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($rankingElementRule);
            $em->flush();
        }

        return $this->redirectToRoute('rankingelementrules_index');
    }

    /**
     * Creates a form to delete a RankingElementRules entity.
     *
     * @param RankingElementRules $rankingElementRule The RankingElementRules entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(RankingElementRules $rankingElementRule)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('rankingelementrules_delete', array('id' => $rankingElementRule->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
