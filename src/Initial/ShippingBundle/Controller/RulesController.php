<?php

namespace Initial\ShippingBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Initial\ShippingBundle\Entity\Rules;
use Initial\ShippingBundle\Form\RulesType;

/**
 * Rules controller.
 *
 * @Route("/rules")
 */
class RulesController extends Controller
{
    /**
     * Lists all Rules entities.
     *
     * @Route("/", name="rules_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $rules = $em->getRepository('InitialShippingBundle:Rules')->findAll();

        return $this->render('rules/index.html.twig', array(
            'rules' => $rules,
        ));
    }


    /**
     * Lists all Rules entities.
     *
     * @Route("/select", name="rules_select")
     * @Method("GET")
     */
    public function selectAction()
    {
        $user = $this->getUser();
        $userId = $user->getId();

        $em = $this->getDoctrine()->getManager();

        if($this->container->get('security.context')->isGranted('ROLE_ADMIN'))
        {
            $query = $em->createQueryBuilder()
                ->select('a')
                ->from('InitialShippingBundle:Rules','a')
                ->leftjoin('InitialShippingBundle:KpiDetails','f', 'WITH', 'f.id = a.kpiDetailsId')
                ->leftjoin('InitialShippingBundle:ShipDetails','d', 'WITH', 'd.id = f.shipDetailsId')
                ->leftjoin('InitialShippingBundle:CompanyDetails','b', 'WITH', 'b.id = d.companyDetailsId')
                ->leftjoin('InitialShippingBundle:User','c','WITH','c.username = b.adminName')
                ->where('c.id = :userId')
                ->setParameter('userId',$userId)
                ->getQuery();
        }
        else
        {
            $query = $em->createQueryBuilder()
                ->select('a')
                ->from('InitialShippingBundle:Rules','a')
                ->leftjoin('InitialShippingBundle:KpiDetails','f', 'WITH', 'f.id = a.kpiDetailsId')
                ->leftjoin('InitialShippingBundle:ShipDetails','c', 'WITH', 'c.id = f.shipDetailsId')
                ->leftjoin('InitialShippingBundle:User','b','WITH','b.companyid = c.companyDetailsId')
                ->where('b.id = :userId')
                ->setParameter('userId',$userId)
                ->getQuery();
        }

        $rules = $query->getResult();

        return $this->render('rules/index.html.twig', array(
            'rules' => $rules,
        ));
    }

    /**
     * Creates a new Rules entity.
     *
     * @Route("/new", name="rules_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $user = $this->getUser();
        $id = $user->getId();
        $role = $this->container->get('security.context')->isGranted('ROLE_ADMIN');

        $rule = new Rules();
        $form = $this->createForm(new RulesType($id,$role), $rule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($rule);
            $em->flush();

            return $this->redirectToRoute('rules_show', array('id' => $rule->getId()));
        }

        return $this->render('rules/new.html.twig', array(
            'rule' => $rule,
            'form' => $form->createView(),
        ));
    }


    /**
     * Creates a new Rules entity.
     *
     * @Route("/new1", name="rules_new1")
     * @Method({"GET", "POST"})
     */
    public function new1Action(Request $request)
    {
        $params = $request->request->get('rules');
        $kpiDetailsId = $params['kpiDetailsId'];
        $elementDetailsId = $params['elementDetailsId'];
        $value = $params['rules'];
        $rul=$request->request->get('rules-1');

        $em = $this->getDoctrine()->getManager();
        $course1 = $em->getRepository('InitialShippingBundle:KpiDetails')->findOneBy(array('id'=>$kpiDetailsId));
        $course2 = $em->getRepository('InitialShippingBundle:ElementDetails')->findOneBy(array('id'=>$elementDetailsId));

        for($i=1;$i<=$value;$i++)
        {
            $rule = new Rules();
            $rule->setKpiDetailsId($course1);
            $rule->setElementDetailsId($course2);
            $variable = "rules-$i";
            $rules=$request->request->get($variable);
            $rule->setRules($rules);
            $em->persist($rule);
            $em->flush();
        }


        return $this->redirectToRoute('rules_show', array('id' => $rule->getId()));

    }


    /**
     * Finds and displays a Rules entity.
     *
     * @Route("/new_temp", name="rules_new_temp")
     */
    public function newtempAction(Request $request)
    {
        $id = $request->request->get('jsid');
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQueryBuilder()
            ->select('a.elementName,a.id')
            ->from('InitialShippingBundle:ElementDetails','a')
            ->leftjoin('InitialShippingBundle:KpiDetails','b', 'WITH', 'b.id = a.kpiDetailsId')
            ->where('b.id = :userId')
            ->setParameter('userId',$id)
            ->getQuery();
        $shipDetails = $query->getResult();

        $response = new JsonResponse();
        $response->setData(array('kpiNameArray' => $shipDetails));

        return $response;
    }


    /**
     * Finds and displays a Rules entity.
     *
     * @Route("/{id}", name="rules_show")
     * @Method("GET")
     */
    public function showAction(Rules $rule)
    {
        $deleteForm = $this->createDeleteForm($rule);

        return $this->render('rules/show.html.twig', array(
            'rule' => $rule,
            'delete_form' => $deleteForm->createView(),
        ));
    }


    /**
     * Displays a form to edit an existing Rules entity.
     *
     * @Route("/{id}/edit", name="rules_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Rules $rule)
    {
        $user = $this->getUser();
        $id = $user->getId();
        $role = $this->container->get('security.context')->isGranted('ROLE_ADMIN');

        $deleteForm = $this->createDeleteForm($rule);
        $editForm = $this->createForm(new RulesType($id,$role), $rule);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($rule);
            $em->flush();

            return $this->redirectToRoute('rules_edit', array('id' => $rule->getId()));
        }

        return $this->render('rules/edit.html.twig', array(
            'rule' => $rule,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Rules entity.
     *
     * @Route("/{id}", name="rules_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Rules $rule)
    {
        $form = $this->createDeleteForm($rule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($rule);
            $em->flush();
        }

        return $this->redirectToRoute('rules_index');
    }

    /**
     * Creates a form to delete a Rules entity.
     *
     * @param Rules $rule The Rules entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Rules $rule)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('rules_delete', array('id' => $rule->getId())))
            ->setMethod('DELETE')
            ->getForm()
            ;
    }
}