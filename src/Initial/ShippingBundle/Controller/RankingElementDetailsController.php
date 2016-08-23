<?php

namespace Initial\ShippingBundle\Controller;

use Initial\ShippingBundle\Entity\RankingElementWeightageStatus;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Initial\ShippingBundle\Entity\RankingElementDetails;
use Initial\ShippingBundle\Form\RankingElementDetailsType;
use Initial\ShippingBundle\Entity\RankingElementComparisonRules;
use Symfony\Component\HttpFoundation\JsonResponse;
use Initial\ShippingBundle\Entity\ElementSymbols;

/**
 * RankingElementDetails controller.
 *
 * @Route("/rankingelementdetails")
 */
class RankingElementDetailsController extends Controller
{
    /**
     * Lists all RankingElementDetails entities.
     *
     * @Route("/", name="rankingelementdetails_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $rankingElementDetails = $em->getRepository('InitialShippingBundle:RankingElementDetails')->findAll();

        return $this->render('rankingelementdetails/index.html.twig', array(
            'rankingElementDetails' => $rankingElementDetails,
        ));
    }

    /**
     * Finds and displays a KpiDetails entity.
     *
     * @Route("/check_elementName", name="rankingelementdetails_check_elementName")
     */
    public function checkElementNameAction(Request $request)
    {
        $user = $this->getUser();
        if ($user != null) {
            $elementName = $request->request->get('elementName');
            $kpiDetailsId = $request->request->get('kpiDetailsId');
            $em = $this->getDoctrine()->getManager();

            $query = $em->createQueryBuilder()
                ->select('a.id', 'a.elementName')
                ->from('InitialShippingBundle:RankingElementDetails', 'a')
                ->where('a.elementName = :elementName')
                ->andwhere('a.kpiDetailsId = :kpiDetailsId')
                ->setParameter('elementName', $elementName)
                ->setParameter('kpiDetailsId', $kpiDetailsId)
                ->getQuery();
            $elementDetail = $query->getResult();

            $response = new JsonResponse();
            if(count($elementDetail)!=0) {
                $response->setData(array(
                    'elementName_status' => 1,
                    'status' => 1
                ));
            } else {
                $response->setData(array(
                    'elementName_status' => 0,
                    'status' => 1
                ));
            }
            return $response;
        } else {
            return $this->redirectToRoute('fos_user_security_login');
        }

    }

    /**
     * Lists all ElementDetails entities.
     *
     * @Route("/select", name="rankingelementdetails_select1")
     * @Method("GET")
     */
    public function select1Action(Request $request)
    {
        $user = $this->getUser();
        if ($user != null) {
            $userId = $user->getId();
            $role = $this->container->get('security.context')->isGranted('ROLE_ADMIN');
            $em = $this->getDoctrine()->getManager();

            if ($this->container->get('security.context')->isGranted('ROLE_ADMIN')) {
                $query = $em->createQueryBuilder()
                    ->select('a')
                    ->from('InitialShippingBundle:RankingElementDetails', 'a')
                    ->leftjoin('InitialShippingBundle:RankingKpiDetails', 'f', 'WITH', 'f.id = a.kpiDetailsId')
                    ->leftjoin('InitialShippingBundle:ShipDetails', 'd', 'WITH', 'd.id = f.shipDetailsId')
                    ->leftjoin('InitialShippingBundle:CompanyDetails', 'b', 'WITH', 'b.id = d.companyDetailsId')
                    ->leftjoin('InitialShippingBundle:User', 'c', 'WITH', 'c.username = b.adminName')
                    ->where('c.id = :userId')
                    ->setParameter('userId', $userId)
                    ->orderby('a.id')
                    ->getQuery();
            } else {
                $query = $em->createQueryBuilder()
                    ->select('a')
                    ->from('InitialShippingBundle:RankingElementDetails', 'a')
                    ->leftjoin('InitialShippingBundle:RankingKpiDetails', 'f', 'WITH', 'f.id = a.kpiDetailsId')
                    ->leftjoin('InitialShippingBundle:ShipDetails', 'c', 'WITH', 'c.id = f.shipDetailsId')
                    ->leftjoin('InitialShippingBundle:User', 'b', 'WITH', 'b.companyid = c.companyDetailsId')
                    ->where('b.id = :userId')
                    ->setParameter('userId', $userId)
                    ->orderby('a.id')
                    ->getQuery();
            }

            $rankingelementdetail = new RankingElementDetails();
            $form = $this->createForm(new RankingElementDetailsType($userId, $role), $rankingelementdetail);
            $form->handleRequest($request);

            $elementDetails = $query->getResult();
            $count = count($elementDetails);

            return $this->render('rankingelementdetails/index.html.twig', array(
                'elementDetails' => $elementDetails,
                'element_count' => $count,
                'elementDetail' => $rankingelementdetail,
                'form' => $form->createView(),
            ));
        } else {
            return $this->redirectToRoute('fos_user_security_login');
        }
    }

