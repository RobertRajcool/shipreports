<?php

namespace Initial\ShippingBundle\Controller;

use Initial\ShippingBundle\Entity\CommonFunctions;
use Initial\ShippingBundle\Entity\RankingFolder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Initial\ShippingBundle\Entity\ReadingKpiValues;
use Initial\ShippingBundle\Entity\RankingMonthlyData;
use Symfony\Component\HttpFoundation\Session\Session;
use Initial\ShippingBundle\Entity\Excel_file_details;
use Initial\ShippingBundle\Entity\PHPNodeJs;
use Initial\ShippingBundle\Entity\Ranking_LookupStatus;
use Initial\ShippingBundle\Entity\Scorecard_LookupStatus;
use Initial\ShippingBundle\Entity\Ranking_LookupData;
use Initial\ShippingBundle\Form\AddExcelFileType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Reader_Excel2007;
use PHPExcel_Shared_Date;
use PHPExcel_Cell;


/**
 * DataVerficationController.
 *
 * @Route("/dataverfication")
 */
class DataVerficationController extends Controller
{
    /**
     * adddata.
     *
     * @Route("/add_data", name="adddata_scorecard")
     */
    public function findnumofshipsAction(Request $request, $mode = '')
    {
        $em = $this->getDoctrine()->getManager();
        //Finding Company for Login user Starts Here//
        $user = $this->getUser();
        if ($user == null) {
            return $this->redirectToRoute('fos_user_security_login');
        } else {
            $userId = $user->getId();
            $username = $user->getUsername();
            $role = $user->getRoles();

            $dateObject = new \DateTime();
            $dateObject->modify('last day of this month');

            if ($this->container->get('security.context')->isGranted('ROLE_ADMIN')) {
                $query = $em->createQueryBuilder()
                    ->select('a.shipName', 'a.id')
                    ->from('InitialShippingBundle:ShipDetails', 'a')
                    ->join('InitialShippingBundle:CompanyDetails', 'b', 'WITH', 'b.id = a.companyDetailsId')
                    ->where('b.adminName = :username')
                    ->andWhere('a.dateTime <= :activeDate')
                    ->setParameter('activeDate', $dateObject)
                    ->groupBy('a.id')
                    ->setParameter('username', $username)
                    ->getQuery();
            } else {
                $query = $em->createQueryBuilder()
                    ->select('a.shipName', 'a.id')
                    ->from('InitialShippingBundle:ShipDetails', 'a')
                    ->leftjoin('InitialShippingBundle:User', 'b', 'WITH', 'b.companyid = a.companyDetailsId')
                    ->where('a.dateTime <= :activeDate')
                    ->setParameter('activeDate', $dateObject)
                    ->andwhere('b.id = :userId')
                    ->setParameter('userId', $userId)
                    ->groupBy('a.id')
                    ->getQuery();
            }

            $vesselList = $query->getResult();

            $template_chosen = 'base.html.twig';

            if (count($vesselList) > 0) {
                if ($mode == 'nextshipajaxcall') {
                    return $vesselList;
                }
//                $statusforship = $this->gotoScorecardMonthDataAction($request, 'fetch');

                $fullData = $this->gotoScorecardMonthDataAction($request, 'fetch');

                if(count($fullData['statusQueryResult'])>0) {
                    $rejections = json_decode($fullData['statusQueryResult'][0]['rejections']);
                    $rejection_status = $fullData['statusQueryResult'][0]['rejectionsStatus'];
                    $updated_ships = explode(',',$fullData['statusQueryResult'][0]['shipid']);
                    $data_status = $fullData['statusQueryResult'][0]['status'];
                } else {
                    $rejections = null;
                    $rejection_status = null;
                    $updated_ships = null;
                    $data_status = null;
                }

                $data_view = '';
                $ship_status_array = array();

                $finddatawithstatus = array();
                $shipid = 0;
                $shipname = '';
//                $counts = array_count_values($statusforship);

                if ($role[0] == 'ROLE_ADMIN') {
                    if($data_status == 3 || $data_status == 4 || $data_status == 2) {
                        if(count($updated_ships) == count($vesselList)) {
                            if($rejection_status) {
                                $data_view = 'editable-view';
                            } else {
                                $data_view = 'summary-view';
                            }

                        } else {
                            $data_view = 'editable-view';
                        }

                        if(count($updated_ships)>0) {
                            foreach ($vesselList as $vessel) {
                                if(in_array($vessel['id'], $updated_ships)) {
                                    if($rejections) {
                                        if(array_key_exists($vessel['id'], $rejections)  && $rejection_status) {
                                            array_push($ship_status_array, 2);
                                        } else {
                                            array_push($ship_status_array, 1);
                                        }
                                    } else {
                                        array_push($ship_status_array, 1);
                                    }
                                } else {
                                    array_push($ship_status_array, 0);
                                }
                            }
                        } else {
                            foreach ($vesselList as $vessel) {
                                array_push($ship_status_array, 0);
                            }
                        }
                    } else {
                        $data_view = 'editable-view';
                        foreach ($vesselList as $vessel) {
                            array_push($ship_status_array, 0);
                        }
                    }
                }

                if ($role[0] == 'ROLE_MANAGER') {
                    /*$status = 5;
                    $index = array_search(0, $statusforship);
                    $shipid = $vesselList[$index]['id'];
                    $shipname = $vesselList[$index]['shipName'];
                    $finddatawithstatus = $this->finddatawithstatus($status, $shipid);
                    if (array_key_exists(2, $counts)) {
                        $ship_status_done_count = $counts[2];
                    } else {
                        $ship_status_done_count = 0;
                    }*/
                    if($data_status == 2 || $data_status == 3 || $data_status == 4 || $data_status == 5) {
                        if(count($updated_ships) == count($vesselList)) {
                            if($rejection_status || count($rejections)>0) {
                                $data_view = 'editable-view';
                            } else {
                                $data_view = 'summary-view';
                            }

                        } else {
                            $data_view = 'editable-view';
                        }

                        if(count($updated_ships)>0) {
                            foreach ($vesselList as $vessel) {
                                if(in_array($vessel['id'], $updated_ships)) {
                                    if($rejections) {
                                        if(array_key_exists($vessel['id'], $rejections)  && $rejection_status) {
                                            array_push($ship_status_array, 2);
                                        } else {
                                            array_push($ship_status_array, 1);
                                        }
                                    } else {
                                        array_push($ship_status_array, 1);
                                    }
                                } else {
                                    array_push($ship_status_array, 0);
                                }
                            }
                        } foreach ($vesselList as $vessel) {
                            array_push($ship_status_array, 0);
                        }

                    } else {
                        $data_view = 'editable-view';
                        foreach ($vesselList as $vessel) {
                            array_push($ship_status_array, 0);
                        }
                    }
                }

                if ($role[0] == 'ROLE_KPI_INFO_PROVIDER') {
                    $template_chosen = 'v-ships_layout.html.twig';
                    if($data_status == 1 || $data_status == 2 || $data_status == 3 || $data_status == 4 || $data_status == 5) {
                        if(count($updated_ships) == count($vesselList)) {
                            if($rejection_status) {
                                $data_view = 'editable-view';
                            } else {
                                $data_view = 'summary-view';
                            }

                        } else {
                            $data_view = 'editable-view';
                        }

                        if(count($updated_ships)>0) {
                            foreach ($vesselList as $vessel) {
                                if(in_array($vessel['id'], $updated_ships)) {
                                    if($rejections) {
                                        if(array_key_exists($vessel['id'], $rejections)  && $rejection_status) {
                                            array_push($ship_status_array, 2);
                                        } else {
                                            array_push($ship_status_array, 1);
                                        }
                                    } else {
                                        array_push($ship_status_array, 1);
                                    }
                                } else {
                                    array_push($ship_status_array, 0);
                                }
                            }
                        } foreach ($vesselList as $vessel) {
                            array_push($ship_status_array, 0);
                        }
                    } else {
                        $data_view = 'editable-view';
                        foreach ($vesselList as $vessel) {
                            array_push($ship_status_array, 0);
                        }
                    }
                }


                $ship_btn_staus = '';
                if($updated_ships != null) {
                    if(in_array($vesselList[0]['id'],$updated_ships)) {
                        $ship_btn_staus = 'update';
                    } else {
                        $ship_btn_staus = 'save';
                    }
                } else {
                    $ship_btn_staus = 'save';
                }

                /*$currentdatetime = date('Y-m-d');
                $last_date = date("Y-m-t", strtotime($currentdatetime));

                $lookup_status = $em->createQueryBuilder()
                    ->select('a.status, a.rejections')
                    ->from('InitialShippingBundle:Scorecard_LookupStatus', 'a')
                    ->where('a.dataofmonth = :dataofmonth')
                    ->setParameter('dataofmonth', $last_date)
                    ->getQuery()
                    ->getResult();
                if (count($lookup_status) > 0) {
                    if ($lookup_status[0]['rejections'] != null) {
                        $rejection_string = $lookup_status[0]['rejections'];
                        $rejection_obj = json_decode($rejection_string);
                        $rejection_obj = json_decode($rejection_string, true);
                        $reject_status = 'yes';
                    } else {
                        $rejection_obj = null;
                        $reject_status = 'empty';
                    }
                } else {
                    $rejection_obj = null;
                    $reject_status = 'empty';
                }*/

                /*if (count($finddatawithstatus) == 7) {
                    return $this->render('InitialShippingBundle:DataVerficationScoreCorad:home.html.twig',
                        array('listofships' => $listallshipforcompany,
                            'shipcount' => count($listallshipforcompany), 'status_ship' => $statusforship, 'statuscount' => $ship_status_done_count,
                            'elementkpiarray' => $finddatawithstatus['elementnamekpiname'], 'elementcount' => $finddatawithstatus['maxelementcount'],
                            'elementvalues' => $finddatawithstatus['elementvalues'],
                            'elementweightage' => $finddatawithstatus['elementweightage'],
                            'indicationValue' => $finddatawithstatus['indicationValue'],
                            'symbolIndication' => $finddatawithstatus['symbolIndication'],
                            'kpi_details' => $finddatawithstatus['kpi_details'],
                            'rejections' => $rejection_obj, 'rejection_status' => $reject_status,
                            'currentshipid' => $shipid, 'currentshipname' => $shipname, 'templatechoosen' => $template_chosen, 'currentdatetime' => $currentdatetime,
                            'lookup_status' => 2
                        ));
                } else {
                    return $this->render('InitialShippingBundle:DataVerficationScoreCorad:home.html.twig',
                        array('listofships' => $listallshipforcompany,
                            'shipcount' => count($listallshipforcompany), 'status_ship' => $statusforship,
                            'elementkpiarray' => array(), 'elementcount' => 0,
                            'elementvalues' => array(),
                            'currentshipid' => $shipid, 'currentshipname' => $shipname, 'templatechoosen' => $template_chosen, 'currentdatetime' => $currentdatetime
                        ));
                }*/

                return $this->render('InitialShippingBundle:DataVerficationScoreCorad:home.html.twig',
                    array(
                        'listofships' => $vesselList,
                        'shipcount' => count($vesselList),
                        'elementkpiarray' => $fullData['elementkpiarray'], 'elementcount' => $fullData['maxelementcount'],
                        'elementvalues' => $fullData['elementvalues'],
                        'elementweightage' => $fullData['elementweightage'],
                        'indicationValue' => $fullData['indicationValue'],
                        'symbolIndication' => $fullData['symbolIndication'],
                        'kpi_details' => $fullData['kpi_details'],
                        'currentshipid' => $vesselList[0]['id'], 'currentshipname' => $vesselList[0]['shipName'],
                        'templatechoosen' => $template_chosen,
                        'statuscount' => count($updated_ships), 'currentdatetime' => date('Y-m-d'),
                        'data_view' => $data_view, 'ship_status' => $ship_status_array, 'ship_btn_status' => $ship_btn_staus
                    ));

            } else {
                return $this->render('InitialShippingBundle:DataVerficationScoreCorad:home.html.twig',
                    array('listofships' => $vesselList,
                        'shipcount' => count($vesselList), 'status_ship' => 0,
                        'elementkpiarray' => array(), 'elementcount' => 0,
                        'elementvalues' => array(),
                        'currentshipid' => '', 'currentshipname' => '', 'templatechoosen' => $template_chosen, 'statuscount' => ''
                    ));
            }
        }
    }

    //Finding Status For monthdata

    private function findshipstatusmonth($dataofmonth = '', $shipdetialsarray, $role)
    {
        $session = new Session();
        if ($dataofmonth == '') {
            $stringdataofmonth = date('Y-m-d');
        }
        if ($dataofmonth != '') {
            $mydate = '01-' . $dataofmonth;
            $time = strtotime($mydate);
            $stringdataofmonth = date('Y-m-d', $time);
        }
        $status = 0;
        $new_date = new \DateTime($stringdataofmonth);
        $new_date->modify('last day of this month');
        $em = $this->getDoctrine()->getManager();
        $statusarray = array();

        if ($role == 'ROLE_ADMIN') {
            $status = 3;

            for ($ship = 0; $ship < count($shipdetialsarray); $ship++) {
                $statusfromresult = $em->createQueryBuilder()
                    ->select('b.status')
                    ->from('InitialShippingBundle:ReadingKpiValues', 'b')
                    ->where('b.shipDetailsId = :shipdetailsid')
                    ->andWhere('b.status = :status')
                    ->andWhere('b.monthdetail =:dataofmonth')
                    ->setParameter('shipdetailsid', $shipdetialsarray[$ship]['id'])
                    ->setParameter('dataofmonth', $new_date)
                    ->setParameter('status', 3)
                    ->getQuery()
                    ->getResult();
                if (count($statusfromresult) == 0) {
                    array_push($statusarray, 0);
                }
                if (count($statusfromresult) > 0) {
                    array_push($statusarray, 3);
                }
            }
            return $statusarray;
        }
        if ($role == 'ROLE_MANAGER') {
            $status = 2;
            for ($ship = 0; $ship < count($shipdetialsarray); $ship++) {
                $statusfromresult = $em->createQueryBuilder()
                    ->select('b.status')
                    ->from('InitialShippingBundle:ReadingKpiValues', 'b')
                    ->where('b.shipDetailsId = :shipdetailsid')
                    ->andWhere('b.status = 2 OR b.status  = 3')
                    ->andWhere('b.monthdetail =:dataofmonth')
                    ->setParameter('shipdetailsid', $shipdetialsarray[$ship]['id'])
                    ->setParameter('dataofmonth', $new_date)
                    ->getQuery()
                    ->getResult();
                if (count($statusfromresult) == 0) {
                    array_push($statusarray, 0);
                }
                if (count($statusfromresult) > 0) {
                    array_push($statusarray, 2);
                }
            }
            return $statusarray;
        }
        if ($role == 'ROLE_KPI_INFO_PROVIDER') {
            $status = 1;
            $tempvarable = 0;
            $elementtempvariable = 0;

            for ($ship = 0; $ship < count($shipdetialsarray); $ship++) {
                $statusfromresult = $em->createQueryBuilder()
                    ->select('b.status')
                    ->from('InitialShippingBundle:ReadingKpiValues', 'b')
                    ->where('b.shipDetailsId = :shipdetailsid')
                    ->andWhere('b.monthdetail =:dataofmonth')
                    ->andWhere('b.status = 1 OR b.status  = 2 OR b.status  = 3 OR b.status  = 5')
                    ->setParameter('shipdetailsid', $shipdetialsarray[$ship]['id'])
                    ->setParameter('dataofmonth', $new_date)
                    ->getQuery()
                    ->getResult();
                if (count($statusfromresult) == 0) {
                    array_push($statusarray, 0);
                }
                if (count($statusfromresult) > 0) {
                    array_push($statusarray, $statusfromresult[0]['status']);
                }
            }
            return $statusarray;

        } else {
            return $statusarray;
        }
    }

    private function findelementkpiid($shipid)
    {
        $em = $this->getDoctrine()->getManager();
        $returnarray = array();
        $sessionkpielementid = array();
        $currentselectionid = 0;
        $currentselectionname = '';
        $maxelementcount = 0;
        $query = $em->createQueryBuilder()
            ->select('b.kpiName', 'b.id')
            ->from('InitialShippingBundle:KpiDetails', 'b')
            ->where('b.shipDetailsId = :shipdetailsid')
            ->setParameter('shipdetailsid', $shipid)
            ->add('orderBy', 'b.id  ASC ')
            ->getQuery();

        $ids = $query->getResult();
        $maxelementcount = 0;


        $sessionkpielementid = array();
        $k = 0;
        for ($i = 0; $i < count($ids); $i++) {
            $kpiid = $ids[$i]['id'];
            $kpiname = $ids[$i]['kpiName'];

            $query = $em->createQueryBuilder()
                ->select('b.elementName', 'b.id', 'b.indicationValue', 'b.weightage', 'c.symbolIndication')
                ->from('InitialShippingBundle:ElementDetails', 'b')
                ->leftjoin('InitialShippingBundle:ElementSymbols', 'c', 'WITH', 'c.id = b.symbolId')
                ->where('b.kpiDetailsId = :kpidetailsid')
                ->setParameter('kpidetailsid', $kpiid)
                ->add('orderBy', 'b.id  ASC ')
                ->getQuery();
            $elementids = $query->getResult();
            if (count($elementids) == 0) {
                $query1 = $em->createQueryBuilder()
                    ->select('b.kpiName', 'b.id')
                    ->from('InitialShippingBundle:KpiDetails', 'b')
                    ->where('b.kpiName = :kpiName')
                    ->setParameter('kpiName', $kpiname)
                    ->add('orderBy', 'b.id  ASC ')
                    ->groupby('b.kpiName')
                    ->getQuery();

                $ids1 = $query1->getResult();
                $newkpiid = $ids1[0]['id'];
                $newkpiname = $ids1[0]['kpiName'];
                $query = $em->createQueryBuilder()
                    ->select('b.elementName', 'b.id', 'b.indicationValue', 'b.weightage', 'c.symbolIndication')
                    ->from('InitialShippingBundle:ElementDetails', 'b')
                    ->leftjoin('InitialShippingBundle:ElementSymbols', 'c', 'WITH', 'c.id = b.symbolId')
                    ->where('b.kpiDetailsId = :kpidetailsid')
                    ->setParameter('kpidetailsid', $newkpiid)
                    ->add('orderBy', 'b.id  ASC ')
                    ->getQuery();
                $elementids = $query->getResult();
                if ($maxelementcount < count($elementids)) {
                    $maxelementcount = count($elementids);
                }
                for ($j = 0; $j < count($elementids); $j++) {
                    $sessionkpielementid[$newkpiid][$j] = $elementids[$j]['id'];
                    $returnarray[$newkpiname][$j] = $elementids[$j]['elementName'];


                }
            } else {
                if ($maxelementcount < count($elementids)) {
                    $maxelementcount = count($elementids);
                }

                for ($j = 0; $j < count($elementids); $j++) {
                    $sessionkpielementid[$kpiid][$j] = $elementids[$j]['id'];
                    $returnarray[$kpiname][$j] = $elementids[$j]['elementName'];

                }
            }


        }
        return array('elementids' => $sessionkpielementid);
    }

    private function finddatawithstatus($status, $shipid, $dataofmonth = '')
    {

        if ($dataofmonth == '') {
            $stringdataofmonth = date('Y-m-d');
        }
        if ($dataofmonth != '') {
            $mydate = '01-' . $dataofmonth;
            $time = strtotime($mydate);
            $stringdataofmonth = date('Y-m-d', $time);

        }
        $new_date = new \DateTime($stringdataofmonth);
        $new_date->modify('last day of this month');
        $em = $this->getDoctrine()->getManager();

        $elementvalues = array();
        $returnarray = array();
        $elementweightage = array();
        $elementindicationValue = array();
        $symbolIndication = array();
        $resularray = $em->createQueryBuilder()
            ->select('b.value')
            ->from('InitialShippingBundle:ReadingKpiValues', 'b')
            ->where('b.shipDetailsId = :shipdetailsid')
            ->andWhere('b.monthdetail =:dataofmonth')
            ->andWhere('b.status = :status')
            ->setParameter('shipdetailsid', $shipid)
            ->setParameter('dataofmonth', $new_date)
            ->setParameter('status', $status)
            ->getQuery()
            ->getResult();
        $query = $em->createQueryBuilder()
            ->select('b.kpiName', 'b.id')
            ->from('InitialShippingBundle:KpiDetails', 'b')
            ->where('b.shipDetailsId = :shipdetailsid')
            ->setParameter('shipdetailsid', $shipid)
            ->add('orderBy', 'b.id  ASC ')
            ->getQuery();
        $ids = $query->getResult();
        $maxelementcount = 0;
        $k = 0;
        for ($i = 0; $i < count($ids); $i++) {
            $kpiid = $ids[$i]['id'];
            $kpiname = $ids[$i]['kpiName'];
            $query = $em->createQueryBuilder()
                ->select('b.elementName', 'b.id', 'b.indicationValue', 'b.weightage', 'c.symbolIndication')
                ->from('InitialShippingBundle:ElementDetails', 'b')
                ->leftjoin('InitialShippingBundle:ElementSymbols', 'c', 'WITH', 'c.id = b.symbolId')
                ->where('b.kpiDetailsId = :kpidetailsid')
                ->setParameter('kpidetailsid', $kpiid)
                ->add('orderBy', 'b.id  ASC ')
                ->getQuery();
            $elementids = $query->getResult();
            if (count($elementids) == 0) {
                $query1 = $em->createQueryBuilder()
                    ->select('b.kpiName', 'b.id')
                    ->from('InitialShippingBundle:KpiDetails', 'b')
                    ->where('b.kpiName = :kpiName')
                    ->setParameter('kpiName', $kpiname)
                    ->add('orderBy', 'b.id  ASC ')
                    ->groupby('b.kpiName')
                    ->getQuery();
                $ids1 = $query1->getResult();
                $newkpiid = $ids1[0]['id'];
                $newkpiname = $ids1[0]['kpiName'];
                $query = $em->createQueryBuilder()
                    ->select('b.elementName', 'b.id', 'b.indicationValue', 'b.weightage', 'c.symbolIndication')
                    ->from('InitialShippingBundle:ElementDetails', 'b')
                    ->leftjoin('InitialShippingBundle:ElementSymbols', 'c', 'WITH', 'c.id = b.symbolId')
                    ->where('b.kpiDetailsId = :kpidetailsid')
                    ->setParameter('kpidetailsid', $newkpiid)
                    ->add('orderBy', 'b.id  ASC ')
                    ->getQuery();
                $elementids = $query->getResult();
                if ($maxelementcount < count($elementids)) {
                    $maxelementcount = count($elementids);
                }
                for ($j = 0; $j < count($elementids); $j++) {
                    $returnarray[$newkpiname][$j] = $elementids[$j]['elementName'];
                    array_push($elementweightage, $elementids[$j]['weightage']);
                    $indicationvalue = $elementids[$j]['symbolIndication'];
                    if ($indicationvalue == null) {
                        array_push($symbolIndication, "");
                    } else {
                        array_push($symbolIndication, $elementids[$j]['symbolIndication']);
                    }
                    array_push($elementindicationValue, $elementids[$j]['indicationValue']);
                }
            } else {
                if ($maxelementcount < count($elementids)) {
                    $maxelementcount = count($elementids);
                }
                for ($j = 0; $j < count($elementids); $j++) {
                    $returnarray[$kpiname][$j] = $elementids[$j]['elementName'];
                    array_push($elementweightage, $elementids[$j]['weightage']);
                    $indicationvalue = $elementids[$j]['symbolIndication'];
                    if ($indicationvalue == null) {
                        array_push($symbolIndication, "");
                    } else {
                        array_push($symbolIndication, $elementids[$j]['symbolIndication']);
                    }
                    array_push($elementindicationValue, $elementids[$j]['indicationValue']);
                }
            }
        }
        for ($kkk = 0; $kkk < count($resularray); $kkk++) {
            array_push($elementvalues, $resularray[$kkk]['value']);
        }

        return array(
            'elementvalues' => $elementvalues,
            'symbolIndication' => $symbolIndication,
            'indicationValue' => $elementindicationValue,
            'elementweightage' => $elementweightage,
            'elementnamekpiname' => $returnarray,
            'maxelementcount' => $maxelementcount,
            'kpi_details' => $ids
        );
    }

    private function finddatawithstatus_MonthChange($status, $status1, $shipid, $dataofmonth = '')
    {


        if ($dataofmonth == '') {
            $stringdataofmonth = date('Y-m-d');
        }
        if ($dataofmonth != '') {
            $mydate = '01-' . $dataofmonth;
            $time = strtotime($mydate);
            $stringdataofmonth = date('Y-m-d', $time);

        }

        $new_date = new \DateTime($stringdataofmonth);
        $new_date->modify('last day of this month');
        $em = $this->getDoctrine()->getManager();

        $elementvalues = array();
        $returnarray = array();
        $elementweightage = array();

        $resularray = $em->createQueryBuilder()
            ->select('b.value')
            ->from('InitialShippingBundle:ReadingKpiValues', 'b')
            ->where('b.shipDetailsId = :shipdetailsid')
            ->andWhere('b.monthdetail =:dataofmonth')
            ->andWhere('b.status = ' . $status . ' OR b.status  = ' . $status1 . '')
            ->setParameter('shipdetailsid', $shipid)
            ->setParameter('dataofmonth', $new_date)
            ->getQuery()
            ->getResult();
        $query = $em->createQueryBuilder()
            ->select('b.kpiName', 'b.id')
            ->from('InitialShippingBundle:KpiDetails', 'b')
            ->where('b.shipDetailsId = :shipdetailsid')
            ->setParameter('shipdetailsid', $shipid)
            ->add('orderBy', 'b.id  ASC ')
            ->getQuery();
        $ids = $query->getResult();
        $maxelementcount = 0;
        $k = 0;
        for ($i = 0; $i < count($ids); $i++) {
            $kpiid = $ids[$i]['id'];
            $kpiname = $ids[$i]['kpiName'];
            $query = $em->createQueryBuilder()
                ->select('b.elementName', 'b.id', 'b.weightage')
                ->from('InitialShippingBundle:ElementDetails', 'b')
                ->where('b.kpiDetailsId = :kpidetailsid')
                ->setParameter('kpidetailsid', $kpiid)
                ->add('orderBy', 'b.id  ASC ')
                ->getQuery();
            $elementids = $query->getResult();
            if (count($elementids) == 0) {
                $query1 = $em->createQueryBuilder()
                    ->select('b.kpiName', 'b.id')
                    ->from('InitialShippingBundle:KpiDetails', 'b')
                    ->where('b.kpiName = :kpiName')
                    ->setParameter('kpiName', $kpiname)
                    ->add('orderBy', 'b.id  ASC ')
                    ->groupby('b.kpiName')
                    ->getQuery();
                $ids1 = $query1->getResult();
                $newkpiid = $ids1[0]['id'];
                $newkpiname = $ids1[0]['kpiName'];
                $query = $em->createQueryBuilder()
                    ->select('b.elementName', 'b.id', 'b.weightage')
                    ->from('InitialShippingBundle:ElementDetails', 'b')
                    ->where('b.kpiDetailsId = :kpidetailsid')
                    ->setParameter('kpidetailsid', $newkpiid)
                    ->add('orderBy', 'b.id  ASC ')
                    ->getQuery();
                $elementids = $query->getResult();
                if ($maxelementcount < count($elementids)) {
                    $maxelementcount = count($elementids);
                }
                for ($j = 0; $j < count($elementids); $j++) {
                    // $sessionkpielementid[$newkpiid][$j] = $elementids[$j]['id'];
                    $returnarray[$newkpiname][$j] = $elementids[$j]['elementName'];
                    array_push($elementweightage, $elementids[$j]['weightage']);
                }
            } else {
                if ($maxelementcount < count($elementids)) {
                    $maxelementcount = count($elementids);
                }
                for ($j = 0; $j < count($elementids); $j++) {
                    // $sessionkpielementid[$kpiid][$j] = $elementids[$j]['id'];
                    $returnarray[$kpiname][$j] = $elementids[$j]['elementName'];
                    array_push($elementweightage, $elementids[$j]['weightage']);
                }
            }
        }
        for ($kkk = 0; $kkk < count($resularray); $kkk++) {
            array_push($elementvalues, $resularray[$kkk]['value']);
        }

        return array('elementvalues' => $elementvalues, 'elementweightage' => $elementweightage, 'elementnamekpiname' => $returnarray, 'maxelementcount' => $maxelementcount);
    }


    /**
     * Adding Kpi Values.
     *
     * @Route("/{buttonid}/addkpivalues", name="addkpivaluesname")
     */
    public function addkpivaluesAction(Request $request, $buttonid)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $userid = $user->getId();
        $userId = $user->getId();
        $username = $user->getUsername();
        $reject_id_array = $request->request->get('reject_id');
        $shipid = $request->request->get('shipid');
        $requestedshipid = $request->request->get('shipid');
        $returnfromcontroller = $this->findelementkpiid($shipid);
        $kpiandelementids = $returnfromcontroller['elementids'];
        $elementvalues = $request->request->get('newelemetvalues');
        $dataofmonth = $request->request->get('dataofmonth');
        $new_date = new \DateTime($dataofmonth);
        $new_date->modify('last day of this month');
        $newtemp_date = date_format($new_date, "M-Y");
        $tempdate = date_format($new_date, "d-M-Y");
        $baseValueChangeMonth = date_format($new_date, "m");
        $k = 0;
        $rejection_obj = null;
        $reject_status = 'empty';
        $returnmsg = '';
        $newshipid = $em->getRepository('InitialShippingBundle:ShipDetails')->findOneBy(array('id' => $requestedshipid));
        $newlookupstatus = "";
        $data_view = '';

        $dateObject = new \DateTime();
        $dateObject->modify('last day of this month');

