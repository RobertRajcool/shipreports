<?php

namespace Initial\ShippingBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Initial\ShippingBundle\Entity\RankingRules;
use Initial\ShippingBundle\Form\RankingRulesType;
use Symfony\Component\HttpFoundation\JsonResponse;


/**
 * RankingRules controller.
 *
 * @Route("/rankingrules")
 */
class RankingRulesController extends Controller
{
    /**
     * Lists all RankingRules entities.
     *
     * @Route("/", name="rankingrules_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $rankingRules = $em->getRepository('InitialShippingBundle:RankingRules')->findAll();

        return $this->render('rankingrules/index.html.twig', array(
            'rankingRules' => $rankingRules,
        ));
    }


    /**
     * Lists all Rules entities.
     *
     * @Route("/select", name="rankingrules_select")
     * @Method("GET")
     */
    public function selectAction(Request $request)
    {
        $user = $this->getUser();
        $userId = $user->getId();
        $role = $this->container->get('security.context')->isGranted('ROLE_ADMIN');

        $em = $this->getDoctrine()->getManager();

        if($this->container->get('security.context')->isGranted('ROLE_ADMIN'))
        {
            $query = $em->createQueryBuilder()
                ->select('a')
                ->from('InitialShippingBundle:RankingRules','a')
                ->leftjoin('InitialShippingBundle:RankingKpiDetails','f', 'WITH', 'f.id = a.kpiDetailsId')
                ->leftjoin('InitialShippingBundle:ShipDetails','d', 'WITH', 'd.id = f.shipDetailsId')
                ->leftjoin('InitialShippingBundle:CompanyDetails','b', 'WITH', 'b.id = d.companyDetailsId')
                ->leftjoin('InitialShippingBundle:User','c','WITH','c.username = b.adminName')
                ->where('c.id = :userId')
                ->setParameter('userId',$userId)
                ->groupby('a.elementDetailsId')
                ->getQuery();
        }
        else
        {
            $query = $em->createQueryBuilder()
                ->select('a')
                ->from('InitialShippingBundle:RankingRules','a')
                ->leftjoin('InitialShippingBundle:RankingKpiDetails','f', 'WITH', 'f.id = a.kpiDetailsId')
                ->leftjoin('InitialShippingBundle:ShipDetails','c', 'WITH', 'c.id = f.shipDetailsId')
                ->leftjoin('InitialShippingBundle:User','b','WITH','b.companyid = c.companyDetailsId')
                ->where('b.id = :userId')
                ->setParameter('userId',$userId)
                ->groupby('a.elementDetailsId')
                ->getQuery();
        }

        $rules = $query->getResult();
        $count = count($rules);

        $rule = new RankingRules();
        $form = $this->createForm(new RankingRulesType($userId,$role), $rule);
        $form->handleRequest($request);

        return $this->render('rankingrules/index.html.twig', array(
            'rules' => $rules,
            'rule' => $rule,
            'form' => $form->createView(),
            'rule_count' => $count
        ));
    }



    /**
     * Creates a new RankingRules entity.
     *
     * @Route("/new", name="rankingrules_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $rankingRule = new RankingRules();
        $form = $this->createForm('Initial\ShippingBundle\Form\RankingRulesType', $rankingRule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($rankingRule);
            $em->flush();

            return $this->redirectToRoute('rankingrules_show', array('id' => $rankingRule->getId()));
        }

        return $this->render('rankingrules/new.html.twig', array(
            'rankingRule' => $rankingRule,
            'form' => $form->createView(),
        ));
    }


    /**
     * Creates a new Rules entity.
     *
     * @Route("/new1", name="rankingrules_new1")
     * @Method({"GET", "POST"})
     */
    public function new1Action(Request $request)
    {
        $params = $request->request->get('ranking_rules');
        $kpiDetailsId = $params['kpiDetailsId'];
        $elementDetailsId = $params['elementDetailsId'];
        $value = $request->request->get('value');
        //$rul=$request->request->get('rules-1');

        $em = $this->getDoctrine()->getManager();
        $course1 = $em->getRepository('InitialShippingBundle:RankingKpiDetails')->findOneBy(array('id'=>$kpiDetailsId));
        $course2 = $em->getRepository('InitialShippingBundle:RankingElementDetails')->findOneBy(array('id'=>$elementDetailsId));

        for($i=1;$i<=$value;$i++)
        {
            $variable = "rules-$i";
            $rules=$request->request->get($variable);
            if($rules!="")
            {
                $rule = new RankingRules();
                $rule->setKpiDetailsId($course1);
                $rule->setElementDetailsId($course2);
                $rule->setRules($rules);
                $em->persist($rule);
                $em->flush();
            }
        }

        return $this->redirectToRoute('rankingrules_select');
    }