    /**
     * Creates a new RankingElementDetails entity.
     *
     * @Route("/new", name="rankingelementdetails_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $user = $this->getUser();
        $id = $user->getId();
        $role = $this->container->get('security.context')->isGranted('ROLE_ADMIN');

        $rankingElementDetail = new RankingElementDetails();
        $form = $this->createForm(new RankingElementDetailsType($id, $role), $rankingElementDetail);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($rankingElementDetail);
            $em->flush();

            return $this->redirectToRoute('rankingelementdetails_show', array('id' => $rankingElementDetail->getId()));
        }

        return $this->render('rankingelementdetails/new.html.twig', array(
            'rankingElementDetail' => $rankingElementDetail,
            'form' => $form->createView(),
        ));
    }


    /**
     * Get elements based on kpi id.
     *
     * @Route("/elements_for_kpi", name="rankingelementdetails_elements_for_kpi")
     */
    public function elementsForKpiAction(Request $request)
    {
        $user = $this->getUser();
        if ($user != null) {
            $kpiDetailsId = $request->request->get('kpiDetailsId');
            $em = $this->getDoctrine()->getManager();

            $query = $em->createQueryBuilder()
                ->select('a.id', 'a.elementName')
                ->from('InitialShippingBundle:RankingElementDetails', 'a')
                ->where('a.kpiDetailsId = :kpiDetailsId')
                ->setParameter('kpiDetailsId', $kpiDetailsId)
                ->getQuery();
            $elementDetail = $query->getResult();

            $response = new JsonResponse();
            $response->setData(array(
                'elementDetails' => $elementDetail
            ));
            return $response;
        } else {
            return $this->redirectToRoute('fos_user_security_login');
        }
    }