        if ($this->container->get('security.context')->isGranted('ROLE_ADMIN')) {
            $query = $em->createQueryBuilder()
                ->select('a.shipName', 'a.id')
                ->from('InitialShippingBundle:ShipDetails', 'a')
                ->join('InitialShippingBundle:CompanyDetails', 'b', 'WITH', 'b.id = a.companyDetailsId')
                ->where('b.adminName = :username')
                ->andWhere('a.dateTime <= :activeDate')
                ->setParameter('activeDate', $dateObject)
                ->groupBy('a.id')
                ->setParameter('username', $username)
                ->getQuery();
        } else {
            $query = $em->createQueryBuilder()
                ->select('a.shipName', 'a.id')
                ->from('InitialShippingBundle:ShipDetails', 'a')
                ->leftjoin('InitialShippingBundle:User', 'b', 'WITH', 'b.companyid = a.companyDetailsId')
                ->where('a.dateTime <= :activeDate')
                ->setParameter('activeDate', $dateObject)
                ->andwhere('b.id = :userId')
                ->setParameter('userId', $userId)
                ->groupBy('a.id')
                ->getQuery();
        }

        $shipDetails = $query->getResult();

        if ($buttonid == 'updatebuttonid' || $buttonid == 'adminbuttonid' || $buttonid == 'verfiybuttonid') {

            $returnarrayids = $em->createQueryBuilder()
                ->select('b.id')
                ->from('InitialShippingBundle:ReadingKpiValues', 'b')
                ->where('b.shipDetailsId = :shipdetailsid')
                ->andWhere('b.monthdetail =:dataofmonth')
                ->setParameter('shipdetailsid', $shipid)
                ->setParameter('dataofmonth', $new_date)
                ->getQuery()
                ->getResult();
            for ($kkk = 0; $kkk < count($elementvalues); $kkk++) {
                $entityobject = $em->getRepository('InitialShippingBundle:ReadingKpiValues')->find($returnarrayids[$kkk]['id']);
                if ($buttonid == 'adminbuttonid') {
                    $entityobject->setValue($elementvalues[$kkk]);
                    $entityobject->setStatus(3);
                }
                if ($buttonid == 'verfiybuttonid') {
                    $entityobject->setValue($elementvalues[$kkk]);
                    $entityobject->setStatus(2);
                }
                if ($buttonid == 'updatebuttonid') {
                    $entityobject->setValue($elementvalues[$kkk]);
                    $entityobject->setStatus(1);
                }

                $em->flush();
            }

            $returnmsg = ' Data Updated...';

            if ($buttonid == 'adminbuttonid') {
                $rankinglookuptable = array('shipid' => $shipid, 'dataofmonth' => $tempdate, 'userid' => $userid, 'status' => 3, 'datetime' => date('Y-m-d H:i:s'));
                // $lookstatus = $em->getRepository('InitialShippingBundle:Ranking_LookupStatus')->findBy(array('shipid' => $newshipid,'dataofmonth'=>$new_date));
                $lookstatus = $em->getRepository('InitialShippingBundle:Scorecard_LookupStatus')->findBy(array('dataofmonth' => $new_date));
                if (count($lookstatus) != 0) {
                    $newlookupstatus = $lookstatus[0];
                    $TotalShipsInserted = $em->createQueryBuilder()
                        ->select('identity(a.shipDetailsId)')
                        ->from('InitialShippingBundle:ReadingKpiValues', 'a')
                        ->where('a.monthdetail = :dateOfMonth and a.status=:statusValue')
                        ->setParameter('dateOfMonth', $new_date)
                        ->groupby('a.shipDetailsId')
                        ->setParameter('statusValue', 3)
                        ->getQuery()
                        ->getResult();
                    if (count($TotalShipsInserted) != 0) {
                        $shipids = array();
                        for ($findshipidcount = 0; $findshipidcount < count($TotalShipsInserted); $findshipidcount++) {
                            array_push($shipids, $TotalShipsInserted[$findshipidcount][1]);
                        }
                        $shipids = implode(',', $shipids);
                    } else {
                        $shipids = $shipid;
                    }

                    $newlookupstatus->setStatus(3);
                    $newlookupstatus->setShipid($shipids);
                    $newlookupstatus->setDatetime(new \DateTime());
                    $em->flush();
                }


                $gearman = $this->get('gearman');
                $gearman->doBackgroundJob('InitialShippingBundleserviceReadExcelWorker~addscorecardlookupdataupdate', json_encode($rankinglookuptable));
            }

            if ($buttonid == 'verfiybuttonid') {
                //$lookstatus = $em->getRepository('InitialShippingBundle:Ranking_LookupStatus')->findBy(array('shipid' => $newshipid,'dataofmonth'=>$new_date));
                $lookstatus = $em->getRepository('InitialShippingBundle:Scorecard_LookupStatus')->findBy(array('dataofmonth' => $new_date));
                if (count($lookstatus) != 0) {
                    $newlookupstatus = $lookstatus[0];

                    $pre_rejections = $em->createQueryBuilder()
                        ->select('a.temp_rejections, a.rejections, a.rejectionsStatus')
                        ->from('InitialShippingBundle:Scorecard_LookupStatus', 'a')
                        ->where('a.dataofmonth = :dataofmonth')
                        ->setParameter('dataofmonth', $new_date)
                        ->getQuery()
                        ->getResult();

                    if (count($pre_rejections) > 0) {
                        if ($pre_rejections[0]['rejections'] != null && $pre_rejections[0]['rejections'] != "null" && $pre_rejections[0]['rejectionsStatus']) {
                            $temp = json_decode($pre_rejections[0]['rejections']);
                            $temp = json_decode($pre_rejections[0]['rejections'], true);
                            if (array_key_exists($shipid, $temp)) {
                                unset($temp[$shipid]);
                                if (count($temp) <= 0) {
                                    $temp = null;
                                }
                                $temp = json_encode($temp);
                            } else {
                                $temp = json_encode($temp);
                            }
                            $reject_status = 'yes';
                            $rejection_obj = $temp;

                        } else {
                            $rejection_obj = null;
                            $reject_status = 'empty';
                        }
                    } else {
                        $rejection_obj = null;
                        $reject_status = 'empty';
                    }

                    if(!$pre_rejections[0]['rejectionsStatus']) {
                        $TotalShipsInserted = $em->createQueryBuilder()
                            ->select('a.shipid')
                            ->from('InitialShippingBundle:Scorecard_LookupStatus', 'a')
                            ->where('a.dataofmonth = :dateOfMonth and a.status=:statusValue')
                            ->setParameter('dateOfMonth', $new_date)
                            ->setParameter('statusValue', 2)
                            ->getQuery()
                            ->getResult();
                        if (count($TotalShipsInserted) != 0) {
                            $updated_ship_id_array = explode(',',$TotalShipsInserted[0]['shipid']);
                            $shipids = array();
                            for ($findshipidcount = 0; $findshipidcount < count($updated_ship_id_array); $findshipidcount++) {
                                array_push($shipids, $updated_ship_id_array[$findshipidcount]);
                            }
                            if (!in_array($shipid, $shipids)) {
                                array_push($shipids, $shipid);
                            }
                            $shipids = implode(',', $shipids);
                        } else {
                            $shipids = $shipid;
                        }
                        $newlookupstatus->setShipid($shipids);
                    }

                    $newlookupstatus->setStatus(2);
                    $newlookupstatus->setDatetime(new \DateTime());
                    $newlookupstatus->setRejections($rejection_obj);
                    $em->flush();
                }

            }

            if ($buttonid == 'updatebuttonid') {
                $lookstatus = $em->getRepository('InitialShippingBundle:Scorecard_LookupStatus')->findBy(array('dataofmonth' => $new_date));
                if (count($lookstatus) != 0) {

                    $bool_rejection_status = false;

                    $pre_rejections = $em->createQueryBuilder()
                        ->select('a.temp_rejections')
                        ->from('InitialShippingBundle:Scorecard_LookupStatus', 'a')
                        ->where('a.dataofmonth = :dataofmonth')
                        ->setParameter('dataofmonth', $new_date)
                        ->getQuery()
                        ->getResult();

                    if (count($pre_rejections) > 0) {
                        if ($pre_rejections[0]['temp_rejections'] != null && $pre_rejections[0]['temp_rejections'] != "null") {
                            $data_view = 'finished';
                            $temp = json_decode($pre_rejections[0]['temp_rejections']);
                            $temp = json_decode($pre_rejections[0]['temp_rejections'], true);
                            if (array_key_exists($shipid, $temp)) {
                                unset($temp[$shipid]);
                                if (count($temp) <= 0) {
                                    $temp = null;
                                    $bool_rejection_status = false;
                                } else {
                                    $bool_rejection_status = true;
                                }
                                $temp = json_encode($temp);
                            } else {
                                if (count($temp) <= 0) {
                                    $bool_rejection_status = false;
                                } else {
                                    $bool_rejection_status = true;
                                }
                                $temp = json_encode($temp);
                            }
                            $reject_status = 'yes';
                            $rejection_obj = $temp;
                        } else {
                            $bool_rejection_status = false;
                            $rejection_obj = null;
                            $reject_status = 'empty';
                        }
                    } else {
                        $bool_rejection_status = false;
                        $rejection_obj = null;
                        $reject_status = 'empty';
                    }

                    $newlookupstatus = $lookstatus[0];
                    /*$TotalShipsInserted = $em->createQueryBuilder()
                        ->select('identity(a.shipDetailsId)')
                        ->from('InitialShippingBundle:ReadingKpiValues', 'a')
                        ->where('a.monthdetail = :dateOfMonth and a.status=:statusValue')
                        ->setParameter('dateOfMonth', $new_date)
                        ->groupby('a.shipDetailsId')
                        ->setParameter('statusValue', 1)
                        ->getQuery()
                        ->getResult();

                    if (count($TotalShipsInserted) != 0) {
                        $shipids = array();
                        for ($findshipidcount = 0; $findshipidcount < count($TotalShipsInserted); $findshipidcount++) {
                            array_push($shipids, $TotalShipsInserted[$findshipidcount][1]);
                        }
                        $shipids = implode(',', $shipids);
                    } else {
                        $shipids = $shipid;
                    }*/

//                    $newlookupstatus->setStatus(1);
//                    $newlookupstatus->setShipid($shipids);
                    $newlookupstatus->setTempRejections($rejection_obj);
                    $newlookupstatus->setDatetime(new \DateTime());
                    $newlookupstatus->setRejectionsStatus($bool_rejection_status);
                    $em->flush();
                }
            }
        }

        if ($buttonid == 'savebuttonid') {

            foreach ($kpiandelementids as $element) {
                for ($elementCount = 0; $elementCount < count($element); $elementCount++) {
                    $baseValueQuery = $em->createQueryBuilder()
                        ->select('a.baseValue')
                        ->from('InitialShippingBundle:ElementDetails', 'a')
                        ->where('a.id=:elementId')
                        ->setParameter('elementId', $element[$elementCount])
                        ->getQuery()
                        ->getResult();
                    $baseValue = $baseValueQuery[0]['baseValue'];
                    if ($baseValue != 0) {
                        $baseValueForAllShips = $baseValue * count($shipDetails);
                        $currentMonth = (int)$baseValueChangeMonth;
                        $monthlyCount = $baseValueForAllShips / 12;
                        $currentMonthValue = (int)$currentMonth * $monthlyCount;
                        $elementRulesQuery = $em->createQueryBuilder()
                            ->select('a.rules,a.id')
                            ->from('InitialShippingBundle:Rules', 'a')
                            ->where('a.elementDetailsId=:elementId')
                            ->setParameter('elementId', $element[$elementCount])
                            ->getQuery()
                            ->getResult();
                        foreach ($elementRulesQuery as $rules) {
                            $currentMonthValue1 = 0;
                            $ruleObj = json_decode($rules['rules']);
                            $ruleColor = $ruleObj->actions->value;
                            if ($ruleColor == 'Green') {
                                $currentMonthValue1 = (int)$currentMonthValue;
                            } else if ($ruleColor == 'Yellow') {
                                $currentMonthValue1 = (int)$currentMonthValue + (int)$currentMonthValue / 4;
                            } else if ($ruleColor == 'Red') {
                                $currentMonthValue1 = (int)$currentMonthValue + (int)$currentMonthValue / 4;
                            }
                            $ruleObj->conditions->all[0]->value = $currentMonthValue1;
                            $ruleString = json_encode($ruleObj);
                            $rulesDetailObject = $em->getRepository('InitialShippingBundle:Rules')->find($rules['id']);
                            $rulesDetailObject->setRules($ruleString);
                            $em->flush();
                        }
                    }
                }
            }
            foreach ($kpiandelementids as $kpikey => $kpipvalue) {

                $newkpiid = $em->getRepository('InitialShippingBundle:KpiDetails')->findOneBy(array('id' => $kpikey));
                foreach ($kpipvalue as $elementkey => $elementvalue) {
                    $newelementid = $em->getRepository('InitialShippingBundle:ElementDetails')->findOneBy(array('id' => $elementvalue));
                    $readingkpivalue = new ReadingKpiValues();
                    $readingkpivalue->setKpiDetailsId($newkpiid);
                    $readingkpivalue->setElementDetailsId($newelementid);
                    $readingkpivalue->setShipDetailsId($newshipid);
                    $readingkpivalue->setMonthdetail($new_date);
                    $readingkpivalue->setValue($elementvalues[$k]);
                    $readingkpivalue->setStatus(1);
                    $em->persist($readingkpivalue);
                    $em->flush();
                    $k++;
                }
            }
            $returnmsg = ' Data Saved...';
            $protocol = empty($_SERVER['HTTPS']) ? 'http' : 'https';
            $domain = $_SERVER['SERVER_NAME'];
            $url = $protocol . '://' . $domain . '/login';
            /*$fullurl = "http://shipreports/login";
            $mailer = $this->container->get('mailer');
            $message = \Swift_Message::newInstance()
                ->setFrom('lawrance@commusoft.co.uk')
                ->setTo("doss.cclawranc226@gmail.com")
                ->setSubject($newshipid->getShipName() . ' Data Added By V-Ship Team')
                ->setBody("This Web Url:" . $url);
            $mailer->send($message);*/

            $lookstatus = $em->getRepository('InitialShippingBundle:Scorecard_LookupStatus')->findBy(array('dataofmonth' => $new_date));
            if (count($lookstatus) != 0) {
                $newlookupstatus = $lookstatus[0];
                $TotalShipsInserted = $em->createQueryBuilder()
                    ->select('identity(a.shipDetailsId)')
                    ->from('InitialShippingBundle:ReadingKpiValues', 'a')
                    ->where('a.monthdetail = :dateOfMonth and a.status=:statusValue')
                    ->setParameter('dateOfMonth', $new_date)
                    ->groupby('a.shipDetailsId')
                    ->setParameter('statusValue', 1)
                    ->getQuery()
                    ->getResult();

                if (count($TotalShipsInserted) != 0) {
                    $shipids = array();
                    for ($findshipidcount = 0; $findshipidcount < count($TotalShipsInserted); $findshipidcount++) {
                        array_push($shipids, $TotalShipsInserted[$findshipidcount][1]);
                    }
                    $shipids = implode(',', $shipids);
                } else {
                    $shipids = $shipid;
                }

                $newlookupstatus->setShipid($shipids);
                $newlookupstatus->setDatetime(new \DateTime());
                $em->flush();
            } else {
                $lookupstatusobject = new Scorecard_LookupStatus();
                $lookupstatusobject->setShipid($requestedshipid);
                $lookupstatusobject->setStatus(1);
                $lookupstatusobject->setDataofmonth($new_date);
                $lookupstatusobject->setDatetime(new \DateTime());
                $lookupstatusobject->setUserid($userid);
                $lookupstatusobject->setRejectionsStatus(false);
                $em->persist($lookupstatusobject);
                $em->flush();
            }

        }

        if ($buttonid == 'upload_btn_id') {

            $status = 5;

            $pre_rejections = $em->createQueryBuilder()
                ->select('a.temp_rejections, a.rejections')
                ->from('InitialShippingBundle:Scorecard_LookupStatus', 'a')
                ->where('a.dataofmonth = :dataofmonth')
                ->setParameter('dataofmonth', $new_date)
                ->getQuery()
                ->getResult();
            if (count($pre_rejections) > 0) {
                if ($pre_rejections[0]['temp_rejections'] != null && $pre_rejections[0]['temp_rejections'] != "null") {
                    $response = new JsonResponse();

                    $response->setData(array (
                        'returnmsg' => 'rejection_error'
                        )
                    );
                    return $response;
                }
                if ($pre_rejections[0]['rejections'] != null && $pre_rejections[0]['rejections'] != "null") {
                    $status = 2;
                }
            }

            foreach ($shipDetails as $vessel) {
                $check_empty_value_query = $em->createQueryBuilder()
                    ->select('a.value')
                    ->from('InitialShippingBundle:ReadingKpiValues', 'a')
                    ->where('a.shipDetailsId = :shipdetailsid')
                    ->andWhere('a.monthdetail =:dataofmonth')
                    ->setParameter('shipdetailsid', $vessel['id'])
                    ->setParameter('dataofmonth', $new_date)
                    ->getQuery()
                    ->getResult();
                foreach ($check_empty_value_query as $check_empty_values) {
                    if ($check_empty_values['value'] != null) {
                        continue;
                    } else {
                        $return_msg = $vessel['shipName'] . ' data is not completed !';
                        $response = new JsonResponse();
                        $response->setData(array(
                            'return_msg' => $return_msg,
                            'status' => -1,
                            'ship_id' => $vessel['id']
                        ));
                        return $response;
                    }
                }
            }

            $lookstatus = $em->getRepository('InitialShippingBundle:Scorecard_LookupStatus')->findBy(array('dataofmonth' => $new_date));
            if (count($lookstatus) != 0) {
                $rejection_string = null;
                $newlookupstatus = $lookstatus[0];

                $newlookupstatus->setStatus($status);
                $newlookupstatus->setTempRejections($rejection_string);
                $newlookupstatus->setDatetime(new \DateTime());
                $newlookupstatus->setRejectionsStatus(false);
                $em->flush();
            }

            /*$reading_kpi_values_status = $em->getRepository('InitialShippingBundle:ReadingKpiValues')->findBy(array('monthdetail' => $new_date));
            if (count($reading_kpi_values_status) != 0) {
                foreach ($reading_kpi_values_status as $reading_kpi_values_status_obj) {
                    $reading_kpi_values_status_obj->setStatus(5);
                    $em->flush();
                }
            }*/

            $ship_status_done_count = -1;
            $returnmsg = ' Data Uploaded...';

        }

        if ($buttonid == 'reject_btn_id') {
            $pre_rejections = $em->createQueryBuilder()
                ->select('a.rejections')
                ->from('InitialShippingBundle:Scorecard_LookupStatus', 'a')
                ->where('a.dataofmonth = :dataofmonth')
                ->setParameter('dataofmonth', $new_date)
                ->getQuery()
                ->getResult();
            $reject_id_string = null;
            if ($reject_id_array != null) {
                $reject_id_string = implode(",", $reject_id_array);
            } else {
                $reject_id_string = 'ALL';
            }

            if (count($pre_rejections) > 0) {
                if ($pre_rejections[0]['rejections'] != null && $pre_rejections[0]['rejections'] != "null") {
                    $temp = json_decode($pre_rejections[0]['rejections']);
                    if ($reject_id_string == 'ALL') {
                        $temp->$shipid = $reject_id_string;
                    } else {
                        $temp->$shipid = explode(',', $reject_id_string);
                    }
                    $temp = json_encode($temp);
                } else {
                    if ($reject_id_string == 'ALL') {
                        $temp = json_encode(array($shipid => $reject_id_string));
                    } else {
                        $temp = json_encode(array($shipid => explode(',', $reject_id_string)));
                    }
                }
            } else {
                if ($reject_id_string == 'ALL') {
                    $temp = json_encode(array($shipid => $reject_id_string));
                } else {
                    $temp = json_encode(array($shipid => explode(',', $reject_id_string)));
                }
            }

            $lookstatus = $em->getRepository('InitialShippingBundle:Scorecard_LookupStatus')->findBy(array('dataofmonth' => $new_date));
            if (count($lookstatus) != 0) {
                $newlookupstatus = $lookstatus[0];

//                $newlookupstatus->setStatus(1);
                $newlookupstatus->setRejections($temp);
                $newlookupstatus->setTempRejections($temp);
                $newlookupstatus->setDatetime(new \DateTime());
                $newlookupstatus->setRejectionsStatus(true);
                $em->flush();
            }
            /*$reading_kpi_values_status = $em->getRepository('InitialShippingBundle:ReadingKpiValues')->findBy(array('monthdetail' => $new_date, 'shipDetailsId' => $shipid));
            if (count($reading_kpi_values_status) != 0) {
                foreach ($reading_kpi_values_status as $reading_kpi_values_status_obj) {
                    $reading_kpi_values_status_obj->setStatus(1);
                    $em->flush();
                }
            }*/
            $ship_status_done_count = -1;
            $returnmsg = ' rejected...';
        }

        if ($buttonid != 'upload_btn_id') {
            $shipname = $newshipid->getShipName();
        } else {
            $shipname = 'All vessels';
        }
        $nextshipid = 0;
        $nextshipname = '';
        $user = $this->getUser();
        $role = $user->getRoles();

        $StatusQuery = $em->createQueryBuilder()
            ->select('a.shipid, a.status, a.rejectionsStatus, a.rejections, a.temp_rejections')
            ->from('InitialShippingBundle:Scorecard_LookupStatus', 'a')
            ->where('a.dataofmonth = :dateOfMonth')
            ->setParameter('dateOfMonth', $new_date)
            ->getQuery()
            ->getResult();

        $updated_vessels = explode(',',$StatusQuery[0]['shipid']);
        $data_status = $StatusQuery[0]['status'];
        $rejection_status = $StatusQuery[0]['rejectionsStatus'];
        $rejections = $StatusQuery[0]['rejections'];

        $fullData = $this->gotoScorecardMonthDataAction($request, 'fetch');
        $shipOverallStatus = '';
        $element_values_array = array();
        $ship_status_array = array();

        $kpielementarray = $this->findnumofshipsAction($request, 'nextshipajaxcall');
        $statusforship = $this->findshipstatusmonth($newtemp_date, $kpielementarray, $role[0]);
        $counts = array_count_values($statusforship);
        $finddatawithstatus = array();


        if ($role[0] == 'ROLE_ADMIN') {
            /*$status = 2;
            $index = array_search(0, $statusforship);
            $nextshipid = $kpielementarray[$index]['id'];
            $nextshipname = $kpielementarray[$index]['shipName'];
            $finddatawithstatus = $this->finddatawithstatus($status, $nextshipid, $newtemp_date);
            if (array_key_exists(3, $counts)) {
                $ship_status_done_count = $counts[3];
            } else {
                $ship_status_done_count = 0;
            }*/

            if($data_status == 2 || $data_status == 3 || $data_status == 4) {
                if(count($updated_vessels) == count($shipDetails)) {
                    if($rejection_status || count($rejections)>0) {
                        $data_view = 'editable-view';
                    } else {
                        $data_view = 'summary-view';
                    }

                } else {
                    $data_view = 'editable-view';
                    foreach ($shipDetails as $ship_list) {
                        if(!in_array($ship_list['id'], $updated_vessels)) {
                            $nextshipid = $ship_list['id'];
                            $nextshipname = $ship_list['shipName'];
                            break;
                        }
                    }
                    $next_month_values = $em->createQueryBuilder()
                        ->select('b.value')
                        ->from('InitialShippingBundle:ReadingKpiValues', 'b')
                        ->where('b.shipDetailsId = :shipdetailsid')
                        ->andWhere('b.monthdetail =:dataofmonth')
                        ->setParameter('shipdetailsid', $shipid)
                        ->setParameter('dataofmonth', $new_date)
                        ->getQuery()
                        ->getResult();
                    $element_values_array[0] = array();
                    foreach($next_month_values as $values) {
                        array_push($element_values_array[0], $values['value']);
                    }
                }
                if(count($updated_vessels)>0 && count($rejections)>0) {
                    foreach ($rejections as $vessel) {
                        if(in_array($vessel['id'], $updated_vessels)) {
                            if($rejections) {
                                if(array_key_exists($vessel['id'], $rejections)  && $rejection_status) {
                                    array_push($ship_status_array, 2);
                                } else {
                                    array_push($ship_status_array, 1);
                                }
                            } else {
                                array_push($ship_status_array, 1);
                            }
                        } else {
                            array_push($ship_status_array, 0);
                        }
                    }
                }

            } else {
                $data_view = 'editable-view';
                foreach ($shipDetails as $vessel) {
                    array_push($ship_status_array, 0);
                }
            }

        }

        if ($role[0] == 'ROLE_MANAGER') {
            /*$status = 5;
            $index = array_search(0, $statusforship);
            $nextshipid = $kpielementarray[$index]['id'];
            $nextshipname = $kpielementarray[$index]['shipName'];
            $finddatawithstatus = $this->finddatawithstatus($status, $nextshipid, $newtemp_date);
            if (array_key_exists(2, $counts)) {
                $ship_status_done_count = $counts[2];
            } else {
                $ship_status_done_count = 0;
            }*/
            if($data_status == 2 || $data_status == 3 || $data_status == 4 || $data_status == 5) {
                if(count($updated_vessels) == count($shipDetails)) {
                    if($rejection_status || count($rejections)>0) {
                        $data_view = 'editable-view';
                    } else {
                        $data_view = 'summary-view';
                    }

                } else {
                    $data_view = 'editable-view';
                    foreach ($shipDetails as $ship_list) {
                        if(!in_array($ship_list['id'], $updated_vessels)) {
                            $nextshipid = $ship_list['id'];
                            $nextshipname = $ship_list['shipName'];
                            break;
                        }
                    }
                    $next_month_values = $em->createQueryBuilder()
                        ->select('b.value')
                        ->from('InitialShippingBundle:ReadingKpiValues', 'b')
                        ->where('b.shipDetailsId = :shipdetailsid')
                        ->andWhere('b.monthdetail =:dataofmonth')
                        ->setParameter('shipdetailsid', $shipid)
                        ->setParameter('dataofmonth', $new_date)
                        ->getQuery()
                        ->getResult();
                    $element_values_array[0] = array();
                    foreach($next_month_values as $values) {
                        array_push($element_values_array[0], $values['value']);
                    }
                }

                if(count($updated_vessels)>0 && count($rejections)>0) {
                    foreach ($rejections as $vessel) {
                        if(in_array($vessel['id'], $updated_vessels)) {
                            if($rejections) {
                                if(array_key_exists($vessel['id'], $rejections)  && $rejection_status) {
                                    array_push($ship_status_array, 2);
                                } else {
                                    array_push($ship_status_array, 1);
                                }
                            } else {
                                array_push($ship_status_array, 1);
                            }
                        } else {
                            array_push($ship_status_array, 0);
                        }
                    }
                }

            } else {
                $data_view = 'editable-view';
                foreach ($shipDetails as $vessel) {
                    array_push($ship_status_array, 0);
                }
            }
        }

        if ($role[0] == 'ROLE_KPI_INFO_PROVIDER') {
            /*$status = 0;
            $index = array_search(0, $statusforship);
            $nextshipid = $kpielementarray[$index]['id'];
            $nextshipname = $kpielementarray[$index]['shipName'];
            $finddatawithstatus = $this->finddatawithstatus($status, $nextshipid, $newtemp_date);
            if (array_key_exists(1, $counts)) {
                $ship_status_done_count = $counts[1];
            } else {
                $ship_status_done_count = 0;
            }*/

            $rejections = $StatusQuery[0]['temp_rejections'];
            if($data_status == 2 || $data_status == 3 || $data_status == 4 || $data_status == 5) {
                if(count($updated_vessels) == count($shipDetails)) {
                    if($rejection_status || count($rejections)>0) {
                        if($rejections != 'null' && $rejections != null) {
                            $data_view = 'editable-view';
                        } else {
                            $data_view = 'summary-view';
                        }
                    } else {
                        $data_view = 'summary-view';
                    }
                } else {
                    $data_view = 'editable-view';
                    foreach ($shipDetails as $ship_list) {
                        if (!in_array($ship_list['id'], $updated_vessels)) {
                            $nextshipid = $ship_list['id'];
                            $nextshipname = $ship_list['shipName'];
                            break;
                        }
                    }
                    if(count($updated_vessels)>0 && count($rejections)>0) {
                        foreach ($rejections as $vessel) {
                            if(in_array($vessel['id'], $updated_vessels)) {
                                if($rejections) {
                                    if(array_key_exists($vessel['id'], $rejections)  && $rejection_status) {
                                        array_push($ship_status_array, 2);
                                    } else {
                                        array_push($ship_status_array, 1);
                                    }
                                } else {
                                    array_push($ship_status_array, 1);
                                }
                            } else {
                                array_push($ship_status_array, 0);
                            }
                        }
                    }
                }
            } else {
                $data_view = 'editable-view';
                foreach ($shipDetails as $vessel) {
                    array_push($ship_status_array, 0);
                }
                foreach ($shipDetails as $ship_list) {
                    if (!in_array($ship_list['id'], $updated_vessels)) {
                        $nextshipid = $ship_list['id'];
                        $nextshipname = $ship_list['shipName'];
                        break;
                    }
                }
            }
        }

        $response = new JsonResponse();
        /*if (count($finddatawithstatus) == 7) {
            $response->setData(array('returnmsg' => $shipname . $returnmsg,
                'shipname' => $nextshipname,
                'shipid' => $nextshipid,
                'kpiNameArray' => $finddatawithstatus['elementnamekpiname'],
                'elementcount' => $finddatawithstatus['maxelementcount'],
                'elementweightage' => $finddatawithstatus['elementweightage'],
                'elementvalues' => $element_values_array,
                'kpi_details' => $finddatawithstatus['kpi_details'],
                'shipcount' => count($statusforship),
                'indicationValue' => $finddatawithstatus['indicationValue'],
                'symbolIndication' => $finddatawithstatus['symbolIndication'], 'currentdatetime' => $dataofmonth,
                'ship_status_done_count' => $ship_status_done_count,
                'rejections' => $rejection_obj,
                'rejection_status' => $reject_status,
                'status' => 1,
                'data_view' => $data_view
            ));
            return $response;
        } else {

            $response->setData(array('returnmsg' => $shipname . $returnmsg, 'currentdatetime' => $dataofmonth,
                'shipname' => $nextshipname,
                'shipid' => $nextshipid,
                'kpiNameArray' => array(),
                'elementcount' => 0,
                'status' => 1,
                'elementvalues' => array()));

            return $response;
        }*/