    /**
     * Finds and displays a KpiDetails entity.
     *
     * @Route("/rule_ajax_show", name="rankingrules_rule_ajax_show")
     */
    public function rule_ajax_showAction(Request $request,$hi='')
    {
        $id = $request->request->get('Id');
        $em = $this->getDoctrine()->getManager();

        $element_kpi_id = $em->createQueryBuilder()
            ->select('identity(a.elementDetailsId)','identity(a.kpiDetailsId)')
            ->from('InitialShippingBundle:RankingRules','a')
            ->where('a.id = :rule_id')
            ->setParameter('rule_id',$id)
            ->getQuery()
            ->getResult();

        $element_name = $em->createQueryBuilder()
            ->select('a.elementName')
            ->from('InitialShippingBundle:RankingElementDetails','a')
            ->where('a.id = :element_id')
            ->setParameter('element_id',$element_kpi_id[0][1])
            ->getQuery()
            ->getResult();

        $element_array = $em->createQueryBuilder()
            ->select('a.elementName,a.id')
            ->from('InitialShippingBundle:RankingElementDetails','a')
            ->getQuery()
            ->getResult();

        $kpi_name = $em->createQueryBuilder()
            ->select('a.kpiName')
            ->from('InitialShippingBundle:RankingKpiDetails','a')
            ->where('a.id = :kpi_id')
            ->setParameter('kpi_id',$element_kpi_id[0][2])
            ->getQuery()
            ->getResult();

        $kpi_array = $em->createQueryBuilder()
            ->select('a.kpiName,a.id')
            ->from('InitialShippingBundle:RankingKpiDetails','a')
            ->groupby('a.kpiName')
            ->getQuery()
            ->getResult();

        $rule_id_array = $em->createQueryBuilder()
            ->select('a.id')
            ->from('InitialShippingBundle:RankingRules','a')
            ->where('a.elementDetailsId = :element_id')
            ->setParameter('element_id',$element_kpi_id[0][1])
            ->getQuery()
            ->getResult();

        for($i=0;$i<count($rule_id_array);$i++)
        {
            $rules_query_array = $em->createQueryBuilder()
                ->select('a.rules')
                ->from('InitialShippingBundle:RankingRules','a')
                ->where('a.id = :rule_id')
                ->setParameter('rule_id',$rule_id_array[$i]['id'])
                ->getQuery();
                $rules_array[$i]=$rules_query_array->getResult();
        }

        $response = new JsonResponse();
        $response->setData(array(
            'rule_id' => $id,
            'element_kpi_id' => $element_kpi_id,
            'element_name' => $element_name,
            'kpi_name' => $kpi_name,
            'rules' => $rules_array,
            'element_array' => $element_array,
            'kpi_array' => $kpi_array
        ));
        if($hi=='hi')
        {
            return $response;
        }
        return $response;

    }


