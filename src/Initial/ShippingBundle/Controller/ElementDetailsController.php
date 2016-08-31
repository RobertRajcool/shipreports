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
use Initial\ShippingBundle\Entity\ElementComparisonRules;
use Initial\ShippingBundle\Entity\ElementSymbols;

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
     * Finds and displays a ElementDetails entity.
     *
     * @Route("/check_elementName", name="elementdetails_check_elementName")
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
                ->from('InitialShippingBundle:ElementDetails', 'a')
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
     * Finds and displays a ElementDetails entity.
     *
     * @Route("/add_element_symbol", name="add_element_symbol")
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
     * Finds and displays a ElementDetails entity.
     *
     * @Route("/element_symbol", name="element_symbol")
     */
    public function elementSymbolAction(Request $request)
    {
        $user = $this->getUser();
        if ($user != null) {
            $em = $this->getDoctrine()->getManager();

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
     * Finds and displays a ElementDetails entity.
     *
     * @Route("/find_element_symbol_name", name="find_element_symbol_name")
     */
    public function FindElementSymbolNameAction(Request $request)
    {
        $user = $this->getUser();
        if ($user != null) {
            $symbolId = $request->request->get('symbolId');
            $em = $this->getDoctrine()->getManager();

            $query = $em->createQueryBuilder()
                ->select('a.symbolName, a.symbolIndication, a.id')
                ->from('InitialShippingBundle:ElementSymbols', 'a')
                ->where('a.id = :symbolId')
                ->setParameter('symbolId', $symbolId)
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
     * Finds and displays a ElementDetails entity.
     *
     * @Route("/save_element_symbol", name="save_element_symbol")
     */
    public function saveElementSymbolNameAction(Request $request)
    {
        $user = $this->getUser();
        if ($user != null) {
            $symbolId = $request->request->get('symbolId');
            $symbolName = $request->request->get('symbolName');
            $symbolIndication = $request->request->get('symbolIndication');
            $em = $this->getDoctrine()->getManager();

            $entity = $em->getRepository('InitialShippingBundle:ElementSymbols')->find($symbolId);
            $entity->setSymbolName($symbolName);
            $entity->setSymbolIndication($symbolIndication);
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
     * Finds and displays a ElementDetails entity.
     *
     * @Route("/delete_element_symbol", name="delete_element_symbol")
     */
    public function deleteElementSymbolNameAction(Request $request)
    {
        $user = $this->getUser();
        if ($user != null) {
            $symbolId = $request->request->get('symbolId');
            $em = $this->getDoctrine()->getManager();

            $entity = $em->getRepository('InitialShippingBundle:ElementSymbols')->find($symbolId);
            $em->remove($entity);
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
     * Get elements based on kpi id.
     *
     * @Route("/elements_for_kpi", name="elementdetails_elements_for_kpi")
     */
    public function elementsForKpiAction(Request $request)
    {
        $user = $this->getUser();
        if ($user != null) {
            $kpiDetailsId = $request->request->get('kpiDetailsId');
            $em = $this->getDoctrine()->getManager();

            $query = $em->createQueryBuilder()
                ->select('a.id', 'a.elementName')
                ->from('InitialShippingBundle:ElementDetails', 'a')
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
        if ($user != null) {
            $role = $this->container->get('security.context')->isGranted('ROLE_ADMIN');
            $em = $this->getDoctrine()->getManager();

            if ($this->container->get('security.context')->isGranted('ROLE_ADMIN')) {
                $query = $em->createQueryBuilder()
                    ->select('a')
                    ->from('InitialShippingBundle:ElementDetails', 'a')
                    ->leftjoin('InitialShippingBundle:KpiDetails', 'f', 'WITH', 'f.id = a.kpiDetailsId')
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
                    ->from('InitialShippingBundle:ElementDetails', 'a')
                    ->leftjoin('InitialShippingBundle:KpiDetails', 'f', 'WITH', 'f.id = a.kpiDetailsId')
                    ->leftjoin('InitialShippingBundle:ShipDetails', 'c', 'WITH', 'c.id = f.shipDetailsId')
                    ->leftjoin('InitialShippingBundle:User', 'b', 'WITH', 'b.companyid = c.companyDetailsId')
                    ->where('b.id = :userId')
                    ->setParameter('userId', $userId)
                    ->orderby('a.id')
                    ->getQuery();
            }

            $elementdetail = new ElementDetails();
            $form = $this->createForm(new ElementDetailsType($userId, $role), $elementdetail);
            $form->handleRequest($request);

            $elementDetails = $query->getResult();
            $count = count($elementDetails);

            return $this->render('elementdetails/index.html.twig', array(
                'elementDetails' => $elementDetails,
                'element_count' => $count,
                'elementDetail' => $elementdetail,
                'form' => $form->createView(),
            ));
        } else {
            return $this->redirectToRoute('fos_user_security_login');
        }
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
        $role = $this->container->get('security.context')->isGranted('ROLE_ADMIN');

        $elementdetails = new ElementDetails();
        $form = $this->createForm(new ElementDetailsType($id, $role), $elementdetails);
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
        $user = $this->getUser();
        if ($user != null) {
            $params = $request->request->get('element_details');
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
            $activeYear = $request->request->get('activeYear');
            $endMonth = $request->request->get('endMonth');
            $endYear = $request->request->get('endYear');
            $day = 1;
            $monthtostring = $activeYear . '-' . $activeMonth . '-' . $day;
            $new_date = new \DateTime($monthtostring);
            $monthtostring1 = $endYear . '-' . $endMonth . '-' . $day;
            $new_date1 = new \DateTime($monthtostring1);
            $weightage = $params['weightage'];
            $baseValue = $params['baseValue'];
            //$rules         = $request->request->get('value');

            $elementDetail = new ElementDetails();
            $elementDetail->setkpiDetailsId($this->getDoctrine()->getManager()->getRepository('InitialShippingBundle:KpiDetails')->findOneBy(array('id' => $kpiDetailsId)));
            $elementDetail->setelementName($elementName);
            $elementDetail->setdescription($description);
            $elementDetail->setcellName($cellName);
            $elementDetail->setcellDetails($cellDetails);
            $elementDetail->setactivatedDate($new_date);
            $elementDetail->setendDate($new_date1);
            $elementDetail->setweightage($weightage);
            $elementDetail->setVesselWiseTotal($vesselWiseTotal);
            $elementDetail->setComparisonStatus($comparisonStatus);
            $elementDetail->setIndicationValue($indicationValue);
            $elementDetail->setSymbolId($this->getDoctrine()->getManager()->getRepository('InitialShippingBundle:ElementSymbols')->findOneBy(array('id' => $symbolId)));
            $elementDetail->setBaseValue($baseValue);

            $em = $this->getDoctrine()->getManager();
            $em->persist($elementDetail);
            $em->flush();

            if($comparisonStatus==1) {
                for($i=1;$i<=$comparisonValueTotal;$i++) {
                    $comparisonRule = $request->request->get('comparison-rules-'.$i);
                    $elementComparisonRule = new ElementComparisonRules();
                    $elementComparisonRule->setElementDetailsId($this->getDoctrine()->getManager()->getRepository('InitialShippingBundle:ElementDetails')->findOneBy(array('id' => $elementDetail->getId())));
                    $elementComparisonRule->setRules($comparisonRule);

                    $em->persist($elementComparisonRule);
                    $em->flush();
                }
            }

            return $this->redirectToRoute('elementdetails_select1');
        } else {
            return $this->redirectToRoute('fos_user_security_login');
        }
    }


    /**
     * Creates a new elementDetails entity.
     *
     * @Route("/element_rule", name="elementdetails_element_rule")
     */
    public function element_ruleAction(Request $request, $hi = '')
    {
        $user = $this->getUser();
        if ($user != null) {
            $id = $request->request->get('Id');
            $em = $this->getDoctrine()->getManager();

            $query = $em->createQueryBuilder()
                ->select('a.rules')
                ->from('InitialShippingBundle:ElementRules', 'a')
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
     * Finds and displays a KpiDetails entity.
     *
     * @Route("/element_ajax_weightage", name="elementdetails_element_ajax_weightage")
     */
    public function element_ajax_weightageAction(Request $request)
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
                ->from('InitialShippingBundle:ElementDetails', 'a')
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
     * @Route("/ajax_show", name="elementdetails_ajax_show")
     */
    public function ajax_showAction(Request $request, $hi = '')
    {
        $user = $this->getUser();
        if ($user != null) {
            $id = $request->request->get('Id');
            $em = $this->getDoctrine()->getManager();
            $user = $this->getUser();
            $userId = $user->getId();

            if ($this->container->get('security.context')->isGranted('ROLE_ADMIN')) {
                $kpi_name_array_query = $em->createQueryBuilder()
                    ->select('a.id', 'a.kpiName')
                    ->from('InitialShippingBundle:KpiDetails', 'a')
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
                    ->from('InitialShippingBundle:KpiDetails', 'a')
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
                ->from('InitialShippingBundle:ElementDetails', 'a')
                ->where('a.id = :element_id')
                ->setParameter('element_id', $id)
                ->getQuery();
            $kpi_name_id = $query->getResult();

            $elementDetailQuery = $em->createQueryBuilder()
                ->select('a.id', 'a.elementName')
                ->from('InitialShippingBundle:ElementDetails', 'a')
                ->where('a.kpiDetailsId = :kpiDetailsId')
                ->setParameter('kpiDetailsId', $kpi_name_id[0][1])
                ->getQuery();

            $kpi_name = $em->createQueryBuilder()
                ->select('a.kpiName', 'a.id')
                ->from('InitialShippingBundle:KpiDetails', 'a')
                ->where('a.id = :kpi_id')
                ->setParameter('kpi_id', $kpi_name_id[0][1])
                ->getQuery()
                ->getResult();

            $query1 = $em->createQueryBuilder()
                ->select('a.id', 'a.elementName', 'a.weightage', 'a.activatedDate', 'a.endDate', 'a.cellName', 'a.cellDetails', 'a.description', 'a.vesselWiseTotal', 'a.indicationValue', 'identity(a.symbolId)', 'a.comparisonStatus', 'a.baseValue')
                ->from('InitialShippingBundle:ElementDetails', 'a')
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
                    ->from('InitialShippingBundle:ElementComparisonRules', 'a')
                    ->where('a.elementDetailsId = :element_id')
                    ->setParameter('element_id', $elementDetail[0]['id'])
                    ->getQuery();
                $comparisonRule = $comparisonRuleQuery->getResult();
                for($count=0;$count<count($comparisonRule);$count++) {
                    $ruleObject = json_decode($comparisonRule[$count]['rules']);

                    $firstElementQuery = $em->createQueryBuilder()
                        ->select('a.elementName')
                        ->from('InitialShippingBundle:ElementDetails', 'a')
                        ->where('a.id = :element_id')
                        ->setParameter('element_id', $ruleObject->first->id)
                        ->getQuery()
                        ->getScalarResult();
                    $secondElementQuery = $em->createQueryBuilder()
                        ->select('a.elementName')
                        ->from('InitialShippingBundle:ElementDetails', 'a')
                        ->where('a.id = :element_id')
                        ->setParameter('element_id', $ruleObject->second->id)
                        ->getQuery()
                        ->getScalarResult();
                    array_push($comparisonRuleArray,array(
                        'firstId' =>$ruleObject->first->id,
                        'firstName'=>$firstElementQuery[0]['elementName'],
                        'firstValue' => $ruleObject->first->value,
                        'firstOption' => $ruleObject->first->option,
                        'secondId' =>$ruleObject->second->id,
                        'secondName' => $secondElementQuery[0]['elementName'],
                        'secondValue' => $ruleObject->second->value,
                        'secondOption' => $ruleObject->second->option,
                        'action' => $ruleObject->action->color
                    ));
                }
            }

            $rules = $this->element_ruleAction($request, 'hi');

            $response = new JsonResponse();
            $response->setData(array(
                'element_detail' => $elementDetail,
                'element_rules' => $rules,
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
     * @Route("/ajax_element_edit", name="elementdetails_ajax_element_edit")
     */
    public function ajax_element_editAction(Request $request)
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
            $activeMonth = $request->request->get('activeMonth');
            $integerActiveMonth = (int)$activeMonth + 1;
            $activeYear = $request->request->get('activeYear');
            $endMonth = $request->request->get('endMonth');
            $endYear = $request->request->get('endYear');
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
            $integerEndMonth = (int)$endMonth + 1;
            $activeMonthDate = $activeYear . '-' . $integerActiveMonth . '-' . '01';
            $activeMonthDateObject = new \DateTime($activeMonthDate);
            $activeMonthDateObject->modify("last day of this month");
            $endMonthDate = $endYear . '-' . $integerEndMonth . '-' . '01';
            $endMonthDateObject = new \DateTime($endMonthDate);
            $endMonthDateObject->modify("last day of this month");

            $em = $this->getDoctrine()->getManager();

            $entity = $em->getRepository('InitialShippingBundle:ElementDetails')->find($id);
            $kpiName_obj = $this->getDoctrine()->getManager()->getRepository('InitialShippingBundle:KpiDetails')->findOneBy(array('id' => $kpiName_id));

            $entity->setkpiDetailsId($kpiName_obj);
            $entity->setelementName($elementName);
            $entity->setDescription($description);
            $entity->setWeightage($weightage);
            $entity->setCellName($cellName);
            $entity->setCellDetails($cellDetails);
            $entity->setActivatedDate($activeMonthDateObject);
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
                    ->from('InitialShippingBundle:ElementComparisonRules', 'a')
                    ->where('a.elementDetailsId = :element_id')
                    ->setParameter('element_id', $lastId)
                    ->getQuery()
                    ->getResult();
                for($i=0;$i<count($symbolQuery);$i++) {
                    $tempElementRuleObj = $this->getDoctrine()->getManager()->getRepository('InitialShippingBundle:ElementComparisonRules')->find($symbolQuery[$i]['id']);
                    $em->remove($tempElementRuleObj);
                    $em->flush();
                }
                for($comparisonCount=0;$comparisonCount<count($comparisonRuleArray);$comparisonCount++) {
                    $elementComparisonRuleObj = new ElementComparisonRules();
                    $elementComparisonRuleObj->setElementDetailsId($em->getRepository('InitialShippingBundle:ElementDetails')->findOneBy(array('id' => $lastId)));
                    $elementComparisonRuleObj->setRules(json_encode($comparisonRuleArray[$comparisonCount]));
                    $em->persist($elementComparisonRuleObj);
                    $em->flush();
                }
            }
            $show_response = $this->ajax_showAction($request, 'hi');

            return $show_response;

        } else {
            return $this->redirectToRoute('fos_user_security_login');
        }
    }


    /**
     * Creates a new elementDetails entity.
     *
     * @Route("/{id}/element_rule1", name="elementdetails_element_rule1")
     */
    public function element_rule1Action(Request $request)
    {
        $user = $this->getUser();
        if ($user != null) {
            $id = $request->request->get('Id');
            $em = $this->getDoctrine()->getManager();

            $query = $em->createQueryBuilder()
                ->select('a.rules')
                ->from('InitialShippingBundle:ElementRules', 'a')
                ->where('a.elementDetailsId = :element_Id')
                ->setParameter('element_Id', $id)
                ->getQuery();

            $element_rules = $query->getResult();
            $response = new JsonResponse();
            $response->setData(array('Rule_Array' => $element_rules));

            return $response;
        } else {
            return $this->redirectToRoute('fos_user_security_login');
        }
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
            ->from('InitialShippingBundle:ElementRules', 'a')
            ->where('a.elementDetailsId = :element_Id')
            ->setParameter('element_Id', $id)
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
        $role = $this->container->get('security.context')->isGranted('ROLE_ADMIN');

        $deleteForm = $this->createDeleteForm($elementDetail);
        $editForm = $this->createForm(new ElementDetailsType($id, $role), $elementDetail);
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
        $kpiDetailsId = $params['kpiDetailsId'];
        $elementName = $params['elementName'];
        $description = $params['description'];
        $cellName = $params['cellName'];
        $cellDetails = $params['cellDetails'];
        $activatedDate = $params['activatedDate'];
        $endDate = $params['endDate'];
        $count = $request->request->get('id');
        $rule11 = $request->request->get('rule_name');

        $monthtostring = $activatedDate['year'] . '-' . $activatedDate['month'] . '-' . $activatedDate['day'];
        $new_date = new \DateTime($monthtostring);
        $monthtostring1 = $endDate['year'] . '-' . $endDate['month'] . '-' . $endDate['day'];
        $new_date1 = new \DateTime($monthtostring1);

        $weightage = $params['weightage'];
        $rules = $params['rules'];

        $course = $this->getDoctrine()->getManager()->getRepository('InitialShippingBundle:KpiDetails')->findOneBy(array('id' => $kpiDetailsId));

        $em = $this->getDoctrine()->getManager();

        $element_id_array = $em->createQueryBuilder()
            ->select('a.id')
            ->from('InitialShippingBundle:ElementDetails', 'a')
            ->where('a.elementName = :element_name')
            ->setParameter('element_name', $elementName)
            ->getQuery()
            ->getResult();

        for ($j = 1; $j < count($element_id_array); $j++) {
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

        $element_rules_id_array = $em->createQueryBuilder()
            ->select('a.id')
            ->from('InitialShippingBundle:ElementRules', 'a')
            ->where('a.elementDetailsId = :element_id')
            ->setParameter('element_id', $element_id_array[1]['id'])
            ->getQuery()
            ->getResult();

        if ($count == 1) {
            for ($i = 0; $i < count($rule11); $i++) {
                $element_rules_obj = $em->getRepository('InitialShippingBundle:ElementRules')->find($element_rules_id_array[$i]);
                $element_obj = $em->getRepository('InitialShippingBundle:KpiDetails')->findOneBy(array('id' => $element_id_array[0]));

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
            ->getForm();
    }
}