        $response->setData(array(
            'shipname' => $nextshipname,
            'shipid' => $nextshipid,
            'listofships' => $shipDetails,
            'shipcount' => count($shipDetails),
            'elementkpiarray' => $fullData['elementkpiarray'], 'elementcount' => $fullData['maxelementcount'],
            'elementvalues' => $element_values_array,
            'elementweightage' => $fullData['elementweightage'],
            'indicationValue' => $fullData['indicationValue'],
            'symbolIndication' => $fullData['symbolIndication'],
            'kpi_details' => $fullData['kpi_details'],
            'currentshipid' => $nextshipid, 'currentshipname' => $nextshipname,
            'statuscount' => count($updated_vessels), 'currentdatetime' => $dataofmonth,
            'data_view' => $data_view, 'ship_status' => $ship_status_array, 'ship_btn_status' => ' ',
            'statusQueryResult' => $StatusQuery
        ));

        return $response;

    }


    /**
     * Element and Kpi for Particular Ships.
     *
     * @Route("/{shipid}/{monthdetail}/shipskpielment", name="shipskpielment")
     */
    public function shipskpielmentAction(Request $request, $shipid, $monthdetail, $mode = '')
    {
        $em = $this->getDoctrine()->getManager();
        $mydate = '01-' . $monthdetail;
        $time = strtotime($mydate);
        $newformat = date('Y-m-d', $time);
        $new_date = new \DateTime($newformat);
        $new_date->modify('last day of this month');
        $user = $this->getUser();
        $role = $user->getRoles();
        $status = 0;
        $resularray = array();
        if ($role[0] == 'ROLE_ADMIN') {
            $query = $em->createQueryBuilder()
                ->select('b.value')
                ->from('InitialShippingBundle:ReadingKpiValues', 'b')
                ->where('b.shipDetailsId = :shipdetailsid')
                ->andWhere('b.monthdetail =:dataofmonth')
                ->setParameter('shipdetailsid', $shipid)
                ->setParameter('dataofmonth', $new_date)
                ->getQuery();
        }
        if ($role[0] == 'ROLE_MANAGER') {
            $query = $em->createQueryBuilder()
                ->select('b.value')
                ->from('InitialShippingBundle:ReadingKpiValues', 'b')
                ->where('b.shipDetailsId = :shipdetailsid')
                ->andWhere('b.monthdetail =:dataofmonth')
                ->setParameter('shipdetailsid', $shipid)
                ->setParameter('dataofmonth', $new_date)
                ->getQuery();
        }
        if ($role[0] == 'ROLE_KPI_INFO_PROVIDER') {
            $query = $em->createQueryBuilder()
                ->select('b.value')
                ->from('InitialShippingBundle:ReadingKpiValues', 'b')
                ->where('b.shipDetailsId = :shipdetailsid')
                ->andWhere('b.monthdetail =:dataofmonth')
                ->setParameter('shipdetailsid', $shipid)
                ->setParameter('dataofmonth', $new_date)
                ->getQuery();
        }
        $resularray = $query->getResult();

        $lookup_status = $em->createQueryBuilder()
            ->select('a.status, a.rejections')
            ->from('InitialShippingBundle:Scorecard_LookupStatus', 'a')
            ->where('a.dataofmonth = :dataofmonth')
            ->setParameter('dataofmonth', $new_date)
            ->getQuery()
            ->getResult();
        if (count($lookup_status) > 0) {
            if ($lookup_status[0]['rejections'] != null && $lookup_status[0]['rejections'] != "null") {
                $rejection_string = $lookup_status[0]['rejections'];
                $rejection_obj = json_decode($rejection_string);
                $rejection_obj = json_decode($rejection_string, true);
                if (array_key_exists($shipid, $rejection_obj)) {
                    $reject_status = 'yes';
                    $rejection_obj = $rejection_obj[$shipid];
                } else {
                    $rejection_obj = null;
                    $reject_status = 'empty';
                }

            } else {
                $rejection_obj = null;
                $reject_status = 'empty';
            }
        } else {
            $rejection_obj = null;
            $reject_status = 'empty';
        }

        $query = $em->createQueryBuilder()
            ->select('b.kpiName', 'b.id')
            ->from('InitialShippingBundle:KpiDetails', 'b')
            ->where('b.shipDetailsId = :shipdetailsid')
            ->setParameter('shipdetailsid', $shipid)
            ->add('orderBy', 'b.id  ASC ')
            ->getQuery();

        $ids = $query->getResult();
        $maxelementcount = 0;

        $returnarray = array();
        $elementweightage = array();
        $elementindicationValue = array();
        $symbolIndication = array();
        $sessionkpielementid = array();
        $k = 0;
        for ($i = 0; $i < count($ids); $i++) {
            $kpiid = $ids[$i]['id'];
            $kpiname = $ids[$i]['kpiName'];

            $query = $em->createQueryBuilder()
                ->select('b.elementName', 'b.id', 'b.indicationValue', 'b.weightage', 'c.symbolIndication')
                ->from('InitialShippingBundle:ElementDetails', 'b')
                ->leftjoin('InitialShippingBundle:ElementSymbols', 'c', 'WITH', 'c.id = b.symbolId')
                ->where('b.kpiDetailsId = :kpidetailsid')
                ->setParameter('kpidetailsid', $kpiid)
                ->add('orderBy', 'b.id  ASC ')
                ->getQuery();
            $elementids = $query->getResult();
            if (count($elementids) == 0) {
                $query1 = $em->createQueryBuilder()
                    ->select('b.kpiName', 'b.id')
                    ->from('InitialShippingBundle:KpiDetails', 'b')
                    ->where('b.kpiName = :kpiName')
                    ->setParameter('kpiName', $kpiname)
                    ->add('orderBy', 'b.id  ASC ')
                    ->groupby('b.kpiName')
                    ->getQuery();

                $ids1 = $query1->getResult();
                $newkpiid = $ids1[0]['id'];
                $newkpiname = $ids1[0]['kpiName'];
                $query = $em->createQueryBuilder()
                    ->select('b.elementName', 'b.id', 'b.indicationValue', 'b.weightage', 'c.symbolIndication')
                    ->from('InitialShippingBundle:ElementDetails', 'b')
                    ->leftjoin('InitialShippingBundle:ElementSymbols', 'c', 'WITH', 'c.id = b.symbolId')
                    ->where('b.kpiDetailsId = :kpidetailsid')
                    ->setParameter('kpidetailsid', $newkpiid)
                    ->add('orderBy', 'b.id  ASC ')
                    ->getQuery();
                $elementids = $query->getResult();
                if ($maxelementcount < count($elementids)) {
                    $maxelementcount = count($elementids);
                }
                for ($j = 0; $j < count($elementids); $j++) {
                    $sessionkpielementid[$newkpiid][$j] = $elementids[$j]['id'];
                    $returnarray[$newkpiname][$j] = $elementids[$j]['elementName'];
                    array_push($elementweightage, $elementids[$j]['weightage']);
                    $indicationvalue = $elementids[$j]['symbolIndication'];
                    if ($indicationvalue == null) {
                        array_push($symbolIndication, "");
                    } else {
                        array_push($symbolIndication, $elementids[$j]['symbolIndication']);
                    }
                    array_push($elementindicationValue, $elementids[$j]['indicationValue']);
                }

            } else {
                if ($maxelementcount < count($elementids)) {
                    $maxelementcount = count($elementids);
                }

                for ($j = 0; $j < count($elementids); $j++) {
                    $sessionkpielementid[$kpiid][$j] = $elementids[$j]['id'];
                    $returnarray[$kpiname][$j] = $elementids[$j]['elementName'];
                    array_push($elementweightage, $elementids[$j]['weightage']);
                    $indicationvalue = $elementids[$j]['symbolIndication'];
                    if ($indicationvalue == null) {
                        array_push($symbolIndication, "");
                    } else {
                        array_push($symbolIndication, $elementids[$j]['symbolIndication']);
                    }
                    array_push($elementindicationValue, $elementids[$j]['indicationValue']);
                }
            }

        }
        $elementvalues = array();
        for ($kkk = 0; $kkk < count($resularray); $kkk++) {
            array_push($elementvalues, $resularray[$kkk]['value']);
        }
        if ($mode == 'listkpielement') {
            return array('returnarray' => $returnarray, 'elementindicationValue' => $elementindicationValue, 'symbolIndication' => $symbolIndication, 'elementweightage' => $elementweightage, 'elementcount' => $maxelementcount, 'elementvalues' => $elementvalues, 'kpi_details' => $ids);
        }
        $response = new JsonResponse();
        $response->setData(array(
            'kpiNameArray' => $returnarray,
            'indicationValue' => $elementindicationValue,
            'symbolIndication' => $symbolIndication,
            'elementweightage' => $elementweightage,
            'elementcount' => $maxelementcount,
            'elementvalues' => $elementvalues,
            'kpi_details' => $ids,
            'rejections' => $rejection_obj,
            'rejection_status' => $reject_status,
        ));
        return $response;
    }


    /**
     * adddata Ranking.
     *
     * @Route("/add_data_forranking", name="adddata_scorecard_forranking")
     */
    public function findnumofshipsforrankingAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($user == null) {
            return $this->redirectToRoute('fos_user_security_login');
        } else {
            $userId = $user->getId();
            $username = $user->getUsername();
            $role = $user->getRoles();
            $templatechoosen = 'base.html.twig';
            if ($role[0] == 'ROLE_KPI_INFO_PROVIDER') {
                $status = 5;
                $templatechoosen = 'v-ships_layout.html.twig';
            }
            $findRankingdata=$this->gotoRankingMonthDataAction($request,"defulatmonth");
            $currentdatetime = date('Y-m-d');
            $findListVieworEditViewStatus="";
            if ($findRankingdata['shipcount'] != 0) {
                if ($findRankingdata['shipoverallStatus'] == "yes" && $findRankingdata['currentshipid'] =="") {
                    $findstatusofship=$findRankingdata['statusQueryResult'];
                    $listviewStatusField='listView';
                    foreach ($findstatusofship as $key => $val)
                    {
                        if ($role[0] == 'ROLE_KPI_INFO_PROVIDER' ||$role[0] == 'ROLE_MANAGER' ||$role[0] == 'ROLE_ADMIN')
                        {
                            if ($val['status'] ==1)
                            {
                                $listviewStatusField = $key;
                            }
                        }
                    }
                    if ($listviewStatusField=='listView')
                    {
                        $findListVieworEditViewStatus="listView";
                    }
                    else
                    {
                        $findListVieworEditViewStatus="editView";
                    }
                }
                else if ($findRankingdata['shipoverallStatus'] == "no")
                {
                    $findListVieworEditViewStatus="editView";
                }
                else {
                    $findListVieworEditViewStatus="editView";
                }
            }
            else
            {
                $findListVieworEditViewStatus="editView";

            }
            $elementvalues="";
            if($findRankingdata['currentshipid']==""){
                $elementvalues=array();
            }
            else{
                $elementvalues=$findRankingdata['shipwiseElementValues'][$findRankingdata['currentshipid']];
            }
            //Find Shipstatus with Rejections//
            for($shipStatusCount=0;$shipStatusCount<count($findRankingdata['listofships']);$shipStatusCount++){
                $shipId=$findRankingdata['listofships'][$shipStatusCount]['id'];
                $statusofshipOverall=$findRankingdata['statusQueryResult'];
                $shipwisestatusCountOverall=$findRankingdata['shipwiseStatuscount'];
                if(count($statusofshipOverall)==count($findRankingdata['listofships'])){
                    $statusofship=$statusofshipOverall[$shipStatusCount];
                    if($statusofship['rejections']!=null){
                        $findRankingdata['listofships'][$shipStatusCount]['rejections']=true;
                        $findRankingdata['listofships'][$shipStatusCount]['status']=$statusofship['status'];

                    }
                    else{
                        $findRankingdata['listofships'][$shipStatusCount]['rejections']=false;
                        $findRankingdata['listofships'][$shipStatusCount]['status']=$statusofship['status'];
                    }
                }
                else{
                    $shipwisecountStatus=$shipwisestatusCountOverall[$shipId];
                    if(count($shipwisecountStatus)>0){
                        if($shipwisecountStatus['rejections']!=null){
                            $findRankingdata['listofships'][$shipStatusCount]['rejections']=true;
                            $findRankingdata['listofships'][$shipStatusCount]['status']=$shipwisecountStatus['status'];
                        }
                        else{
                            $findRankingdata['listofships'][$shipStatusCount]['rejections']=false;
                            $findRankingdata['listofships'][$shipStatusCount]['status']=$shipwisecountStatus['status'];
                        }

                    }
                    else{
                        $findRankingdata['listofships'][$shipStatusCount]['rejections']=false;
                        $findRankingdata['listofships'][$shipStatusCount]['status']=0;
                    }
                }
            }
            //Find Kpi Status and Rejections
            for($kpiCount=0;$kpiCount<count($findRankingdata['currentshipkpilist']);$kpiCount++){
                $statusofshipOverall=$findRankingdata['statusQueryResult'];
                $shipwisestatusCountOverall=$findRankingdata['shipwiseStatuscount'];
                if(count($statusofshipOverall)==count($findRankingdata['listofships'])){
                    if($findRankingdata['currentshipid']!=""){
                        $rejectionIds=$statusofshipOverall[$findRankingdata['currentshipid']-1]['rejections'];
                        if($rejectionIds!=null){
                            if($rejectionIds=='ALL'){
                                $findRankingdata['currentshipkpilist'][$kpiCount]['rejections']=true;
                            }
                            else {
                                $rejectionIdsArray=explode(",",$rejectionIds);
                                if(in_array($findRankingdata['currentshipkpilist'][$kpiCount]['id'],$rejectionIdsArray)){
                                    $findRankingdata['currentshipkpilist'][$kpiCount]['rejections']=true;
                                }
                                else{
                                    $findRankingdata['currentshipkpilist'][$kpiCount]['rejections']=false;
                                }
                            }

                        }
                        else {
                            $findRankingdata['currentshipkpilist'][$kpiCount]['rejections']=false;
                        }
                    }
                }
                else{
                    $shipwisecountStatus=$shipwisestatusCountOverall[$findRankingdata['currentshipid']];
                    if(count($shipwisecountStatus)>0){
                        if($shipwisecountStatus['rejections']!=null){
                            $findRankingdata['currentshipkpilist'][$kpiCount]['rejections']=true;
                        }
                        else{
                            $findRankingdata['currentshipkpilist'][$kpiCount]['rejections']=false;
                        }

                    }
                    else{
                        $findRankingdata['currentshipkpilist'][$kpiCount]['rejections']=false;
                    }


                }
            }
            if ($role[0] == 'ROLE_ADMIN' ||$role[0] == 'ROLE_MANAGER') {
                if(count($findRankingdata['statusQueryResult'])!=count($findRankingdata['listofships'])){
                    $findRankingdata['currentshipid']="";
                    $findRankingdata['currentshipname']="";
                }
            }
            return $this->render('InitialShippingBundle:DataVerficationRanking:home.html.twig',
                array(
                    'listoreditViewStatus'=>$findListVieworEditViewStatus,
                    'templatechoosen' => $templatechoosen,
                    'currentdatetime' => $currentdatetime,
                    'listofships' => $findRankingdata['listofships'],
                    'shipcount' => $findRankingdata['shipcount'],
                    'symbolIndication' => $findRankingdata['symbolIndication'],
                    'indicationValue' => $findRankingdata['indicationValue'],
                    'elementweightage' => $findRankingdata['elementweightage'],
                    'elementkpiarray' => $findRankingdata['elementkpiarray'],
                    'maxelementcount' => $findRankingdata['maxelementcount'],
                    'kpi_details' => $findRankingdata['kpi_details'],
                    'currentshipid' => $findRankingdata['currentshipid'],
                    'currentshipname' => $findRankingdata['currentshipname'],
                    'commontext' => false,
                    'dateFormat' => $findRankingdata['dateFormat'],
                    'statusQueryResult' => $findRankingdata['statusQueryResult'],
                    'shipoverallStatus' => $findRankingdata['shipoverallStatus'],
                    'shipwiseElementValues' => $findRankingdata['shipwiseElementValues'],
                    'currentshipkpilist'=>$findRankingdata['currentshipkpilist'],
                    'shipwiseStatuscount'=>$findRankingdata['shipwiseStatuscount'],
                    'elementvalues'=>$elementvalues,
                ));
            /*
            $rejection_obj = null;
            $reject_status = 'empty';
            if ($this->container->get('security.context')->isGranted('ROLE_ADMIN')) {
                $query = $em->createQueryBuilder()
                    ->select('a.shipName', 'a.id')
                    ->from('InitialShippingBundle:ShipDetails', 'a')
                    ->join('InitialShippingBundle:CompanyDetails', 'b', 'WITH', 'b.id = a.companyDetailsId')
                    ->where('b.adminName = :username')
                    ->setParameter('username', $username)
                    ->getQuery();
            } else {
                $query = $em->createQueryBuilder()
                    ->select('a.shipName', 'a.id')
                    ->from('InitialShippingBundle:ShipDetails', 'a')
                    ->leftjoin('InitialShippingBundle:User', 'b', 'WITH', 'b.companyid = a.companyDetailsId')
                    ->where('b.id = :userId')
                    ->setParameter('userId', $userId)
                    ->getQuery();
            }
            $listallshipforcompany = $query->getResult();
            if (count($listallshipforcompany) > 0) {
                if ($mode == 'nextshipajaxcall') {
                    return $listallshipforcompany;
                }
                $statusforship = $this->findshipstatus_ranking($dataofmonth = '', $listallshipforcompany, $role[0]);
                $counts = array_count_values($statusforship);
                $finddatawithstatus = array();
                $shipid = 0;
                $shipname = '';

                if ($role[0] == 'ROLE_ADMIN') {
                    $status = 2;
                    $index = array_search(0, $statusforship);
                    $shipid = $listallshipforcompany[$index]['id'];
                    $shipname = $listallshipforcompany[$index]['shipName'];
                    $finddatawithstatus = $this->finddatawithstatus_ranking($status, $shipid);
                    if (array_key_exists(3, $counts)) {
                        $ship_status_done_count = $counts[3];
                    } else {
                        $ship_status_done_count = 0;
                    }
                }

                if ($role[0] == 'ROLE_MANAGER') {
                    $status = 5;
                    $index = array_search(0, $statusforship);
                    $shipid = $listallshipforcompany[$index]['id'];
                    $shipname = $listallshipforcompany[$index]['shipName'];
                    $finddatawithstatus = $this->finddatawithstatus_ranking($status, $shipid);
                    if (array_key_exists(2, $counts)) {
                        $ship_status_done_count = $counts[2];
                    } else {
                        $ship_status_done_count = 0;
                    }
                }

                if ($role[0] == 'ROLE_KPI_INFO_PROVIDER') {
                    $status = 5;
                    $templatechoosen = 'v-ships_layout.html.twig';
                    $index = array_search(0, $statusforship);
                    $shipid = $listallshipforcompany[$index]['id'];
                    $shipname = $listallshipforcompany[$index]['shipName'];
                    $finddatawithstatus = $this->finddatawithstatus_ranking($status, $shipid);
                    if (array_key_exists(1, $counts)) {
                        $ship_status_done_count = $counts[1];
                    } else {
                        $ship_status_done_count = 0;
                    }

                }

                $currentdatetime = date('Y-m-d');
                $last_date = date("Y-m-t", strtotime($currentdatetime));

                $temp_rejection_obj = array();
                foreach ($listallshipforcompany as $vessel) {
                    $lookup_status = $em->createQueryBuilder()
                        ->select('a.status, a.rejections')
                        ->from('InitialShippingBundle:Ranking_LookupStatus', 'a')
                        ->where('a.dataofmonth = :dataofmonth')
                        ->andwhere('a.shipid = :shipid')
                        ->setParameter('dataofmonth', $last_date)
                        ->setParameter('shipid', $vessel['id'])
                        ->getQuery()
                        ->getResult();
                    if (count($lookup_status) > 0) {
                        if ($lookup_status[0]['rejections'] != null) {
                            $rejection_string = $lookup_status[0]['rejections'];
                            array_push($temp_rejection_obj, explode(',', $rejection_string));
                            $rejection_obj = $temp_rejection_obj;
                            $reject_status = 'yes';
                        } else {
                            array_push($temp_rejection_obj, null);
                            $rejection_obj = $temp_rejection_obj;
                            $reject_status = 'yes';
                        }
                    } else {
                        $rejection_obj = null;
                        $reject_status = 'empty';
                    }
                }

                if (count($finddatawithstatus) == 7) {
                    $currentdatetime = date('Y-m-d');

                    return $this->render('InitialShippingBundle:DataVerficationRanking:home.html.twig',
                        array('listofships' => $listallshipforcompany,
                            'shipcount' => count($listallshipforcompany), 'status_ship' => $statusforship, 'statuscount' => $ship_status_done_count,
                            'elementkpiarray' => $finddatawithstatus['elementnamekpiname'], 'elementcount' => $finddatawithstatus['maxelementcount'],
                            'elementvalues' => $finddatawithstatus['elementvalues'],
                            'elementweightage' => $finddatawithstatus['elementweightage'],
                            'indicationValue' => $finddatawithstatus['indicationValue'],
                            'symbolIndication' => $finddatawithstatus['symbolIndication'],
                            'currentshipid' => $shipid, 'currentshipname' => $shipname,
                            'templatechoosen' => $templatechoosen,
                            'kpi_details' => $finddatawithstatus['kpi_details'],
                            'rejections' => $rejection_obj, 'rejection_status' => $reject_status,
                            'currentdatetime' => $currentdatetime
                        ));
                } else {

                    return $this->render('InitialShippingBundle:DataVerficationRanking:home.html.twig',
                        array('listofships' => $listallshipforcompany,
                            'shipcount' => count($listallshipforcompany), 'status_ship' => $statusforship,
                            'elementkpiarray' => array(), 'elementcount' => 0,
                            'elementvalues' => array(),
                            'currentshipid' => $shipid, 'currentshipname' => $shipname, 'templatechoosen' => $templatechoosen, 'currentdatetime' => $currentdatetime
                        ));
                }
            } else {
                return $this->render('InitialShippingBundle:DataVerficationRanking:home.html.twig',
                    array('listofships' => $listallshipforcompany,
                        'shipcount' => count($listallshipforcompany), 'status_ship' => '', 'statuscount' => 0,
                        'elementkpiarray' => array(), 'elementcount' => 0,
                        'elementvalues' => array(),
                        'currentshipid' => '', 'currentshipname' => '', 'templatechoosen' => $templatechoosen
                    ));
            }
            */
        }
    }


    //Finding Status For monthdata While after add,save,verify,upload

    private function findshipstatus_ranking($dataofmonth = '', $shipdetialsarray, $role)
    {
        $session = new Session();
        if ($dataofmonth == '') {
            $stringdataofmonth = date('Y-m-d');
        }
        if ($dataofmonth != '') {
            $mydate = '01-' . $dataofmonth;
            $time = strtotime($mydate);
            $stringdataofmonth = date('Y-m-d', $time);
        }
        $status = 0;
        $new_date = new \DateTime($stringdataofmonth);
        $new_date->modify('last day of this month');
        $em = $this->getDoctrine()->getManager();
        $statusarray = array();

        if ($role == 'ROLE_ADMIN') {
            $status = 3;
            for ($ship = 0; $ship < count($shipdetialsarray); $ship++) {
                $statusfromresult = $em->createQueryBuilder()
                    ->select('b.status')
                    ->from('InitialShippingBundle:RankingMonthlyData', 'b')
                    ->where('b.shipDetailsId = :shipdetailsid')
                    ->andWhere('b.status = :status')
                    ->andWhere('b.monthdetail =:dataofmonth')
                    ->setParameter('shipdetailsid', $shipdetialsarray[$ship]['id'])
                    ->setParameter('dataofmonth', $new_date)
                    ->setParameter('status', 3)
                    ->getQuery()
                    ->getResult();
                if (count($statusfromresult) == 0) {
                    array_push($statusarray, 0);
                }
                if (count($statusfromresult) > 0) {
                    array_push($statusarray, 3);
                }
            }

            return $statusarray;
        }
        if ($role == 'ROLE_MANAGER') {
            $status = 2;
            for ($ship = 0; $ship < count($shipdetialsarray); $ship++) {
                $statusfromresult = $em->createQueryBuilder()
                    ->select('b.status')
                    ->from('InitialShippingBundle:RankingMonthlyData', 'b')
                    ->where('b.shipDetailsId = :shipdetailsid')
                    ->andWhere('b.status  = 2 OR b.status  = 3')
                    ->andWhere('b.monthdetail =:dataofmonth')
                    ->setParameter('shipdetailsid', $shipdetialsarray[$ship]['id'])
                    ->setParameter('dataofmonth', $new_date)
                    ->getQuery()
                    ->getResult();
                if (count($statusfromresult) == 0) {
                    array_push($statusarray, 0);
                }
                if (count($statusfromresult) > 0) {
                    array_push($statusarray, 2);
                }
            }
            return $statusarray;
        }
        if ($role == 'ROLE_KPI_INFO_PROVIDER') {
            $status = 1;
            $tempvarable = 0;
            $elementtempvariable = 0;

            for ($ship = 0; $ship < count($shipdetialsarray); $ship++) {
                $statusfromresult = $em->createQueryBuilder()
                    ->select('b.status')
                    ->from('InitialShippingBundle:RankingMonthlyData', 'b')
                    ->where('b.shipDetailsId = :shipdetailsid')
                    ->andWhere('b.status = 1 OR b.status  = 2 OR b.status  = 3 OR b.status  = 5')
                    ->andWhere('b.monthdetail =:dataofmonth')
                    ->setParameter('shipdetailsid', $shipdetialsarray[$ship]['id'])
                    ->setParameter('dataofmonth', $new_date)
                    ->getQuery()
                    ->getResult();
                if (count($statusfromresult) == 0) {
                    array_push($statusarray, 0);
                }
                if (count($statusfromresult) > 0) {
                    array_push($statusarray, $statusfromresult[0]['status']);
                }
            }

            return $statusarray;

        } else {
            return $statusarray;
        }
    }

    private function findelementkpiid_ranking($shipid)
    {
        $em = $this->getDoctrine()->getManager();
        $returnarray = array();
        $sessionkpielementid = array();
        $currentselectionid = 0;
        $currentselectionname = '';
        $maxelementcount = 0;
        $query = $em->createQueryBuilder()
            ->select('b.kpiName', 'b.id')
            ->from('InitialShippingBundle:RankingKpiDetails', 'b')
            ->where('b.shipDetailsId = :shipdetailsid')
            ->setParameter('shipdetailsid', $shipid)
            ->add('orderBy', 'b.id  ASC ')
            ->getQuery();

        $ids = $query->getResult();
        $maxelementcount = 0;

        $sessionkpielementid = array();
        $k = 0;
        for ($i = 0; $i < count($ids); $i++) {
            $kpiid = $ids[$i]['id'];
            $kpiname = $ids[$i]['kpiName'];
            $query = $em->createQueryBuilder()
                ->select('b.elementName', 'b.id', 'b.indicationValue', 'b.weightage', 'c.symbolIndication')
                ->from('InitialShippingBundle:RankingElementDetails', 'b')
                ->leftjoin('InitialShippingBundle:ElementSymbols', 'c', 'WITH', 'c.id = b.symbolId')
                ->where('b.kpiDetailsId = :kpidetailsid')
                ->setParameter('kpidetailsid', $kpiid)
                ->add('orderBy', 'b.id  ASC ')
                ->getQuery();
            $elementids = $query->getResult();
            if (count($elementids) == 0) {
                $query1 = $em->createQueryBuilder()
                    ->select('b.kpiName', 'b.id')
                    ->from('InitialShippingBundle:RankingKpiDetails', 'b')
                    ->where('b.kpiName = :kpiName')
                    ->setParameter('kpiName', $kpiname)
                    ->add('orderBy', 'b.id  ASC ')
                    ->groupby('b.kpiName')
                    ->getQuery();

                $ids1 = $query1->getResult();
                $newkpiid = $ids1[0]['id'];
                $newkpiname = $ids1[0]['kpiName'];
                $query = $em->createQueryBuilder()
                    ->select('b.elementName', 'b.id', 'b.indicationValue', 'b.weightage', 'c.symbolIndication')
                    ->from('InitialShippingBundle:RankingElementDetails', 'b')
                    ->leftjoin('InitialShippingBundle:ElementSymbols', 'c', 'WITH', 'c.id = b.symbolId')
                    ->where('b.kpiDetailsId = :kpidetailsid')
                    ->setParameter('kpidetailsid', $newkpiid)
                    ->add('orderBy', 'b.id  ASC ')
                    ->getQuery();
                $elementids = $query->getResult();
                if ($maxelementcount < count($elementids)) {
                    $maxelementcount = count($elementids);
                }
                for ($j = 0; $j < count($elementids); $j++) {
                    $sessionkpielementid[$newkpiid][$j] = $elementids[$j]['id'];
                    $returnarray[$newkpiname][$j] = $elementids[$j]['elementName'];


                }
            } else {
                if ($maxelementcount < count($elementids)) {
                    $maxelementcount = count($elementids);
                }

                for ($j = 0; $j < count($elementids); $j++) {
                    $sessionkpielementid[$kpiid][$j] = $elementids[$j]['id'];
                    $returnarray[$kpiname][$j] = $elementids[$j]['elementName'];

                }
            }


        }
        return array('elementids' => $sessionkpielementid);
    }

    private function finddatawithstatus_ranking($status, $shipid, $dataofmonth = '')
    {

        if ($dataofmonth == '') {
            $stringdataofmonth = date('Y-m-d');
        }
        if ($dataofmonth != '') {
            $mydate = '01-' . $dataofmonth;
            $time = strtotime($mydate);
            $stringdataofmonth = date('Y-m-d', $time);

        }

        $new_date = new \DateTime($stringdataofmonth);
        $new_date->modify('last day of this month');
        $em = $this->getDoctrine()->getManager();

        $elementvalues = array();
        $elementweightage = array();
        $returnarray = array();
        $elementindicationValue = array();
        $symbolIndication = array();

        $resularray = $em->createQueryBuilder()
            ->select('b.value')
            ->from('InitialShippingBundle:RankingMonthlyData', 'b')
            ->where('b.shipDetailsId = :shipdetailsid')
            ->andWhere('b.monthdetail =:dataofmonth')
            ->andWhere('b.status = :status')
            ->setParameter('shipdetailsid', $shipid)
            ->setParameter('dataofmonth', $new_date)
            ->setParameter('status', $status)
            ->getQuery()
            ->getResult();
        $query = $em->createQueryBuilder()
            ->select('b.kpiName', 'b.id')
            ->from('InitialShippingBundle:RankingKpiDetails', 'b')
            ->where('b.shipDetailsId = :shipdetailsid')
            ->setParameter('shipdetailsid', $shipid)
            ->add('orderBy', 'b.id  ASC ')
            ->getQuery();
        $ids = $query->getResult();
        $maxelementcount = 0;
        $k = 0;
        for ($i = 0; $i < count($ids); $i++) {
            $kpiid = $ids[$i]['id'];
            $kpiname = $ids[$i]['kpiName'];

            $query = $em->createQueryBuilder()
                ->select('b.elementName', 'b.id', 'b.indicationValue', 'b.weightage', 'c.symbolIndication')
                ->from('InitialShippingBundle:RankingElementDetails', 'b')
                ->leftjoin('InitialShippingBundle:ElementSymbols', 'c', 'WITH', 'c.id = b.symbolId')
                ->where('b.kpiDetailsId = :kpidetailsid')
                ->setParameter('kpidetailsid', $kpiid)
                ->add('orderBy', 'b.id  ASC ')
                ->getQuery();
            $elementids = $query->getResult();
            if (count($elementids) == 0) {
                $query1 = $em->createQueryBuilder()
                    ->select('b.kpiName', 'b.id')
                    ->from('InitialShippingBundle:RankingKpiDetails', 'b')
                    ->where('b.kpiName = :kpiName')
                    ->setParameter('kpiName', $kpiname)
                    ->add('orderBy', 'b.id  ASC ')
                    ->groupby('b.kpiName')
                    ->getQuery();

                $ids1 = $query1->getResult();
                $newkpiid = $ids1[0]['id'];
                $newkpiname = $ids1[0]['kpiName'];
                $query = $em->createQueryBuilder()
                    ->select('b.elementName', 'b.id', 'b.indicationValue', 'b.weightage', 'c.symbolIndication')
                    ->from('InitialShippingBundle:RankingElementDetails', 'b')
                    ->leftjoin('InitialShippingBundle:ElementSymbols', 'c', 'WITH', 'c.id = b.symbolId')
                    ->where('b.kpiDetailsId = :kpidetailsid')
                    ->setParameter('kpidetailsid', $newkpiid)
                    ->add('orderBy', 'b.id  ASC ')
                    ->getQuery();
                $elementids = $query->getResult();

                if ($maxelementcount < count($elementids)) {
                    $maxelementcount = count($elementids);
                }
                for ($j = 0; $j < count($elementids); $j++) {
                    $returnarray[$newkpiname][$j] = $elementids[$j]['elementName'];
                    array_push($elementweightage, $elementids[$j]['weightage']);
                    $indicationvalue = $elementids[$j]['symbolIndication'];
                    if ($indicationvalue == null) {
                        array_push($symbolIndication, "");
                    } else {
                        array_push($symbolIndication, $elementids[$j]['symbolIndication']);
                    }
                    array_push($elementindicationValue, $elementids[$j]['indicationValue']);
                }
            } else {
                if ($maxelementcount < count($elementids)) {
                    $maxelementcount = count($elementids);
                }
                for ($j = 0; $j < count($elementids); $j++) {
                    $returnarray[$kpiname][$j] = $elementids[$j]['elementName'];
                    array_push($elementweightage, $elementids[$j]['weightage']);
                    $indicationvalue = $elementids[$j]['symbolIndication'];
                    if ($indicationvalue == null) {
                        array_push($symbolIndication, "");
                    } else {
                        array_push($symbolIndication, $elementids[$j]['symbolIndication']);
                    }
                    array_push($elementindicationValue, $elementids[$j]['indicationValue']);
                }
            }
        }
        for ($kkk = 0; $kkk < count($resularray); $kkk++) {
            array_push($elementvalues, $resularray[$kkk]['value']);
        }

        return array(
            'elementvalues' => $elementvalues,
            'symbolIndication' => $symbolIndication,
            'indicationValue' => $elementindicationValue,
            'elementweightage' => $elementweightage,
            'elementnamekpiname' => $returnarray,
            'maxelementcount' => $maxelementcount,
            'kpi_details' => $ids
        );
    }


    /**
     * Adding Kpi Values Ranking.
     *
     * @Route("/{buttonid}/addkpivalues_forranking", name="addkpivaluesname_forranking")
     */
    public function addkpivaluesforrankingAction(Request $request, $buttonid)
    {
        $user = $this->getUser();
        if ($user == null) {
            return $this->redirectToRoute('fos_user_security_login');
        } else {
            $userid = $user->getId();
            $role = $user->getRoles();
            $shipid = $request->request->get('shipid');
            $reject_id_array = $request->request->get('reject_id');
            $returnfromcontroller = $this->findelementkpiid_ranking($shipid);
            $kpiandelementids = $returnfromcontroller['elementids'];
            $elementvalues = $request->request->get('newelemetvalues');
            $dataofmonth = $request->request->get('dataofmonth');
            $em = $this->getDoctrine()->getManager();
            $new_date = new \DateTime($dataofmonth);
            $new_date->modify('last day of this month');
            $newtemp_date = date_format($new_date, "M-Y");
            $tempdate = date_format($new_date, "d-M-Y");
            $rejection_obj = null;
            $reject_status = 'empty';
            $k = 0;
            $returnmsg = '';
            $newshipid = $em->getRepository('InitialShippingBundle:ShipDetails')->findOneBy(array('id' => $shipid));

            if ($buttonid == 'updatebuttonid' || $buttonid == 'adminbuttonid' || $buttonid == 'verfiybuttonid') {

                $returnarrayids = $em->createQueryBuilder()
                    ->select('b.id')
                    ->from('InitialShippingBundle:RankingMonthlyData', 'b')
                    ->where('b.shipDetailsId = :shipdetailsid')
                    ->andWhere('b.monthdetail =:dataofmonth')
                    ->setParameter('shipdetailsid', $shipid)
                    ->setParameter('dataofmonth', $new_date)
                    ->getQuery()
                    ->getResult();
                for ($kkk = 0; $kkk < count($returnarrayids); $kkk++) {
                    $entityobject = $em->getRepository('InitialShippingBundle:RankingMonthlyData')->find($returnarrayids[$kkk]['id']);

                    if ($buttonid == 'adminbuttonid') {
                        $entityobject->setValue($elementvalues[$kkk]);
                        $entityobject->setStatus(3);
                    }
                    if ($buttonid == 'verfiybuttonid') {
                        $entityobject->setValue($elementvalues[$kkk]);
                        $entityobject->setStatus(2);
                    }
                    if ($buttonid == 'updatebuttonid') {
                        $entityobject->setValue($elementvalues[$kkk]);
                        $entityobject->setStatus(1);
                    }
                    $em->flush();

                }
                $returnmsg = ' Data Updated...';

                if ($buttonid == 'adminbuttonid') {
                    $lookstatus = $em->getRepository('InitialShippingBundle:Ranking_LookupStatus')->findBy(array('shipid' => $newshipid, 'dataofmonth' => $new_date));
                    $newlookupstatus = $lookstatus[0];

                    $rankinglookuptable = array('shipid' => $shipid, 'dataofmonth' => $tempdate, 'userid' => $userid, 'status' => 3, 'datetime' => date('Y-m-d H:i:s'));
                    $newlookupstatus->setStatus(3);
                    $newlookupstatus->setDatetime(new \DateTime());
                    $em->flush();
                    $gearman = $this->get('gearman');
                    $gearman->doBackgroundJob('InitialShippingBundleserviceReadExcelWorker~addrankinglookupdataupdate', json_encode($rankinglookuptable));
                }

                if ($buttonid == 'verfiybuttonid') {
                    $lookstatus = $em->getRepository('InitialShippingBundle:Ranking_LookupStatus')->findBy(array('shipid' => $newshipid, 'dataofmonth' => $new_date));
                    $newlookupstatus = $lookstatus[0];

                    $newlookupstatus->setStatus(2);
                    $newlookupstatus->setRejections(null);
                    $newlookupstatus->setDatetime(new \DateTime());
                    $em->flush();

                }

                if ($buttonid == 'updatebuttonid') {

                    $lookstatus = $em->getRepository('InitialShippingBundle:Ranking_LookupStatus')->findBy(array('shipid' => $newshipid, 'dataofmonth' => $new_date));
                    $newlookupstatus = $lookstatus[0];
                    $pre_rejections = $em->createQueryBuilder()
                        ->select('a.rejections')
                        ->from('InitialShippingBundle:Scorecard_LookupStatus', 'a')
                        ->where('a.dataofmonth = :dataofmonth')
                        ->andwhere('a.shipid = :shipid')
                        ->setParameter('dataofmonth', $new_date)
                        ->setParameter('shipid', $shipid)
                        ->getQuery()
                        ->getResult();

                    if (count($pre_rejections) > 0) {
                        if ($pre_rejections[0]['rejections'] != null && $pre_rejections[0]['rejections'] != "null") {
                            $temp = null;
                            $reject_status = 'yes';
                            $rejection_obj = $temp;
                        } else {
                            $rejection_obj = null;
                            $reject_status = 'empty';
                        }
                    } else {
                        $rejection_obj = null;
                        $reject_status = 'empty';
                    }

                    $newlookupstatus->setStatus(1);
                    $newlookupstatus->setRejections($rejection_obj);
                    $newlookupstatus->setDatetime(new \DateTime());
                    $em->flush();
                }

            }
            if ($buttonid == 'savebuttonid') {

//                $lookstatus = $em->getRepository('InitialShippingBundle:Ranking_LookupStatus')->findBy(array('shipid' => $newshipid, 'dataofmonth' => $new_date));
//                $newlookupstatus = $lookstatus[0];

                foreach ($kpiandelementids as $element) {
                    for ($elementCount = 0; $elementCount < count($element); $elementCount++) {
                        $baseValueQuery = $em->createQueryBuilder()
                            ->select('a.baseValue')
                            ->from('InitialShippingBundle:RankingElementDetails', 'a')
                            ->where('a.id=:elementId')
                            ->setParameter('elementId', $element[$elementCount])
                            ->getQuery()
                            ->getResult();
                        $baseValue = $baseValueQuery[0]['baseValue'];
                        if ($baseValue != 0) {
                            $currentMonth = date('m');
                            $monthlyCount = $baseValue / 12;
                            $currentMonthValue = (int)$currentMonth * $monthlyCount;
                            $elementRulesQuery = $em->createQueryBuilder()
                                ->select('a.rules,a.id')
                                ->from('InitialShippingBundle:RankingRules', 'a')
                                ->where('a.elementDetailsId=:elementId')
                                ->setParameter('elementId', $element[$elementCount])
                                ->getQuery()
                                ->getResult();
                            foreach ($elementRulesQuery as $rules) {
                                $ruleObj = json_decode($rules['rules']);
                                $ruleObj->conditions->all[0]->value = $currentMonthValue;
                                $ruleString = json_encode($ruleObj);
                                $rulesDetailObject = $em->getRepository('InitialShippingBundle:RankingRules')->find($rules['id']);
                                $rulesDetailObject->setRules($ruleString);
                                $em->flush();
                            }
                        }
                    }
                }
                foreach ($kpiandelementids as $kpikey => $kpipvalue) {
                    $newkpiid = $em->getRepository('InitialShippingBundle:RankingKpiDetails')->findOneBy(array('id' => $kpikey));
                    foreach ($kpipvalue as $elementkey => $elementvalue) {
                        $newelementid = $em->getRepository('InitialShippingBundle:RankingElementDetails')->findOneBy(array('id' => $elementvalue));

                        $readingkpivalue = new RankingMonthlyData();
                        $readingkpivalue->setKpiDetailsId($newkpiid);
                        $readingkpivalue->setElementDetailsId($newelementid);
                        $readingkpivalue->setShipDetailsId($newshipid);
                        $readingkpivalue->setMonthdetail($new_date);
                        $readingkpivalue->setValue($elementvalues[$k]);
                        $readingkpivalue->setStatus(1);
                        $em->persist($readingkpivalue);
                        $em->flush();
                        $k++;

                    }
                }
                $returnmsg = ' Data Saved...';
                $protocol = empty($_SERVER['HTTPS']) ? 'http' : 'https';
                $domain = $_SERVER['SERVER_NAME'];
                $url = $protocol . '://' . $domain . '/login';

                $lookupstatusobject = new Ranking_LookupStatus();
                $lookupstatusobject->setShipid($newshipid);
                $lookupstatusobject->setStatus(1);
                $lookupstatusobject->setDataofmonth($new_date);
                $lookupstatusobject->setDatetime(new \DateTime());
                $lookupstatusobject->setUserid($userid);
                $em->persist($lookupstatusobject);
                $em->flush();

            }

            if ($buttonid == 'upload_btn_id') {
                $userId = $user->getId();
                $username = $user->getUsername();
                /*if ($this->container->get('security.context')->isGranted('ROLE_ADMIN')) {
                    $query = $em->createQueryBuilder()
                        ->select('a.shipName', 'a.id')
                        ->from('InitialShippingBundle:ShipDetails', 'a')
                        ->join('InitialShippingBundle:CompanyDetails', 'b', 'WITH', 'b.id = a.companyDetailsId')
                        ->where('b.adminName = :username')
                        ->setParameter('username', $username)
                        ->getQuery();
                } else {
                    $query = $em->createQueryBuilder()
                        ->select('a.shipName', 'a.id')
                        ->from('InitialShippingBundle:ShipDetails', 'a')
                        ->leftjoin('InitialShippingBundle:User', 'b', 'WITH', 'b.companyid = a.companyDetailsId')
                        ->where('b.id = :userId')
                        ->setParameter('userId', $userId)
                        ->getQuery();
                }*/
                if ($this->container->get('security.context')->isGranted('ROLE_ADMIN')) {
                    $query = $em->createQueryBuilder()
                        ->select('a.shipName', 'a.id')
                        ->from('InitialShippingBundle:ShipDetails', 'a')
                        ->join('InitialShippingBundle:CompanyDetails', 'b', 'WITH', 'b.id = a.companyDetailsId')
                        ->where('b.adminName = :username')
                        ->andWhere('a.dateTime <= :activeDate')
                        ->setParameter('activeDate', $new_date)
                        ->groupBy('a.id')
                        ->setParameter('username', $username)
                        ->getQuery();
                } else {
                    $query = $em->createQueryBuilder()
                        ->select('a.shipName', 'a.id')
                        ->from('InitialShippingBundle:ShipDetails', 'a')
                        ->leftjoin('InitialShippingBundle:User', 'b', 'WITH', 'b.companyid = a.companyDetailsId')
                        ->where('a.dateTime <= :activeDate')
                        ->setParameter('activeDate', $new_date)
                        ->andwhere('b.id = :userId')
                        ->setParameter('userId', $userId)
                        ->groupBy('a.id')
                        ->getQuery();
                }

                $ship_details = $query->getResult();
                foreach ($ship_details as $vessel) {
                    $check_empty_value_query = $em->createQueryBuilder()
                        ->select('a.value')
                        ->from('InitialShippingBundle:RankingMonthlyData', 'a')
                        ->where('a.shipDetailsId = :shipdetailsid')
                        ->andWhere('a.monthdetail =:dataofmonth')
                        ->setParameter('shipdetailsid', $vessel['id'])
                        ->setParameter('dataofmonth', $new_date)
                        ->getQuery()
                        ->getResult();
                    foreach ($check_empty_value_query as $check_empty_values) {
                        if ($check_empty_values['value'] != null) {
                            continue;
                        }
                        else
                            {
                            $return_msg = $vessel['shipName'] . ' data is not completed !';
                            $response = new JsonResponse();
                            $response->setData(array(
                                'return_msg' => $return_msg,
                                'status' => -1,
                                'ship_id' => $vessel['id']
                            ));
                            return $response;
                        }
                    }
                }

                foreach ($ship_details as $vessel) {
                    $lookstatus = $em->getRepository('InitialShippingBundle:Ranking_LookupStatus')->findBy(array('shipid' => $vessel['id'], 'dataofmonth' => $new_date));
                    if (count($lookstatus) != 0) {
                        $newlookupstatus = $lookstatus[0];
                        if($newlookupstatus->getStatus()==1){
                            $newlookupstatus->setStatus(5);
                            $newlookupstatus->setDatetime(new \DateTime());
                            $em->flush();
                        }
                    }
                }

                /*$reading_kpi_values_status = $em->getRepository('InitialShippingBundle:RankingMonthlyData')->findBy(array('monthdetail' => $new_date));
                if (count($reading_kpi_values_status) != 0) {
                    foreach ($reading_kpi_values_status as $reading_kpi_values_status_obj) {
                        $reading_kpi_values_status_obj->setStatus(5);
                        $em->flush();
                    }
                }*/

                $ship_status_done_count = -1;
                $returnmsg = ' Data Uploaded...';

            }

            if ($buttonid == 'reject_btn_id') {

                $pre_rejections = $em->createQueryBuilder()
                    ->select('a.rejections')
                    ->from('InitialShippingBundle:Ranking_LookupStatus', 'a')
                    ->where('a.dataofmonth = :dataofmonth')
                    ->andwhere('a.shipid = :shipid')
                    ->setParameter('dataofmonth', $new_date)
                    ->setParameter('shipid', $shipid)
                    ->getQuery()
                    ->getResult();
                $reject_id_string = null;
                if ($reject_id_array != null) {
                    $reject_id_string = implode(",", $reject_id_array);
                } else {
                    $reject_id_string = 'ALL';
                }
                if (count($pre_rejections) > 0) {
                    if ($pre_rejections[0]['rejections'] != null && $pre_rejections[0]['rejections'] != "null") {
                        $temp = $reject_id_string;
                    } else {
                        $temp = $reject_id_string;
                    }
                } else {
                    $temp = $reject_id_string;
                }

                $lookstatus = $em->getRepository('InitialShippingBundle:Ranking_LookupStatus')->findBy(array('dataofmonth' => $new_date, 'shipid' => $shipid));
                if (count($lookstatus) != 0) {
                    $newlookupstatus = $lookstatus[0];
                    if ($role[0] == 'ROLE_ADMIN') {
                        $newlookupstatus->setStatus(2);
                    }
                    if ($role[0] == 'ROLE_MANAGER') {

                        $newlookupstatus->setStatus(1);
                    }
                    $newlookupstatus->setRejections($temp);
                    $newlookupstatus->setDatetime(new \DateTime());
                    $em->flush();
                }

                /* $reading_kpi_values_status = $em->getRepository('InitialShippingBundle:RankingMonthlyData')->findBy(array('monthdetail' => $new_date, 'shipDetailsId' => $shipid));
                 if (count($reading_kpi_values_status) != 0) {
                     foreach ($reading_kpi_values_status as $reading_kpi_values_status_obj) {
                         $reading_kpi_values_status_obj->setStatus(1);
                         $em->flush();
                     }
                 }*/

                $ship_status_done_count = -1;
                $returnmsg = ' rejected...';

            }

            if ($buttonid != 'upload_btn_id') {
                $shipname = $newshipid->getShipName();
            } else {
                $shipname = 'All vessels';
            }

            /*$nextshipid = 0;
            $nextshipname = '';
            $user = $this->getUser();
            $role = $user->getRoles();
            $kpielementarray = $this->findnumofshipsforrankingAction($request, 'nextshipajaxcall');
            $statusforship = $this->findshipstatus_ranking($newtemp_date, $kpielementarray, $role[0]);
            $counts = array_count_values($statusforship);
            $finddatawithstatus = array();


            if ($role[0] == 'ROLE_ADMIN') {
                $status = 2;
                $index = array_search(0, $statusforship);
                $nextshipid = $kpielementarray[$index]['id'];
                $nextshipname = $kpielementarray[$index]['shipName'];
                $finddatawithstatus = $this->finddatawithstatus_ranking($status, $nextshipid, $newtemp_date);
                if (array_key_exists(3, $counts)) {
                    $ship_status_done_count = $counts[3];
                } else {
                    $ship_status_done_count = 0;
                }
            }

            if ($role[0] == 'ROLE_MANAGER') {
                $status = 5;
                $index = array_search(0, $statusforship);
                $nextshipid = $kpielementarray[$index]['id'];
                $nextshipname = $kpielementarray[$index]['shipName'];
                $finddatawithstatus = $this->finddatawithstatus_ranking($status, $nextshipid, $newtemp_date);
                if (array_key_exists(2, $counts)) {
                    $ship_status_done_count = $counts[2];
                } else {
                    $ship_status_done_count = 0;
                }
            }

            if ($role[0] == 'ROLE_KPI_INFO_PROVIDER') {
                $status = 0;
                $index = array_search(0, $statusforship);
                $nextshipid = $kpielementarray[$index]['id'];
                $nextshipname = $kpielementarray[$index]['shipName'];
                $finddatawithstatus = $this->finddatawithstatus_ranking($status, $nextshipid, $newtemp_date);
                if (array_key_exists(1, $counts)) {
                    $ship_status_done_count = $counts[1];
                } else {
                    $ship_status_done_count = 0;
                }

            }

            $response = new JsonResponse();
            if (count($finddatawithstatus) == 7) {
                $response->setData(array('returnmsg' => $shipname . $returnmsg,
                    'shipname' => $nextshipname,
                    'shipid' => $nextshipid,
                    'kpiNameArray' => $finddatawithstatus['elementnamekpiname'],
                    'elementcount' => $finddatawithstatus['maxelementcount'],
                    'elementweightage' => $finddatawithstatus['elementweightage'],
                    'kpi_details' => $finddatawithstatus['kpi_details'],
                    'shipcount' => count($statusforship),
                    'ship_status_done_count' => $ship_status_done_count,
                    'indicationValue' => $finddatawithstatus['indicationValue'],
                    'symbolIndication' => $finddatawithstatus['symbolIndication'],
                    'elementvalues' => $finddatawithstatus['elementvalues'],
                    'currentdatetime' => $dataofmonth,
                    'rejections' => $rejection_obj,
                    'rejection_status' => $reject_status,
                    'status' => 1
                ));
                return $response;
            } else {

                $response->setData(array('returnmsg' => $shipname . $returnmsg,
                    'shipname' => $nextshipname,
                    'shipid' => $nextshipid,
                    'kpiNameArray' => array(),
                    'elementcount' => 0, 'currentdatetime' => $dataofmonth,
                    'elementvalues' => array()));
                return $response;
            }*/
            $findStattus=$this->gotoRankingMonthDataAction($request,$reuseMode="rebuildcode");
            $respone = new JsonResponse();
            $respone->setData(array(
                'returnmsg' => $shipname . $returnmsg,
                'listofships' => $findStattus['listofships'],
                'shipcount' => $findStattus['shipcount'],
                'symbolIndication' => $findStattus['symbolIndication'],
                'indicationValue' => $findStattus['indicationValue'],
                'elementweightage' => $findStattus['elementweightage'],
                'elementkpiarray' => $findStattus['elementkpiarray'],
                'maxelementcount' => $findStattus['maxelementcount'],
                'kpi_details' => $findStattus['kpi_details'],
                'currentshipid' => $findStattus['currentshipid'],
                'currentshipname' => $findStattus['currentshipname'],
                'commontext' => false,
                'dateFormat' => $findStattus['dateFormat'],
                'statusQueryResult' => $findStattus['statusQueryResult'],
                'shipoverallStatus' => $findStattus['shipoverallStatus'],
                'shipwiseElementValues' => $findStattus['shipwiseElementValues'],
                'currentshipkpilist'=>$findStattus['currentshipkpilist'],
                'shipwiseStatuscount'=>$findStattus['shipwiseStatuscount']
            ));
            return $respone;
        }
    }


    /**
     * Element and Kpi for Particular Ships Ranking.
     *
     * @Route("/{shipid}/{monthdetail}/shipskpielment_forranking", name="shipskpielment_forranking")
     */
    public function shipskpielmentforrankingAction(Request $request, $shipid, $monthdetail, $mode = '')
    {
        $em = $this->getDoctrine()->getManager();
        //Finding Company for Login user Starts Here//
        $user = $this->getUser();
        if ($user == null) {
            return $this->redirectToRoute('fos_user_security_login');
        } else {

            $em = $this->getDoctrine()->getManager();
            $mydate = '01-' . $monthdetail;
            $time = strtotime($mydate);
            $newformat = date('Y-m-d', $time);
            $new_date = new \DateTime($newformat);
            $new_date->modify('last day of this month');
            $user = $this->getUser();
            $role = $user->getRoles();
            $status = 0;
            $resularray = array();
            $rejection_obj = null;
            $reject_status = 'empty';

            if ($role[0] == 'ROLE_ADMIN') {
                $query = $em->createQueryBuilder()
                    ->select('b.value')
                    ->from('InitialShippingBundle:RankingMonthlyData', 'b')
                    ->where('b.shipDetailsId = :shipdetailsid')
                    ->andWhere('b.monthdetail =:dataofmonth')
                    ->andWhere('b.status = 2 OR b.status  = 3')
                    ->setParameter('shipdetailsid', $shipid)
                    ->setParameter('dataofmonth', $new_date)
                    ->getQuery();
            }

            if ($role[0] == 'ROLE_MANAGER') {
                $query = $em->createQueryBuilder()
                    ->select('b.value')
                    ->from('InitialShippingBundle:RankingMonthlyData', 'b')
                    ->where('b.shipDetailsId = :shipdetailsid')
                    ->andWhere('b.monthdetail =:dataofmonth')
                    ->andWhere('b.status = 1 OR b.status  = 2 OR b.status  = 3 OR b.status  = 5')
                    ->setParameter('shipdetailsid', $shipid)
                    ->setParameter('dataofmonth', $new_date)
                    ->getQuery();
            }

            if ($role[0] == 'ROLE_KPI_INFO_PROVIDER') {
                $query = $em->createQueryBuilder()
                    ->select('b.value')
                    ->from('InitialShippingBundle:RankingMonthlyData', 'b')
                    ->where('b.shipDetailsId = :shipdetailsid')
                    ->andWhere('b.status = 0 OR b.status  = 1 OR b.status  = 2 OR b.status  = 3 OR b.status  = 5')
                    ->andWhere('b.monthdetail =:dataofmonth')
                    ->setParameter('shipdetailsid', $shipid)
                    ->setParameter('dataofmonth', $new_date)
                    ->getQuery();
            }

            $resularray = $query->getResult();

            $lookup_status = $em->createQueryBuilder()
                ->select('a.status, a.rejections')
                ->from('InitialShippingBundle:Ranking_LookupStatus', 'a')
                ->where('a.dataofmonth = :dataofmonth')
                ->andwhere('a.shipid = :shipid')
                ->setParameter('dataofmonth', $new_date)
                ->setParameter('shipid', $shipid)
                ->getQuery()
                ->getResult();
            if (count($lookup_status) > 0) {
                if ($lookup_status[0]['rejections'] != null) {
                    $rejection_string = $lookup_status[0]['rejections'];
                    $rejection_obj = explode(',', $rejection_string);
                    $reject_status = 'yes';
                } else if ($lookup_status[0]['rejections'] != 'ALL' && $lookup_status[0]['rejections'] != null) {
                    $rejection_obj = 'ALL';
                    $reject_status = 'yes';
                } else {
                    $rejection_obj = null;
                    $reject_status = 'empty';
                }
            } else {
                $rejection_obj = null;
                $reject_status = 'empty';
            }
            $query = $em->createQueryBuilder()
                ->select('b.kpiName', 'b.id')
                ->from('InitialShippingBundle:RankingKpiDetails', 'b')
                ->where('b.shipDetailsId = :shipdetailsid')
                ->andwhere('b.dateTime <= :dateTime')
                ->andwhere('b.kpiName != :vesselageValue')
                ->setParameter('vesselageValue', 'Vessel age')
                ->setParameter('dateTime', $new_date)
                ->setParameter('shipdetailsid', $shipid)
                ->add('orderBy', 'b.id  ASC ')
                ->getQuery();

            $ids = $query->getResult();
            $maxelementcount = 0;

            $returnarray = array();
            $elementweightage = array();
            $elementindicationValue = array();
            $symbolIndication = array();
            $sessionkpielementid_ranking = array();
            $k = 0;
            for ($i = 0; $i < count($ids); $i++) {
                $kpiid = $ids[$i]['id'];
                $kpiname = $ids[$i]['kpiName'];

                $query = $em->createQueryBuilder()
                    ->select('b.elementName', 'b.id', 'b.indicationValue', 'b.weightage', 'c.symbolIndication')
                    ->from('InitialShippingBundle:RankingElementDetails', 'b')
                    ->leftjoin('InitialShippingBundle:ElementSymbols', 'c', 'WITH', 'c.id = b.symbolId')
                    ->where('b.kpiDetailsId = :kpidetailsid')
                    ->setParameter('kpidetailsid', $kpiid)
                    ->add('orderBy', 'b.id  ASC ')
                    ->getQuery();
                $elementids = $query->getResult();
                if (count($elementids) == 0) {
                    $query1 = $em->createQueryBuilder()
                        ->select('b.kpiName', 'b.id')
                        ->from('InitialShippingBundle:RankingKpiDetails', 'b')
                        ->where('b.kpiName = :kpiName')
                        ->andwhere('b.dateTime <= :dateTime')
                        ->setParameter('dateTime', $new_date)
                        ->setParameter('kpiName', $kpiname)
                        ->add('orderBy', 'b.id  ASC ')
                        ->groupby('b.kpiName')
                        ->getQuery();

                    $ids1 = $query1->getResult();
                    $newkpiid = $ids1[0]['id'];
                    $newkpiname = $ids1[0]['kpiName'];
                    /* $query = $em->createQueryBuilder()
                         ->select('b.elementName', 'b.id','b.weightage')
                         ->from('InitialShippingBundle:RankingElementDetails', 'b')
                         ->where('b.kpiDetailsId = :kpidetailsid')
                         ->setParameter('kpidetailsid', $newkpiid)
                         ->add('orderBy', 'b.id  ASC ')
                         ->getQuery();*/
                    $query = $em->createQueryBuilder()
                        ->select('b.elementName', 'b.id', 'b.indicationValue', 'b.weightage', 'c.symbolIndication')
                        ->from('InitialShippingBundle:RankingElementDetails', 'b')
                        ->leftjoin('InitialShippingBundle:ElementSymbols', 'c', 'WITH', 'c.id = b.symbolId')
                        ->where('b.kpiDetailsId = :kpidetailsid')
                        ->setParameter('kpidetailsid', $newkpiid)
                        ->add('orderBy', 'b.id  ASC ')
                        ->getQuery();
                    $elementids = $query->getResult();
                    if ($maxelementcount < count($elementids)) {
                        $maxelementcount = count($elementids);
                    }
                    for ($j = 0; $j < count($elementids); $j++) {
                        $sessionkpielementid_ranking[$newkpiid][$j] = $elementids[$j]['id'];
                        $returnarray[$newkpiname][$j] = $elementids[$j]['elementName'];
                        array_push($elementweightage, $elementids[$j]['weightage']);
                        $indicationvalue = $elementids[$j]['symbolIndication'];
                        if ($indicationvalue == null) {
                            array_push($symbolIndication, "");
                        } else {
                            array_push($symbolIndication, $elementids[$j]['symbolIndication']);
                        }
                        array_push($elementindicationValue, $elementids[$j]['indicationValue']);
                    }
                } else {
                    if ($maxelementcount < count($elementids)) {
                        $maxelementcount = count($elementids);
                    }

                    for ($j = 0; $j < count($elementids); $j++) {
                        $sessionkpielementid_ranking[$kpiid][$j] = $elementids[$j]['id'];
                        $returnarray[$kpiname][$j] = $elementids[$j]['elementName'];
                        array_push($elementweightage, $elementids[$j]['weightage']);
                        $indicationvalue = $elementids[$j]['symbolIndication'];
                        if ($indicationvalue == null) {
                            array_push($symbolIndication, "");
                        } else {
                            array_push($symbolIndication, $elementids[$j]['symbolIndication']);
                        }
                        array_push($elementindicationValue, $elementids[$j]['indicationValue']);

                    }
                }
            }
            $elementvalues = array();
            for ($kkk = 0; $kkk < count($resularray); $kkk++) {
                array_push($elementvalues, $resularray[$kkk]['value']);
            }
            if ($mode == 'listkpielement') {
                return array('returnarray' => $returnarray, 'elementindicationValue' => $elementindicationValue, 'symbolIndication' => $symbolIndication, 'elementweightage' => $elementweightage, 'elementcount' => $maxelementcount, 'elementvalues' => $elementvalues);
            }
            $response = new JsonResponse();
            $response->setData(array(
                'kpiNameArray' => $returnarray,
                'indicationValue' => $elementindicationValue,
                'symbolIndication' => $symbolIndication,
                'elementweightage' => $elementweightage,
                'elementcount' => $maxelementcount,
                'elementvalues' => $elementvalues,
                'rejections' => $rejection_obj,
                'rejection_status' => $reject_status,
                'kpi_details' => $ids
            ));
            return $response;
        }
    }

    /**
     * Upload Index For Ranking.
     *
     * @Route("/uploadfile_ranking", name="uploadfile_ranking")
     */
    public function upAction(Request $request)
    {
        $user = $this->getUser();
        if ($user == null) {
            return $this->redirectToRoute('fos_user_security_login');
        } else {
            $em = $this->getDoctrine()->getManager();
            $userId = $user->getId();
            $username = $user->getUsername();
            $role = $user->getRoles();
            $excelobj = new Excel_file_details();

            $form = $this->createCreateForm($excelobj);
            $templatechoosen = 'base.html.twig';
            if ($role[0] == 'ROLE_KPI_INFO_PROVIDER') {
                $templatechoosen = 'v-ships_layout.html.twig';
            }

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
                $vesselQuery = $em->createQueryBuilder()
                    ->select('a.id', 'a.shipName')
                    ->from('InitialShippingBundle:ShipDetails', 'a')
                    ->leftjoin('InitialShippingBundle:CompanyDetails', 'b', 'WITH', 'b.id = a.companyDetailsId')
                    ->leftjoin('InitialShippingBundle:User', 'c', 'WITH', 'c.username = b.adminName')
                    ->where('c.id = :userId')
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
                $vesselQuery = $em->createQueryBuilder()
                    ->select('a.id', 'a.shipName')
                    ->from('InitialShippingBundle:ShipDetails', 'a')
                    ->leftjoin('InitialShippingBundle:User', 'b', 'WITH', 'b.companyid = a.companyDetailsId')
                    ->where('b.id = :userId')
                    ->setParameter('userId', $userId)
                    ->getQuery();
            }

            $kpiDetails = $query->getResult();
            $shipDetails = $vesselQuery->getResult();

            return $this->render('InitialShippingBundle:DataImportRanking:excelfile.html.twig', array(
                'form' => $form->createView(), 'template' => $templatechoosen,
                'kpiDetails' => $kpiDetails,
                'shipDetails' => $shipDetails
            ));
        }
    }

    /**
     * Lists all RankingDataImport entities.
     *
     * @Route("/ranking_data_import_find_element", name="ranking_data_import_find_element")
     */
    public function findElementAction(Request $request)
    {
        $user = $this->getUser();
        if ($user != null) {
            $kpiId = $request->get('kpiDetailsId');
            $em = $this->getDoctrine()->getManager();
            $query = $em->createQueryBuilder()
                ->select('a.id,a.elementName')
                ->from('InitialShippingBundle:RankingElementDetails', 'a')
                ->where('a.kpiDetailsId = :kpiId')
                ->setParameter('kpiId', $kpiId)
                ->getQuery();

            $elementDetails = $query->getResult();

            $response = new JsonResponse();
            $response->setData(array(
                'elementDetails' => $elementDetails
            ));

            return $response;
        } else {
            return $this->redirectToRoute('fos_user_security_login');
        }
    }

    /**
     * Read the File Using Gearman For Ranking.
     *
     * @Route("/readfile_gearman_ranking", name="readfile_gearman_ranking")
     */
    public function newuploadAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($user == null) {
            return $this->redirectToRoute('fos_user_security_login');
        } else {
            $userid = $user->getId();
            $vesselArray = $request->request->get('vessels');
            $vesselString = implode(',', $vesselArray);
            $kpiId = $request->request->get('kpis');
            $elementId = $request->request->get('elements');
            $excelobj = new Excel_file_details();
            //$uploadsucess=false;
            $form = $this->createCreateForm($excelobj);


            $form->handleRequest($request);

            if ($form->isValid()) {
                $exceldataofmonth = $excelobj->getDataOfMonth();
                $myexcelnewdatevalue = $exceldataofmonth->modify('last day of this month');
                $folderName = date('F-Y', strtotime(date_format($myexcelnewdatevalue, 'Y-m-d')));
                $uploaddir = $this->container->getParameter('kernel.root_dir') . '/../web/uploads/excelfiles/' . $folderName;
                $file = $excelobj->getFilename();
                $fileName = $excelobj->getFilename()->getClientOriginalName();
                $ext = pathinfo($uploaddir . $fileName, PATHINFO_EXTENSION);
                $name = substr($fileName, 0, -(strlen($ext) + 1));
                $fileName = $name . '@' . date('Y-m-d H-i-s') . '.' . $ext;
                if (!file_exists($uploaddir)) {
                    mkdir($uploaddir, 0777, true);
                    $folderobject = new RankingFolder();
                    $folderobject->setFolderName($folderName);
                    $em->persist($folderobject);
                    $em->flush();

                }
                if ($file->move($uploaddir, $fileName)) {
                    $username = $user->getUsername();
                    $userquery = $em->createQueryBuilder()
                        ->select('IDENTITY(a.companyid)')
                        ->from('InitialShippingBundle:User', 'a')
                        ->where('a.id = :userId')
                        ->setParameter('userId', $userid)
                        ->getQuery();
                    $useremailid = $userquery->getSingleScalarResult();
                    $folderId = $em->getRepository('InitialShippingBundle:RankingFolder')->findBy(array('folderName' => $folderName));
                    $excelobj->setFilename($fileName);
                    $excelobj->setFolderId($folderId[0]);
                    $input = $uploaddir . '/' . $excelobj->getFilename();
                    $excelobj->setUserid($username);
                    $excelobj->setCompanyId($useremailid);
                    $nowdate1 = date("Y-m-d H:i:s");
                    $nowdatetime = new \DateTime($nowdate1);
                    $excelobj->setDatetime($nowdatetime);
                    $excelobj->setDataOfMonth($myexcelnewdatevalue);
                    $excelobj->setElementDetailsId($elementId);
                    $excelobj->setKpiDetailsId($kpiId);
                    $excelobj->setVesselId($vesselString);
                    $em->persist($excelobj);
                    $em->flush();
                }
            }

            $cre = "File upload!!!!!!!!!";
            return $this->redirect('showfile_ranking');
        }
    }

    /**
     * Read the File For Ranking.
     *
     * @Route("/readfile_ranking", name="readfile_ranking")
     */
    public function uploadAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($user == null) {
            return $this->redirectToRoute('fos_user_security_login');
        } else {
            $excelobj = new Excel_file_details();
            //$uploadsucess=false;
            $form = $this->createCreateForm($excelobj);


            $form->handleRequest($request);

            if ($form->isValid()) {
                $uploaddir = $this->container->getParameter('kernel.root_dir') . '/../web/uploads/excelfiles';
                $file = $excelobj->getFilename();

                $fileName = $excelobj->getFilename()->getClientOriginalName();

                $ext = pathinfo($uploaddir . $fileName, PATHINFO_EXTENSION);

                $name = substr($fileName, 0, -(strlen($ext) + 1));
                //  echo $name.'<br>';
                $i = 1;

                $fileName = $name . date('Y-m-d H-i-s') . '.' . $ext;


                if ($file->move($uploaddir, $fileName)) {


                    $username = $user->getUsername();
                    $userquery = $em->createQueryBuilder()
                        ->select('a.emailId', 'a.id')
                        ->from('InitialShippingBundle:CompanyDetails', 'a')
                        ->where('a.adminName = :userId')
                        ->setParameter('userId', $username)
                        ->getQuery();
                    $useremailid = $userquery->getResult();
                    $excelobj->setFilename($fileName);
                    $mailer = $this->container->get('mailer');
                    $input = $uploaddir . '/' . $excelobj->getFilename();

                    $mydatevalue = $excelobj->getDataofmonth();


                    $inputFileType = "";

                    switch ($ext) {
                        case "xls":
                            $inputFileType = 'Excel5';
                            break;
                        case "xlsx":
                            $inputFileType = 'Excel2007';
                            break;

                    }


                    // Creating Excel Sheet Objects....//

                    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
                    $objReader->setLoadAllSheets();
                    $objPHPExcel = $objReader->load($input);

                    $objWorksheet = $objPHPExcel->getActiveSheet();
                    $sheetCount = $objPHPExcel->getSheetCount();
                    $cre = "";


                    if ($sheetCount == 1) {

                        //Validation For Ship Details
                        $user = $this->getUser();
                        $userId = $user->getId();

                        $databaseshipsname = array();


                        if ($this->container->get('security.context')->isGranted('ROLE_ADMIN')) {
                            $query = $em->createQueryBuilder()
                                ->select('a.shipName', 'a.id')
                                ->from('InitialShippingBundle:ShipDetails', 'a')
                                ->join('InitialShippingBundle:CompanyDetails', 'b', 'WITH', 'b.id = a.companyDetailsId')
                                ->where('b.adminName = :username')
                                ->setParameter('username', $username)
                                ->getQuery();
                        } else {
                            $query = $em->createQueryBuilder()
                                ->select('a.shipName', 'a.id')
                                ->from('InitialShippingBundle:ShipDetails', 'a')
                                ->leftjoin('InitialShippingBundle:User', 'b', 'WITH', 'b.companyid = a.companyDetailsId')
                                ->where('b.id = :userId')
                                ->setParameter('userId', $userId)
                                ->getQuery();
                        }


                        $shipdetailsarray = $query->getResult();


                        for ($k = 0; $k < count($shipdetailsarray); $k++) {
                            $shipnamename = $shipdetailsarray[$k]['shipName'];
                            if ($shipnamename != " " && $shipnamename != null) {

                                array_push($databaseshipsname, $shipnamename);

                            }


                        }

                        // print_r($databaseshipsname);die;

                        $sheetshipsname = array();

                        $arrayLabel = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "w", "X", "Y", "z");
                        //$myArray=array();
                        $shipnameflag = true;
                        for ($x = 1; $x <= 10; $x++) {

                            for ($y = 1; $y < count($arrayLabel); $y++) {
                                //== display each cell value
                                $pcellvale = $objWorksheet->getCell($arrayLabel[$y] . $x)->getValue();
                                $mystaticvalue = 25;
                                $mystartvalue = "KPI";


                                if ($pcellvale == $mystartvalue) {

                                    $excelArray = $objWorksheet->rangeToArray($arrayLabel[$y] . $x . ':' . $arrayLabel[$mystaticvalue] . $x);

                                    foreach ($excelArray as $key => $value) {

                                        for ($m = 4; $m < count($value); $m++) {
                                            if ($value[$m] != " " && $value[$m] != null) {
                                                array_push($sheetshipsname, $value[$m]);
                                            }
                                        }

                                    }


                                    if (!(count($sheetshipsname) > count($databaseshipsname))) {

                                        for ($b = 0; $b < count($sheetshipsname); $b++) {
                                            if (!(in_array($sheetshipsname[$b], $databaseshipsname))) {
                                                $shipnameflag = false;
                                                $cre = "";
                                                $msg = "Ships Names Are Mismatch.Mismatch Shipnae: " . $sheetshipsname[$b];
                                                $message = \Swift_Message::newInstance()
                                                    ->setFrom('lawrance@commusoft.co.uk')
                                                    ->setTo($useremailid[0]['emailId'])
                                                    ->setSubject("Your Document Has Mismatch Values!!!!")
                                                    ->setBody($msg);
                                                $message->attach(\Swift_Attachment::fromPath($input)->setFilename($excelobj->getFilename()));
                                                $mailer->send($message);
                                                $excelobj->removeUpload($input);

                                                $this->addFlash(
                                                    'notice',
                                                    'Your File not Readed. Because, Ship Names are Mismatch !!!. Your Document Has Mismatch Value.so document resend to Your Mail. Check Your Mail!!!'
                                                );

                                                return $this->render(
                                                    'InitialShippingBundle:ExcelFileviews:showmessage.html.twig',
                                                    array('creator' => $cre, 'msg' => '')
                                                );
                                            }
                                        }

                                    }
                                    if ((count($sheetshipsname) > count($databaseshipsname))) {
                                        $shipnameflag = false;
                                    }

                                    if ($shipnameflag == false) {
                                        $cre = "";
                                        $msg = "Ships Are Mismatch";
                                        $message = \Swift_Message::newInstance()
                                            ->setFrom('lawrance@commusoft.co.uk')
                                            ->setTo($useremailid[0]['emailId'])
                                            ->setSubject("Your Document Has Mismatch Values!!!!")
                                            ->setBody($msg);
                                        $message->attach(\Swift_Attachment::fromPath($input)->setFilename($excelobj->getFilename()));
                                        $mailer->send($message);
                                        $excelobj->removeUpload($input);

                                        $this->addFlash(
                                            'notice',
                                            'Your File not Readed. Because, Ship Names are Mismatch !!!. Your Document Has Mismatch Value.so document resend to Your Mail. Check Your Mail!!!'
                                        );

                                        return $this->render(
                                            'InitialShippingBundle:DataImportRanking:showmessage.html.twig',
                                            array('creator' => $cre, 'msg' => '')
                                        );
                                    }


                                }

                            }
                        }


                        //Validation For Kpi Details
                        if ($this->container->get('security.context')->isGranted('ROLE_ADMIN')) {
                            $kpiquery = $em->createQueryBuilder()
                                ->select('a.cellName', 'a.kpiName', 'a.id', 'a.endDate')
                                ->from('InitialShippingBundle:RankingKpiDetails', 'a')
                                ->leftjoin('InitialShippingBundle:ShipDetails', 'e', 'WITH', 'e.id = a.shipDetailsId')
                                ->leftjoin('InitialShippingBundle:CompanyDetails', 'b', 'WITH', 'b.id = e.companyDetailsId')
                                ->leftjoin('InitialShippingBundle:User', 'c', 'WITH', 'c.username = b.adminName')
                                ->where('c.id = :userId')
                                ->groupby('a.kpiName')
                                ->setParameter('userId', $userId)
                                ->getQuery();
                        } else {
                            $kpiquery = $em->createQueryBuilder()
                                ->select('a.cellName', 'a.kpiName', 'a.id', 'a.endDate')
                                ->from('InitialShippingBundle:RankingKpiDetails', 'a')
                                ->leftjoin('InitialShippingBundle:ShipDetails', 'e', 'WITH', 'e.id = a.shipDetailsId')
                                ->leftjoin('InitialShippingBundle:User', 'b', 'WITH', 'b.companyid = e.companyDetailsId')
                                ->where('b.id = :userId')
                                ->groupby('a.kpiName')
                                ->setParameter('userId', $userId)
                                ->getQuery();

                        }
                        $newkpidetailsarray = $kpiquery->getResult();

                        $mycount = 0;
                        //$flag=true;
                        $j = count($newkpidetailsarray);
                        for ($i = 0; $i < $j; $i++) {


                            $cellname = $newkpidetailsarray[$i]['cellName'];
                            $cellvalue = $newkpidetailsarray[$i]['kpiName'];

                            $columnvalue1 = $objPHPExcel->getActiveSheet()->getCell($cellname)->getValue();
                            // echo 'The Column value'.$columnvalue1;die;
                            if ($cellvalue == $columnvalue1) {
                                $mycount++;

                                //Validation For Elements Starts Here...//

                                $elementid = $newkpidetailsarray[$i]['id'];
                                $query = $em->createQueryBuilder()
                                    ->select('b.cellName', 'b.elementName', 'b.id')
                                    ->from('InitialShippingBundle:RankingElementDetails', 'b')
                                    ->where('b.kpiDetailsId = :kpidetailsid')
                                    ->setParameter('kpidetailsid', $elementid)
                                    ->getQuery();
                                $elementarray = $query->getResult();

                                $elementcount = 0;
                                $c = count($elementarray);
                                for ($e = 0; $e < $c; $e++) {


                                    $elementcell = $elementarray[$e]['cellName'];
                                    $elementname = $elementarray[$e]['elementName'];
                                    // echo $cellvalue.'<br>';
                                    // echo $cellname.'<br>';
                                    $elementcellvalue = $objPHPExcel->getActiveSheet()->getCell($elementcell)->getValue();
                                    if ($elementcellvalue == $elementname) {
                                        $elementcount++;
                                    }
                                    if ($elementcellvalue != $elementname) {
                                        $elementcount--;

                                        $msg = 'In Cell ' . $elementcell . ' having that value:' . $elementname . ' Thats Mismatch Value So Correct!!!..';
                                        $cre = "";
                                        $message = \Swift_Message::newInstance()
                                            ->setFrom('lawrance@commusoft.co.uk')
                                            ->setTo($useremailid[0]['emailId'])
                                            ->setSubject("Your Document Has Mismatch Values!!!!")
                                            ->setBody($msg);
                                        $message->attach(\Swift_Attachment::fromPath($input)->setFilename($excelobj->getFilename()));
                                        $mailer->send($message);
                                        $excelobj->removeUpload($input);

                                        $this->addFlash(
                                            'notice',
                                            'Your File not Readed!!!.Because, Your Document Has Mismatch Value.so document resend to Your Mail. Check Your Mail!!!'
                                        );

                                        return $this->render(
                                            'InitialShippingBundle:DataImportRanking:showmessage.html.twig',
                                            array('creator' => $cre, 'msg' => $msg)
                                        );

                                    }
                                }


                                if ($c != $elementcount) {
                                    $msg = 'In Cell ' . $elementcell . ' having that value:' . $elementname . ' Thats Mismatch Value So Correct!!!..';
                                    $cre = "";
                                    $message = \Swift_Message::newInstance()
                                        ->setFrom('lawrance@commusoft.co.uk')
                                        ->setTo($useremailid[0]['emailId'])
                                        ->setSubject("Your Document Has Mismatch Values!!!!")
                                        ->setBody($msg);
                                    $message->attach(\Swift_Attachment::fromPath($input)->setFilename($excelobj->getFilename()));
                                    $mailer->send($message);
                                    $excelobj->removeUpload($input);

                                    $this->addFlash(
                                        'notice',
                                        'Your File not Readed!!!.Because, Your Document Has Mismatch Value.so document resend to Your Mail. Check Your Mail!!!'
                                    );

                                    return $this->render(
                                        'InitialShippingBundle:DataImportRanking:showmessage.html.twig',
                                        array('creator' => $cre, 'msg' => '')
                                    );
                                }


                            }
                            if ($cellvalue != $columnvalue1) {
                                $mycount--;
                                $msg = 'In Cell ' . $cellname . ' having that value:' . $cellvalue . ' Thats Mismatch Value So Correct!!!..';
                                $cre = "";
                                $message = \Swift_Message::newInstance()
                                    ->setFrom('lawrance@commusoft.co.uk')
                                    ->setTo($useremailid[0]['emailId'])
                                    ->setSubject("Your Document Has Mismatch Values!!!!")
                                    ->setBody($msg);
                                $message->attach(\Swift_Attachment::fromPath($input)->setFilename($excelobj->getFilename()));
                                $mailer->send($message);

                                $excelobj->removeUpload($input);

                                $this->addFlash(
                                    'notice',
                                    'Your File not Readed!!! Your Document Has Mismatch Value.so document resend to Your Mail. Check Your Mail!!!'
                                );

                                return $this->render(
                                    'InitialShippingBundle:DataImportRanking:showmessage.html.twig',
                                    array('creator' => $cre, 'msg' => $msg)
                                );


                            }


                        }


                        if ($j == $mycount) {


                            /* $highestRow = $objWorksheet->getHighestRow();
                             $highestColumn = $objWorksheet->getHighestColumn();*/

                            $excelsheet_data_array = array();
                            /*
                                                        $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
                                                        $nrColumns = ord($highestColumn) - 64;
                                                        $worksheetTitle = $objWorksheet->getTitle();*/
                            $kpielementvaluearray = array();
                            $newkpielementvaluearray = array();

                            $usergivendata = date_format($excelobj->getDataofmonth(), "-m-Y");
                            $elementid = 0;

                            for ($d = 0; $d < count($newkpidetailsarray); $d++) {


                                $cellname = $newkpidetailsarray[$d]['cellName'];
                                $kpiid = $newkpidetailsarray[$d]['id'];
                                $cellvalue = $newkpidetailsarray[$d]['kpiName'];
                                $cellenddate = $newkpidetailsarray[$d]['endDate'];
                                $databasedate = date_format($cellenddate, "m-Y");

                                /* if ($usergivendata <= $databasedate) { */

                                $columnvalue1 = $objPHPExcel->getActiveSheet()->getCell($cellname)->getValue();
                                if ($cellvalue == $columnvalue1) {
                                    $elementid = $newkpidetailsarray[$d]['id'];
                                    $query = $em->createQueryBuilder()
                                        ->select('b.cellName', 'b.elementName', 'b.id')
                                        ->from('InitialShippingBundle:RankingElementDetails', 'b')
                                        ->where('b.kpiDetailsId = :kpidetailsid')
                                        ->setParameter('kpidetailsid', $elementid)
                                        ->getQuery();
                                    $elementarray = $query->getResult();
                                    $o = count($elementarray);
                                    for ($p = 0; $p < $o; $p++) {


                                        $elementcell = $elementarray[$p]['cellName'];
                                        $elementname = $elementarray[$p]['elementName'];
                                        $elementid = $elementarray[$p]['id'];
                                        $mysheetelementvalues = array();
                                        $numbers_array = $excelobj->extract_numbers($elementcell);
                                        $totalshipcount = count($sheetshipsname) + 3;
                                        $columnLetter = PHPExcel_Cell::stringFromColumnIndex($totalshipcount + 1);
                                        $elementexcesheetvalue = $objWorksheet->rangeToArray($elementcell . ':' . $columnLetter . $numbers_array[0]);

                                        foreach ($elementexcesheetvalue as $key1 => $value1) {


                                            for ($mb = 3; $mb < $totalshipcount; $mb++) {

                                                $rulesresultarray = array();
                                                $read1 = "";
                                                //Finding rulues for Element
                                                $rulesarray = $em->createQueryBuilder()
                                                    ->select('b.rules')
                                                    ->from('InitialShippingBundle:RankingElementRules', 'b')
                                                    ->where('b.elementDetailsId = :elementDetailsId')
                                                    ->setParameter('elementDetailsId', $elementid)
                                                    ->getQuery()
                                                    ->getResult();


                                                $totalcountofrulesarry = count($rulesarray);
                                                //If element for rule is zero thats going take excel sheeet value that validation goes Starts Here..//
                                                if ($totalcountofrulesarry > 0) {

                                                    for ($aaa = 0; $aaa < count($rulesarray); $aaa++) {
                                                        $argu = $value1[$mb];
                                                        $jsfiledirectry = $this->container->getParameter('kernel.root_dir') . '/../web/js/87f1824_part_1_nodejs_3.js \'' . $rulesarray[$aaa]['rules'] . ' \' ' . $argu;
                                                        $jsfilename = 'node ' . $jsfiledirectry;
                                                        $handle = popen($jsfilename, 'r');
                                                        $read = fread($handle, 2096);
                                                        $read1 = str_replace("\n", '', $read);
                                                        if ($read1 != "false") {
                                                            break;
                                                        }

                                                    }
                                                    //If Element rule return null answer that shows error message starts Here//
                                                    if ($read1 == "false") {
                                                        $elementnameforfule = $em->createQueryBuilder()
                                                            ->select('a.elementName')
                                                            ->from('InitialShippingBundle:RankingElementDetails', 'a')
                                                            ->where('a.id = :userId')
                                                            ->setParameter('userId', $elementid)
                                                            ->getQuery()
                                                            ->getSingleScalarResult();
                                                        $msg = 'In Rule for Element  ' . $elementnameforfule . ' . Thats Mismatch Value So Correct!!!';
                                                        $cre = "";
                                                        $message = \Swift_Message::newInstance()
                                                            ->setFrom('lawrance@commusoft.co.uk')
                                                            ->setTo($useremailid[0]['emailId'])
                                                            ->setSubject("Your Document Has Mismatch Values!!!!")
                                                            ->setBody($msg);
                                                        $message->attach(\Swift_Attachment::fromPath($input)->setFilename($excelobj->getFilename()));
                                                        $mailer->send($message);

                                                        $excelobj->removeUpload($input);

                                                        $this->addFlash(
                                                            'notice',
                                                            'Your File not Readed!!! Your Document Has Mismatch Value.so document resend to Your Mail. Check Your Mail!!!'
                                                        );

                                                        return $this->render(
                                                            'InitialShippingBundle:ExcelFileviews:showmessage.html.twig',
                                                            array('creator' => $cre, 'msg' => $msg)
                                                        );

                                                    } //If Element rule return null answer that shows error message Ends Here//
                                                    else {
                                                        $kpielementvaluearray[$kpiid][$elementid][$mb - 3] = $read1;

                                                    }
                                                } else {
                                                    $kpielementvaluearray[$kpiid][$elementid][$mb - 3] = $value1[$mb];
                                                }
                                                //If element for rule is zero thats going take excel sheeet value that validation goes Ends  Here..//
                                            }
                                        }
                                    }
                                }
                            }
                            // Insertion process Starts Here //

                            if (count($shipdetailsarray) == count($databaseshipsname)) {

                                // $arrayexcelsheetvalues=array('shipideatilsarray'=>$shipdetailsarray,'kpielementvaluearray'=>$kpielementvaluearray,'dataofmonth'=>$excelobj->getDataOfMonth());


                                $abc = 0;
                                foreach ($kpielementvaluearray as $kpikey => $kpipvalue) {


                                    foreach ($kpipvalue as $elementkey => $elementvalue) {

                                        foreach ($elementvalue as $valuekey => $finalvalue) {

                                            $shipid = $shipdetailsarray[$abc]['id'];

                                            $newshipid = $em->getRepository('InitialShippingBundle:ShipDetails')->findOneBy(array('id' => $shipid));
                                            $newkpiid = $em->getRepository('InitialShippingBundle:RankingKpiDetails')->findOneBy(array('id' => $kpikey));
                                            $newelementid = $em->getRepository('InitialShippingBundle:RankingElementDetails')->findOneBy(array('id' => $elementkey));
                                            $readingkpivalue = new RankingMonthlyData();
                                            $readingkpivalue->setElementDetailsId($newelementid);
                                            $exceldataofmonth = $excelobj->getDataOfMonth();
                                            $myexcelnewdatevalue = $exceldataofmonth->modify('last day of this month');
                                            $readingkpivalue->setMonthdetail($myexcelnewdatevalue);
                                            $readingkpivalue->setShipDetailsId($newshipid);
                                            $readingkpivalue->setKpiDetailsId($newkpiid);
                                            $readingkpivalue->setValue($finalvalue);
                                            $em = $this->getDoctrine()->getManager();
                                            $em->persist($readingkpivalue);
                                            $em->flush();
                                            $abc++;
                                        }
                                        $abc = 0;
                                    }

                                }
                            }

                            $exceldataofmonth = $excelobj->getDataOfMonth();
                            $myexcelnewdatevalue = $exceldataofmonth->modify('last day of this month');
                            $excelobj->setUserid($username);
                            $excelobj->setCompanyId($useremailid[0]['id']);
                            $nowdate1 = date("Y-m-d H:i:s");
                            $nowdatetime = new \DateTime($nowdate1);
                            $excelobj->setDatetime($nowdatetime);
                            $excelobj->setDataOfMonth($myexcelnewdatevalue);

                            $em->persist($excelobj);
                            $em->flush();
                            // Insertion process Starts Ends Here //

                            $cre = "Your File Readed!!!";

                            $this->addFlash(
                                'notice',
                                'Your Document Has Been Added!!!!'
                            );
                            return $this->redirectToRoute('showfile_ranking');

                        }

                        if ($j != $mycount) {
                            $msg = 'In Cell ' . $cellname . ' having that value:' . $cellvalue . ' Thats Mismatch Value So Correct!!!1';
                            $cre = "";
                            $message = \Swift_Message::newInstance()
                                ->setFrom('lawrance@commusoft.co.uk')
                                ->setTo($useremailid[0]['emailId'])
                                ->setSubject("Your Document Has Mismatch Values!!!!")
                                ->setBody($msg);
                            $message->attach(\Swift_Attachment::fromPath($input)->setFilename($excelobj->getFilename()));
                            $mailer->send($message);

                            $excelobj->removeUpload($input);

                            $this->addFlash(
                                'notice',
                                'Your File not Readed!!! Your Document Has Mismatch Value.so document resend to Your Mail. Check Your Mail!!!'
                            );

                            return $this->render(
                                'InitialShippingBundle:DataImportRanking:showmessage.html.twig',
                                array('creator' => $cre, 'msg' => $msg)
                            );
                        }

                    }

                    if ($sheetCount > 1) {

                        $message = \Swift_Message::newInstance()
                            ->setFrom('lawrance@commusoft.co.uk')
                            ->setTo($useremailid[0]['emailId'])
                            ->setSubject("Your Document having more than One Sheets!!!!")
                            ->setBody("Your Document having more than One Sheets!!!!");
                        $message->attach(\Swift_Attachment::fromPath($input)->setFilename($excelobj->getFilename()));
                        $mailer->send($message);
                        $loadedSheetNames = $objPHPExcel->getSheetNames();
                        $excelobj->removeUpload($input);

                        $this->addFlash(
                            'notice',
                            'Your Document having more than One Sheets.so document resend to Your Mail. Check Your Mail!!!'
                        );

                        return $this->render(
                            'InitialShippingBundle:DataImportRanking:showmessage.html.twig',
                            array('creator' => $cre, 'msg' => 'Number of Sheets: ' . $sheetCount)
                        );
                    }

                }


            }

            $cre = "Not Valid Document";
            return $this->render(
                'InitialShippingBundle:DataImportRanking:showmessage.html.twig',
                array('creator' => $cre, 'msg' => '')
            );
        }
    }

    /**
     * Show File For Ranking.
     *
     * @Route("/showfile_ranking", name="showfile_ranking")
     */
    public function showfileAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($user == null) {
            return $this->redirectToRoute('fos_user_security_login');
        } else {
            $username = $user->getUsername();
            $role = $user->getRoles();
            $companyid = $user->getCompanyid();
            if ($companyid == null) {
                $userquery = $em->createQueryBuilder()
                    ->select('a.id')
                    ->from('InitialShippingBundle:CompanyDetails', 'a')
                    ->where('a.adminName = :adminName')
                    ->setParameter('adminName', $username)
                    ->getQuery();
                $companyid = $userquery->getSingleScalarResult();
            }
            $templatechoosen = 'base.html.twig';
            if ($role[0] == 'ROLE_KPI_INFO_PROVIDER') {
                $templatechoosen = 'v-ships_layout.html.twig';
            }
            //  $userdetails = $em->getRepository('InitialShippingBundle:RankingFolder')->findAll();
            $listoffiles = $em->createQueryBuilder()
                ->select('c.folderName')
                ->from('InitialShippingBundle:RankingFolder', 'c')
                ->getQuery()
                ->getResult();
            $filenamesarray = array();
            for ($filecount = 0; $filecount < count($listoffiles); $filecount++) {
                $foldername = $listoffiles[$filecount]['folderName'];
                $listoffiles_foldername = $em->createQueryBuilder()
                    ->select('a.id', 'a.dataOfMonth', 'a.datetime', 'a.filename', 'a.userid', 'c.folderName')
                    ->from('InitialShippingBundle:Excel_file_details', 'a')
                    ->leftjoin('InitialShippingBundle:RankingFolder', 'c', 'WITH', 'c.id = a.folderId')
                    ->where('a.company_id = :company_id')
                    ->andwhere('c.folderName = :folderName')
                    ->setParameter('company_id', $companyid)
                    ->setParameter('folderName', $foldername)
                    ->getQuery()
                    ->getResult();
                $filenamesarray[$foldername] = $listoffiles_foldername;
            }


            return $this->render('InitialShippingBundle:DataImportRanking:listall.html.twig', array(
                'filenamesarray' => $filenamesarray, 'template' => $templatechoosen
            ));
        }

    }

    /**
     * Show File For Ranking.
     *
     * @Route("/{dataofmonth}/showfileslist_ranking", name="showfileslist_ranking")
     */
    public function showfiles_listfileAction(Request $request, $dataofmonth)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($user == null) {
            return $this->redirectToRoute('fos_user_security_login');
        } else {
            $username = $user->getUsername();
            $role = $user->getRoles();
            $companyid = $user->getCompanyid();
            //$convertedformat=new \DateTime($datofmonth);
            //$convertedformat->modify('last day of this month');
            if ($companyid == null) {
                $userquery = $em->createQueryBuilder()
                    ->select('a.id')
                    ->from('InitialShippingBundle:CompanyDetails', 'a')
                    ->where('a.adminName = :adminName')
                    ->setParameter('adminName', $username)
                    ->getQuery();
                $companyid = $userquery->getSingleScalarResult();
            }
            /*
                $templatechoosen = 'base.html.twig';
                if ($role[0] == 'ROLE_KPI_INFO_PROVIDER') {
                    $templatechoosen = 'v-ships_layout.html.twig';
                }*/
            $listoffiles = $em->createQueryBuilder()
                ->select('a.id', 'a.dataOfMonth', 'a.datetime', 'a.filename', 'a.userid', 'c.folderName')
                ->from('InitialShippingBundle:Excel_file_details', 'a')
                ->leftjoin('InitialShippingBundle:RankingFolder', 'c', 'WITH', 'c.id = a.folderId')
                ->where('a.company_id = :company_id')
                ->andwhere('c.folderName = :folderName')
                ->setParameter('company_id', $companyid)
                ->setParameter('folderName', $dataofmonth)
                ->getQuery()
                ->getResult();

            $childerarray = array();
            for ($listofFileCount = 0; $listofFileCount < count($listoffiles); $listofFileCount++) {
                $fileid = 'file-' . ($listofFileCount + 1);
                $childerarray[$listofFileCount]['id'] = $fileid;
                $childerarray[$listofFileCount]['name'] = $listoffiles[$listofFileCount]['filename'];
                $childerarray[$listofFileCount]['type'] = 'xls';
                if ($role[0] != 'ROLE_KPI_INFO_PROVIDER') {
                    $childerarray[$listofFileCount]['url'] = '/dataverfication/' . $listoffiles[$listofFileCount]['filename'] . '/' . $listoffiles[0]['folderName'] . '/downfile_ranking';
                }


            }
            $finalconstractorarray = array();
            $finalconstractorarray['id'] = 'dir-1';
            $finalconstractorarray['name'] = $listoffiles[0]['folderName'];
            $finalconstractorarray['type'] = 'dir';
            $finalconstractorarray['children'] = $childerarray;


            $response = new JsonResponse();
            $response->setData(array('listoffiles' => array($finalconstractorarray)));
            return $response;
        }

    }

    /**
     * Show File For Ranking.
     *
     * @Route("/{dataofmonth}/showfileslist_filter", name="showfileslist_filter")
     */
    public function showfiles_withfilterAction(Request $request, $dataofmonth)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($user == null) {
            return $this->redirectToRoute('fos_user_security_login');
        } else {
            $username = $user->getUsername();
            $role = $user->getRoles();
            $companyid = $user->getCompanyid();
            if ($dataofmonth != 1) {
                $datofmonth = '01-' . $dataofmonth;
                $convertedformat = new \DateTime($datofmonth);
                $convertedformat->modify('last day of this month');
                if ($companyid == null) {
                    $userquery = $em->createQueryBuilder()
                        ->select('a.id')
                        ->from('InitialShippingBundle:CompanyDetails', 'a')
                        ->where('a.adminName = :adminName')
                        ->setParameter('adminName', $username)
                        ->getQuery();
                    $companyid = $userquery->getSingleScalarResult();
                }
                $templatechoosen = 'base.html.twig';
                if ($role[0] == 'ROLE_KPI_INFO_PROVIDER') {
                    $templatechoosen = 'v-ships_layout.html.twig';
                }
                $listoffiles = $em->createQueryBuilder()
                    ->select('a.id', 'a.dataOfMonth', 'a.datetime', 'a.filename', 'a.userid')
                    ->from('InitialShippingBundle:Excel_file_details', 'a')
                    ->where('a.company_id = :company_id')
                    ->andwhere('a.dataOfMonth = :dataOfMonth')
                    ->setParameter('company_id', $companyid)
                    ->setParameter('dataOfMonth', $convertedformat)
                    ->getQuery()
                    ->getResult();
            } else {

                if ($companyid == null) {
                    $userquery = $em->createQueryBuilder()
                        ->select('a.id')
                        ->from('InitialShippingBundle:CompanyDetails', 'a')
                        ->where('a.adminName = :adminName')
                        ->setParameter('adminName', $username)
                        ->getQuery();
                    $companyid = $userquery->getSingleScalarResult();
                }
                $templatechoosen = 'base.html.twig';
                if ($role[0] == 'ROLE_KPI_INFO_PROVIDER') {
                    $templatechoosen = 'v-ships_layout.html.twig';
                }
                $listoffiles = $em->createQueryBuilder()
                    ->select('a.id', 'a.dataOfMonth', 'a.datetime', 'a.filename', 'a.userid')
                    ->from('InitialShippingBundle:Excel_file_details', 'a')
                    ->where('a.company_id = :company_id')
                    ->setParameter('company_id', $companyid)
                    ->getQuery()
                    ->getResult();
            }

            $response = new JsonResponse();
            $response->setData(array('listoffiles' => $listoffiles));
            return $response;
        }

    }

    /**
     * Download File For Ranking.
     *
     * @Route("/{filename}/{foldername}/downfile_ranking", name="downfile_ranking")
     */
    public function downloadexcelAction($filename, $foldername, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $uploaddir = $this->container->getParameter('kernel.root_dir') . '/../web/uploads/excelfiles/' . $foldername . '/' . $filename;
        $content = file_get_contents($uploaddir);
        $fileType = pathinfo($uploaddir, PATHINFO_EXTENSION);
        $filenamearray = explode("@", $filename);
        $outputFilename = $filenamearray[0] . "." . $fileType;

        $response = new Response();

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $outputFilename);

        $response->setContent($content);
        return $response;
    }

    private function createCreateForm(Excel_file_details $excelobj)
    {
        $form = $this->createForm(new AddExcelFileType(), $excelobj, array(
            'action' => $this->generateUrl('readfile_gearman_ranking'),
            'method' => 'POST',
        ));

        /* $form->add('submit', 'submit', array('label' => 'Upload'));
         $form->add('add', 'submit', array('label' => 'Reading.....'));*/

        return $form;
    }

    /**
     * Ajax Call For change of monthdata of Scorecard
     *
     * @Route("/monthchangescorecard", name="monthchangescorecard")
     */
    public function monthchangedataverfication_scorecardAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($user != null) {
            $dataofmonth = $request->request->get('dataofmonth');
            // $time = strtotime($mydate);
            // $newformat = date('Y-m-d', $time);
            // $new_date = new \DateTime($newformat);
            // $new_date->modify('last day of this month');
            $userId = $user->getId();
            $username = $user->getUsername();
            $role = $user->getRoles();
            if ($this->container->get('security.context')->isGranted('ROLE_ADMIN')) {
                $query = $em->createQueryBuilder()
                    ->select('a.shipName', 'a.id')
                    ->from('InitialShippingBundle:ShipDetails', 'a')
                    ->join('InitialShippingBundle:CompanyDetails', 'b', 'WITH', 'b.id = a.companyDetailsId')
                    ->where('b.adminName = :username')
                    ->setParameter('username', $username)
                    ->getQuery();
            } else {
                $query = $em->createQueryBuilder()
                    ->select('a.shipName', 'a.id')
                    ->from('InitialShippingBundle:ShipDetails', 'a')
                    ->leftjoin('InitialShippingBundle:User', 'b', 'WITH', 'b.companyid = a.companyDetailsId')
                    ->where('b.id = :userId')
                    ->setParameter('userId', $userId)
                    ->getQuery();
            }


            $listallshipforcompany = $query->getResult();
            $statusforship = $this->findshipstatusmonth($dataofmonth, $listallshipforcompany, $role[0]);
            $finddatawithstatus = array();
            $shipid = 0;
            $shipname = '';

            if ($role[0] == 'ROLE_ADMIN') {
                $status = 2;
                $index = array_search(0, $statusforship);
                $shipid = $listallshipforcompany[$index]['id'];
                $shipname = $listallshipforcompany[$index]['shipName'];
                $finddatawithstatus = $this->finddatawithstatus($status, $shipid, $dataofmonth);
            }
            if ($role[0] == 'ROLE_MANAGER') {
                $status = 1;
                $index = array_search(0, $statusforship);
                $shipid = $listallshipforcompany[$index]['id'];
                $shipname = $listallshipforcompany[$index]['shipName'];
                $finddatawithstatus = $this->finddatawithstatus($status, $shipid, $dataofmonth);
            }
            if ($role[0] == 'ROLE_KPI_INFO_PROVIDER') {
                $status = 0;
                $index = array_search(0, $statusforship);
                $shipid = $listallshipforcompany[$index]['id'];
                $shipname = $listallshipforcompany[$index]['shipName'];
                $finddatawithstatus = $this->finddatawithstatus($status, $shipid, $dataofmonth);

            }

            $response = new JsonResponse();
            if (count($finddatawithstatus) == 4) {
                $response->setData(
                    array('listofships' => $listallshipforcompany,
                        'shipcount' => count($listallshipforcompany), 'status_ship' => $statusforship,
                        'elementkpiarray' => $finddatawithstatus['elementnamekpiname'], 'elementcount' => $finddatawithstatus['maxelementcount'],
                        'elementvalues' => $finddatawithstatus['elementvalues'],
                        'elementweightage' => $finddatawithstatus['elementweightage'],
                        'currentshipid' => $shipid, 'currentshipname' => $shipname
                    ));
                return $response;
            } else {
                $response->setData(
                    array('listofships' => $listallshipforcompany,
                        'shipcount' => count($listallshipforcompany), 'status_ship' => $statusforship,
                        'elementkpiarray' => array(), 'elementcount' => 0,
                        'elementvalues' => array(),
                        'currentshipid' => $shipid, 'currentshipname' => $shipname
                    ));
                return $response;
            }
        } else {
            return $this->redirectToRoute('fos_user_security_login');
        }

    }

    /**
     * Ajax Call For change of monthdata of Ranking
     *
     * @Route("/monthchangescorecard_ranking", name="monthchangescorecard_ranking")
     */
    public function monthchangedataverfication_rankingAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($user != null) {
            $dataofmonth = $request->request->get('dataofmonth');
            // $time = strtotime($mydate);
            // $newformat = date('Y-m-d', $time);
            // $new_date = new \DateTime($newformat);
            // $new_date->modify('last day of this month');
            $userId = $user->getId();
            $username = $user->getUsername();
            $role = $user->getRoles();
            if ($this->container->get('security.context')->isGranted('ROLE_ADMIN')) {
                $query = $em->createQueryBuilder()
                    ->select('a.shipName', 'a.id')
                    ->from('InitialShippingBundle:ShipDetails', 'a')
                    ->join('InitialShippingBundle:CompanyDetails', 'b', 'WITH', 'b.id = a.companyDetailsId')
                    ->where('b.adminName = :username')
                    ->setParameter('username', $username)
                    ->getQuery();
            } else {
                $query = $em->createQueryBuilder()
                    ->select('a.shipName', 'a.id')
                    ->from('InitialShippingBundle:ShipDetails', 'a')
                    ->leftjoin('InitialShippingBundle:User', 'b', 'WITH', 'b.companyid = a.companyDetailsId')
                    ->where('b.id = :userId')
                    ->setParameter('userId', $userId)
                    ->getQuery();
            }


            $listallshipforcompany = $query->getResult();
            $statusforship = $this->findshipstatus_ranking($dataofmonth, $listallshipforcompany, $role[0]);
            $finddatawithstatus = array();
            $shipid = 0;
            $shipname = '';

            if ($role[0] == 'ROLE_ADMIN') {
                $status = 2;
                $index = array_search(0, $statusforship);
                /*if($index!=false)
                {
                 */
                $shipid = $listallshipforcompany[$index]['id'];
                $shipname = $listallshipforcompany[$index]['shipName'];
                $finddatawithstatus = $this->finddatawithstatus_ranking($status, $shipid, $dataofmonth);
                /*}*/

            }
            if ($role[0] == 'ROLE_MANAGER') {
                $status = 1;
                $index = array_search(0, $statusforship);
                if ($index != false) {

                }
                $shipid = $listallshipforcompany[$index]['id'];
                $shipname = $listallshipforcompany[$index]['shipName'];
                $finddatawithstatus = $this->finddatawithstatus_ranking($status, $shipid, $dataofmonth);

            }
            if ($role[0] == 'ROLE_KPI_INFO_PROVIDER') {
                $status = 0;
                $index = array_search(0, $statusforship);
                if ($index != false) {

                }
                $shipid = $listallshipforcompany[$index]['id'];
                $shipname = $listallshipforcompany[$index]['shipName'];
                $finddatawithstatus = $this->finddatawithstatus_ranking($status, $shipid, $dataofmonth);


            }

            $response = new JsonResponse();
            if (count($finddatawithstatus) == 4) {
                $response->setData(
                    array('listofships' => $listallshipforcompany,
                        'shipcount' => count($listallshipforcompany), 'status_ship' => $statusforship,
                        'elementkpiarray' => $finddatawithstatus['elementnamekpiname'], 'elementcount' => $finddatawithstatus['maxelementcount'],
                        'elementvalues' => $finddatawithstatus['elementvalues'],
                        'elementweightage' => $finddatawithstatus['elementweightage'],
                        'currentshipid' => $shipid, 'currentshipname' => $shipname
                    ));
                return $response;
            } else {
                $response->setData(
                    array('listofships' => $listallshipforcompany,
                        'shipcount' => count($listallshipforcompany), 'status_ship' => $statusforship,
                        'elementkpiarray' => array(), 'elementcount' => 0,
                        'elementvalues' => array(),
                        'currentshipid' => $shipid, 'currentshipname' => $shipname
                    ));
                return $response;
            }

        } else {
            return $this->redirectToRoute('fos_user_security_login');
        }

    }

    /**
     * Ajax Call For change of previous monthdata of Ranking
     *
     * @Route("/ranking_previousmonth", name="ranking_previousmonth")
     */
    public function monthdata_previous_rankingAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $dataofmonth = $request->request->get('dataofmonth');
        $userId = $user->getId();
        $username = $user->getUsername();
        $role = $user->getRoles();

        $mydate = '01-' . $dataofmonth;
        $time = strtotime($mydate);
        $dateFormat = date('Y-m-d', $time);

        if ($this->container->get('security.context')->isGranted('ROLE_ADMIN')) {
            $query = $em->createQueryBuilder()
                ->select('a.shipName', 'a.id')
                ->from('InitialShippingBundle:ShipDetails', 'a')
                ->join('InitialShippingBundle:CompanyDetails', 'b', 'WITH', 'b.id = a.companyDetailsId')
                ->where('b.adminName = :username')
                ->setParameter('username', $username)
                ->getQuery();
        } else {
            $query = $em->createQueryBuilder()
                ->select('a.shipName', 'a.id')
                ->from('InitialShippingBundle:ShipDetails', 'a')
                ->leftjoin('InitialShippingBundle:User', 'b', 'WITH', 'b.companyid = a.companyDetailsId')
                ->where('b.id = :userId')
                ->setParameter('userId', $userId)
                ->getQuery();
        }

        $listallshipforcompany = $query->getResult();
        $statusforship = $this->findshipstatus_ranking($dataofmonth, $listallshipforcompany, $role[0]);
        $counts = array_count_values($statusforship);
        $ship_status_done_count = 0;
        if ($role[0] == 'ROLE_ADMIN') {
            if (array_key_exists(3, $counts)) {
                $ship_status_done_count = $counts[3];
            } else {
                $ship_status_done_count = 0;
            }


        } else if ($role[0] == 'ROLE_MANAGER') {
            if (array_key_exists(2, $counts)) {
                $ship_status_done_count = $counts[2];
            } else {
                $ship_status_done_count = 0;
            }
        } else if ($role[0] == 'ROLE_KPI_INFO_PROVIDER') {
            if (array_key_exists(5, $counts)) {
                $ship_status_done_count = $counts[5];
            } else {
                $ship_status_done_count = 0;
            }
        }


        if ($ship_status_done_count != count($listallshipforcompany)) {
            $finddatawithstatus = array();
            $shipid = 0;
            $shipname = '';

            if ($role[0] == 'ROLE_ADMIN') {
                $status = 2;
                $index = array_search(0, $statusforship);
                $shipid = $listallshipforcompany[$index]['id'];
                $shipname = $listallshipforcompany[$index]['shipName'];
                $finddatawithstatus = $this->finddatawithstatus_ranking($status, $shipid, $dataofmonth);
            }
            if ($role[0] == 'ROLE_MANAGER') {
                $status = 1;
                $index = array_search(0, $statusforship);
                if ($index != false) {

                }
                $shipid = $listallshipforcompany[$index]['id'];
                $shipname = $listallshipforcompany[$index]['shipName'];
                $finddatawithstatus = $this->finddatawithstatus_ranking($status, $shipid, $dataofmonth);

            }
            if ($role[0] == 'ROLE_KPI_INFO_PROVIDER') {
                $status = 0;
                $index = array_search(0, $statusforship);
                if ($index != false) {

                }
                $shipid = $listallshipforcompany[$index]['id'];
                $shipname = $listallshipforcompany[$index]['shipName'];
                $finddatawithstatus = $this->finddatawithstatus_ranking($status, $shipid, $dataofmonth);


            }

            $response = new JsonResponse();
            if (count($finddatawithstatus) == 7) {
                $response->setData(
                    array('listofships' => $listallshipforcompany,
                        'shipcount' => count($listallshipforcompany), 'status_ship' => $statusforship,
                        'elementkpiarray' => $finddatawithstatus['elementnamekpiname'], 'elementcount' => $finddatawithstatus['maxelementcount'],
                        'elementvalues' => $finddatawithstatus['elementvalues'],
                        'elementweightage' => $finddatawithstatus['elementweightage'],
                        'indicationValue' => $finddatawithstatus['indicationValue'],
                        'symbolIndication' => $finddatawithstatus['symbolIndication'],
                        'kpi_details' => $finddatawithstatus['kpi_details'],
                        'currentshipid' => $shipid, 'currentshipname' => $shipname, 'commontext' => false, 'dateFormat' => $dateFormat
                    ));
                return $response;
            } else {
                $response->setData(
                    array('listofships' => $listallshipforcompany,
                        'shipcount' => count($listallshipforcompany), 'status_ship' => $statusforship,
                        'elementkpiarray' => array(), 'elementcount' => 0,
                        'elementvalues' => array(),
                        'currentshipid' => $shipid, 'currentshipname' => $shipname, 'commontext' => false, 'dateFormat' => $dateFormat
                    ));
                return $response;
            }
        } else {
            if ($dataofmonth == '') {
                $monthInString = date('Y-m-d');
                $lastMonthDetail = new \DateTime($monthInString);
                $lastMonthDetail->modify('last day of this month');
            }
            if ($dataofmonth != '') {
                $monthInString = '01-' . $dataofmonth;
                $lastMonthDetail = new \DateTime($monthInString);
                $lastMonthDetail->modify('last day of this month');
            }

            $overallShipDetailArray = array();
            $Ships_Element_Value = array();
            $ElmentNameArray = array();
            $ElementWeighate_Array = array();
            $Element_status_count = 0;
            $common_RankingKpiList = $em->createQueryBuilder()
                ->select('b.kpiName', 'b.id', 'b.weightage')
                ->from('InitialShippingBundle:RankingKpiDetails', 'b')
                ->where('b.shipDetailsId = :shipid')
                ->setParameter('shipid', $listallshipforcompany[0]['id'])
                ->getQuery()
                ->getResult();
            for ($shipCount = 0; $shipCount < count($listallshipforcompany); $shipCount++) {
                $rankingKpiValueCountArray = array();
                $rankingShipName = $listallshipforcompany[$shipCount]['shipName'];
                $rankingShipId = $listallshipforcompany[$shipCount]['id'];

                $rankingKpiList = $em->createQueryBuilder()
                    ->select('b.kpiName', 'b.id', 'b.weightage')
                    ->from('InitialShippingBundle:RankingKpiDetails', 'b')
                    ->where('b.shipDetailsId = :shipid')
                    ->setParameter('shipid', $rankingShipId)
                    ->getQuery()
                    ->getResult();
                $ElementValuesfor_Ships = array();

                for ($rankingKpiCount = 0; $rankingKpiCount < count($rankingKpiList); $rankingKpiCount++) {

                    $kpiid = $rankingKpiList[$rankingKpiCount]['id'];
                    $kpiname = $rankingKpiList[$rankingKpiCount]['kpiName'];
                    $ElementName_KPI = array();
                    $ElementWeightage_KPI = array();
                    $query = $em->createQueryBuilder()
                        ->select('b.elementName', 'b.id', 'b.weightage')
                        ->from('InitialShippingBundle:RankingElementDetails', 'b')
                        ->where('b.kpiDetailsId = :kpidetailsid')
                        ->setParameter('kpidetailsid', $kpiid)
                        ->add('orderBy', 'b.id  ASC ')
                        ->getQuery();
                    $elementids = $query->getResult();
                    if (count($elementids) == 0) {
                        $query1 = $em->createQueryBuilder()
                            ->select('b.kpiName', 'b.id')
                            ->from('InitialShippingBundle:RankingKpiDetails', 'b')
                            ->where('b.kpiName = :kpiName')
                            ->setParameter('kpiName', $kpiname)
                            ->add('orderBy', 'b.id  ASC ')
                            ->groupby('b.kpiName')
                            ->getQuery();

                        $ids1 = $query1->getResult();
                        $newkpiid = $ids1[0]['id'];
                        $newkpiname = $ids1[0]['kpiName'];
                        $query = $em->createQueryBuilder()
                            ->select('b.elementName', 'b.id', 'b.weightage')
                            ->from('InitialShippingBundle:RankingElementDetails', 'b')
                            ->where('b.kpiDetailsId = :kpidetailsid')
                            ->setParameter('kpidetailsid', $newkpiid)
                            ->add('orderBy', 'b.id  ASC ')
                            ->getQuery();
                        $elementids = $query->getResult();
                        for ($j = 0; $j < count($elementids); $j++) {
                            $ElementId = $elementids[$j]['id'];

                            $ElementValue_Result = $em->createQueryBuilder()
                                ->select('b.value')
                                ->from('InitialShippingBundle:RankingMonthlyData', 'b')
                                ->where('b.shipDetailsId = :shipdetailsid')
                                ->andWhere('b.monthdetail =:dataofmonth')
                                ->andWhere('b.kpiDetailsId =:kpiDetailsId')
                                ->andWhere('b.elementDetailsId =:elementDetailsId')
                                ->setParameter('shipdetailsid', $rankingShipId)
                                ->setParameter('dataofmonth', $lastMonthDetail)
                                ->setParameter('kpiDetailsId', $newkpiid)
                                ->setParameter('elementDetailsId', $ElementId)
                                ->getQuery()
                                ->getResult();
                            if (count($ElementValue_Result) > 0) {
                                array_push($ElementValuesfor_Ships, $ElementValue_Result[0]['value']);
                            } else {
                                array_push($ElementValuesfor_Ships, null);
                            }


                        }
                    } else {

                        for ($j = 0; $j < count($elementids); $j++) {
                            $ElementId = $elementids[$j]['id'];
                            array_push($ElementName_KPI, $elementids[$j]['weightage']);
                            array_push($ElementWeightage_KPI, $elementids[$j]['elementName']);
                            $ElementValue_Result = $em->createQueryBuilder()
                                ->select('b.value')
                                ->from('InitialShippingBundle:RankingMonthlyData', 'b')
                                ->where('b.shipDetailsId = :shipdetailsid')
                                ->andWhere('b.monthdetail =:dataofmonth')
                                ->andWhere('b.kpiDetailsId =:kpiDetailsId')
                                ->andWhere('b.elementDetailsId =:elementDetailsId')
                                ->setParameter('shipdetailsid', $rankingShipId)
                                ->setParameter('dataofmonth', $lastMonthDetail)
                                ->setParameter('kpiDetailsId', $kpiid)
                                ->setParameter('elementDetailsId', $ElementId)
                                ->getQuery()
                                ->getResult();
                            if (count($ElementValue_Result) > 0) {
                                array_push($ElementValuesfor_Ships, $ElementValue_Result[0]['value']);
                            } else {
                                array_push($ElementValuesfor_Ships, null);
                            }
                        }
                        $ElmentNameArray[$kpiid] = $ElementName_KPI;
                        $ElementWeighate_Array[$kpiid] = $ElementWeightage_KPI;
                    }

                }
                $Ships_Element_Value[$rankingShipId] = $ElementValuesfor_Ships;
            }


            $response = new JsonResponse();

            $response->setData(
                array('listofships' => $listallshipforcompany,
                    'shipcount' => count($listallshipforcompany),
                    'elementvalues_ship' => $Ships_Element_Value,
                    'rankingKpiList' => $common_RankingKpiList,
                    'elementname' => $ElmentNameArray,
                    'elementweightage' => $ElementWeighate_Array,
                    'commontext' => true,
                    'dateFormat' => $dateFormat
                ));
            return $response;
        }
    }

    /**
     * Ajax Call For change of Prev monthdata of Scorecard
     *
     * @Route("/gotoscorecardmonthdata", name="gotoscorecardmonthdata")
     */
    public function gotoScorecardMonthDataAction(Request $request, $mode='')
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $dataofmonth = $request->request->get('dataofmonth');
        $userId = $user->getId();
        $username = $user->getUsername();
        $role = $user->getRoles();

        if($mode!='') {
            $dateFormat = date('Y-m-d');
            $dateObject = new \DateTime($dateFormat);
            $dateObject->modify('last day of this month');
        } else {
            $mydate = '01-' . $dataofmonth;
            $time = strtotime($mydate);
            $dateFormat = date('Y-m-d', $time);
            $dateObject = new \DateTime($dateFormat);
            $dateObject->modify('last day of this month');
        }

        if ($this->container->get('security.context')->isGranted('ROLE_ADMIN')) {
            $query = $em->createQueryBuilder()
                ->select('a.shipName', 'a.id')
                ->from('InitialShippingBundle:ShipDetails', 'a')
                ->join('InitialShippingBundle:CompanyDetails', 'b', 'WITH', 'b.id = a.companyDetailsId')
                ->where('b.adminName = :username')
                ->andWhere('a.dateTime <= :activeDate')
                ->setParameter('activeDate', $dateObject)
                ->groupBy('a.id')
                ->setParameter('username', $username)
                ->getQuery();
        } else {
            $query = $em->createQueryBuilder()
                ->select('a.shipName', 'a.id')
                ->from('InitialShippingBundle:ShipDetails', 'a')
                ->leftjoin('InitialShippingBundle:User', 'b', 'WITH', 'b.companyid = a.companyDetailsId')
                ->where('a.dateTime <= :activeDate')
                ->setParameter('activeDate', $dateObject)
                ->andwhere('b.id = :userId')
                ->setParameter('userId', $userId)
                ->groupBy('a.id')
                ->getQuery();
        }
        $listallshipforcompany = $query->getResult();

        $statusQueryResult = $em->createQueryBuilder()
            ->select('b.shipid, b.rejections, b.rejectionsStatus, b.status, b.temp_rejections')
            ->from('InitialShippingBundle:Scorecard_LookupStatus', 'b')
            ->Where('b.dataofmonth =:dataofmonth')
            ->setParameter('dataofmonth', $dateObject)
            ->getQuery()
            ->getResult();

        $elementvalues = array();
        $returnarray = array();
        $elementweightage = array();
        $elementindicationValue = array();
        $symbolIndication = array();

        $kpiids = $em->createQueryBuilder()
            ->select('b.kpiName', 'b.id')
            ->from('InitialShippingBundle:KpiDetails', 'b')
            ->where('b.dateTime <= :dateTime')
            ->setParameter('dateTime', $dateObject)
            ->add('orderBy', 'b.id  ASC ')
            ->groupBy('b.kpiName')
            ->getQuery()
            ->getResult();

        $maxelementcount = 0;

        for ($i = 0; $i < count($kpiids); $i++) {
            $kpiid = $kpiids[$i]['id'];
            $kpiname = $kpiids[$i]['kpiName'];

            $query = $em->createQueryBuilder()
                ->select('b.elementName', 'b.id', 'b.indicationValue', 'b.weightage', 'c.symbolIndication')
                ->from('InitialShippingBundle:ElementDetails', 'b')
                ->leftjoin('InitialShippingBundle:ElementSymbols', 'c', 'WITH', 'c.id = b.symbolId')
                ->where('b.kpiDetailsId = :kpidetailsid')
                ->andwhere('b.dateTime <= :dateTime')
                ->setParameter('dateTime', $dateObject)
                ->setParameter('kpidetailsid', $kpiid)
                ->add('orderBy', 'b.id  ASC ')
                ->getQuery();

            $elementids = $query->getResult();

            if (count($elementids) == 0) {

                $query1 = $em->createQueryBuilder()
                    ->select('b.kpiName', 'b.id')
                    ->from('InitialShippingBundle:KpiDetails', 'b')
                    ->where('b.kpiName = :kpiName')
                    ->andwhere('b.dateTime <= :dateTime')
                    ->setParameter('dateTime', $dateObject)
                    ->setParameter('kpiName', $kpiname)
                    ->add('orderBy', 'b.id  ASC ')
                    ->groupby('b.kpiName')
                    ->getQuery();

                $ids1 = $query1->getResult();

                $newkpiid = $ids1[0]['id'];
                $newkpiname = $ids1[0]['kpiName'];

                $query = $em->createQueryBuilder()
                    ->select('b.elementName', 'b.id', 'b.indicationValue', 'b.weightage', 'c.symbolIndication')
                    ->from('InitialShippingBundle:ElementDetails', 'b')
                    ->leftjoin('InitialShippingBundle:ElementSymbols', 'c', 'WITH', 'c.id = b.symbolId')
                    ->where('b.kpiDetailsId = :kpidetailsid')
                    ->andwhere('b.dateTime <= :dateTime')
                    ->setParameter('dateTime', $dateObject)
                    ->setParameter('kpidetailsid', $newkpiid)
                    ->add('orderBy', 'b.id  ASC ')
                    ->getQuery();

                $elementids = $query->getResult();
                if ($maxelementcount < count($elementids)) {
                    $maxelementcount = count($elementids);
                }
                for ($j = 0; $j < count($elementids); $j++) {
                    $returnarray[$newkpiname][$j] = $elementids[$j]['elementName'];
                    array_push($elementweightage, $elementids[$j]['weightage']);
                    $indicationvalue = $elementids[$j]['symbolIndication'];
                    if ($indicationvalue == null) {
                        array_push($symbolIndication, "");
                    } else {
                        array_push($symbolIndication, $elementids[$j]['symbolIndication']);
                    }
                    array_push($elementindicationValue, $elementids[$j]['indicationValue']);
                }
            } else {
                if ($maxelementcount < count($elementids)) {
                    $maxelementcount = count($elementids);
                }
                for ($j = 0; $j < count($elementids); $j++) {
                    $returnarray[$kpiname][$j] = $elementids[$j]['elementName'];
                    array_push($elementweightage, $elementids[$j]['weightage']);
                    $indicationvalue = $elementids[$j]['symbolIndication'];
                    if ($indicationvalue == null) {
                        array_push($symbolIndication, "");
                    } else {
                        array_push($symbolIndication, $elementids[$j]['symbolIndication']);
                    }
                    array_push($elementindicationValue, $elementids[$j]['indicationValue']);
                }
            }
        }

        $shipOverallStatus = "";
        $elementValues = array();

        if (count($statusQueryResult) == 0) {
            $shipOverallStatus = "no";
        } else {
            $shipidinString = $statusQueryResult[0]['shipid'];
            $shipidinArray = explode(",", $shipidinString);

            if (count($listallshipforcompany) == count($shipidinArray)) {

                $temp_status = $statusQueryResult[0]['status'];
                if ($role[0] == 'ROLE_ADMIN') {
                    if($temp_status == 3 || $temp_status == 4) {
                        if($statusQueryResult[0]['rejections'] == null || $statusQueryResult[0]['rejections'] == 'null') {
                            $shipOverallStatus = "yes";
                        } else {
                            $shipOverallStatus = "no";
                        }
                    } else {
                        $shipOverallStatus = "no";
                    }

                    if($temp_status == 2 || $temp_status == 3 || $temp_status == 4) {
                        for ($shipCount = 0; $shipCount < count($listallshipforcompany); $shipCount++) {
                            $resultarray = $em->createQueryBuilder()
                                ->select('b.value')
                                ->from('InitialShippingBundle:ReadingKpiValues', 'b')
                                ->where('b.shipDetailsId = :shipdetailsid')
                                ->andWhere('b.monthdetail =:dataofmonth')
                                ->setParameter('shipdetailsid', $listallshipforcompany[$shipCount]['id'])
                                ->setParameter('dataofmonth', $dateObject)
                                ->getQuery()
                                ->getResult();

                            $tempshipValueArray = array();

                            for ($valueCount = 0; $valueCount < count($resultarray); $valueCount++) {
                                array_push($tempshipValueArray, $resultarray[$valueCount]['value']);
                            }
                            array_push($elementValues, $tempshipValueArray);
                        }
                    }

                } else if ($role[0] == 'ROLE_MANAGER') {
                    if($temp_status == 2 || $temp_status == 3 || $temp_status == 4) {
                        if($statusQueryResult[0]['rejections'] == null || $statusQueryResult[0]['rejections'] == 'null') {
                            $shipOverallStatus = "yes";
                        } else {
                            $shipOverallStatus = "no";
                        }
                    } else {
                        $shipOverallStatus = "no";
                    }

                    if($temp_status == 2 || $temp_status == 3 || $temp_status == 4 || $temp_status == 5) {
                        for ($shipCount = 0; $shipCount < count($listallshipforcompany); $shipCount++) {
                            $resultarray = $em->createQueryBuilder()
                                ->select('b.value')
                                ->from('InitialShippingBundle:ReadingKpiValues', 'b')
                                ->where('b.shipDetailsId = :shipdetailsid')
                                ->andWhere('b.monthdetail =:dataofmonth')
                                ->setParameter('shipdetailsid', $listallshipforcompany[$shipCount]['id'])
                                ->setParameter('dataofmonth', $dateObject)
                                ->getQuery()
                                ->getResult();

                            $tempshipValueArray = array();

                            for ($valueCount = 0; $valueCount < count($resultarray); $valueCount++) {
                                array_push($tempshipValueArray, $resultarray[$valueCount]['value']);
                            }
                            array_push($elementValues, $tempshipValueArray);
                        }
                    }

                } else if ($role[0] == 'ROLE_KPI_INFO_PROVIDER') {
                    if($temp_status == 2 || $temp_status == 3 || $temp_status == 4 || $temp_status == 5) {
                        if($statusQueryResult[0]['temp_rejections'] == null || $statusQueryResult[0]['temp_rejections'] == 'null') {
                            $shipOverallStatus = "yes";
                        } else {
                            $shipOverallStatus = "no";
                        }
                    } else {
                        $shipOverallStatus = "no";
                    }

                    if($temp_status == 1 || $temp_status == 2 || $temp_status == 3 || $temp_status == 4 || $temp_status == 5) {
                        for ($shipCount = 0; $shipCount < count($listallshipforcompany); $shipCount++) {
                            $resultarray = $em->createQueryBuilder()
                                ->select('b.value')
                                ->from('InitialShippingBundle:ReadingKpiValues', 'b')
                                ->where('b.shipDetailsId = :shipdetailsid')
                                ->andWhere('b.monthdetail =:dataofmonth')
                                ->setParameter('shipdetailsid', $listallshipforcompany[$shipCount]['id'])
                                ->setParameter('dataofmonth', $dateObject)
                                ->getQuery()
                                ->getResult();

                            $tempshipValueArray = array();

                            for ($valueCount = 0; $valueCount < count($resultarray); $valueCount++) {
                                array_push($tempshipValueArray, $resultarray[$valueCount]['value']);
                            }
                            array_push($elementValues, $tempshipValueArray);
                        }
                    }
                }

            } else {
                /*$resultarray = $em->createQueryBuilder()
                    ->select('b.value')
                    ->from('InitialShippingBundle:ReadingKpiValues', 'b')
                    ->where('b.shipDetailsId = :shipdetailsid')
                    ->andWhere('b.monthdetail =:dataofmonth')
                    ->setParameter('shipdetailsid', $listallshipforcompany[0]['id'])
                    ->setParameter('dataofmonth', $dateObject)
                    ->getQuery()
                    ->getResult();

                $tempshipValueArray = array();

                for ($valueCount = 0; $valueCount < count($resultarray); $valueCount++) {
                    array_push($tempshipValueArray, $resultarray[$valueCount]['value']);
                }
                array_push($elementValues, $tempshipValueArray);

                $shipOverallStatus = "no";*/

                $shipOverallStatus = "no";

                $temp_status = $statusQueryResult[0]['status'];
                if ($role[0] == 'ROLE_ADMIN') {
                    if($temp_status == 3 || $temp_status == 4) {
                        for ($shipCount = 0; $shipCount < count($listallshipforcompany); $shipCount++) {
                            $resultarray = $em->createQueryBuilder()
                                ->select('b.value')
                                ->from('InitialShippingBundle:ReadingKpiValues', 'b')
                                ->where('b.shipDetailsId = :shipdetailsid')
                                ->andWhere('b.monthdetail =:dataofmonth')
                                ->setParameter('shipdetailsid', $listallshipforcompany[$shipCount]['id'])
                                ->setParameter('dataofmonth', $dateObject)
                                ->getQuery()
                                ->getResult();

                            $tempshipValueArray = array();

                            for ($valueCount = 0; $valueCount < count($resultarray); $valueCount++) {
                                array_push($tempshipValueArray, $resultarray[$valueCount]['value']);
                            }
                            array_push($elementValues, $tempshipValueArray);
                        }
                    }
                    if($temp_status == 3 || $temp_status == 4) {
                        if($statusQueryResult[0]['rejections'] == null || $statusQueryResult[0]['rejections'] == 'null') {
                            $shipOverallStatus = "yes";
                        }
                    }

                } else if ($role[0] == 'ROLE_MANAGER') {
                    if($temp_status == 2 || $temp_status == 3 || $temp_status == 4 || $temp_status == 5) {
                        for ($shipCount = 0; $shipCount < count($listallshipforcompany); $shipCount++) {
                            $resultarray = $em->createQueryBuilder()
                                ->select('b.value')
                                ->from('InitialShippingBundle:ReadingKpiValues', 'b')
                                ->where('b.shipDetailsId = :shipdetailsid')
                                ->andWhere('b.monthdetail =:dataofmonth')
                                ->setParameter('shipdetailsid', $listallshipforcompany[$shipCount]['id'])
                                ->setParameter('dataofmonth', $dateObject)
                                ->getQuery()
                                ->getResult();

                            $tempshipValueArray = array();

                            for ($valueCount = 0; $valueCount < count($resultarray); $valueCount++) {
                                array_push($tempshipValueArray, $resultarray[$valueCount]['value']);
                            }
                            array_push($elementValues, $tempshipValueArray);
                        }
                    }
                    if($temp_status == 2 || $temp_status == 3 || $temp_status == 4) {
                        if($statusQueryResult[0]['rejections'] == null || $statusQueryResult[0]['rejections'] == 'null') {
                            $shipOverallStatus = "yes";
                        }
                    }

                } else if ($role[0] == 'ROLE_KPI_INFO_PROVIDER') {
                    if($temp_status == 1 || $temp_status == 2 || $temp_status == 3 || $temp_status == 4 || $temp_status == 5) {
                        for ($shipCount = 0; $shipCount < count($listallshipforcompany); $shipCount++) {
                            $resultarray = $em->createQueryBuilder()
                                ->select('b.value')
                                ->from('InitialShippingBundle:ReadingKpiValues', 'b')
                                ->where('b.shipDetailsId = :shipdetailsid')
                                ->andWhere('b.monthdetail =:dataofmonth')
                                ->setParameter('shipdetailsid', $listallshipforcompany[$shipCount]['id'])
                                ->setParameter('dataofmonth', $dateObject)
                                ->getQuery()
                                ->getResult();

                            $tempshipValueArray = array();

                            for ($valueCount = 0; $valueCount < count($resultarray); $valueCount++) {
                                array_push($tempshipValueArray, $resultarray[$valueCount]['value']);
                            }
                            array_push($elementValues, $tempshipValueArray);
                        }
                    }
                    if($temp_status == 2 || $temp_status == 3 || $temp_status == 4 || $temp_status == 5) {
                        if($statusQueryResult[0]['temp_rejections'] == null || $statusQueryResult[0]['temp_rejections'] == 'null') {
                            $shipOverallStatus = "yes";
                        }
                    }
                }
            }
        }
        if (count($listallshipforcompany) != 0) {
            $currentShipId = $listallshipforcompany[0]['id'];
            $currentShipName = $listallshipforcompany[0]['shipName'];
        } else {
            $currentShipId = "";
            $currentShipName = "";
        }

        if($mode!='') {
            return array(
                'listofships' => $listallshipforcompany,
                'shipcount' => count($listallshipforcompany),
                'symbolIndication' => $symbolIndication,
                'indicationValue' => $elementindicationValue,
                'elementweightage' => $elementweightage,
                'elementkpiarray' => $returnarray,
                'maxelementcount' => $maxelementcount,
                'elementvalues' => $elementValues,
                'kpi_details' => $kpiids,
                'currentshipid' => $currentShipId,
                'currentshipname' => $currentShipName,
                'commontext' => false,
                'dateFormat' => $dateFormat,
                'statusQueryResult' => $statusQueryResult,
                'shipoverallStatus' => $shipOverallStatus,
            );
        }

        $respone = new JsonResponse();
        $respone->setData(array(
            'listofships' => $listallshipforcompany,
            'shipcount' => count($listallshipforcompany),
            'symbolIndication' => $symbolIndication,
            'indicationValue' => $elementindicationValue,
            'elementweightage' => $elementweightage,
            'elementkpiarray' => $returnarray,
            'maxelementcount' => $maxelementcount,
            'elementvalues' => $elementValues,
            'kpi_details' => $kpiids,
            'currentshipid' => $currentShipId,
            'currentshipname' => $currentShipName,
            'commontext' => false,
            'dateFormat' => $dateFormat,
            'statusQueryResult' => $statusQueryResult,
            'shipoverallStatus' => $shipOverallStatus,
            'shipwiseElementValues' => $elementValues
        ));
        return $respone;
    }
    /**
     * Ajax Call For change of Prev monthdata of Ranking
     *
     * @Route("/gotorankingdmonthdata", name="gotorankingmonthdata")
     */
    public function gotoRankingMonthDataAction(Request $request, $reuseMode='')
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $dataofmonth = $request->request->get('dataofmonth');
        $userId = $user->getId();
        $username = $user->getUsername();
        $role = $user->getRoles();

        if($reuseMode!='') {
            if($reuseMode=='defulatmonth')
            {
                $stringdataofmonth = date('Y-m-d');
                $time = strtotime($stringdataofmonth);
                $dateFormat = date('Y-m-d', $time);
                $dateObject = new \DateTime($stringdataofmonth);
                $dateObject->modify('last day of this month');
            }
            else
            {
                $time = strtotime($dataofmonth);
                $dateFormat = date('Y-m-d', $time);
                $dateObject = new \DateTime($dataofmonth);
                $dateObject->modify('last day of this month');
            }

        } else {
            $mydate = '01-' . $dataofmonth;
            $time = strtotime($mydate);
            $dateFormat = date('Y-m-d', $time);
            $dateObject = new \DateTime($dateFormat);
            $dateObject->modify('last day of this month');
        }

        if ($this->container->get('security.context')->isGranted('ROLE_ADMIN')) {
            $query = $em->createQueryBuilder()
                ->select('a.shipName', 'a.id')
                ->from('InitialShippingBundle:ShipDetails', 'a')
                ->join('InitialShippingBundle:CompanyDetails', 'b', 'WITH', 'b.id = a.companyDetailsId')
                ->where('b.adminName = :username')
                ->andWhere('a.dateTime <= :activeDate')
                ->setParameter('activeDate', $dateObject)
                ->groupBy('a.id')
                ->setParameter('username', $username)
                ->getQuery();
        } else {
            $query = $em->createQueryBuilder()
                ->select('a.shipName', 'a.id')
                ->from('InitialShippingBundle:ShipDetails', 'a')
                ->leftjoin('InitialShippingBundle:User', 'b', 'WITH', 'b.companyid = a.companyDetailsId')
                ->where('a.dateTime <= :activeDate')
                ->setParameter('activeDate', $dateObject)
                ->andwhere('b.id = :userId')
                ->setParameter('userId', $userId)
                ->groupBy('a.id')
                ->getQuery();
        }
        $listallshipforcompany = $query->getResult();

        $statusQueryResult = $em->createQueryBuilder()
            ->select('identity(b.shipid), b.rejections, b.status')
            ->from('InitialShippingBundle:Ranking_LookupStatus', 'b')
            ->Where('b.dataofmonth =:dataofmonth')
            ->setParameter('dataofmonth', $dateObject)
            ->addOrderBy('b.shipid', 'ASC')
            ->getQuery()
            ->getResult();
        $elementvalues = array();
        $shipOverallStatus = "";
        $elementValues = array();
        $overallShipStatusarray=array();
        $currentShipId = "";
        $currentShipName = "";
        $tempvaluefindcurrentShip=0;
        if (count($listallshipforcompany) == count($statusQueryResult)) {
            $shipdetailswithStatus=$this->findRankingOverallStatus($listallshipforcompany,$statusQueryResult);
            if ($role[0] == 'ROLE_ADMIN') {
                foreach ($shipdetailswithStatus as $key => $val)
                {
                    if ($val['status'] == 2 && $val['rejections'] ==false)
                    {
                        $currentShipId = $val['id'];
                        $currentShipName=$val['shipName'];
                        $shipOverallStatus = "no";
                        break;
                    }
                }
                if($currentShipName==""){
                    foreach ($shipdetailswithStatus as $key => $val)
                    {
                        if ($val['status'] == 2 && $val['rejections'] ==true)
                        {
                            $currentShipId = $val['id'];
                            $currentShipName=$val['shipName'];
                            $shipOverallStatus = "no";
                            break;
                        }
                    }
                }

                if($currentShipName==""){
                    $shipOverallStatus = "yes";
                }

            }
            if ($role[0] == 'ROLE_MANAGER') {
                foreach ($shipdetailswithStatus as $key => $val)
                {
                    if ($val['status'] == 5 && $val['rejections'] ==false)
                    {
                        $currentShipId = $val['id'];
                        $currentShipName=$val['shipName'];
                        $shipOverallStatus = "no";
                        break;
                    }
                }
                if($currentShipName==""){
                    foreach ($shipdetailswithStatus as $key => $val)
                    {
                        if ($val['status'] == 2 && $val['rejections'] ==true)
                        {
                            $currentShipId = $val['id'];
                            $currentShipName=$val['shipName'];
                            $shipOverallStatus = "no";
                            break;
                        }
                    }
                }
                if($currentShipName==""){
                    $shipOverallStatus = "yes";
                }

            }
            if ($role[0] == 'ROLE_KPI_INFO_PROVIDER') {
                /*foreach ($shipdetailswithStatus as $key => $val)
                {
                    if ($val['status'] == 1 && $val['rejections'] ==false)
                    {
                        $currentShipId = $val['id'];
                        $currentShipName=$val['shipName'];
                        $shipOverallStatus = "no";
                        break;
                    }
                }*/
                if($currentShipName==""){
                    foreach ($shipdetailswithStatus as $key => $val)
                    {
                        if ($val['status'] == 1 && $val['rejections'] ==true)
                        {
                            $currentShipId = $val['id'];
                            $currentShipName=$val['shipName'];
                            $shipOverallStatus = "no";
                            break;
                        }
                    }
                }

                if($currentShipName==""){
                    $shipOverallStatus = "yes";
                }

            }
            for ($shipCount = 0; $shipCount < count($listallshipforcompany); $shipCount++) {
               /*  if ($role[0] == 'ROLE_MANAGER') {
                    if( $statusQueryResult[$shipCount]['status'] == 2 || $statusQueryResult[$shipCount]['status'] == 3 || $statusQueryResult[$shipCount]['status'] == 4 )
                    {
                        if($statusQueryResult[$shipCount]['rejections']==null){
                            for($FirstinnerLoopCount=$shipCount+1;$FirstinnerLoopCount<count($listallshipforcompany);$FirstinnerLoopCount++){
                                $statusField=$statusQueryResult[$FirstinnerLoopCount]['status'];
                                $rejectionField=$statusQueryResult[$FirstinnerLoopCount]['rejections'];
                                if($statusField==5 && $rejectionField ==null){
                                    if($tempvaluefindcurrentShip==0){
                                        $currentShipId = $listallshipforcompany[$FirstinnerLoopCount]['id'];
                                        $currentShipName =$listallshipforcompany[$FirstinnerLoopCount]['shipName'];
                                        $tempvaluefindcurrentShip++;
                                    }
                                    $shipOverallStatus = "no";
                                }
                                else{
                                    $shipOverallStatus = "yes";
                                }
                            }
                        }
                        else if($statusQueryResult[$shipCount]['rejections']!=null){
                            if($statusQueryResult[$shipCount]['rejections']!=null &&$statusQueryResult[$shipCount]['status']==2){
                                if($tempvaluefindcurrentShip==0){
                                    $currentShipId = $listallshipforcompany[$shipCount]['id'];
                                    $currentShipName =$listallshipforcompany[$shipCount]['shipName'];
                                    $tempvaluefindcurrentShip++;
                                }
                                $shipOverallStatus = "no";
                            }
                            else{
                                for($innerLoopCount=$shipCount+1;$innerLoopCount<count($listallshipforcompany);$innerLoopCount++){
                                    $statusField=$statusQueryResult[$innerLoopCount]['status'];
                                    $rejectionField=$statusQueryResult[$innerLoopCount]['rejections'];
                                    if($statusField==2 && $rejectionField ==null){
                                        if($tempvaluefindcurrentShip==0){
                                            $currentShipId = $listallshipforcompany[$innerLoopCount]['id'];
                                            $currentShipName =$listallshipforcompany[$innerLoopCount]['shipName'];
                                            $tempvaluefindcurrentShip++;
                                        }
                                        $shipOverallStatus = "no";
                                    }
                                    elseif ($rejectionField !=null){
                                        if($tempvaluefindcurrentShip==0){
                                            $currentShipId = $listallshipforcompany[$innerLoopCount]['id'];
                                            $currentShipName =$listallshipforcompany[$innerLoopCount]['shipName'];
                                            $tempvaluefindcurrentShip++;
                                        }
                                        $shipOverallStatus = "no";
                                    }

                                }
                            }
                        }
                    }
                    if($statusQueryResult[$shipCount]['rejections']!=null &&$statusQueryResult[$shipCount]['status']==1){
                        if($tempvaluefindcurrentShip==0){
                            $currentShipId = $listallshipforcompany[$shipCount]['id'];
                            $currentShipName =$listallshipforcompany[$shipCount]['shipName'];
                            $tempvaluefindcurrentShip++;
                        }
                        $shipOverallStatus = "no";
                    }
                    if($statusQueryResult[$shipCount]['rejections']==null &&$statusQueryResult[$shipCount]['status']==1){
                        if($tempvaluefindcurrentShip==0){
                            $currentShipId = $listallshipforcompany[$shipCount]['id'];
                            $currentShipName =$listallshipforcompany[$shipCount]['shipName'];
                            $tempvaluefindcurrentShip++;
                        }
                        $shipOverallStatus = "no";
                    }
                    if($statusQueryResult[$shipCount]['rejections']==null &&$statusQueryResult[$shipCount]['status']==5){
                        if($tempvaluefindcurrentShip==0){
                            $currentShipId = $listallshipforcompany[$shipCount]['id'];
                            $currentShipName =$listallshipforcompany[$shipCount]['shipName'];
                            $tempvaluefindcurrentShip++;
                        }
                        $shipOverallStatus = "no";
                    }
                    /*else {
                        if($tempvaluefindcurrentShip==0){
                            $currentShipId = $listallshipforcompany[$shipCount]['id'];
                            $currentShipName =$listallshipforcompany[$shipCount]['shipName'];
                            $tempvaluefindcurrentShip++;
                        }
                        $shipOverallStatus = "no";
                    }
                }
                else if ($role[0] == 'ROLE_KPI_INFO_PROVIDER') {
                    if($statusQueryResult[$shipCount]['status'] == 1 || $statusQueryResult[$shipCount]['status'] == 2 || $statusQueryResult[$shipCount]['status'] == 3 || $statusQueryResult[$shipCount]['status'] == 4 || $statusQueryResult[$shipCount]['status'] == 5)
                    {
                        if($statusQueryResult[$shipCount]['rejections']==null){

                            $shipOverallStatus = "yes";
                        }
                        else if($statusQueryResult[$shipCount]['rejections']!=null){
                            if($statusQueryResult[$shipCount]['rejections']!=null &&$statusQueryResult[$shipCount]['status']==1){
                                if($tempvaluefindcurrentShip==0){
                                    $currentShipId = $listallshipforcompany[$shipCount]['id'];
                                    $currentShipName =$listallshipforcompany[$shipCount]['shipName'];
                                    $tempvaluefindcurrentShip++;
                                }
                                $shipOverallStatus = "no";
                            }
                            else{
                                for($innerLoopCount=$shipCount+1;$innerLoopCount<count($listallshipforcompany);$innerLoopCount++){
                                    $statusField=$statusQueryResult[$innerLoopCount]['status'];
                                    $rejectionField=$statusQueryResult[$innerLoopCount]['rejections'];

                                    if ($rejectionField !=null && $statusField==1){
                                        if($tempvaluefindcurrentShip==0){
                                            $currentShipId = $listallshipforcompany[$innerLoopCount]['id'];
                                            $currentShipName =$listallshipforcompany[$innerLoopCount]['shipName'];
                                            $tempvaluefindcurrentShip++;
                                        }
                                        $shipOverallStatus = "no";
                                    }

                                }
                            }
                        }
                    }

                }*/

                $resultarray = $em->createQueryBuilder()
                    ->select('b.value')
                    ->from('InitialShippingBundle:RankingMonthlyData', 'b')
                    ->where('b.shipDetailsId = :shipdetailsid')
                    ->andWhere('b.monthdetail =:dataofmonth')
                    ->setParameter('shipdetailsid', $listallshipforcompany[$shipCount]['id'])
                    ->setParameter('dataofmonth', $dateObject)
                    ->getQuery()
                    ->getResult();

                $tempshipValueArray = array();

                for ($valueCount = 0; $valueCount < count($resultarray); $valueCount++) {
                    array_push($tempshipValueArray, $resultarray[$valueCount]['value']);
                }
                $elementValues[$listallshipforcompany[$shipCount]['id']]=$tempshipValueArray;
            }
        }
        else {
            $shipOverallStatus = "no";
            for ($shipCount = 0; $shipCount < count($listallshipforcompany); $shipCount++) {
                $resultarray = $em->createQueryBuilder()
                    ->select('b.value')
                    ->from('InitialShippingBundle:RankingMonthlyData', 'b')
                    ->where('b.shipDetailsId = :shipdetailsid')
                    ->andWhere('b.monthdetail =:dataofmonth')
                    ->setParameter('shipdetailsid', $listallshipforcompany[$shipCount]['id'])
                    ->setParameter('dataofmonth', $dateObject)
                    ->getQuery()
                    ->getResult();
                $tempshipValueArray = array();
                for ($valueCount = 0; $valueCount < count($resultarray); $valueCount++) {
                    array_push($tempshipValueArray, $resultarray[$valueCount]['value']);
                }
                $elementValues[$listallshipforcompany[$shipCount]['id']]=$tempshipValueArray;
            }
            $shipStatuswithCurrentshipDetail=$this->findRankingShipStatus($listallshipforcompany,$dateObject);
            $currentShipId=$shipStatuswithCurrentshipDetail['currentshipId'];
            $currentShipName=$shipStatuswithCurrentshipDetail['currentShipName'];
            $overallShipStatusarray=$shipStatuswithCurrentshipDetail['shipStatus'];

        }
        $returnarray = array();
        $elementweightage = array();
        $elementindicationValue = array();
        $symbolIndication = array();
        $curretshipObject=$em->getRepository('InitialShippingBundle:ShipDetails')->findOneBy(array('id' =>$currentShipId));
        if($currentShipId==""){
            $kpiids = $em->createQueryBuilder()
                ->select('b.kpiName', 'b.id')
                ->from('InitialShippingBundle:RankingKpiDetails', 'b')
                ->where('b.dateTime <= :dateTime')
                ->andwhere('b.kpiName != :vesselageValue')
                ->setParameter('vesselageValue', 'Vessel age')
                ->setParameter('dateTime', $dateObject)
                ->add('orderBy', 'b.id  ASC ')
                ->groupBy('b.kpiName')
                ->getQuery()
                ->getResult();
        }
        else
        {
            $kpiids = $em->createQueryBuilder()
                ->select('b.kpiName', 'b.id')
                ->from('InitialShippingBundle:RankingKpiDetails', 'b')
                ->where('b.dateTime <= :dateTime')
                ->andwhere('b.kpiName != :vesselageValue')
                ->andwhere('b.shipDetailsId =:currentshipId')
                ->setParameter('currentshipId',$curretshipObject)
                ->setParameter('vesselageValue', 'Vessel age')
                ->setParameter('dateTime', $dateObject)
                ->add('orderBy', 'b.id  ASC ')
                ->groupBy('b.kpiName')
                ->getQuery()
                ->getResult();
        }
        $maxelementcount = 0;
        for ($i = 0; $i < count($kpiids); $i++) {
            $kpiid = $kpiids[$i]['id'];
            $kpiname = $kpiids[$i]['kpiName'];

            $query = $em->createQueryBuilder()
                ->select('b.elementName', 'b.id', 'b.indicationValue', 'b.weightage', 'c.symbolIndication')
                ->from('InitialShippingBundle:RankingElementDetails', 'b')
                ->leftjoin('InitialShippingBundle:ElementSymbols', 'c', 'WITH', 'c.id = b.symbolId')
                ->where('b.kpiDetailsId = :kpidetailsid')
                ->andwhere('b.dateTime <= :dateTime')
                ->setParameter('dateTime', $dateObject)
                ->setParameter('kpidetailsid', $kpiid)
                ->add('orderBy', 'b.id  ASC ')
                ->getQuery();
            $elementids = $query->getResult();
            if (count($elementids) == 0) {
                $query1 = $em->createQueryBuilder()
                    ->select('b.kpiName', 'b.id')
                    ->from('InitialShippingBundle:RankingKpiDetails', 'b')
                    ->where('b.kpiName = :kpiName')
                    ->andwhere('b.dateTime <= :dateTime')
                    ->setParameter('dateTime', $dateObject)
                    ->setParameter('kpiName', $kpiname)
                    ->add('orderBy', 'b.id  ASC ')
                    ->groupby('b.kpiName')
                    ->getQuery();
                $ids1 = $query1->getResult();
                $newkpiid = $ids1[0]['id'];
                $newkpiname = $ids1[0]['kpiName'];
                $query = $em->createQueryBuilder()
                    ->select('b.elementName', 'b.id', 'b.indicationValue', 'b.weightage', 'c.symbolIndication')
                    ->from('InitialShippingBundle:RankingElementDetails', 'b')
                    ->leftjoin('InitialShippingBundle:ElementSymbols', 'c', 'WITH', 'c.id = b.symbolId')
                    ->where('b.kpiDetailsId = :kpidetailsid')
                    ->andwhere('b.dateTime <= :dateTime')
                    ->setParameter('dateTime', $dateObject)
                    ->setParameter('kpidetailsid', $newkpiid)
                    ->add('orderBy', 'b.id  ASC ')
                    ->getQuery();
                $elementids = $query->getResult();
                if ($maxelementcount < count($elementids)) {
                    $maxelementcount = count($elementids);
                }
                for ($j = 0; $j < count($elementids); $j++) {
                    $returnarray[$newkpiname][$j] = $elementids[$j]['elementName'];
                    array_push($elementweightage, $elementids[$j]['weightage']);
                    $indicationvalue = $elementids[$j]['symbolIndication'];
                    if ($indicationvalue == null) {
                        array_push($symbolIndication, "");
                    } else {
                        array_push($symbolIndication, $elementids[$j]['symbolIndication']);
                    }
                    array_push($elementindicationValue, $elementids[$j]['indicationValue']);
                }
            } else {
                if ($maxelementcount < count($elementids)) {
                    $maxelementcount = count($elementids);
                }
                for ($j = 0; $j < count($elementids); $j++) {
                    $returnarray[$kpiname][$j] = $elementids[$j]['elementName'];
                    array_push($elementweightage, $elementids[$j]['weightage']);
                    $indicationvalue = $elementids[$j]['symbolIndication'];
                    if ($indicationvalue == null) {
                        array_push($symbolIndication, "");
                    } else {
                        array_push($symbolIndication, $elementids[$j]['symbolIndication']);
                    }
                    array_push($elementindicationValue, $elementids[$j]['indicationValue']);
                }
            }
        }


        if($currentShipId==""){
            $currentShipKpiList = $em->createQueryBuilder()
                ->select('b.kpiName', 'b.id')
                ->from('InitialShippingBundle:RankingKpiDetails', 'b')
                ->where('b.dateTime <= :dateTime')
                ->andwhere('b.kpiName != :vesselageValue')
                ->setParameter('vesselageValue', 'Vessel age')
                ->setParameter('dateTime', $dateObject)
                ->add('orderBy', 'b.id  ASC ')
                ->groupBy('b.kpiName')
                ->getQuery()
                ->getResult();
        }
        else
        {
            $currentShipKpiList = $em->createQueryBuilder()
                ->select('b.kpiName', 'b.id')
                ->from('InitialShippingBundle:RankingKpiDetails', 'b')
                ->where('b.dateTime <= :dateTime')
                ->andwhere('b.kpiName != :vesselageValue')
                ->andwhere('b.shipDetailsId =:currentshipId')
                ->setParameter('currentshipId',$curretshipObject)
                ->setParameter('vesselageValue', 'Vessel age')
                ->setParameter('dateTime', $dateObject)
                ->add('orderBy', 'b.id  ASC ')
                ->groupBy('b.kpiName')
                ->getQuery()
                ->getResult();
        }

        if($reuseMode!=''){
            return array(
                'listofships' => $listallshipforcompany,
                'shipcount' => count($listallshipforcompany),
                'symbolIndication' => $symbolIndication,
                'indicationValue' => $elementindicationValue,
                'elementweightage' => $elementweightage,
                'elementkpiarray' => $returnarray,
                'maxelementcount' => $maxelementcount,
                'kpi_details' => $kpiids,
                'currentshipid' => $currentShipId,
                'currentshipname' => $currentShipName,
                'commontext' => false,
                'dateFormat' => $dateFormat,
                'statusQueryResult' => $statusQueryResult,
                'shipoverallStatus' => $shipOverallStatus,
                'shipwiseElementValues' => $elementValues,
                'currentshipkpilist'=>$currentShipKpiList,
                'shipwiseStatuscount'=>$overallShipStatusarray,
            );
        }
        $respone = new JsonResponse();
        $respone->setData(array(
            'listofships' => $listallshipforcompany,
            'shipcount' => count($listallshipforcompany),
            'symbolIndication' => $symbolIndication,
            'indicationValue' => $elementindicationValue,
            'elementweightage' => $elementweightage,
            'elementkpiarray' => $returnarray,
            'maxelementcount' => $maxelementcount,
            'kpi_details' => $kpiids,
            'currentshipid' => $currentShipId,
            'currentshipname' => $currentShipName,
            'commontext' => false,
            'dateFormat' => $dateFormat,
            'statusQueryResult' => $statusQueryResult,
            'shipoverallStatus' => $shipOverallStatus,
            'shipwiseElementValues' => $elementValues,
            'currentshipkpilist'=>$currentShipKpiList,
            'shipwiseStatuscount'=>$overallShipStatusarray,
        ));
        return $respone;
    }
    private function findRankingOverallStatus($listofShips,$statusQueryResult)
    {
        $returnshipwithStaus=$listofShips;
        for($shipLimitCount=0;$shipLimitCount<count($listofShips);$shipLimitCount++){
            $returnshipwithStaus[$shipLimitCount]['status']=$statusQueryResult[$shipLimitCount]['status'];
            if($statusQueryResult[$shipLimitCount]['rejections']!=null){
                $returnshipwithStaus[$shipLimitCount]['rejections']=true;
            }
            else{
                $returnshipwithStaus[$shipLimitCount]['rejections']=false;
            }

        }
        return $returnshipwithStaus;

    }
    private function findRankingShipStatus($listofShips,$dateObject){
        $em = $this->getDoctrine()->getManager();
        $shipStatusArray=array();
        $currentShipId=0;
        $currentShipName="";
        $tempCountValue=0;
        for($outerLoopStatusCount=0;$outerLoopStatusCount<count($listofShips);$outerLoopStatusCount++){
            $statusandRejectionarray=array();
            $loopshipObject=$em->getRepository('InitialShippingBundle:ShipDetails')->findOneBy(array('id' =>$listofShips[$outerLoopStatusCount]['id']));
            $currentMonthStatusQueryResult = $em->createQueryBuilder()
                ->select('identity(b.shipid) as shipId, b.rejections, b.status')
                ->from('InitialShippingBundle:Ranking_LookupStatus', 'b')
                ->Where('b.dataofmonth =:dataofmonth')
                ->andwhere('b.shipid =:loopshipObject')
                ->setParameter('dataofmonth', $dateObject)
                ->setParameter('loopshipObject',$loopshipObject)
                ->getQuery()
                ->getResult();
            if(count($currentMonthStatusQueryResult)!=0){
                $statuValue=$currentMonthStatusQueryResult[0]['status'];
                $rejectionvalue=$currentMonthStatusQueryResult[0]['rejections'];
                $statusandRejectionarray['status']=$statuValue;
                $statusandRejectionarray['rejections']=$rejectionvalue;
                if($rejectionvalue!=null){
                    for($innerLoopCount=$outerLoopStatusCount+1;$innerLoopCount<count($listofShips);$innerLoopCount++){
                        $innterloopshipObject=$em->getRepository('InitialShippingBundle:ShipDetails')->findOneBy(array('id' =>$listofShips[$innerLoopCount]['id']));
                        $innterLoopStatusQueryResult = $em->createQueryBuilder()
                            ->select('identity(b.shipid) as shipId, b.rejections, b.status')
                            ->from('InitialShippingBundle:Ranking_LookupStatus', 'b')
                            ->Where('b.dataofmonth =:dataofmonth')
                            ->andwhere('b.shipid =:loopshipObject')
                            ->setParameter('dataofmonth', $dateObject)
                            ->setParameter('loopshipObject',$innterloopshipObject)
                            ->getQuery()
                            ->getResult();
                        if($innterLoopStatusQueryResult==0){
                            $statusCheckFiled=false;
                            if($tempCountValue==0){
                                $currentShipId=$listofShips[$innerLoopCount]['id'];
                                $currentShipName=$listofShips[$innerLoopCount]['shipName'];
                                $tempCountValue++;
                            }
                            break;
                        }
                        else{
                            continue;
                        }
                    }
                    /*if($statusCheckFiled){
                        if($tempCountValue==0){
                            $currentShipId=$listofShips[$outerLoopStatusCount]['id'];
                            $currentShipName=$listofShips[$outerLoopStatusCount]['shipName'];
                            $tempCountValue++;
                        }
                    }*/
                }

            }
            else{
                if($tempCountValue==0){
                    $currentShipId=$listofShips[$outerLoopStatusCount]['id'];
                    $currentShipName=$listofShips[$outerLoopStatusCount]['shipName'];
                    $tempCountValue++;
                }
            }
            $shipStatusArray[$listofShips[$outerLoopStatusCount]['id']]=$statusandRejectionarray;
        }
        return array('currentshipId'=>$currentShipId,'currentShipName'=>$currentShipName,'shipStatus'=>$shipStatusArray);
    }

    /**
     * Ajax Call For change of Prev monthdata of Scorecard
     *
     * @Route("/prev_month_change_scorecard", name="prev_month_change_scorecard")
     */
    public function prevmonthchangedataverfication_scorecardAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $dataofmonth = $request->request->get('dataofmonth');
        $userId = $user->getId();
        $username = $user->getUsername();
        $role = $user->getRoles();

        $mydate = '01-' . $dataofmonth;
        $time = strtotime($mydate);
        $dateFormat = date('Y-m-d', $time);
        $dateObject = new \DateTime($dateFormat);
        $dateObject->modify('last day of this month');
        if ($this->container->get('security.context')->isGranted('ROLE_ADMIN')) {
            $query = $em->createQueryBuilder()
                ->select('a.shipName', 'a.id')
                ->from('InitialShippingBundle:ShipDetails', 'a')
                ->join('InitialShippingBundle:CompanyDetails', 'b', 'WITH', 'b.id = a.companyDetailsId')
                ->join('InitialShippingBundle:ShipStatusDetails', 'sd', 'WITH', 'sd.shipDetailsId = a.id')
                ->where('b.adminName = :username')
                ->andWhere('sd.activeDate <= :activeDate')
                ->setParameter('activeDate', $dateObject)
                ->groupBy('a.id')
                ->setParameter('username', $username)
                ->getQuery();
        } else {
            $query = $em->createQueryBuilder()
                ->select('a.shipName', 'a.id')
                ->from('InitialShippingBundle:ShipDetails', 'a')
                ->leftjoin('InitialShippingBundle:User', 'b', 'WITH', 'b.companyid = a.companyDetailsId')
                ->join('InitialShippingBundle:ShipStatusDetails', 'sd', 'WITH', 'sd.shipDetailsId = a.id')
                ->where('sd.activeDate <= :activeDate')
                ->setParameter('activeDate', $dateObject)
                ->andwhere('b.id = :userId')
                ->setParameter('userId', $userId)
                ->getQuery();
        }


        $listallshipforcompany = $query->getResult();
        $statusforship = $this->findshipstatusmonth($dataofmonth, $listallshipforcompany, $role[0]);
        $counts = array_count_values($statusforship);
        if ($role[0] == 'ROLE_ADMIN') {
            if (array_key_exists(3, $counts)) {
                $ship_status_done_count = $counts[3];
            } else {
                $ship_status_done_count = 0;
            }


        } else if ($role[0] == 'ROLE_MANAGER') {
            if (array_key_exists(2, $counts)) {
                $ship_status_done_count = $counts[2];
            } else {
                $ship_status_done_count = 0;
            }
        } else if ($role[0] == 'ROLE_KPI_INFO_PROVIDER') {
            if (array_key_exists(5, $counts)) {
                $ship_status_done_count = $counts[5];
            } else {
                $ship_status_done_count = 0;
            }
        }


        if ($ship_status_done_count != count($listallshipforcompany)) {
            $finddatawithstatus = array();
            $shipid = 0;
            $shipname = '';

            if ($role[0] == 'ROLE_ADMIN') {
                $listofStatus = array(3, 4);
                $index = array_search(0, $statusforship);
                $shipid = $listallshipforcompany[$index]['id'];
                $shipname = $listallshipforcompany[$index]['shipName'];
                $finddatawithstatus = $this->finddatawithstatus($listofStatus, $shipid, $dataofmonth);
            }
            if ($role[0] == 'ROLE_MANAGER') {
                $listofStatus = array(2, 3, 4);
                $index = array_search(0, $statusforship);
                $shipid = $listallshipforcompany[$index]['id'];
                $shipname = $listallshipforcompany[$index]['shipName'];
                $finddatawithstatus = $this->finddatawithstatus($listofStatus, $shipid, $dataofmonth);
            }
            if ($role[0] == 'ROLE_KPI_INFO_PROVIDER') {
                $listofStatus = array(1, 5, 2, 3, 4);
                $index = array_search(0, $statusforship);
                $shipid = $listallshipforcompany[$index]['id'];
                $shipname = $listallshipforcompany[$index]['shipName'];
                $finddatawithstatus = $this->finddatawithstatus($listofStatus, $shipid, $dataofmonth);

            }

            $response = new JsonResponse();
            if (count($finddatawithstatus) == 7) {
                $response->setData(
                    array('listofships' => $listallshipforcompany,
                        'shipcount' => count($listallshipforcompany), 'status_ship' => $statusforship,
                        'elementkpiarray' => $finddatawithstatus['elementnamekpiname'],
                        'elementcount' => $finddatawithstatus['maxelementcount'],
                        'elementvalues' => $finddatawithstatus['elementvalues'],
                        'elementweightage' => $finddatawithstatus['elementweightage'],
                        'indicationValue' => $finddatawithstatus['indicationValue'],
                        'symbolIndication' => $finddatawithstatus['symbolIndication'],
                        'kpi_details' => $finddatawithstatus['kpi_details'],
                        'currentshipid' => $shipid, 'currentshipname' => $shipname, 'commontext' => false, 'dateFormat' => $dateFormat
                    ));
                return $response;
            } else {
                $response->setData(
                    array('listofships' => $listallshipforcompany,
                        'shipcount' => count($listallshipforcompany), 'status_ship' => $statusforship,
                        'elementkpiarray' => array(), 'elementcount' => 0,
                        'elementvalues' => array(),
                        'currentshipid' => $shipid, 'currentshipname' => $shipname, 'commontext' => false, 'dateFormat' => $dateFormat
                    ));
                return $response;
            }

        } else {
            if ($dataofmonth == '') {
                $monthInString = date('Y-m-d');
                $lastMonthDetail = new \DateTime($monthInString);
                $lastMonthDetail->modify('last day of this month');
            }
            if ($dataofmonth != '') {
                $monthInString = '01-' . $dataofmonth;
                $lastMonthDetail = new \DateTime($monthInString);
                $lastMonthDetail->modify('last day of this month');
            }

            $overallShipDetailArray = array();
            $Ships_Element_Value = array();
            $ElmentNameArray = array();
            $ElementWeighate_Array = array();
            $Element_status_count = 0;
            $common_RankingKpiList = $em->createQueryBuilder()
                ->select('b.kpiName', 'b.id', 'b.weightage')
                ->from('InitialShippingBundle:KpiDetails', 'b')
                ->where('b.shipDetailsId = :shipid')
                ->setParameter('shipid', $listallshipforcompany[0]['id'])
                ->getQuery()
                ->getResult();
            for ($shipCount = 0; $shipCount < count($listallshipforcompany); $shipCount++) {
                $rankingKpiValueCountArray = array();
                $rankingShipName = $listallshipforcompany[$shipCount]['shipName'];
                $rankingShipId = $listallshipforcompany[$shipCount]['id'];

                $rankingKpiList = $em->createQueryBuilder()
                    ->select('b.kpiName', 'b.id', 'b.weightage')
                    ->from('InitialShippingBundle:KpiDetails', 'b')
                    ->where('b.shipDetailsId = :shipid')
                    ->setParameter('shipid', $rankingShipId)
                    ->getQuery()
                    ->getResult();
                $ElementValuesfor_Ships = array();

                for ($rankingKpiCount = 0; $rankingKpiCount < count($rankingKpiList); $rankingKpiCount++) {

                    $kpiid = $rankingKpiList[$rankingKpiCount]['id'];
                    $kpiname = $rankingKpiList[$rankingKpiCount]['kpiName'];
                    $ElementName_KPI = array();
                    $ElementWeightage_KPI = array();
                    $query = $em->createQueryBuilder()
                        ->select('b.elementName', 'b.id', 'b.weightage')
                        ->from('InitialShippingBundle:ElementDetails', 'b')
                        ->where('b.kpiDetailsId = :kpidetailsid')
                        ->setParameter('kpidetailsid', $kpiid)
                        ->add('orderBy', 'b.id  ASC ')
                        ->getQuery();
                    $elementids = $query->getResult();
                    if (count($elementids) == 0) {
                        $query1 = $em->createQueryBuilder()
                            ->select('b.kpiName', 'b.id')
                            ->from('InitialShippingBundle:KpiDetails', 'b')
                            ->where('b.kpiName = :kpiName')
                            ->setParameter('kpiName', $kpiname)
                            ->add('orderBy', 'b.id  ASC ')
                            ->groupby('b.kpiName')
                            ->getQuery();

                        $ids1 = $query1->getResult();
                        $newkpiid = $ids1[0]['id'];
                        $newkpiname = $ids1[0]['kpiName'];
                        $query = $em->createQueryBuilder()
                            ->select('b.elementName', 'b.id', 'b.weightage')
                            ->from('InitialShippingBundle:ElementDetails', 'b')
                            ->where('b.kpiDetailsId = :kpidetailsid')
                            ->setParameter('kpidetailsid', $newkpiid)
                            ->add('orderBy', 'b.id  ASC ')
                            ->getQuery();
                        $elementids = $query->getResult();
                        for ($j = 0; $j < count($elementids); $j++) {
                            $ElementId = $elementids[$j]['id'];

                            $ElementValue_Result = $em->createQueryBuilder()
                                ->select('b.value')
                                ->from('InitialShippingBundle:ReadingKpiValues', 'b')
                                ->where('b.shipDetailsId = :shipdetailsid')
                                ->andWhere('b.monthdetail =:dataofmonth')
                                ->andWhere('b.kpiDetailsId =:kpiDetailsId')
                                ->andWhere('b.elementDetailsId =:elementDetailsId')
                                ->setParameter('shipdetailsid', $rankingShipId)
                                ->setParameter('dataofmonth', $lastMonthDetail)
                                ->setParameter('kpiDetailsId', $newkpiid)
                                ->setParameter('elementDetailsId', $ElementId)
                                ->getQuery()
                                ->getResult();
                            if (count($ElementValue_Result) > 0) {
                                array_push($ElementValuesfor_Ships, $ElementValue_Result[0]['value']);
                            } else {
                                array_push($ElementValuesfor_Ships, null);
                            }


                        }
                    } else {

                        for ($j = 0; $j < count($elementids); $j++) {
                            $ElementId = $elementids[$j]['id'];
                            array_push($ElementName_KPI, $elementids[$j]['weightage']);
                            array_push($ElementWeightage_KPI, $elementids[$j]['elementName']);
                            $ElementValue_Result = $em->createQueryBuilder()
                                ->select('b.value')
                                ->from('InitialShippingBundle:ReadingKpiValues', 'b')
                                ->where('b.shipDetailsId = :shipdetailsid')
                                ->andWhere('b.monthdetail =:dataofmonth')
                                ->andWhere('b.kpiDetailsId =:kpiDetailsId')
                                ->andWhere('b.elementDetailsId =:elementDetailsId')
                                ->setParameter('shipdetailsid', $rankingShipId)
                                ->setParameter('dataofmonth', $lastMonthDetail)
                                ->setParameter('kpiDetailsId', $kpiid)
                                ->setParameter('elementDetailsId', $ElementId)
                                ->getQuery()
                                ->getResult();
                            if (count($ElementValue_Result) > 0) {
                                array_push($ElementValuesfor_Ships, $ElementValue_Result[0]['value']);
                            } else {
                                array_push($ElementValuesfor_Ships, null);
                            }
                        }
                        $ElmentNameArray[$kpiid] = $ElementName_KPI;
                        $ElementWeighate_Array[$kpiid] = $ElementWeightage_KPI;
                    }

                }
                $Ships_Element_Value[$rankingShipId] = $ElementValuesfor_Ships;
            }


            $response = new JsonResponse();

            $response->setData(
                array('listofships' => $listallshipforcompany,
                    'shipcount' => count($listallshipforcompany),
                    'elementvalues_ship' => $Ships_Element_Value,
                    'rankingKpiList' => $common_RankingKpiList,
                    'elementname' => $ElmentNameArray,
                    'elementweightage' => $ElementWeighate_Array,
                    'commontext' => true,
                    'dateFormat' => $dateFormat
                ));
            return $response;
        }

    }

    public function dbBackupAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($user == null) {
            return $this->redirectToRoute('fos_user_security_login');
        } else {
            $connection = $em->getConnection();
            $refConn = new \ReflectionObject($connection);
            $refParams = $refConn->getProperty('_params');
            $refParams->setAccessible('public');
            $params = $refParams->getValue($connection);

            $filelocation = $this->container->getParameter('kernel.root_dir') . '/../web/sqlfiles';
            if (!is_dir($filelocation)) {
                mkdir($filelocation);
            }
            $outfile_filepath = $filelocation . '/' . $params['dbname'] . '.sql';
            if (file_exists($outfile_filepath)) {
                unlink($outfile_filepath);
            }

            $command = 'mysqldump -u' . $params['user'] . ' -p' . $params['password'] . ' ' . $params['dbname'] . '  > ' . $outfile_filepath;
            system($command);
            $content = file_get_contents($outfile_filepath);
            $response = new Response();
            $response->setContent($content);
            header('Content-Type: application/octet-stream');
            header("Content-Transfer-Encoding: Binary");
            header("Content-disposition: attachment; filename=\"" . $params['dbname'] . ".sql\"");
            return $response;
        }
    }

    /**
     * Ajax Call For change of Prev monthdata of Scorecard
     *
     * @Route("/db_backup", name="database_export")
     */
    public function newbackupAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($user == null) {
            return $this->redirectToRoute('fos_user_security_login');
        } else {
            $connection = $em->getConnection();
            $refConn = new \ReflectionObject($connection);
            $refParams = $refConn->getProperty('_params');
            $refParams->setAccessible('public');
            $params = $refParams->getValue($connection);
            $dbhost = $params["host"];
            $dbuser = $params['user'];
            $dbpass = $params['password'];
            $dbname = $params['dbname'];
// db connect
            $pdo = new \PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
// file header stuff
            $output = "-- PHP MySQL Dump\n--\n";
            $output .= "-- Host: $dbhost\n";
            $output .= "-- Generated: " . date("r", time()) . "\n";
            $output .= "-- PHP Version: " . phpversion() . "\n\n";
            $output .= "SET SQL_MODE=\"NO_AUTO_VALUE_ON_ZERO\";\n\n";
            $output .= "--\n-- Database: `$dbname`\n--\n";
// get all table names in db and stuff them into an array
            $tables = array();
            $stmt = $pdo->query("SHOW TABLES");
            while ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
                $tables[] = $row[0];
            }
