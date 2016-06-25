<?php

namespace Initial\ShippingBundle\Controller;

use Initial\ShippingBundle\Entity\KpiRules;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Initial\ShippingBundle\Entity\KpiDetails;
use Initial\ShippingBundle\Form\KpiDetailsType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * KpiDetails controller.
 *
 * @Route("/kpidetails", name="kpidetails_index")
 */
class KpiDetailsController extends Controller
{
    /**
     * Lists all KpiDetails entities.
     *
     * @Route("/", name="kpidetails_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $kpiDetails = $em->getRepository('InitialShippingBundle:KpiDetails')->findAll();

        return $this->render('kpidetails/index.html.twig', array(
            'kpiDetails' => $kpiDetails,
        ));
    }

    /**
     * Finds and displays a KpiDetails entity.
     *
     * @Route("/check_kpiName", name="kpidetails_check_kpiName")
     */
    public function checkKpiNameAction(Request $request)
    {
        $user = $this->getUser();
        if ($user != null) {
            $kpiName = $request->request->get('kpiName');
            $em = $this->getDoctrine()->getManager();

            $query = $em->createQueryBuilder()
                ->select('a.id', 'a.kpiName')
                ->from('InitialShippingBundle:KpiDetails', 'a')
                ->where('a.kpiName = :kpiName')
                ->setParameter('kpiName', $kpiName)
                ->getQuery();
            $kpiDetail = $query->getResult();

            $response = new JsonResponse();
            if(count($kpiDetail)!=0) {
                $response->setData(array(
                    'kpiName_status' => 1,
                    'status' => 1
                ));
            } else {
                $response->setData(array(
                    'kpiName_status' => 0,
                    'status' => 1
                ));
            }
            return $response;
        } else {
            return $this->redirectToRoute('fos_user_security_login');
        }

    }

    /**
     * Lists all KpiDetails entities.
     *
     * @Route("/ajax_edit", name="kpidetails_ajax_edit")
     */
    public function ajax_editAction(Request $request)
    {
        $user = $this->getUser();
        if ($user != null) {
            $id = $request->request->get('Id');
            $kpiName = $request->request->get('kpiName');
            $weightage = $request->request->get('weightage');
            $description = $request->request->get('description');
            $cellName = $request->request->get('cellName');
            $cellDetails = $request->request->get('cellDetails');
            $activeMonth = $request->request->get('activeMonth');
            $integerActiveMonth = (int)$activeMonth + 1;
            $activeYear = $request->request->get('activeYear');
            $endMonth = $request->request->get('endMonth');
            $endYear = $request->request->get('endYear');
            $integerEndMonth = (int)$endMonth + 1;
            $rules_array = $request->request->get('rules');
            $activeMonthDate = $activeYear . '-' . $integerActiveMonth . '-' . '01';
            $activeMonthDateObject = new \DateTime($activeMonthDate);
            $activeMonthDateObject->modify("last day of this month");
            $endMonthDate = $endYear . '-' . $integerEndMonth . '-' . '01';
            $endMonthDateObject = new \DateTime($endMonthDate);
            $endMonthDateObject->modify("last day of this month");

            $em = $this->getDoctrine()->getManager();
            $kpi_id_array = $em->createQueryBuilder()
                ->select('a.id')
                ->from('InitialShippingBundle:KpiDetails', 'a')
                ->where('a.kpiName = :kpi_name')
                ->setParameter('kpi_name', $kpiName)
                ->getQuery()
                ->getResult();
            for ($j = 0; $j < count($kpi_id_array); $j++) {
                $entity = $em->getRepository('InitialShippingBundle:KpiDetails')->find($kpi_id_array[$j]);
                $entity->setKpiName($kpiName);
                $entity->setDescription($description);
                $entity->setWeightage($weightage);
                $entity->setCellName($cellName);
                $entity->setCellDetails($cellDetails);
                $entity->setActiveDate($activeMonthDateObject);
                $entity->setEndDate($endMonthDateObject);
                $em->flush();
            }
            if (count($rules_array) > 0) {
                $kpi_rules_id_array = $em->createQueryBuilder()
                    ->select('a.id')
                    ->from('InitialShippingBundle:KpiRules', 'a')
                    ->where('a.kpiDetailsId = :kpi_id')
                    ->setParameter('kpi_id', $id)
                    ->getQuery()
                    ->getResult();
                for ($i = 0; $i < count($rules_array); $i++) {
                    $kpi_rules_obj = $em->getRepository('InitialShippingBundle:KpiRules')->find($kpi_rules_id_array[$i]);
                    $kpi_obj = $em->getRepository('InitialShippingBundle:KpiDetails')->findOneBy(array('id' => $id));

                    $kpi_rules_obj->setRules($rules_array[$i]);
                    $kpi_rules_obj->setKpiDetailsId($kpi_obj);
                    $em->flush();
                }
            }
            $show_response = $this->kpi_ajax_showAction($request, 'hi');
            return $show_response;
        } else {
            return $this->redirectToRoute('fos_user_security_login');
        }
    }