    /**
     * Creates a new ElementDetails entity.
     *
     * @Route("/new1", name="rankingelementdetails_new1")
     * @Method({"GET", "POST"})
     */
    public function new1Action(Request $request)
    {
        $user = $this->getUser();
        if ($user != null) {
            $params = $request->request->get('ranking_element_details');
            $kpiDetailsId = $params['kpiDetailsId'];
            $elementName = $params['elementName'];
            $description = $params['description'];
            $cellName = $params['cellName'];
            $cellDetails = $params['cellDetails'];
            $indicationValue = $params['indicationValue'];
            $vesselWiseTotal = $params['vesselWiseTotal'];
            $symbolId = $params['SymbolId'];
            if(array_key_exists('ComparisonStatus',$params)) {
                $comparisonStatus = $params['ComparisonStatus'];
            } else {
                $comparisonStatus = 0;
            }
            $comparisonValueTotal = $request->request->get('comparison-rule-total');
            $activeMonth = $request->request->get('activeMonth');
            $activeYear = $request->request->get('activeYear');;
            $endMonth = $request->request->get('endMonth');;
            $endYear = $request->request->get('endYear');;
            $day = 1;
            $monthtostring = $activeYear . '-' . $activeMonth . '-' . $day;
            $new_date = new \DateTime($monthtostring);
            $monthtostring1 = $endYear . '-' . $endMonth . '-' . $day;
            $new_date1 = new \DateTime($monthtostring1);
            $weightage = $params['weightage'];
            $basevalue = $params['baseValue'];
            //$rules         = $params['rules'];
            $em=$this->getDoctrine()->getManager();
            $course = $this->getDoctrine()->getManager()->getRepository('InitialShippingBundle:RankingKpiDetails')->findOneBy(array('id' => $kpiDetailsId));
            $elementDetail = new RankingElementDetails();
            $elementDetail->setkpiDetailsId($course);
            $elementDetail->setelementName($elementName);
            $elementDetail->setdescription($description);
            $elementDetail->setcellName($cellName);
            $elementDetail->setcellDetails($cellDetails);
            $elementDetail->setactiveDate($new_date);
            $elementDetail->setendDate($new_date1);
            $elementDetail->setweightage($weightage);
            $elementDetail->setBaseValue($basevalue);
            $elementDetail->setVesselWiseTotal($vesselWiseTotal);
            $elementDetail->setComparisonStatus($comparisonStatus);
            $elementDetail->setIndicationValue($indicationValue);
            $elementDetail->setSymbolId($this->getDoctrine()->getManager()->getRepository('InitialShippingBundle:ElementSymbols')->findOneBy(array('id' => $symbolId)));
            $em->persist($elementDetail);
            $em->flush();
            //This is for Weightage status Starts Here//
            $weightage_status_object=new RankingElementWeightageStatus();
            $weightage_status_object->setActiveDate($new_date);
            $weightage_status_object->setElementId($elementDetail->getId());
            $weightage_status_object->setStatus(0);
            $weightage_status_object->setWeightage($weightage);
            $em->persist($weightage_status_object);
            $em->flush();

            //This is for Weightage status Starts Here//

            if($comparisonStatus==1) {
                for($i=1;$i<=$comparisonValueTotal;$i++) {
                    $comparisonRule = $request->request->get('comparison-rules-'.$i);
                    $elementComparisonRule = new RankingElementComparisonRules();
                    $elementComparisonRule->setElementDetailsId($this->getDoctrine()->getManager()->getRepository('InitialShippingBundle:RankingElementDetails')->findOneBy(array('id' => $elementDetail->getId())));
                    $elementComparisonRule->setRules($comparisonRule);
                    $em->persist($elementComparisonRule);
                    $em->flush();
                }
            }

            return $this->redirectToRoute('rankingelementdetails_select1');

        } else {
            return $this->redirectToRoute('fos_user_security_login');
        }
    }


    /**
     * Creates a new elementDetails entity.
     *
     * @Route("/ranking_element_rule", name="rankingelementdetails_ranking_element_rule")
     */
    public function ranking_element_ruleAction(Request $request, $hi = '')
    {
        $user = $this->getUser();
        if ($user != null) {
            $id = $request->request->get('Id');
            $em = $this->getDoctrine()->getManager();

            $query = $em->createQueryBuilder()
                ->select('a.rules')
                ->from('InitialShippingBundle:RankingElementRules', 'a')
                ->where('a.elementDetailsId = :element_Id')
                ->setParameter('element_Id', $id)
                ->getQuery();

            $element_rules = $query->getResult();
            if ($hi == 'hi') {
                return $element_rules;
            }
            $response = new JsonResponse();
            $response->setData(array('Rule_Array' => $element_rules));

            return $response;
        } else {
            return $this->redirectToRoute('fos_user_security_login');
        }
    }

    /**
     * Finds and displays a RankingElementDetails entity.
     *
     * @Route("/add_ranking_element_symbol", name="add_ranking_element_symbol")
     */
    public function addElementSymbolAction(Request $request)
    {
        $user = $this->getUser();
        if ($user != null) {
            $symbolName = $request->request->get('symbolName');
            $symbolIndication = $request->request->get('symbolIndication');
            $em = $this->getDoctrine()->getManager();

            $elementSymbol = new ElementSymbols();
            $elementSymbol->setSymbolName($symbolName);
            $elementSymbol->setSymbolIndication($symbolIndication);
            $em->persist($elementSymbol);
            $em->flush();

            $query = $em->createQueryBuilder()
                ->select('a.symbolName, a.symbolIndication, a.id')
                ->from('InitialShippingBundle:ElementSymbols', 'a')
                ->getQuery()
                ->getResult();

            $response = new JsonResponse();
            $response->setData(array(
                'elementSymbolDetail' => $query
            ));
            return $response;
        } else {
            return $this->redirectToRoute('fos_user_security_login');
        }

    }