// process each table in the db
            foreach ($tables as $table) {
                $fields = "";
                $sep2 = "";
                $output .= "\n-- " . str_repeat("-", 60) . "\n\n";
                $output .= "--\n-- Table structure for table `$table`\n--\n\n";
                // get table create info
                $stmt = $pdo->query("SHOW CREATE TABLE $table");
                $row = $stmt->fetch(\PDO::FETCH_NUM);
                $output .= $row[1] . ";\n\n";
                // get table data
                $output .= "--\n-- Dumping data for table `$table`\n--\n\n";
                $stmt = $pdo->query("SELECT * FROM $table");
                while ($row = $stmt->fetch(\PDO::FETCH_OBJ)) {
                    // runs once per table - create the INSERT INTO clause
                    if ($fields == "") {
                        $fields = "INSERT INTO `$table` (";
                        $sep = "";
                        // grab each field name
                        foreach ($row as $col => $val) {
                            $fields .= $sep . "`$col`";
                            $sep = ", ";
                        }
                        $fields .= ") VALUES";
                        $output .= $fields . "\n";
                    }
                    // grab table data
                    $sep = "";
                    $output .= $sep2 . "(";
                    foreach ($row as $col => $val) {
                        // add slashes to field content
                        $val = addslashes($val);
                        // replace stuff that needs replacing
                        $search = array("\'", "\n", "\r");
                        $replace = array("''", "\\n", "\\r");
                        $val = str_replace($search, $replace, $val);
                        $output .= $sep . "'$val'";
                        $sep = ", ";
                    }
                    // terminate row data
                    $output .= ")";
                    $sep2 = ",\n";
                }
                // terminate insert data
                $output .= ";\n";
            }
