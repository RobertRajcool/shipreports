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
                ->from('InitialShippingBundle:Rules','a')
                ->leftjoin('InitialShippingBundle:KpiDetails','f', 'WITH', 'f.id = a.kpiDetailsId')
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
                ->from('InitialShippingBundle:Rules','a')
                ->leftjoin('InitialShippingBundle:KpiDetails','f', 'WITH', 'f.id = a.kpiDetailsId')
                ->leftjoin('InitialShippingBundle:ShipDetails','c', 'WITH', 'c.id = f.shipDetailsId')
                ->leftjoin('InitialShippingBundle:User','b','WITH','b.companyid = c.companyDetailsId')
                ->where('b.id = :userId')
                ->setParameter('userId',$userId)
                ->groupby('a.elementDetailsId')
                ->getQuery();
        }

        $rules = $query->getResult();
        $count = count($rules);

        $rule = new Rules();
        $form = $this->createForm(new RulesType($userId,$role), $rule);
        $form->handleRequest($request);

        return $this->render('rules/index.html.twig', array(
            'rules' => $rules,
            'rule' => $rule,
            'form' => $form->createView(),
            'rule_count' => $count
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
        $value = $request->request->get('value');
        $rul=$request->request->get('rules-1');

        $em = $this->getDoctrine()->getManager();
        $course1 = $em->getRepository('InitialShippingBundle:KpiDetails')->findOneBy(array('id'=>$kpiDetailsId));
        $course2 = $em->getRepository('InitialShippingBundle:ElementDetails')->findOneBy(array('id'=>$elementDetailsId));

        for($i=1;$i<=$value;$i++)
        {
            $variable = "rules-$i";
            $rules=$request->request->get($variable);
            if($rules!="")
            {
                $rule = new Rules();
                $rule->setKpiDetailsId($course1);
                $rule->setElementDetailsId($course2);
                $rule->setRules($rules);
                $em->persist($rule);
                $em->flush();
            }
        }

        return $this->redirectToRoute('rules_select');
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
     * Finds and displays a KpiDetails entity.
     *
     * @Route("/rule_ajax_show", name="rules_rule_ajax_show")
     */
    public function rule_ajax_showAction(Request $request,$hi='')
    {
        $id = $request->request->get('Id');
        $em = $this->getDoctrine()->getManager();

        $element_kpi_id = $em->createQueryBuilder()
            ->select('identity(a.elementDetailsId)','identity(a.kpiDetailsId)')
            ->from('InitialShippingBundle:Rules','a')
            ->where('a.id = :rule_id')
            ->setParameter('rule_id',$id)
            ->getQuery()
            ->getResult();

        $element_name = $em->createQueryBuilder()
            ->select('a.elementName')
            ->from('InitialShippingBundle:ElementDetails','a')
            ->where('a.id = :element_id')
            ->setParameter('element_id',$element_kpi_id[0][1])
            ->getQuery()
            ->getResult();

        $element_array = $em->createQueryBuilder()
            ->select('a.elementName,a.id')
            ->from('InitialShippingBundle:ElementDetails','a')
            ->getQuery()
            ->getResult();

        $kpi_name = $em->createQueryBuilder()
            ->select('a.kpiName')
            ->from('InitialShippingBundle:KpiDetails','a')
            ->where('a.id = :kpi_id')
            ->setParameter('kpi_id',$element_kpi_id[0][2])
            ->getQuery()
            ->getResult();

        $kpi_array = $em->createQueryBuilder()
            ->select('a.kpiName,a.id')
            ->from('InitialShippingBundle:KpiDetails','a')
            ->groupby('a.kpiName')
            ->getQuery()
            ->getResult();

        $rule_id_array = $em->createQueryBuilder()
            ->select('a.id')
            ->from('InitialShippingBundle:Rules','a')
            ->where('a.elementDetailsId = :element_id')
            ->setParameter('element_id',$element_kpi_id[0][1])
            ->getQuery()
            ->getResult();

        for($i=0;$i<count($rule_id_array);$i++)
        {
            $rules_query_array = $em->createQueryBuilder()
                ->select('a.rules')
                ->from('InitialShippingBundle:Rules','a')
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
     * @Route("/rule_ajax_edit", name="rules_rule_ajax_edit")
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
            ->from('InitialShippingBundle:Rules','a')
            ->where('a.id = :rule_id')
            ->setParameter('rule_id',$id)
            ->getQuery()
            ->getResult();

        $rule_id_array = $em->createQueryBuilder()
            ->select('a.id')
            ->from('InitialShippingBundle:Rules','a')
            ->where('a.elementDetailsId = :element_id')
            ->setParameter('element_id',$element_kpi_id[0][1])
            ->getQuery()
            ->getResult();

        if(count($rules_array)!=NULL)
        {
            for($i=0;$i<count($rule_id_array);$i++)
            {
                $rules_obj = $em->getRepository('InitialShippingBundle:Rules')->find($rule_id_array[$i]['id']);
                $kpi_obj= $em->getRepository('InitialShippingBundle:KpiDetails')->findOneBy(array('id'=>$kpiName));
                $element_obj= $em->getRepository('InitialShippingBundle:ElementDetails')->findOneBy(array('id'=>$elementName));

                $rule = new Rules();
                $rules_obj->setKpiDetailsId($kpi_obj);
                $rules_obj->setElementDetailsId($element_obj);
                $rules_obj->setRules($rules_array[$i]);
                $em->flush();
            }
        }
        else
        {
            $rules_obj = $em->getRepository('InitialShippingBundle:KpiRules')->find($rule_id_array[$id]);
            $kpi_obj= $em->getRepository('InitialShippingBundle:KpiDetails')->findOneBy(array('id'=>$kpiName));
            $element_obj= $em->getRepository('InitialShippingBundle:ElementDetails')->findOneBy(array('id'=>$elementName));

            $rule = new Rules();
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
     * @Route("/rule1", name="rules_rule1")
     */
    public function rule1Action(Request $request)
    {
        $id = $request->request->get('Id');
        $em = $this->getDoctrine()->getManager();

        $ids = $em->createQueryBuilder()
            ->select('identity(a.elementDetailsId)')
            ->from('InitialShippingBundle:Rules','a')
            ->where('a.id = :rule_Id')
            ->setParameter('rule_Id',$id)
            ->getQuery()
            ->getResult();


        $query = $em->createQueryBuilder()
            ->select('a.rules')
            ->from('InitialShippingBundle:Rules','a')
            ->where('a.elementDetailsId = :element_id')
            ->setParameter('element_id',$ids)
            ->getQuery()
            ->getResult();

        $response = new JsonResponse();
        $response->setData(array('Rule_Array' => $query));

        return $response;
    }


    /**
     * Finds and displays a Rules entity.
     *
     * @Route("/{id}/rule", name="rules_rule")
     */
    public function ruleAction(Request $request)
    {
        $id = $request->request->get('Id');
        $em = $this->getDoctrine()->getManager();

        $ids = $em->createQueryBuilder()
            ->select('identity(a.elementDetailsId)')
            ->from('InitialShippingBundle:Rules','a')
            ->where('a.id = :rule_Id')
            ->setParameter('rule_Id',$id)
            ->getQuery()
            ->getResult();


        $query = $em->createQueryBuilder()
            ->select('a.rules')
            ->from('InitialShippingBundle:Rules','a')
            ->where('a.elementDetailsId = :element_id')
            ->setParameter('element_id',$ids)
            ->getQuery()
            ->getResult();

        $response = new JsonResponse();
        $response->setData(array('Rule_Array' => $query));

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
     * Displays a form to edit an existing Rules entity.
     *
     * @Route("/edit1", name="rules_edit1")
     * @Method({"GET", "POST"})
     */
    public function edit1Action(Request $request)
    {
        //echo 'hi';
        $params = $request->request->get('rules');
        $kpi_id = $params['kpiDetailsId'];
        $element_id = $params['elementDetailsId'];
        $value = $request->request->get('id');
        $rule = $request->request->get('rule_name');

        $em = $this->getDoctrine()->getManager();

        //rules id finding
        $rules_id_array= $em->createQueryBuilder()
            ->select('a.id')
            ->from('InitialShippingBundle:Rules','a')
            ->where('a.elementDetailsId = :id')
            ->setParameter('id',$element_id)
            ->getQuery()
            ->getResult();


        for($j=0;$j<count($rules_id_array);$j++)
        {
            $entity = $em->getRepository('InitialShippingBundle:Rules')->find($rules_id_array[$j]);
            $kpi_obj= $em->getRepository('InitialShippingBundle:KpiDetails')->findOneBy(array('id'=>$kpi_id));
            $element_obj= $em->getRepository('InitialShippingBundle:ElementDetails')->findOneBy(array('id'=>$element_id));
            $vv = $rule[$j];
            $rule = new Rules();
            $entity->setKpiDetailsId($kpi_obj);
            $entity->setElementDetailsId($element_obj);
            $entity->setRules($vv);
            $em->flush();
        }
        return $this->redirectToRoute('rules_select');

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