    /**
     * Finds and displays a KpiDetails entity.
     *
     * @Route("/ranking_element_ajax_weightage", name="elementdetails_ranking_element_ajax_weightage")
     */
    public function ranking_element_ajax_weightageAction(Request $request)
    {
        $user = $this->getUser();
        if ($user != null) {
            $weightage = $request->request->get('weightage');
            $kpiId = $request->request->get('kpiDetailsId');
            $elementId = $request->request->get('elementId');
            $status = $request->request->get('status');
            $em = $this->getDoctrine()->getManager();

            $query = $em->createQueryBuilder()
                ->select('a.id', 'a.elementName', 'a.weightage')
                ->from('InitialShippingBundle:RankingElementDetails', 'a')
                ->where('a.kpiDetailsId = :kpi_id')
                ->setParameter('kpi_id', $kpiId)
                ->getQuery()
                ->getResult();

            $sum = $weightage;

            for ($i = 0; $i < count($query); $i++) {
                if ($query[$i]['id'] == $elementId && $status == 0) {
                    $query[$i]['weightage'] = 0;
                }
                $sum = $sum + $query[$i]['weightage'];
            }

            $response = new JsonResponse();
            $response->setData(array('Weightage' => $sum));

            return $response;
        } else {
            return $this->redirectToRoute('fos_user_security_login');
        }
    }