// output file to browser
            header('Content-Description: File Transfer');
            header('Content-type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . $dbname . '.sql');
            header('Content-Transfer-Encoding: binary');
            header('Content-Length: ' . strlen($output));
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Expires: 0');
            header('Pragma: public');
            echo $output;
        }
    }

    /**
     * List All User Activities(Logs)
     *
     * @Route("/log_details/{page}", name="log_details")
     */
    public function logdetailsAction(Request $request, $page)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($user == null) {
            return $this->redirectToRoute('fos_user_security_login');
        } else {
            $log_Details = $em->createQueryBuilder()
                ->select('b.createdondatetime', 'b.oldvalue', 'b.newvalue', 'b.fieldName', 'c.username')
                ->from('InitialShippingBundle:LogDetails', 'b')
                ->join('InitialShippingBundle:User', 'c', 'WITH', 'c.id = b.createdbyid')
                ->getQuery()
                ->getResult();
            $total_records = count($log_Details);
            $records_per_page = $this->container->getParameter('maxrecords_per_page');
            $last_page = ceil($total_records / $records_per_page);
            $previous_page = $page > 1 ? $page - 1 : 1;
            $next_page = $page < $last_page ? $page + 1 : $last_page;
            $listActiveRecords = $em->createQueryBuilder()
                ->select('b.createdondatetime', 'b.oldvalue', 'b.newvalue', 'b.fieldName', 'c.username', 'b.tablename')
                ->from('InitialShippingBundle:LogDetails', 'b')
                ->join('InitialShippingBundle:User', 'c', 'WITH', 'c.id = b.createdbyid')
                ->setMaxResults($records_per_page)
                ->setFirstResult(($page - 1) * $records_per_page)
                ->getQuery()
                ->getResult();
            return $this->render('InitialShippingBundle:LogDetails:log_details.html.twig',
                array('logdetails' => $listActiveRecords,
                    'last_page' => $last_page,
                    'previous_page' => $previous_page,
                    'current_page' => (int)$page,
                    'next_page' => $next_page,
                    'total_records' => $total_records
                ));

        }
    }

}