    /**
     * Finds and displays a KpiDetails entity.
     *
     * @Route("/rule_ajax_edit", name="rankingrules_rule_ajax_edit")
     */
    public function rule_ajax_editAction(Request $request)
    {
        $id = $request->request->get('Id');
        $kpiName = $request->request->get('kpiName');
        $elementName = $request->request->get('elementName');
        $rules_array = $request->request->get('rules');

        $em = $this->getDoctrine()->getManager();

        $element_kpi_id = $em->createQueryBuilder()
            ->select('identity(a.elementDetailsId)','identity(a.kpiDetailsId)')
            ->from('InitialShippingBundle:RankingRules','a')
            ->where('a.id = :rule_id')
            ->setParameter('rule_id',$id)
            ->getQuery()
            ->getResult();

        $rule_id_array = $em->createQueryBuilder()
            ->select('a.id')
            ->from('InitialShippingBundle:RankingRules','a')
            ->where('a.elementDetailsId = :element_id')
            ->setParameter('element_id',$element_kpi_id[0][1])
            ->getQuery()
            ->getResult();

        if(count($rules_array)!=NULL)
        {
            for($i=0;$i<count($rule_id_array);$i++)
            {
                $rules_obj = $em->getRepository('InitialShippingBundle:RankingRules')->find($rule_id_array[$i]['id']);
                $kpi_obj= $em->getRepository('InitialShippingBundle:RankingKpiDetails')->findOneBy(array('id'=>$kpiName));
                $element_obj= $em->getRepository('InitialShippingBundle:RankingElementDetails')->findOneBy(array('id'=>$elementName));

                $rule = new RankingRules();
                $rules_obj->setKpiDetailsId($kpi_obj);
                $rules_obj->setElementDetailsId($element_obj);
                $rules_obj->setRules($rules_array[$i]);
                $em->flush();
            }
        }
        else
        {
            $rules_obj = $em->getRepository('InitialShippingBundle:RankingKpiRules')->find($rule_id_array[$id]);
            $kpi_obj= $em->getRepository('InitialShippingBundle:RankingKpiDetails')->findOneBy(array('id'=>$kpiName));
            $element_obj= $em->getRepository('InitialShippingBundle:RankingElementDetails')->findOneBy(array('id'=>$elementName));

            $rule = new RankingRules();
            $rules_obj->setKpiDetailsId($kpi_obj);
            $rules_obj->setElementDetailsId($element_obj);
            $em->flush();
        }

        $show_response = $this->rule_ajax_showAction($request,'hi');

        return $show_response;
    }



    /**
     * Finds and displays a Rules entity.
     *
     * @Route("/new_temp", name="rankingrules_new_temp")
     */
    public function newtempAction(Request $request)
    {
        $id = $request->request->get('jsid');
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQueryBuilder()
            ->select('a.elementName,a.id')
            ->from('InitialShippingBundle:RankingElementDetails','a')
            ->leftjoin('InitialShippingBundle:RankingKpiDetails','b', 'WITH', 'b.id = a.kpiDetailsId')
            ->where('b.id = :userId')
            ->setParameter('userId',$id)
            ->getQuery();
        $shipDetails = $query->getResult();

        $response = new JsonResponse();
        $response->setData(array('kpiNameArray' => $shipDetails));

        return $response;
    }


    /**
     * Finds and displays a RankingRules entity.
     *
     * @Route("/{id}", name="rankingrules_show")
     * @Method("GET")
     */
    public function showAction(RankingRules $rankingRule)
    {
        $deleteForm = $this->createDeleteForm($rankingRule);

        return $this->render('rankingrules/show.html.twig', array(
            'rankingRule' => $rankingRule,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing RankingRules entity.
     *
     * @Route("/{id}/edit", name="rankingrules_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, RankingRules $rankingRule)
    {
        $deleteForm = $this->createDeleteForm($rankingRule);
        $editForm = $this->createForm('Initial\ShippingBundle\Form\RankingRulesType', $rankingRule);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($rankingRule);
            $em->flush();

            return $this->redirectToRoute('rankingrules_edit', array('id' => $rankingRule->getId()));
        }

        return $this->render('rankingrules/edit.html.twig', array(
            'rankingRule' => $rankingRule,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a RankingRules entity.
     *
     * @Route("/{id}", name="rankingrules_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, RankingRules $rankingRule)
    {
        $form = $this->createDeleteForm($rankingRule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($rankingRule);
            $em->flush();
        }

        return $this->redirectToRoute('rankingrules_index');
    }

    /**
     * Creates a form to delete a RankingRules entity.
     *
     * @param RankingRules $rankingRule The RankingRules entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(RankingRules $rankingRule)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('rankingrules_delete', array('id' => $rankingRule->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