    /**
     * Finds and displays a KpiDetails entity.
     *
     * @Route("/ranking_ajax_show", name="rankingelementdetails_ranking_ajax_show")
     */
    public function ranking_ajax_showAction(Request $request, $hi = '')
    {
        $user = $this->getUser();
        if ($user != null) {
            $id = $request->request->get('Id');
            $em = $this->getDoctrine()->getManager();
            $userId = $user->getId();

            if ($this->container->get('security.context')->isGranted('ROLE_ADMIN')) {
                $kpi_name_array_query = $em->createQueryBuilder()
                    ->select('a.id', 'a.kpiName')
                    ->from('InitialShippingBundle:RankingKpiDetails', 'a')
                    ->leftjoin('InitialShippingBundle:ShipDetails', 'd', 'WITH', 'd.id = a.shipDetailsId')
                    ->leftjoin('InitialShippingBundle:CompanyDetails', 'b', 'WITH', 'b.id = d.companyDetailsId')
                    ->leftjoin('InitialShippingBundle:User', 'c', 'WITH', 'c.username = b.adminName')
                    ->where('c.id = :userId')
                    ->groupby('a.kpiName')
                    ->setParameter('userId', $userId)
                    ->getQuery();
            } else {
                $kpi_name_array_query = $em->createQueryBuilder()
                    ->select('a.id', 'a.kpiName')
                    ->from('InitialShippingBundle:RankingKpiDetails', 'a')
                    ->leftjoin('InitialShippingBundle:ShipDetails', 'c', 'WITH', 'c.id = a.shipDetailsId')
                    ->leftjoin('InitialShippingBundle:User', 'b', 'WITH', 'b.companyid = c.companyDetailsId')
                    ->where('b.id = :userId')
                    ->groupby('a.kpiName')
                    ->setParameter('userId', $userId)
                    ->getQuery();
            }

            $kpi_name_array = $kpi_name_array_query->getResult();

            $query = $em->createQueryBuilder()
                ->select('identity(a.kpiDetailsId)')
                ->from('InitialShippingBundle:RankingElementDetails', 'a')
                ->where('a.id = :element_id')
                ->setParameter('element_id', $id)
                ->getQuery();
            $kpi_name_id = $query->getResult();

            $elementDetailQuery = $em->createQueryBuilder()
                ->select('a.id', 'a.elementName')
                ->from('InitialShippingBundle:RankingElementDetails', 'a')
                ->where('a.kpiDetailsId = :kpiDetailsId')
                ->setParameter('kpiDetailsId', $kpi_name_id[0][1])
                ->getQuery();

            $kpi_name = $em->createQueryBuilder()
                ->select('a.kpiName', 'a.id')
                ->from('InitialShippingBundle:RankingKpiDetails', 'a')
                ->where('a.id = :kpi_id')
                ->setParameter('kpi_id', $kpi_name_id[0][1])
                ->getQuery()
                ->getResult();

            $query1 = $em->createQueryBuilder()
                ->select('a.id', 'a.elementName', 'a.weightage', 'a.activeDate', 'a.endDate', 'a.cellName', 'a.cellDetails', 'a.description', 'a.vesselWiseTotal', 'a.indicationValue', 'identity(a.symbolId)', 'a.comparisonStatus', 'a.baseValue')
                ->from('InitialShippingBundle:RankingElementDetails', 'a')
                ->where('a.id = :element_id')
                ->setParameter('element_id', $id)
                ->getQuery();
            $elementDetail = $query1->getResult();

            $symbolDetail="";
            if($elementDetail[0][1]!="") {
                $symbolQuery = $em->createQueryBuilder()
                    ->select('a.id','a.symbolName')
                    ->from('InitialShippingBundle:ElementSymbols', 'a')
                    ->where('a.id = :symbol_id')
                    ->setParameter('symbol_id', $elementDetail[0][1])
                    ->getQuery();
                $symbolDetail = $symbolQuery->getResult();
            }
            $symbolAllQuery = $em->createQueryBuilder()
                ->select('a.id','a.symbolName')
                ->from('InitialShippingBundle:ElementSymbols', 'a')
                ->getQuery();

            $comparisonRuleArray = array();
            if($elementDetail[0]['comparisonStatus']==1) {
                $comparisonRuleQuery = $em->createQueryBuilder()
                    ->select('a.id','a.rules')
                    ->from('InitialShippingBundle:RankingElementComparisonRules', 'a')
                    ->where('a.elementDetailsId = :element_id')
                    ->setParameter('element_id', $elementDetail[0]['id'])
                    ->getQuery();
                $comparisonRule = $comparisonRuleQuery->getResult();
                for($count=0;$count<count($comparisonRule);$count++) {
                    $ruleObject = json_decode($comparisonRule[$count]['rules']);

                    $firstElementQuery = $em->createQueryBuilder()
                        ->select('a.elementName')
                        ->from('InitialShippingBundle:RankingElementDetails', 'a')
                        ->where('a.id = :element_id')
                        ->setParameter('element_id', $ruleObject->first->id)
                        ->getQuery()
                        ->getScalarResult();
                    $secondElementQuery = $em->createQueryBuilder()
                        ->select('a.elementName')
                        ->from('InitialShippingBundle:RankingElementDetails', 'a')
                        ->where('a.id = :element_id')
                        ->setParameter('element_id', $ruleObject->second->id)
                        ->getQuery()
                        ->getScalarResult();
                    array_push($comparisonRuleArray,array(
                        'firstId' =>$ruleObject->first->id,
                        'firstName'=>$firstElementQuery[0]['elementName'],
                        'firstValue' => $ruleObject->first->value,
                        'secondId' =>$ruleObject->second->id,
                        'secondName' => $secondElementQuery[0]['elementName'],
                        'secondValue' => $ruleObject->second->value,
                        'action' => $ruleObject->action->color
                    ));
                }
            }

            $response = new JsonResponse();
            $response->setData(array(
                'element_detail' => $elementDetail,
                'kpi_name' => $kpi_name,
                'kpi_name_array' => $kpi_name_array,
                'symbolDetail' => $symbolDetail,
                'symbolAllDetail' => $symbolAllQuery->getResult(),
                'comparisonRule' => $comparisonRuleArray,
                'elementDetailAll' => $elementDetailQuery->getResult()
            ));

            if ($hi == 'hi') {
                return $response;
            }

            return $response;

        } else {
            return $this->redirectToRoute('fos_user_security_login');
        }
    }


