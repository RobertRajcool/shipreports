<?php

namespace Initial\ShippingBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Initial\ShippingBundle\Entity\CalculationRules;
use Initial\ShippingBundle\Form\CalculationRulesType;

/**
 * CalculationRules controller.
 *
 * @Route("/calculationrules")
 */
class CalculationRulesController extends Controller
{
    /**
     * Lists all CalculationRules entities.
     *
     * @Route("/", name="calculationrules_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $calculationRules = $em->getRepository('InitialShippingBundle:CalculationRules')->findAll();

        return $this->render('calculationrules/index.html.twig', array(
            'calculationRules' => $calculationRules,
        ));
    }


    /**
     * Lists all CalculationRules entities.
     *
     * @Route("/select", name="calculationrules_select")
     * @Method("GET")
     */
    public function selectAction()
    {
        $user = $this->getUser();
        $userId = $user->getId();

        $em = $this->getDoctrine()->getManager();

        //$calculationRules = $em->getRepository('InitialShippingBundle:CalculationRules')->findAll();

        $query = $em->createQueryBuilder()
            ->select('a')
            ->from('InitialShippingBundle:CalculationRules','a')
            ->leftjoin('InitialShippingBundle:KpiDetails','f', 'WITH', 'f.id = a.kpiDetailsId')
            ->leftjoin('InitialShippingBundle:ShipDetails','e', 'WITH', 'e.id = f.shipDetailsId')
            ->leftjoin('InitialShippingBundle:CompanyDetails','b', 'WITH', 'b.id = e.companyDetailsId')
            ->leftjoin('InitialShippingBundle:CompanyUsers','d', 'WITH', 'b.id = d.companyName')
            ->leftjoin('InitialShippingBundle:User','c','WITH','c.username = b.adminName or c.username = d.userName')
            ->where('c.id = :userId')
            ->setParameter('userId',$userId)
            ->getQuery();

        $calculationRules = $query->getResult();

        return $this->render('calculationrules/index.html.twig', array(
            'calculationRules' => $calculationRules,
        ));
    }


    /**
     * Creates a new CalculationRules entity.
     *
     * @Route("/new", name="calculationrules_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $user = $this->getUser();
        $id = $user->getId();
        $calculationRule = new CalculationRules();
        $form = $this->createForm(new CalculationRulesType($id), $calculationRule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($calculationRule);
            $em->flush();

            return $this->redirectToRoute('calculationrules_show', array('id' => $calculationRule->getId()));
        }

        return $this->render('calculationrules/new.html.twig', array(
            'calculationRule' => $calculationRule,
            'form' => $form->createView(),
        ));
    }


    /**
     * Creates a new CalculationRules entity.
     *
     * @Route("/new1", name="calculationrules_new1")
     * @Method({"GET", "POST"})
     */
    public function new1Action(Request $request)
    {

        $user = $this->getUser();
        $id = $user->getId();
        $calculationRule = new CalculationRules();

        $form = $this->createForm(new CalculationRulesType($id), $calculationRule);
        $form->handleRequest($request);


            $em = $this->getDoctrine()->getManager();
            $em->persist($calculationRule);
            $em->flush();

            return $this->redirectToRoute('calculationrules_show', array('id' => $calculationRule->getId()));

    }


    /**
     * Finds and displays a CalculationRules entity.
     *
     * @Route("/{id}", name="calculationrules_show")
     * @Method("GET")
     */
    public function showAction(CalculationRules $calculationRule)
    {
        $deleteForm = $this->createDeleteForm($calculationRule);

        return $this->render('calculationrules/show.html.twig', array(
            'calculationRule' => $calculationRule,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing CalculationRules entity.
     *
     * @Route("/{id}/edit", name="calculationrules_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, CalculationRules $calculationRule)
    {
        $user = $this->getUser();
        $id = $user->getId();
        $deleteForm = $this->createDeleteForm($calculationRule);
        $editForm = $this->createForm(new CalculationRulesType($id), $calculationRule);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($calculationRule);
            $em->flush();

            return $this->redirectToRoute('calculationrules_edit', array('id' => $calculationRule->getId()));
        }

        return $this->render('calculationrules/edit.html.twig', array(
            'calculationRule' => $calculationRule,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a CalculationRules entity.
     *
     * @Route("/{id}", name="calculationrules_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, CalculationRules $calculationRule)
    {
        $form = $this->createDeleteForm($calculationRule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($calculationRule);
            $em->flush();
        }

        return $this->redirectToRoute('calculationrules_index');
    }

    /**
     * Creates a form to delete a CalculationRules entity.
     *
     * @param CalculationRules $calculationRule The CalculationRules entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(CalculationRules $calculationRule)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('calculationrules_delete', array('id' => $calculationRule->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
