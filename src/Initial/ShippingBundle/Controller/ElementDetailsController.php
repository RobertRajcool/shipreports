<?php

namespace Initial\ShippingBundle\Controller;

use Initial\ShippingBundle\Entity\ElementRules;
use Initial\ShippingBundle\Tests\Controller\ElementRulesControllerTest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Initial\ShippingBundle\Entity\ElementDetails;
use Initial\ShippingBundle\Form\ElementDetailsType;

/**
 * ElementDetails controller.
 *
 * @Route("/elementdetails", name="elementdetails_index")
 */
class ElementDetailsController extends Controller
{
    /**
     * Lists all ElementDetails entities.
     *
     * @Route("/", name="elementdetails_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $elementDetails = $em->getRepository('InitialShippingBundle:ElementDetails')->findAll();

        return $this->render('elementdetails/index.html.twig', array(
            'elementDetails' => $elementDetails,
        ));
    }

    /**
     * Lists all ElementDetails entities.
     *
     * @Route("/{id}/select", name="elementdetails_select")
     * @Method("GET")
     */
    public function selectAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $elementDetails = $em->getRepository('InitialShippingBundle:ElementDetails')->findByKpiDetailsId($id);

        return $this->render('elementdetails/index.html.twig', array(
            'elementDetails' => $elementDetails,
        ));
    }


    /**
     * Lists all ElementDetails entities.
     *
     * @Route("/select", name="elementdetails_select1")
     * @Method("GET")
     */
    public function select1Action(Request $request)
    {
        $user = $this->getUser();
        $userId = $user->getId();
        $role=$this->container->get('security.context')->isGranted('ROLE_ADMIN');
        $em = $this->getDoctrine()->getManager();

        if($this->container->get('security.context')->isGranted('ROLE_ADMIN'))
        {
            $query = $em->createQueryBuilder()
                ->select('a')
                ->from('InitialShippingBundle:ElementDetails','a')
                ->leftjoin('InitialShippingBundle:KpiDetails','f', 'WITH', 'f.id = a.kpiDetailsId')
                ->leftjoin('InitialShippingBundle:ShipDetails','d', 'WITH', 'd.id = f.shipDetailsId')
                ->leftjoin('InitialShippingBundle:CompanyDetails','b', 'WITH', 'b.id = d.companyDetailsId')
                ->leftjoin('InitialShippingBundle:User','c','WITH','c.username = b.adminName')
                ->where('c.id = :userId')
                ->setParameter('userId',$userId)
                ->orderby('a.id')
                ->getQuery();
        }
        else
        {
            $query = $em->createQueryBuilder()
                ->select('a')
                ->from('InitialShippingBundle:ElementDetails','a')
                ->leftjoin('InitialShippingBundle:KpiDetails','f', 'WITH', 'f.id = a.kpiDetailsId')
                ->leftjoin('InitialShippingBundle:ShipDetails','c', 'WITH', 'c.id = f.shipDetailsId')
                ->leftjoin('InitialShippingBundle:User','b','WITH','b.companyid = c.companyDetailsId')
                ->where('b.id = :userId')
                ->setParameter('userId',$userId)
                ->orderby('a.id')
                ->getQuery();
        }

        $elementdetail = new ElementDetails();
        $form = $this->createForm(new ElementDetailsType($userId,$role), $elementdetail);
        $form->handleRequest($request);

        $elementDetails = $query->getResult();
        $count = count($elementDetails);

        return $this->render('elementdetails/index.html.twig', array(
            'elementDetails' => $elementDetails,
            'element_count' => $count,
            'elementDetail' => $elementdetail,
            'form' => $form->createView(),
        ));
    }


    /**
     * Creates a new elementDetails entity.
     *
     * @Route("/new", name="elementdetails_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $user = $this->getUser();
        $id = $user->getId();
        $role=$this->container->get('security.context')->isGranted('ROLE_ADMIN');

        $elementdetails = new ElementDetails();
        $form = $this->createForm(new ElementDetailsType($id,$role), $elementdetails);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($elementdetails);
            $em->flush();

            return $this->redirectToRoute('elementdetails_show', array('id' => $elementdetails->getId()));
        }

        return $this->render('elementdetails/new.html.twig', array(
            'elementDetail' => $elementdetails,
            'form' => $form->createView(),
        ));
    }

    /**
     * Creates a new ElementDetails entity.
     *
     * @Route("/new1", name="elementdetails_new1")
     * @Method({"GET", "POST"})
     */
    public function new1Action(Request $request)
    {
        $params = $request->request->get('element_details');
        $kpiDetailsId  = $params['kpiDetailsId'];
        $elementName   = $params['elementName'];
        $description   = $params['description'];
        $cellName      = $params['cellName'];
        $cellDetails   = $params['cellDetails'];
        $activeMonth = $request->request->get('activeMonth');
        $activeYear = $request->request->get('activeYear');;
        $endMonth = $request->request->get('endMonth');;
        $endYear = $request->request->get('endYear');;
        $day = 1;

        $monthtostring=$activeYear.'-'.$activeMonth.'-'.$day;
        $new_date=new \DateTime($monthtostring);
        $monthtostring1=$endYear.'-'.$endMonth.'-'.$day;
        $new_date1=new \DateTime($monthtostring1);

        $weightage     = $params['weightage'];
        $rules         = $request->request->get('value');

        $course = $this->getDoctrine()->getManager()->getRepository('InitialShippingBundle:KpiDetails')->findOneBy(array('id'=>$kpiDetailsId));

        $elementDetail = new ElementDetails();
        $elementDetail->setkpiDetailsId($course);
        $elementDetail->setelementName($elementName);
        $elementDetail->setdescription($description);
        $elementDetail->setcellName($cellName);
        $elementDetail->setcellDetails($cellDetails);
        $elementDetail->setactivatedDate($new_date);
        $elementDetail->setendDate($new_date1);
        $elementDetail->setweightage($weightage);
        $elementDetail->setrules($rules);

        $em = $this->getDoctrine()->getManager();
        $em->persist($elementDetail);
        $em->flush();

        $id = $elementDetail->getId();

        for ($i=1;$i<=$rules;$i++)
        {
            $variable = "rules-$i";
            $engine_rules = $request->request->get($variable);
            if($engine_rules!="")
            {
                $elementRules = new ElementRules();
                $elementRules->setElementDetailsId($this->getDoctrine()->getManager()->getRepository('InitialShippingBundle:ElementDetails')->findOneBy(array('id'=>$id)));
                $elementRules->setRules($engine_rules);
                $em->persist($elementRules);
                $em->flush();
            }
        }
        return $this->redirectToRoute('elementdetails_select1');
    }


    /**
     * Creates a new elementDetails entity.
     *
     * @Route("/element_rule", name="elementdetails_element_rule")
     */
    public function element_ruleAction(Request $request,$hi='')
    {
        $id = $request->request->get('Id');
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQueryBuilder()
            ->select('a.rules')
            ->from('InitialShippingBundle:ElementRules','a')
            ->where('a.elementDetailsId = :element_Id')
            ->setParameter('element_Id',$id)
            ->getQuery();

        $element_rules = $query->getResult();
        if($hi=='hi')
        {
            return $element_rules;
        }
        $response = new JsonResponse();
        $response->setData(array('Rule_Array' => $element_rules));

        return $response;
    }


    /**
     * Finds and displays a KpiDetails entity.
     *
     * @Route("/element_ajax_weightage", name="elementdetails_element_ajax_weightage")
     */
    public function element_ajax_weightageAction(Request $request)
    {
        $weightage = $request->request->get('weightage');
        $kpiId = $request->request->get('kpiDetailsId');
        $elementId = $request->request->get('elementId');
        $status = $request->request->get('status');
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQueryBuilder()
            ->select('a.id','a.elementName','a.weightage')
            ->from('InitialShippingBundle:ElementDetails','a')
            ->where('a.kpiDetailsId = :kpi_id')
            ->setParameter('kpi_id',$kpiId)
            ->getQuery()
            ->getResult();

        $sum = $weightage;

        for($i=0;$i<count($query);$i++)
        {
            if($query[$i]['id']==$elementId && $status==0) {
                $query[$i]['weightage'] = 0;
            }
            $sum = $sum + $query[$i]['weightage'];
        }

        $response = new JsonResponse();
        $response->setData(array('Weightage' => $sum));

        return $response;
    }

    /**
     * Finds and displays a KpiDetails entity.
     *
     * @Route("/ajax_show", name="elementdetails_ajax_show")
     */
    public function ajax_showAction(Request $request,$hi='')
    {
        $id = $request->request->get('Id');
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $userId = $user->getId();

        if($this->container->get('security.context')->isGranted('ROLE_ADMIN'))
        {
            $kpi_name_array_query = $em->createQueryBuilder()
                ->select('a.id','a.kpiName')
                ->from('InitialShippingBundle:KpiDetails','a')
                ->leftjoin('InitialShippingBundle:ShipDetails','d', 'WITH', 'd.id = a.shipDetailsId')
                ->leftjoin('InitialShippingBundle:CompanyDetails','b', 'WITH', 'b.id = d.companyDetailsId')
                ->leftjoin('InitialShippingBundle:User','c','WITH','c.username = b.adminName')
                ->where('c.id = :userId')
                ->groupby('a.kpiName')
                ->setParameter('userId',$userId)
                ->getQuery();
        }
        else
        {
            $kpi_name_array_query = $em->createQueryBuilder()
                ->select('a.id','a.kpiName')
                ->from('InitialShippingBundle:KpiDetails','a')
                ->leftjoin('InitialShippingBundle:ShipDetails','c', 'WITH', 'c.id = a.shipDetailsId')
                ->leftjoin('InitialShippingBundle:User','b','WITH','b.companyid = c.companyDetailsId')
                ->where('b.id = :userId')
                ->groupby('a.kpiName')
                ->setParameter('userId',$userId)
                ->getQuery();
        }

        $kpi_name_array = $kpi_name_array_query->getResult();

        $query = $em->createQueryBuilder()
            ->select('identity(a.kpiDetailsId)')
            ->from('InitialShippingBundle:ElementDetails','a')
            ->where('a.id = :element_id')
            ->setParameter('element_id',$id)
            ->getQuery();
        $kpi_name_id = $query->getResult();

        $kpi_name = $em->createQueryBuilder()
            ->select('a.kpiName','a.id')
            ->from('InitialShippingBundle:KpiDetails','a')
            ->where('a.id = :kpi_id')
            ->setParameter('kpi_id',$kpi_name_id[0][1])
            ->getQuery()
            ->getResult();

        $query1 = $em->createQueryBuilder()
            ->select('a.id','a.elementName','a.weightage','a.activatedDate','a.endDate','a.cellName','a.cellDetails','a.description')
            ->from('InitialShippingBundle:ElementDetails','a')
            ->where('a.id = :element_id')
            ->setParameter('element_id',$id)
            ->getQuery();
        $elementDetail = $query1->getResult();

        $rules = $this->element_ruleAction($request,'hi');

        $response = new JsonResponse();
        $response->setData(array(
            'element_detail' =>$elementDetail,
            'element_rules' => $rules,
            'kpi_name' => $kpi_name,
            'kpi_name_array' => $kpi_name_array
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
     * @Route("/ajax_element_edit", name="kpidetails_ajax_element_edit")
     */
    public function ajax_element_editAction(Request $request)
    {
        $id = $request->request->get('Id');
        $kpiName_id = $request->request->get('kpiDetailsId');
        $elementName = $request->request->get('elementName');
        $weightage = $request->request->get('weightage');
        $description = $request->request->get('description');
        $cellName = $request->request->get('cellName');
        $cellDetails = $request->request->get('cellDetails');
        $activeMonth = $request->request->get('activeMonth');
        $integerActiveMonth = (int)$activeMonth+1;
        $activeYear = $request->request->get('activeYear');
        $endMonth = $request->request->get('endMonth');
        $endYear = $request->request->get('endYear');
        $integerEndMonth = (int)$endMonth+1;
        $activeMonthDate = $activeYear .'-'. $integerActiveMonth .'-'. '01';
        $activeMonthDateObject = new \DateTime($activeMonthDate);
        $activeMonthDateObject->modify("last day of this month");
        $endMonthDate = $endYear .'-'. $integerEndMonth .'-'. '01';
        $endMonthDateObject = new \DateTime($endMonthDate);
        $endMonthDateObject->modify("last day of this month");

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('InitialShippingBundle:ElementDetails')->find($id);
        $kpiName_obj = $this->getDoctrine()->getManager()->getRepository('InitialShippingBundle:KpiDetails')->findOneBy(array('id'=>$kpiName_id));

        $elementDetail = new ElementDetails();
        $entity->setkpiDetailsId($kpiName_obj);
        $entity->setelementName($elementName);
        $entity->setDescription($description);
        $entity->setWeightage($weightage);
        $entity->setCellName($cellName);
        $entity->setCellDetails($cellDetails);
        $entity->setActivatedDate($activeMonthDateObject);
        $entity->setEndDate($endMonthDateObject);
        $em->flush();

        $show_response = $this->ajax_showAction($request,'hi');

        return $show_response;

    }


    /**
     * Creates a new elementDetails entity.
     *
     * @Route("/{id}/element_rule1", name="elementdetails_element_rule1")
     */
    public function element_rule1Action(Request $request)
    {
        $id = $request->request->get('Id');
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQueryBuilder()
            ->select('a.rules')
            ->from('InitialShippingBundle:ElementRules','a')
            ->where('a.elementDetailsId = :element_Id')
            ->setParameter('element_Id',$id)
            ->getQuery();

        $element_rules = $query->getResult();
        $response = new JsonResponse();
        $response->setData(array('Rule_Array' => $element_rules));

        return $response;
    }


    /**
     * Creates a new elementDetails entity.
     *
     * @Route("/element_rule_edit", name="elementdetails_element_rule_edit")
     */
    public function elementruleeditAction(Request $request)
    {
        $id = $request->request->get('element_Id');
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQueryBuilder()
            ->select('a.rules')
            ->from('InitialShippingBundle:ElementRules','a')
            ->where('a.elementDetailsId = :element_Id')
            ->setParameter('element_Id',$id)
            ->getQuery();

        $element_rules = $query->getResult();
        $response = new JsonResponse();
        $response->setData(array('Element_Rule_Array' => $element_rules));

        return $response;
    }


    /**
     * Finds and displays a ElementDetails entity.
     *
     * @Route("/{id}", name="elementdetails_show")
     * @Method("GET")
     */
    public function showAction(ElementDetails $elementDetail)
    {
        $deleteForm = $this->createDeleteForm($elementDetail);

        return $this->render('elementdetails/show.html.twig', array(
            'elementDetail' => $elementDetail,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing ElementDetails entity.
     *
     * @Route("/{id}/edit", name="elementdetails_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, ElementDetails $elementDetail)
    {
        $user = $this->getUser();
        $id = $user->getId();
        $role=$this->container->get('security.context')->isGranted('ROLE_ADMIN');

        $deleteForm = $this->createDeleteForm($elementDetail);
        $editForm = $this->createForm(new ElementDetailsType($id,$role), $elementDetail);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($elementDetail);
            $em->flush();

            return $this->redirectToRoute('elementdetails_edit', array('id' => $elementDetail->getId()));
        }

        return $this->render('elementdetails/edit.html.twig', array(
            'elementDetail' => $elementDetail,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing ElementDetails entity.
     *
     * @Route("/edit1", name="elementdetails_edit1")
     * @Method({"GET", "POST"})
     */
    public function edit1Action(Request $request)
    {
        $user = $this->getUser();
        $params = $request->request->get('element_details');
        $kpiDetailsId  = $params['kpiDetailsId'];
        $elementName   = $params['elementName'];
        $description   = $params['description'];
        $cellName      = $params['cellName'];
        $cellDetails   = $params['cellDetails'];
        $activatedDate = $params['activatedDate'];
        $endDate       = $params['endDate'];
        $count = $request->request->get('id');
        $rule11= $request->request->get('rule_name');

        $monthtostring=$activatedDate['year'].'-'.$activatedDate['month'].'-'.$activatedDate['day'];
        $new_date=new \DateTime($monthtostring);
        $monthtostring1=$endDate['year'].'-'.$endDate['month'].'-'.$endDate['day'];
        $new_date1=new \DateTime($monthtostring1);

        $weightage     = $params['weightage'];
        $rules         = $params['rules'];

        $course = $this->getDoctrine()->getManager()->getRepository('InitialShippingBundle:KpiDetails')->findOneBy(array('id'=>$kpiDetailsId));

        $em = $this->getDoctrine()->getManager();

        $element_id_array= $em->createQueryBuilder()
            ->select('a.id')
            ->from('InitialShippingBundle:ElementDetails','a')
            ->where('a.elementName = :element_name')
            ->setParameter('element_name',$elementName)
            ->getQuery()
            ->getResult();

        for($j=1;$j<count($element_id_array);$j++)
        {
            $elementDetail = $em->getRepository('InitialShippingBundle:ElementDetails')->find($element_id_array[$j]);

            $elementDetail1 = new ElementDetails();
            $elementDetail->setkpiDetailsId($course);
            $elementDetail->setelementName($elementName);
            $elementDetail->setdescription($description);
            $elementDetail->setcellName($cellName);
            $elementDetail->setcellDetails($cellDetails);
            $elementDetail->setactivatedDate($new_date);
            $elementDetail->setendDate($new_date1);
            $elementDetail->setweightage($weightage);
            $elementDetail->setrules($rules);

            $em = $this->getDoctrine()->getManager();
            $em->flush();
        }

        $element_rules_id_array= $em->createQueryBuilder()
            ->select('a.id')
            ->from('InitialShippingBundle:ElementRules','a')
            ->where('a.elementDetailsId = :element_id')
            ->setParameter('element_id',$element_id_array[1]['id'])
            ->getQuery()
            ->getResult();

        if($count==1)
        {
            for($i=0;$i<count($rule11);$i++)
            {
                $element_rules_obj = $em->getRepository('InitialShippingBundle:ElementRules')->find($element_rules_id_array[$i]);
                $element_obj= $em->getRepository('InitialShippingBundle:KpiDetails')->findOneBy(array('id'=>$element_id_array[0]));

                $element_rules_obj->setRules($rule11[$i]);
                $element_rules_obj->setKpiDetailsId($element_obj);
                $em->flush();
            }
        }
        return $this->redirectToRoute('elementdetails_select1');

    }

    /**
     * Deletes a ElementDetails entity.
     *
     * @Route("/{id}", name="elementdetails_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, ElementDetails $elementDetail)
    {
        $form = $this->createDeleteForm($elementDetail);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($elementDetail);
            $em->flush();
        }

        return $this->redirectToRoute('elementdetails_index');
    }

    /**
     * Creates a form to delete a ElementDetails entity.
     *
     * @param ElementDetails $elementDetail The ElementDetails entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(ElementDetails $elementDetail)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('elementdetails_delete', array('id' => $elementDetail->getId())))
            ->setMethod('DELETE')
            ->getForm()
            ;
    }
}