    /**
     * Finds and displays a KpiDetails entity.
     *
     * @Route("/ranking_ajax_element_edit", name="rankingelementdetails_ranking_ajax_element_edit")
     */
    public function ranking_ajax_element_editAction(Request $request)
    {
        $user = $this->getUser();
        if ($user != null) {
            $id = $request->request->get('Id');
            $kpiName_id = $request->request->get('kpiDetailsId');
            $elementName = $request->request->get('elementName');
            $weightage = $request->request->get('weightage');
            $description = $request->request->get('description');
            $cellName = $request->request->get('cellName');
            $cellDetails = $request->request->get('cellDetails');
            $vesselWiseTotal = $request->request->get('vesselWiseTotal');
            $symbolId = $request->request->get('symbolId');
            $indicationValue = $request->request->get('indicationValue');
            $comparisonStatus = $request->request->get('comparisonStatus');
            $baseValue = $request->request->get('baseValue');
            if($comparisonStatus==1) {
                $comparisonStatusValue = 1;
            } else {
                $comparisonStatusValue = 0;
            }
            $comparisonRuleArray = $request->request->get('comparisonRuleArray');
            $activeMonth = $request->request->get('activeMonth');
            $integerActiveMonth = (int)$activeMonth + 1;
            $activeYear = $request->request->get('activeYear');
            $endMonth = $request->request->get('endMonth');
            $endYear = $request->request->get('endYear');
            $integerEndMonth = (int)$endMonth + 1;
            $activeMonthDate = $activeYear . '-' . $integerActiveMonth . '-' . '01';
            $activeMonthDateObject = new \DateTime($activeMonthDate);
            $activeMonthDateObject->modify("last day of this month");
            $endMonthDate = $endYear . '-' . $integerEndMonth . '-' . '01';
            $endMonthDateObject = new \DateTime($endMonthDate);
            $endMonthDateObject->modify("last day of this month");

            $em = $this->getDoctrine()->getManager();
            //This is for Weightedit status Starts Here//
            $weightage_startus_Result = $em->createQueryBuilder()
                ->select('a.weightage','a.endDate','a.id')
                ->from('InitialShippingBundle:RankingElementWeightageStatus', 'a')
                ->where('a.elementId = :kpi_id')
                ->andWhere('a.endDate IS NULL')
                ->setParameter('kpi_id', $id)
                ->getQuery()
                ->getResult();
            if(count($weightage_startus_Result)==1)
            {
                $resultweightage=$weightage_startus_Result[0]['weightage'];
                if($weightage!=(int)$resultweightage)
                {
                    $editweighate_entity = $em->getRepository('InitialShippingBundle:RankingElementWeightageStatus')->find($weightage_startus_Result[0]['id']);
                    //$ranking_weightagestatus=new RankingWeightageStatus();
                    $currentdatetime=new \DateTime();
                    $editweighate_entity->setEndDate($currentdatetime);
                    $em->flush();
                    $ranking_weightagestatus=new RankingElementWeightageStatus();
                    $ranking_weightagestatus->setWeightage($weightage);
                    $ranking_weightagestatus->setElementId($id);
                    $ranking_weightagestatus->setActiveDate($currentdatetime);
                    $ranking_weightagestatus->setStatus(1);
                    $em->persist($ranking_weightagestatus);
                    $em->flush();

                }
            }
            //This is for Weightedit status Stars Here//

            $entity = $em->getRepository('InitialShippingBundle:RankingElementDetails')->find($id);
            $kpiName_obj = $this->getDoctrine()->getManager()->getRepository('InitialShippingBundle:RankingKpiDetails')->findOneBy(array('id' => $kpiName_id));

            $elementDetail = new RankingElementDetails();
            $entity->setkpiDetailsId($kpiName_obj);
            $entity->setelementName($elementName);
            $entity->setDescription($description);
            $entity->setWeightage($weightage);
            $entity->setCellName($cellName);
            $entity->setCellDetails($cellDetails);
            $entity->setActiveDate($activeMonthDateObject);
            $entity->setEndDate($endMonthDateObject);
            $entity->setVesselWiseTotal($vesselWiseTotal);
            $entity->setSymbolId($this->getDoctrine()->getManager()->getRepository('InitialShippingBundle:ElementSymbols')->findOneBy(array('id' => $symbolId)));
            $entity->setIndicationValue($indicationValue);
            $entity->setComparisonStatus($comparisonStatusValue);
            $entity->setBaseValue($baseValue);
            $em->flush();

            if($comparisonStatus==1) {
                $lastId = $entity->getId();
                $symbolQuery = $em->createQueryBuilder()
                    ->select('a.id','a.rules')
                    ->from('InitialShippingBundle:RankingElementComparisonRules', 'a')
                    ->where('a.elementDetailsId = :element_id')
                    ->setParameter('element_id', $lastId)
                    ->getQuery()
                    ->getResult();
                for($i=0;$i<count($symbolQuery);$i++) {
                    $tempElementRuleObj = $this->getDoctrine()->getManager()->getRepository('InitialShippingBundle:RankingElementComparisonRules')->find($symbolQuery[$i]['id']);
                    $em->remove($tempElementRuleObj);
                    $em->flush();
                }
                for($comparisonCount=0;$comparisonCount<count($comparisonRuleArray);$comparisonCount++) {
                    $elementComparisonRuleObj = new RankingElementComparisonRules();
                    $elementComparisonRuleObj->setElementDetailsId($em->getRepository('InitialShippingBundle:RankingElementDetails')->findOneBy(array('id' => $lastId)));
                    $elementComparisonRuleObj->setRules(json_encode($comparisonRuleArray[$comparisonCount]));
                    $em->persist($elementComparisonRuleObj);
                    $em->flush();
                }
            }

            $show_response = $this->ranking_ajax_showAction($request, 'hi');

            return $show_response;
        } else {
            return $this->redirectToRoute('fos_user_security_login');
        }
    }