    /**
     * Lists all KpiDetails entities.
     *
     * @Route("{id}/select", name="kpidetails_select")
     * @Method("GET")
     */
    public function selectAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $kpiDetails = $em->getRepository('InitialShippingBundle:KpiDetails')->findByShipDetailsId($id);

        return $this->render('kpidetails/index.html.twig', array(
            'kpiDetails' => $kpiDetails,
        ));
    }


    /**
     * Lists all KpiDetails entities.
     *
     * @Route("/select", name="kpidetails_select1")
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
                    ->from('InitialShippingBundle:KpiDetails', 'a')
                    ->leftjoin('InitialShippingBundle:ShipDetails', 'd', 'WITH', 'd.id = a.shipDetailsId')
                    ->leftjoin('InitialShippingBundle:CompanyDetails', 'b', 'WITH', 'b.id = d.companyDetailsId')
                    ->leftjoin('InitialShippingBundle:User', 'c', 'WITH', 'c.username = b.adminName')
                    ->where('c.id = :userId')
                    ->groupby('a.kpiName')
                    ->orderby('a.id')
                    ->setParameter('userId', $userId)
                    ->getQuery();
            } else {
                $query = $em->createQueryBuilder()
                    ->select('a')
                    ->from('InitialShippingBundle:KpiDetails', 'a')
                    ->leftjoin('InitialShippingBundle:ShipDetails', 'c', 'WITH', 'c.id = a.shipDetailsId')
                    ->leftjoin('InitialShippingBundle:User', 'b', 'WITH', 'b.companyid = c.companyDetailsId')
                    ->where('b.id = :userId')
                    ->groupby('a.kpiName')
                    ->orderby('a.id')
                    ->setParameter('userId', $userId)
                    ->getQuery();
            }

            $kpiDetails = $query->getResult();
            $count = count($kpiDetails);

            $kpiDetail = new KpiDetails();
            $form = $this->createForm(new KpiDetailsType($userId, $role), $kpiDetail);
            $form->handleRequest($request);

            return $this->render('kpidetails/index.html.twig', array(
                'kpiDetails' => $kpiDetails,
                'kpiDetail' => $kpiDetail,
                'form' => $form->createView(),
                'kpi_count' => $count
            ));
        } else {
            return $this->redirectToRoute('fos_user_security_login');
        }
    }


    /**
     * Creates a new KpiDetails entity.
     *
     * @Route("/new", name="kpidetails_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $user = $this->getUser();
        $id = $user->getId();
        $role = $this->container->get('security.context')->isGranted('ROLE_ADMIN');

        $kpiDetail = new KpiDetails();
        $form = $this->createForm(new KpiDetailsType($id, $role), $kpiDetail);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($kpiDetail);
            $em->flush();

            return $this->redirectToRoute('kpidetails_show', array('id' => $kpiDetail->getId()));
        }

        return $this->render('kpidetails/new.html.twig', array(
            'kpiDetail' => $kpiDetail,
            'form' => $form->createView(),
        ));
    }

    /**
     * Creates a new KpiDetails entity.
     *
     * @Route("/new1", name="kpidetails_new1")
     * @Method({"GET", "POST"})
     */
    public function new1Action(Request $request)
    {
        $user = $this->getUser();
        if ($user != null) {
            $params = $request->request->get('kpi_details');
            $kpiName = $params['kpiName'];
            $shipDetailsId = $params['shipDetailsId'];
            $val = count($shipDetailsId);
            $description = $params['description'];
            $activeMonth = $request->request->get('activeMonth');
            $activeYear = $request->request->get('activeYear');;
            $endMonth = $request->request->get('endMonth');;
            $endYear = $request->request->get('endYear');;
            $cellName = " ";
            $cellDetails = " ";
            $weightage = $params['weightage'];
            $day = 1;

            $monthtostring = $activeYear . '-' . $activeMonth . '-' . $day;
            $new_date = new \DateTime($monthtostring);
            $monthtostring1 = $endYear . '-' . $endMonth . '-' . $day;
            $new_date1 = new \DateTime($monthtostring1);

            $em = $this->getDoctrine()->getManager();

            $id = 0;
            for ($i = 0; $i < $val; $i++) {
                $kpidetails = new KpiDetails();
                $kpidetails->setShipDetailsId($this->getDoctrine()->getManager()->getRepository('InitialShippingBundle:ShipDetails')->findOneBy(array('id' => $shipDetailsId[$i])));
                $kpidetails->setKpiName($kpiName);
                $kpidetails->setDescription($description);
                $kpidetails->setActiveDate($new_date);
                $kpidetails->setEndDate($new_date1);
                $kpidetails->setCellName($cellName);
                $kpidetails->setCellDetails($cellDetails);
                $kpidetails->setWeightage($weightage);
                $em->persist($kpidetails);
                $em->flush();

                if ($i == 0) {
                    $id = $kpidetails->getId();
                }
            }
            $value = $request->request->get('value');
            $course1 = $em->getRepository('InitialShippingBundle:KpiDetails')->findOneBy(array('id' => $id));
            for ($ii = 1; $ii <= $value; $ii++) {
                $variable = "rules-$ii";
                $rules = $request->request->get($variable);
                if ($rules != "") {
                    $rule = new KpiRules();
                    $rule->setKpiDetailsId($course1);
                    $rule->setRules($rules);
                    $em->persist($rule);
                    $em->flush();
                }
            }
            return $this->redirectToRoute('kpidetails_select1');
        } else {
            return $this->redirectToRoute('fos_user_security_login');
        }
    }


    /**
     * Finds and displays a KpiDetails entity.
     *
     * @Route("/{id}", name="kpidetails_show")
     * @Method("GET")
     */
    public function showAction(KpiDetails $kpiDetail)
    {
        $deleteForm = $this->createDeleteForm($kpiDetail);

        return $this->render('kpidetails/show.html.twig', array(
            'kpiDetail' => $kpiDetail,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Finds and displays a KpiDetails entity.
     *
     * @Route("/{id}/show", name="kpidetails_show1")
     * @Method("GET")
     */
    public function show1Action(KpiDetails $kpiDetail)
    {

        $name = $kpiDetail->getKpiName();
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQueryBuilder()
            ->select('identity(a.shipDetailsId)')
            ->from('InitialShippingBundle:KpiDetails', 'a')
            ->where('a.kpiName = :userId')
            ->setParameter('userId', $name)
            ->getQuery()
            ->getResult();

        for ($i = 0; $i < count($query); $i++) {
            $ship_query[$i] = $em->createQueryBuilder()
                ->select('a.shipName')
                ->from('InitialShippingBundle:ShipDetails', 'a')
                ->where('a.id = :Id')
                ->setParameter('Id', $query[$i][1])
                ->getQuery()
                ->getResult();
        }

        return $this->render('kpidetails/show.html.twig', array(
            'kpiDetail' => $kpiDetail,
            'ship_id' => $query,
            'ship_name' => $ship_query
        ));
    }

    /**
     * Finds and displays a KpiDetails entity.
     *
     * @Route("/kpi_ajax_show", name="kpidetails_kpi_ajax_show")
     */
    public function kpi_ajax_showAction(Request $request, $hi = '')
    {
        $user = $this->getUser();
        $userId = $user->getId();
        if ($user != null) {
            $id = $request->request->get('Id');
            $em = $this->getDoctrine()->getManager();

            $query = $em->createQueryBuilder()
                ->select('a.id', 'a.kpiName', 'a.weightage', 'a.description', 'a.activeDate', 'a.endDate', 'a.cellName', 'a.cellDetails')
                ->from('InitialShippingBundle:KpiDetails', 'a')
                ->where('a.id = :kpi_id')
                ->setParameter('kpi_id', $id)
                ->getQuery();
            $kpiDetail = $query->getResult();

            $query1 = $em->createQueryBuilder()
                ->select('a.id')
                ->from('InitialShippingBundle:KpiDetails', 'a')
                ->where('a.kpiName = :kpi_name')
                ->setParameter('kpi_name', $kpiDetail[0]['kpiName'])
                ->getQuery();
            $kpi_id_array = $query1->getResult();

            for ($i = 0; $i < count($kpi_id_array); $i++) {
                $query2 = $em->createQueryBuilder()
                    ->select('identity(a.shipDetailsId)')
                    ->from('InitialShippingBundle:KpiDetails', 'a')
                    ->where('a.id = :kpi_id')
                    ->setParameter('kpi_id', $kpi_id_array[$i]['id'])
                    ->getQuery();
                $ship_id_array[$i] = $query2->getResult();
            }

            for ($j = 0; $j < count($kpi_id_array); $j++) {
                $query2 = $em->createQueryBuilder()
                    ->select('a.shipName', 'a.id')
                    ->from('InitialShippingBundle:ShipDetails', 'a')
                    ->where('a.id = :ship_id')
                    ->setParameter('ship_id', $ship_id_array[$j][0][1])
                    ->getQuery();
                $ship_name_array[$j] = $query2->getResult();
            }

            $rules = $this->ruleAction($request, 'hi');

            if ($this->container->get('security.context')->isGranted('ROLE_ADMIN')) {
                $ship_query = $em->createQueryBuilder()
                    ->select('a.shipName,a.id')
                    ->from('InitialShippingBundle:ShipDetails', 'a')
                    ->leftjoin('InitialShippingBundle:CompanyDetails', 'b', 'WITH', 'b.id = a.companyDetailsId')
                    ->leftjoin('InitialShippingBundle:User', 'c', 'WITH', 'c.username = b.adminName')
                    ->where('c.id = :userId')
                    ->setParameter('userId', $userId)
                    ->getQuery();
            } else {
                $ship_query = $em->createQueryBuilder()
                    ->select('a.shipName,a.id')
                    ->from('InitialShippingBundle:ShipDetails', 'a')
                    ->leftjoin('InitialShippingBundle:User', 'b', 'WITH', 'b.companyid = a.companyDetailsId')
                    ->where('b.id = :userId')
                    ->setParameter('userId', $userId)
                    ->getQuery();
            }
            $shipDetails = $ship_query->getResult();

            $response = new JsonResponse();
            $response->setData(array(
                'kpi_detail' => $kpiDetail,
                'ship_id' => $ship_id_array,
                'ship_name' => $ship_name_array,
                'kpi_rules' => $rules,
                'ship_array' => $shipDetails
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
     * @Route("/kpi_ajax_weightage", name="kpidetails_kpi_ajax_weightage")
     */
    public function kpi_ajax_weightageAction(Request $request)
    {
        $user = $this->getUser();
        if ($user != null) {
            $kpiId = $request->request->get('kpiDetailsId');
            $status = $request->request->get('status');
            $weightage = $request->request->get('weightage');
            $em = $this->getDoctrine()->getManager();

            $query = $em->createQueryBuilder()
                ->select('a.id', 'a.kpiName', 'a.weightage')
                ->from('InitialShippingBundle:KpiDetails', 'a')
                ->groupby('a.kpiName')
                ->getQuery()
                ->getResult();
            $sum = $weightage;

            for ($i = 0; $i < count($query); $i++) {
                if ($query[$i]['id'] == $kpiId && $status == 0) {
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
     * @Route("/{id}/rule", name="kpidetails_rule")
     */
    public function ruleAction(Request $request, $hi = '')
    {
        $user = $this->getUser();
        if ($user != null) {
            $id = $request->request->get('Id');
            $em = $this->getDoctrine()->getManager();

            $name = $em->createQueryBuilder()
                ->select('a.kpiName')
                ->from('InitialShippingBundle:KpiDetails', 'a')
                ->where('a.id = :kpi_Id')
                ->setParameter('kpi_Id', $id)
                ->getQuery()
                ->getResult();

            $name1 = $em->createQueryBuilder()
                ->select('a.id')
                ->from('InitialShippingBundle:KpiDetails', 'a')
                ->where('a.kpiName = :kpi_Id')
                ->setParameter('kpi_Id', $name[0]['kpiName'])
                ->getQuery()
                ->getResult();
            $num = $name1[0]['id'];

            $query = $em->createQueryBuilder()
                ->select('a.rules')
                ->from('InitialShippingBundle:KpiRules', 'a')
                ->where('a.kpiDetailsId = :kpi_id')
                ->setParameter('kpi_id', $num)
                ->getQuery()
                ->getResult();
            if ($hi == 'hi') {
                return $query;
            }

            $response = new JsonResponse();
            $response->setData(array('Rule_Array' => $query));

            return $response;
        } else {
            return $this->redirectToRoute('fos_user_security_login');
        }

    }

    /**
     * Displays a form to edit an existing KpiDetails entity.
     *
     * @Route("/{id}/edit", name="kpidetails_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, KpiDetails $kpiDetail)
    {
        $user = $this->getUser();
        $id = $user->getId();
        $role = $this->container->get('security.context')->isGranted('ROLE_ADMIN');

        $deleteForm = $this->createDeleteForm($kpiDetail);
        $editForm = $this->createForm(new KpiDetailsType($id, $role), $kpiDetail);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($kpiDetail);
            $em->flush();

            return $this->redirectToRoute('kpidetails_edit', array('id' => $kpiDetail->getId()));
        }

        return $this->render('kpidetails/edit.html.twig', array(
            'kpiDetail' => $kpiDetail,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing KpiDetails entity.
     *
     * @Route("/{id}/edit1", name="kpidetails_edit1")
     * @Method({"GET", "POST"})
     */
    public function edit1Action(KpiDetails $kpiDetail)
    {
        $name = $kpiDetail->getKpiName();
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQueryBuilder()
            ->select('identity(a.shipDetailsId)')
            ->from('InitialShippingBundle:KpiDetails', 'a')
            ->where('a.kpiName = :userId')
            ->setParameter('userId', $name)
            ->getQuery()
            ->getResult();

        for ($i = 0; $i < count($query); $i++) {
            $ship_query[$i] = $em->createQueryBuilder()
                ->select('a.shipName')
                ->from('InitialShippingBundle:ShipDetails', 'a')
                ->where('a.id = :Id')
                ->setParameter('Id', $query[$i][1])
                ->getQuery()
                ->getResult();
        }

        return $this->render('kpidetails/edit.html.twig', array(
            'kpiDetail' => $kpiDetail,
            'ship_id' => $query,
            'ship_name' => $ship_query
        ));
    }

    /**
     * Displays a form to edit an existing KpiDetails entity.
     *
     * @Route("/edit2", name="kpidetails_edit2")
     * @Method({"GET", "POST"})
     */
    public function edit2Action(Request $request)
    {
        $ships_id = $request->request->get('ships');
        $kpiName = $request->request->get('kpiName');
        $description = $request->request->get('description');
        $weightage = $request->request->get('weightage');
        $activeDate = $request->request->get('activeDate');
        $activeDate1 = new \DateTime($activeDate);
        $endDate = $request->request->get('endDate');
        $endDate1 = new \DateTime($endDate);
        $cellName = $request->request->get('cellName');
        $cellDetails = $request->request->get('cellDetails');
        $count = $request->request->get('id');
        $rule11 = $request->request->get('rule_name');
        $vv = $rule11[0];

        $em = $this->getDoctrine()->getManager();

        $kpi_id_array = $em->createQueryBuilder()
            ->select('a.id')
            ->from('InitialShippingBundle:KpiDetails', 'a')
            ->where('a.kpiName = :kpi_name')
            ->setParameter('kpi_name', $kpiName)
            ->getQuery()
            ->getResult();

        for ($j = 0; $j < count($kpi_id_array); $j++) {
            $entity = $em->getRepository('InitialShippingBundle:KpiDetails')->find($kpi_id_array[$j]);
            $ship_obj = $em->getRepository('InitialShippingBundle:ShipDetails')->findOneBy(array('id' => $ships_id[$j]));

            $kpiDetail = new KpiDetails();
            $entity->setShipDetailsId($ship_obj);
            $entity->setKpiName($kpiName);
            $entity->setDescription($description);
            $entity->setWeightage($weightage);
            $entity->setActiveDate($activeDate1);
            $entity->setEndDate($endDate1);
            $entity->setCellName($cellName);
            $entity->setCellDetails($cellDetails);
            $em->flush();
        }

        $kpi_rules_id_array = $em->createQueryBuilder()
            ->select('a.id')
            ->from('InitialShippingBundle:KpiRules', 'a')
            ->where('a.kpiDetailsId = :kpi_id')
            ->setParameter('kpi_id', $kpi_id_array[0]['id'])
            ->getQuery()
            ->getResult();

        if ($count == 1) {
            for ($i = 0; $i < count($rule11); $i++) {
                $kpi_rules_obj = $em->getRepository('InitialShippingBundle:KpiRules')->find($kpi_rules_id_array[$i]);
                $kpi_obj = $em->getRepository('InitialShippingBundle:KpiDetails')->findOneBy(array('id' => $kpi_id_array[0]));

                $kpi_rules_obj->setRules($rule11[$i]);
                $kpi_rules_obj->setKpiDetailsId($kpi_obj);
                $em->flush();
            }
        }
        return $this->redirectToRoute('kpidetails_select1');

    }

    /**
     * Deletes a KpiDetails entity.
     *
     * @Route("/{id}", name="kpidetails_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, KpiDetails $kpiDetail)
    {
        $form = $this->createDeleteForm($kpiDetail);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($kpiDetail);
            $em->flush();
        }

        return $this->redirectToRoute('kpidetails_select1');
    }

    /**
     * Creates a form to delete a KpiDetails entity.
     *
     * @param KpiDetails $kpiDetail The KpiDetails entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(KpiDetails $kpiDetail)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('kpidetails_delete', array('id' => $kpiDetail->getId())))
            ->setMethod('DELETE')
            ->getForm();
    }
}