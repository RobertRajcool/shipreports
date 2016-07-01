<?php

namespace Initial\ShippingBundle\Controller;

use Initial\ShippingBundle\Entity\RankingKpiRules;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Initial\ShippingBundle\Entity\RankingKpiDetails;
use Initial\ShippingBundle\Form\RankingKpiDetailsType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * RankingKpiDetails controller.
 *
 * @Route("/rankingkpidetails")
 */
class RankingKpiDetailsController extends Controller
{
    /**
     * Lists all RankingKpiDetails entities.
     *
     * @Route("/", name="rankingkpidetails_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $rankingKpiDetails = $em->getRepository('InitialShippingBundle:RankingKpiDetails')->findAll();

        return $this->render('rankingkpidetails/index.html.twig', array(
            'rankingKpiDetails' => $rankingKpiDetails,
        ));
    }

    /**
     * Finds and displays a KpiDetails entity.
     *
     * @Route("/check_kpiName", name="rankingkpidetails_check_kpiName")
     */
    public function checkKpiNameAction(Request $request)
    {
        $user = $this->getUser();
        if ($user != null) {
            $kpiName = $request->request->get('kpiName');
            $em = $this->getDoctrine()->getManager();

            $query = $em->createQueryBuilder()
                ->select('a.id', 'a.kpiName')
                ->from('InitialShippingBundle:RankingKpiDetails', 'a')
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
     * @Route("/select", name="rankingkpidetails_select")
     * @Method("GET")
     */
    public function selectAction(Request $request)
    {
        $user = $this->getUser();
        if ($user != null) {
            $userId = $user->getId();
            $role = $this->container->get('security.context')->isGranted('ROLE_ADMIN');
            $em = $this->getDoctrine()->getManager();

            if ($this->container->get('security.context')->isGranted('ROLE_ADMIN')) {
                $query = $em->createQueryBuilder()
                    ->select('a')
                    ->from('InitialShippingBundle:RankingKpiDetails', 'a')
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
                    ->from('InitialShippingBundle:RankingKpiDetails', 'a')
                    ->leftjoin('InitialShippingBundle:ShipDetails', 'c', 'WITH', 'c.id = a.shipDetailsId')
                    ->leftjoin('InitialShippingBundle:User', 'b', 'WITH', 'b.companyid = c.companyDetailsId')
                    ->where('b.id = :userId')
                    ->groupby('a.kpiName')
                    ->orderby('a.id')
                    ->setParameter('userId', $userId)
                    ->getQuery();
            }

            $rankingKpiDetails = $query->getResult();
            $count = count($rankingKpiDetails);

            $rankingKpiDetail = new RankingKpiDetails();
            $form = $this->createForm(new RankingKpiDetailsType($userId, $role), $rankingKpiDetail);
            $form->handleRequest($request);


            return $this->render('rankingkpidetails/index.html.twig', array(
                'kpiDetails' => $rankingKpiDetails,
                'kpiDetail' => $rankingKpiDetail,
                'form' => $form->createView(),
                'kpi_count' => $count
            ));
        } else {
            return $this->redirectToRoute('fos_user_security_login');
        }
    }


    /**
     * Creates a new RankingKpiDetails entity.
     *
     * @Route("/new", name="rankingkpidetails_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $user = $this->getUser();
        if ($user != null) {
            $id = $user->getId();
            $role = $this->container->get('security.context')->isGranted('ROLE_ADMIN');

            $rankingKpiDetail = new RankingKpiDetails();
            $form = $this->createForm(new RankingKpiDetailsType($id, $role), $rankingKpiDetail);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($rankingKpiDetail);
                $em->flush();

                return $this->redirectToRoute('rankingkpidetails_show', array('id' => $rankingKpiDetail->getId()));
            }

            return $this->render('rankingkpidetails/new.html.twig', array(
                'rankingKpiDetail' => $rankingKpiDetail,
                'form' => $form->createView(),
            ));
        } else {
            return $this->redirectToRoute('fos_user_security_login');
        }
    }


    /**
     * Creates a new KpiDetails entity.
     *
     * @Route("/new1", name="rankingkpidetails_new1")
     * @Method({"GET", "POST"})
     */
    public function new1Action(Request $request)
    {
        $user = $this->getUser();
        if ($user != null) {
            $params = $request->request->get('ranking_kpi_details');
            $kpiName = $params['kpiName'];
            $shipDetailsId = $params['shipDetailsId'];
            $val = count($shipDetailsId);
            $description = $params['description'];
            $activeMonth = $request->request->get('activeMonth');
            $activeYear = $request->request->get('activeYear');;
            $endMonth = $request->request->get('endMonth');;
            $endYear = $request->request->get('endYear');;
            $cellName = $params['cellName'];
            $cellDetails = $params['cellDetails'];
            $weightage = $params['weightage'];
            $day = 1;

            $monthtostring = $activeYear . '-' . $activeMonth . '-' . $day;
            $new_date = new \DateTime($monthtostring);
            $monthtostring1 = $endYear . '-' . $endMonth . '-' . $day;
            $new_date1 = new \DateTime($monthtostring1);
            $em = $this->getDoctrine()->getManager();

            $id = 0;
            for ($i = 0; $i < $val; $i++) {
                $kpidetails = new RankingKpiDetails();
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
            $course1 = $em->getRepository('InitialShippingBundle:RankingKpiDetails')->findOneBy(array('id' => $id));
            for ($ii = 1; $ii <= $value; $ii++) {
                $variable = "rules-$ii";
                $rules = $request->request->get($variable);
                if ($rules != "") {
                    $rule = new RankingKpiRules();
                    $rule->setKpiDetailsId($course1);
                    $rule->setRules($rules);
                    $em->persist($rule);
                    $em->flush();
                }
            }
            return $this->redirectToRoute('rankingkpidetails_select');
        } else {
            return $this->redirectToRoute('fos_user_security_login');
        }
    }


    /**
     * Finds and displays a KpiDetails entity.
     *
     * @Route("/ranking_kpi_ajax_show", name="kpidetails_ranking_kpi_ajax_show")
     */
    public function ranking_kpi_ajax_showAction(Request $request, $hi = '')
    {
        $user = $this->getUser();
        if ($user != null) {
            $userId = $user->getId();
            $id = $request->request->get('Id');
            $em = $this->getDoctrine()->getManager();

            $query = $em->createQueryBuilder()
                ->select('a.id', 'a.kpiName', 'a.weightage', 'a.description', 'a.activeDate', 'a.endDate', 'a.cellName', 'a.cellDetails')
                ->from('InitialShippingBundle:RankingKpiDetails', 'a')
                ->where('a.id = :kpi_id')
                ->setParameter('kpi_id', $id)
                ->getQuery();
            $kpiDetail = $query->getResult();

            $query1 = $em->createQueryBuilder()
                ->select('a.id')
                ->from('InitialShippingBundle:RankingKpiDetails', 'a')
                ->where('a.kpiName = :kpi_name')
                ->setParameter('kpi_name', $kpiDetail[0]['kpiName'])
                ->getQuery();
            $kpi_id_array = $query1->getResult();

            for ($i = 0; $i < count($kpi_id_array); $i++) {
                $query2 = $em->createQueryBuilder()
                    ->select('identity(a.shipDetailsId)')
                    ->from('InitialShippingBundle:RankingKpiDetails', 'a')
                    ->where('a.id = :kpi_id')
                    ->setParameter('kpi_id', $kpi_id_array[$i]['id'])
                    ->getQuery();
                $ship_id_array[$i] = $query2->getResult();
            }

            for ($j = 0; $j < count($kpi_id_array); $j++) {
                $query2 = $em->createQueryBuilder()
                    ->select('a.shipName,a.id')
                    ->from('InitialShippingBundle:ShipDetails', 'a')
                    ->where('a.id = :ship_id')
                    ->setParameter('ship_id', $ship_id_array[$j][0][1])
                    ->getQuery();
                $ship_name_array[$j] = $query2->getResult();
            }

            $rules = $this->ranking_ruleAction($request, 'hi');

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
     * @Route("/ranking_kpi_ajax_weightage", name="kpidetails_ranking_kpi_ajax_weightage")
     */
    public function ranking_kpi_ajax_weightageAction(Request $request)
    {
        $user = $this->getUser();
        if ($user != null) {
            $kpiId = $request->request->get('kpiDetailsId');
            $status = $request->request->get('status');
            $weightage = $request->request->get('weightage');
            $em = $this->getDoctrine()->getManager();

            $query = $em->createQueryBuilder()
                ->select('a.id', 'a.kpiName', 'a.weightage')
                ->from('InitialShippingBundle:RankingKpiDetails', 'a')
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
     * @Route("/ranking_ajax_edit", name="kpidetails_ranking_ajax_edit")
     */
    public function ranking_ajax_editAction(Request $request)
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
                ->from('InitialShippingBundle:RankingKpiDetails', 'a')
                ->where('a.kpiName = :kpi_name')
                ->setParameter('kpi_name', $kpiName)
                ->getQuery()
                ->getResult();

            for ($j = 0; $j < count($kpi_id_array); $j++) {
                $entity = $em->getRepository('InitialShippingBundle:RankingKpiDetails')->find($kpi_id_array[$j]);

                $kpiDetail = new RankingKpiDetails();
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
                    ->from('InitialShippingBundle:RankingKpiRules', 'a')
                    ->where('a.kpiDetailsId = :kpi_id')
                    ->setParameter('kpi_id', $id)
                    ->getQuery()
                    ->getResult();

                for ($i = 0; $i < count($rules_array); $i++) {
                    $kpi_rules_obj = $em->getRepository('InitialShippingBundle:RankingKpiRules')->find($kpi_rules_id_array[$i]);
                    $kpi_obj = $em->getRepository('InitialShippingBundle:RankingKpiDetails')->findOneBy(array('id' => $id));

                    $kpi_rules_obj->setRules($rules_array[$i]);
                    $kpi_rules_obj->setKpiDetailsId($kpi_obj);
                    $em->flush();
                }
            }

            $show_response = $this->ranking_kpi_ajax_showAction($request, 'hi');

            return $show_response;
        } else {
            return $this->redirectToRoute('fos_user_security_login');
        }

    }


    /**
     * Finds and displays a RankingKpiDetails entity.
     *
     * @Route("/{id}/rule", name="rankingkpidetails_rule")
     */
    public function ranking_ruleAction(Request $request, $hi = '')
    {
        $user = $this->getUser();
        if ($user != null) {
            $id = $request->request->get('Id');
            $em = $this->getDoctrine()->getManager();

            $name = $em->createQueryBuilder()
                ->select('a.kpiName')
                ->from('InitialShippingBundle:RankingKpiDetails', 'a')
                ->where('a.id = :kpi_Id')
                ->setParameter('kpi_Id', $id)
                ->getQuery()
                ->getResult();

            $name1 = $em->createQueryBuilder()
                ->select('a.id')
                ->from('InitialShippingBundle:RankingKpiDetails', 'a')
                ->where('a.kpiName = :kpi_Id')
                ->setParameter('kpi_Id', $name[0]['kpiName'])
                ->getQuery()
                ->getResult();
            $num = $name1[0]['id'];

            $query = $em->createQueryBuilder()
                ->select('a.rules')
                ->from('InitialShippingBundle:RankingKpiRules', 'a')
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
     * Finds and displays a RankingKpiDetails entity.
     *
     * @Route("/{id}", name="rankingkpidetails_show")
     * @Method("GET")
     */
    public function showAction(RankingKpiDetails $rankingKpiDetail)
    {
        $deleteForm = $this->createDeleteForm($rankingKpiDetail);

        return $this->render('rankingkpidetails/show.html.twig', array(
            'rankingKpiDetail' => $rankingKpiDetail,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing RankingKpiDetails entity.
     *
     * @Route("/{id}/edit", name="rankingkpidetails_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, RankingKpiDetails $rankingKpiDetail)
    {
        $deleteForm = $this->createDeleteForm($rankingKpiDetail);
        $editForm = $this->createForm('Initial\ShippingBundle\Form\RankingKpiDetailsType', $rankingKpiDetail);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($rankingKpiDetail);
            $em->flush();

            return $this->redirectToRoute('rankingkpidetails_edit', array('id' => $rankingKpiDetail->getId()));
        }

        return $this->render('rankingkpidetails/edit.html.twig', array(
            'rankingKpiDetail' => $rankingKpiDetail,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a RankingKpiDetails entity.
     *
     * @Route("/{id}", name="rankingkpidetails_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, RankingKpiDetails $rankingKpiDetail)
    {
        $form = $this->createDeleteForm($rankingKpiDetail);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($rankingKpiDetail);
            $em->flush();
        }

        return $this->redirectToRoute('rankingkpidetails_index');
    }

    /**
     * Creates a form to delete a RankingKpiDetails entity.
     *
     * @param RankingKpiDetails $rankingKpiDetail The RankingKpiDetails entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(RankingKpiDetails $rankingKpiDetail)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('rankingkpidetails_delete', array('id' => $rankingKpiDetail->getId())))
            ->setMethod('DELETE')
            ->getForm();
    }
}