    /**
     * Finds and displays a RankingElementDetails entity.
     *
     * @Route("/{id}", name="rankingelementdetails_show")
     * @Method("GET")
     */
    public function showAction(RankingElementDetails $rankingElementDetail)
    {
        $deleteForm = $this->createDeleteForm($rankingElementDetail);

        return $this->render('rankingelementdetails/show.html.twig', array(
            'rankingElementDetail' => $rankingElementDetail,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing RankingElementDetails entity.
     *
     * @Route("/{id}/edit", name="rankingelementdetails_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, RankingElementDetails $rankingElementDetail)
    {
        $deleteForm = $this->createDeleteForm($rankingElementDetail);
        $editForm = $this->createForm('Initial\ShippingBundle\Form\RankingElementDetailsType', $rankingElementDetail);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($rankingElementDetail);
            $em->flush();

            return $this->redirectToRoute('rankingelementdetails_edit', array('id' => $rankingElementDetail->getId()));
        }

        return $this->render('rankingelementdetails/edit.html.twig', array(
            'rankingElementDetail' => $rankingElementDetail,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a RankingElementDetails entity.
     *
     * @Route("/{id}", name="rankingelementdetails_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, RankingElementDetails $rankingElementDetail)
    {
        $form = $this->createDeleteForm($rankingElementDetail);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($rankingElementDetail);
            $em->flush();
        }

        return $this->redirectToRoute('rankingelementdetails_index');
    }

    /**
     * Creates a form to delete a RankingElementDetails entity.
     *
     * @param RankingElementDetails $rankingElementDetail The RankingElementDetails entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(RankingElementDetails $rankingElementDetail)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('rankingelementdetails_delete', array('id' => $rankingElementDetail->getId())))
            ->setMethod('DELETE')
            ->getForm();
    }
}
