<?php

namespace Initial\ShippingBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Initial\ShippingBundle\Entity\ShipDetails;
use Initial\ShippingBundle\Entity\ShipStatusDetails;
use Initial\ShippingBundle\Form\ShipDetailsType;
use Initial\ShippingBundle\Form\ShipStatusDetailsType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\HttpFoundation\Response;

/**
 * ShipDetails controller.
 *
 * @Route("/shipdetails", name="shipdetails_index")
 */
class ShipDetailsController extends Controller
{
    /**
     * Lists all ShipDetails entities.
     *
     * @Route("/", name="shipdetails_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $shipDetails = $em->getRepository('InitialShippingBundle:ShipDetails')->findAll();

        return $this->render('shipdetails/index.html.twig', array(
            'shipDetails' => $shipDetails,
        ));
    }

    /**
     * Finds and displays a KpiDetails entity.
     *
     * @Route("/check_vesselName", name="shipdetails_check_vessel")
     */
    public function checkVesselNameAction(Request $request)
    {
        $user = $this->getUser();
        if ($user != null) {
            $shipName = $request->request->get('shipName');
            $em = $this->getDoctrine()->getManager();

            $query = $em->createQueryBuilder()
                ->select('a.id', 'a.shipName')
                ->from('InitialShippingBundle:ShipDetails', 'a')
                ->where('a.shipName = :Ship_name')
                ->setParameter('Ship_name', $shipName)
                ->getQuery();
            $ShipDetail = $query->getResult();

            $response = new JsonResponse();
            if(count($ShipDetail)!=0) {
                $response->setData(array(
                    'vessel' => 1,
                    'status' => 1
                ));
            } else {
                $response->setData(array(
                    'vessel' => 0,
                    'status' => 1
                ));
            }
            return $response;
        } else {
            return $this->redirectToRoute('fos_user_security_login');
        }

    }


    /**
     * Finds and displays a KpiDetails entity.
     *
     * @Route("/check_imoNumber", name="shipdetails_check_imo")
     */
    public function checkImoNumberAction(Request $request)
    {
        $user = $this->getUser();
        if ($user != null) {
            $imoNumber = $request->request->get('imoNumber');
            $em = $this->getDoctrine()->getManager();

            $query = $em->createQueryBuilder()
                ->select('a.id','a.imoNumber')
                ->from('InitialShippingBundle:ShipDetails', 'a')
                ->where('a.imoNumber = :imoNumber')
                ->setParameter('imoNumber', $imoNumber)
                ->getQuery();
            $ShipDetail = $query->getResult();

            $response = new JsonResponse();
            if(count($ShipDetail)!=0) {
                $response->setData(array(
                    'imoNumber' => 1,
                    'imo_length' => 7,
                    'status' => 1
                ));
            } else {
                $response->setData(array(
                    'imoNumber' => 0,
                    'imo_length' => 7,
                    'status' => 1
                ));
            }
            return $response;
        } else {
            return $this->redirectToRoute('fos_user_security_login');
        }

    }



    /**
     * Lists all ShipDetails entities.
     *
     * @Route("/{id}/select", name="shipdetails_select")
     * @Method("GET")
     */
    public function selectAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $shipDetails = $em->getRepository('InitialShippingBundle:ShipDetails')->findByCompanyDetailsId($id);

        return $this->render('shipdetails/index.html.twig', array(
            'shipDetails' => $shipDetails,
        ));
    }


    /**
     * Lists all ShipDetails entities.
     *
     * @Route("/select1", name="shipdetails_select1")
     * @Method("GET")
     */
    public function select1Action(Request $request, $status = '')
    {
        $user = $this->getUser();
        $userId = $user->getId();
        if ($user != null) {
            $role = $this->container->get('security.context')->isGranted('ROLE_ADMIN');
            $em = $this->getDoctrine()->getManager();

            if ($this->container->get('security.context')->isGranted('ROLE_ADMIN')) {
                $query = $em->createQueryBuilder()
                    ->select('a')
                    ->from('InitialShippingBundle:ShipDetails', 'a')
                    ->leftjoin('InitialShippingBundle:CompanyDetails', 'b', 'WITH', 'b.id = a.companyDetailsId')
                    ->leftjoin('InitialShippingBundle:User', 'c', 'WITH', 'c.username = b.adminName')
                    ->where('c.id = :userId')
                    ->setParameter('userId', $userId)
                    ->getQuery();
            } else {
                $query = $em->createQueryBuilder()
                    ->select('a')
                    ->from('InitialShippingBundle:ShipDetails', 'a')
                    ->leftjoin('InitialShippingBundle:User', 'b', 'WITH', 'b.companyid = a.companyDetailsId')
                    ->where('b.id = :userId')
                    ->setParameter('userId', $userId)
                    ->getQuery();
            }
            $shipDetails = $query->getResult();
            $count = count($shipDetails);

            $shipStatus = array();
            for ($shipCount = 0; $shipCount < $count; $shipCount++) {
                $shipId = $shipDetails[$shipCount]->getId();
                $shipStatusQuery = $em->createQueryBuilder()
                    ->select('a.status')
                    ->from('InitialShippingBundle:ShipStatusDetails', 'a')
                    ->where('a.shipDetailsId = :ship_id')
                    ->setParameter('ship_id', $shipId)
                    ->groupby('a.id')
                    ->getQuery()
                    ->getResult();

                if (count($shipStatusQuery) != 0) {
                    if ((int)$shipStatusQuery[count($shipStatusQuery) - 1]['status'] == 1) {
                        array_push($shipStatus, "Active");
                    } else {
                        array_push($shipStatus, "Inactive");
                    }
                } else {
                    array_push($shipStatus, "Inactive");
                }
            }
            $shipDetail = new shipDetails();
            $form = $this->createForm(new shipDetailsType($userId, $role), $shipDetail);
            $form->handleRequest($request);

            if ($status == 'status') {
                return $shipDetail;
            }

            return $this->render('shipdetails/index.html.twig', array(
                'shipDetails' => $shipDetails,
                'shipDetail' => $shipDetail,
                'form' => $form->createView(),
                'ship_count' => $count,
                'shipStatus' => $shipStatus
            ));
        } else {
            return $this->redirectToRoute('fos_user_security_login');
        }
        
    }

    /**
     * Lists all ShipDetails entities.
     *
     * @Route("/vessel_pdf", name="shipdetails_vessel_pdf")
     * @Method("POST")
     */
    public function vesselPdfAction(Request $request, $status = '')
    {
        $user = $this->getUser();
        $userId = $user->getId();
        if ($user != null) {
            $role = $this->container->get('security.context')->isGranted('ROLE_ADMIN');
            $em = $this->getDoctrine()->getManager();

            if ($this->container->get('security.context')->isGranted('ROLE_ADMIN')) {
                $query = $em->createQueryBuilder()
                    ->select('a')
                    ->from('InitialShippingBundle:ShipDetails', 'a')
                    ->leftjoin('InitialShippingBundle:CompanyDetails', 'b', 'WITH', 'b.id = a.companyDetailsId')
                    ->leftjoin('InitialShippingBundle:User', 'c', 'WITH', 'c.username = b.adminName')
                    ->where('c.id = :userId')
                    ->setParameter('userId', $userId)
                    ->getQuery();
            } else {
                $query = $em->createQueryBuilder()
                    ->select('a')
                    ->from('InitialShippingBundle:ShipDetails', 'a')
                    ->leftjoin('InitialShippingBundle:User', 'b', 'WITH', 'b.companyid = a.companyDetailsId')
                    ->where('b.id = :userId')
                    ->setParameter('userId', $userId)
                    ->getQuery();
            }
            $shipDetails = $query->getResult();

            $pdfObject = $this->container->get('tfox.mpdfport')->getMPdf();
            $pdfObject->defaultheaderline = 0;
            $pdfObject->defaultheaderfontstyle = 'B';
            $waterMarkImage = $this->container->getParameter('kernel.root_dir') . '/../web/images/pioneer_logo_02.png';
            $pdfObject->SetWatermarkImage($waterMarkImage);
            $pdfObject->showWatermarkImage = true;

            $customerListDesign =  $this->renderView('shipdetails/show.html.twig', array(
                'shipDetails' => $shipDetails,
                'headerTitle' => 'Vessel Details'
            ));

            $pdfObject->AddPage('', 4, '', 'on');
            $pdfObject->SetFooter('|{DATE l jS F Y h:i}| Page No: {PAGENO}');
            $pdfObject->WriteHTML($customerListDesign);

            $response = new Response();
            $content = $pdfObject->Output('', 'S');
            $response->setContent($content);
            $response->headers->set('Content-Type', 'application/pdf');
            return $response;
        } else {
            return $this->redirectToRoute('fos_user_security_login');
        }

    }


    /**
     * Creates a new ShipDetails entity.
     *
     * @Route("/new", name="shipdetails_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $shipDetail = new ShipDetails();
        $form = $this->createForm('Initial\ShippingBundle\Form\ShipDetailsType', $shipDetail);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($shipDetail);
            $em->flush();

            return $this->redirectToRoute('shipdetails_show', array('id' => $shipDetail->getId()));
        }

        return $this->render('shipdetails/new.html.twig', array(
            'shipDetail' => $shipDetail,
            'form' => $form->createView(),
        ));
    }


    /**
     * Creates a new ShipDetails entity.
     *
     * @Route("/new1", name="shipdetails_new1")
     * @Method({"GET", "POST"})
     */
    public function new1Action(Request $request)
    {
        $user = $this->getUser();
        if ($user != null) {
            $params = $request->request->get('ship_details');
            $shipName = $params['shipName'];
            $shipType = $params['shipType'];
            $imoNumber = $params['imoNumber'];
            $country = $params['country'];
            $location = $params['location'];
            $description = $params['description'];
            $manufacturingYear = $params['manufacturingYear'];
            $built = $params['built'];
            $size = $params['size'];
            $gt = $params['gt'];

            $user = $this->getUser();
            $userId = $user->getId();
            $em = $this->getDoctrine()->getManager();

            if ($this->container->get('security.context')->isGranted('ROLE_ADMIN')) {
                $query = $em->createQueryBuilder()
                    ->select('a.id')
                    ->from('InitialShippingBundle:CompanyDetails', 'a')
                    ->leftjoin('InitialShippingBundle:User', 'c', 'WITH', 'c.username = a.adminName')
                    ->where('c.id = :userId')
                    ->setParameter('userId', $userId)
                    ->getQuery();
            } else {
                $query = $em->createQueryBuilder()
                    ->select('identity(a.companyid)')
                    ->from('InitialShippingBundle:User', 'a')
                    ->where('a.id = :userId')
                    ->setParameter('userId', $userId)
                    ->getQuery();
            }

            $ans = $query->getResult();
            $companyName = $ans[0]['id'];

            $course = $this->getDoctrine()->getManager()->getRepository('InitialShippingBundle:CompanyDetails')->findOneBy(array('id' => $companyName));
            $shipTypeObj = $this->getDoctrine()->getManager()->getRepository('InitialShippingBundle:ShipTypes')->findOneBy(array('id' => $shipType));
            $countryObj = $this->getDoctrine()->getManager()->getRepository('InitialShippingBundle:AppsCountries')->findOneBy(array('id' => $country));

            $shipdetails = new ShipDetails();
            $shipdetails->setCompanyDetailsId($course);
            $shipdetails->setShipName($shipName);
            $shipdetails->setShipType($shipTypeObj);
            $shipdetails->setImoNumber($imoNumber);
            $shipdetails->setCountry($countryObj);
            $shipdetails->setLocation($location);
            $shipdetails->setDescription($description);
            $shipdetails->setBuilt($built);
            $shipdetails->setSize($size);
            $shipdetails->setGt($gt);
            $shipdetails->setManufacturingYear($manufacturingYear);

            $em->persist($shipdetails);
            $em->flush();

            $ship_id = $shipdetails->getId();
            $today = date("Y-m-d H:i:s");
            $today_obj = date_create($today);

            $shipstatusdetails = new ShipStatusDetails();
            $shipstatusdetails->setShipDetailsId($this->getDoctrine()->getManager()->getRepository('InitialShippingBundle:ShipDetails')->findOneBy(array('id' => $ship_id)));
            $shipstatusdetails->setActiveDate($today_obj);
            $shipstatusdetails->setStatus(1);
            $em->persist($shipstatusdetails);
            $em->flush();

            return $this->redirectToRoute('shipdetails_select1');
        } else {
            return $this->redirectToRoute('fos_user_security_login');
        }

    }


    /**
     * Finds and displays a KpiDetails entity.
     *
     * @Route("/ajax_show", name="shipdetails_ajax_show")
     */
    public function ajax_showAction(Request $request, $hi = '')
    {
        $user = $this->getUser();
        if ($user != null) {
            $id = $request->request->get('Id');
            $em = $this->getDoctrine()->getManager();

            $query = $em->createQueryBuilder()
                ->select('a.id', 'a.shipName', 'a.description', 'a.location', 'a.size', 'a.built', 'a.gt', 'a.manufacturingYear', 'a.imoNumber')
                ->from('InitialShippingBundle:ShipDetails', 'a')
                ->where('a.id = :Ship_id')
                ->setParameter('Ship_id', $id)
                ->getQuery();
            $ShipDetail = $query->getResult();

            $shipTypeId = $em->createQueryBuilder()
                ->select('identity(a.shipType)')
                ->from('InitialShippingBundle:ShipDetails', 'a')
                ->where('a.id = :Ship_id')
                ->setParameter('Ship_id', $id)
                ->getQuery()
                ->getResult();

            $shipTypeName = $em->createQueryBuilder()
                ->select('a.shipType', 'a.id')
                ->from('InitialShippingBundle:ShipTypes', 'a')
                ->where('a.id = :Ship_id')
                ->setParameter('Ship_id', $shipTypeId[0][1])
                ->getQuery()
                ->getResult();

            $countryId = $em->createQueryBuilder()
                ->select('identity(a.country)')
                ->from('InitialShippingBundle:ShipDetails', 'a')
                ->where('a.id = :Ship_id')
                ->setParameter('Ship_id', $id)
                ->getQuery()
                ->getResult();

            $countryName = $em->createQueryBuilder()
                ->select('a.countryName', 'a.id')
                ->from('InitialShippingBundle:AppsCountries', 'a')
                ->where('a.id = :country_id')
                ->setParameter('country_id', $countryId[0][1])
                ->getQuery()
                ->getResult();

            $shipType_array = $em->createQueryBuilder()
                ->select('a.shipType', 'a.id')
                ->from('InitialShippingBundle:ShipTypes', 'a')
                ->getQuery()
                ->getResult();

            $countryName_array = $em->createQueryBuilder()
                ->select('a.countryName', 'a.id')
                ->from('InitialShippingBundle:AppsCountries', 'a')
                ->getQuery()
                ->getResult();

            $shipStatus = " ";
            $shipStatusQuery = $em->createQueryBuilder()
                ->select('a.status')
                ->from('InitialShippingBundle:ShipStatusDetails', 'a')
                ->where('a.shipDetailsId = :ship_id')
                ->setParameter('ship_id', $id)
                ->groupby('a.id')
                ->getQuery()
                ->getResult();

            if (count($shipStatusQuery) != 0) {
                if ((int)$shipStatusQuery[count($shipStatusQuery) - 1]['status'] == 1) {
                    $shipStatus = "Active";
                } else {
                    $shipStatus = "Inactive";
                }
            } else {
                $shipStatus = "Inactive";
            }

            $response = new JsonResponse();
            $response->setData(array(
                'Ship_detail' => $ShipDetail,
                'ship_type' => $shipTypeName,
                'country_name' => $countryName,
                'shipType_array' => $shipType_array,
                'countryName_array' => $countryName_array,
                'shipStatus' => $shipStatus
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
     * @Route("/ajax_edit", name="shipdetails_ajax_edit")
     */
    public function ajax_editAction(Request $request)
    {
        $user = $this->getUser();
        if ($user != null) {
            $id = $request->request->get('Id');

            $shipName = $request->request->get('shipName');
            $shipType = $request->request->get('shipType');
            $imoNumber = $request->request->get('imoNumber');
            $country = $request->request->get('country');
            $location = $request->request->get('location');
            $description = $request->request->get('description');
            $manufacturingYear = $request->request->get('manufacturingYear');
            $built = $request->request->get('built');
            $size = $request->request->get('size');
            $gt = $request->request->get('gt');
            $em = $this->getDoctrine()->getManager();

            $shipdetails = $this->getDoctrine()->getManager()->getRepository('InitialShippingBundle:ShipDetails')->findOneBy(array('id' => $id));
            $shipTypeObj = $this->getDoctrine()->getManager()->getRepository('InitialShippingBundle:ShipTypes')->findOneBy(array('id' => $shipType));
            $countryObj = $this->getDoctrine()->getManager()->getRepository('InitialShippingBundle:AppsCountries')->findOneBy(array('id' => $country));

            $entity = new ShipDetails();
            $shipdetails->setShipName($shipName);
            $shipdetails->setShipType($shipTypeObj);
            $shipdetails->setImoNumber($imoNumber);
            $shipdetails->setCountry($countryObj);
            $shipdetails->setLocation($location);
            $shipdetails->setDescription($description);
            $shipdetails->setBuilt($built);
            $shipdetails->setSize($size);
            $shipdetails->setGt($gt);
            $shipdetails->setManufacturingYear($manufacturingYear);

            $em->flush();

            $show_response = $this->ajax_showAction($request, 'hi');

            return $show_response;
        } else {
            return $this->redirectToRoute('fos_user_security_login');
        }

    }


    /**
     * Finds and displays a KpiDetails entity.
     *
     * @Route("/ajax_status", name="shipdetails_ajax_status")
     */
    public function ajax_statusAction(Request $request)
    {
        $user = $this->getUser();
        if ($user != null) {
            $id = $request->request->get('Id');
            $today = date("Y-m-d H:i:s");
            $today_obj = date_create($today);

            $em = $this->getDoctrine()->getManager();

            $status_value = $em->createQueryBuilder()
                ->select('a.status')
                ->from('InitialShippingBundle:ShipStatusDetails', 'a')
                ->where('a.shipDetailsId = :ship_id')
                ->setParameter('ship_id', $id)
                ->groupby('a.id')
                ->getQuery()
                ->getResult();
            $index = count($status_value) - 1;
            $shipStatusDetails = new ShipStatusDetails();
            if (count($status_value) != 0) {
                if ($status_value[$index]['status'] == 1) {
                    $shipStatusDetails->setShipDetailsId($this->getDoctrine()->getManager()->getRepository('InitialShippingBundle:ShipDetails')->findOneBy(array('id' => $id)));
                    $shipStatusDetails->setEndDate($today_obj);
                    $shipStatusDetails->setStatus(0);
                    $em->persist($shipStatusDetails);
                    $em->flush();
                } else {
                    $shipStatusDetails->setShipDetailsId($this->getDoctrine()->getManager()->getRepository('InitialShippingBundle:ShipDetails')->findOneBy(array('id' => $id)));
                    $shipStatusDetails->setActiveDate($today_obj);
                    $shipStatusDetails->setStatus(1);
                    $em->persist($shipStatusDetails);
                    $em->flush();
                }
            } else {
                $shipStatusDetails->setShipDetailsId($this->getDoctrine()->getManager()->getRepository('InitialShippingBundle:ShipDetails')->findOneBy(array('id' => $id)));
                $shipStatusDetails->setActiveDate($today_obj);
                $shipStatusDetails->setStatus(1);
                $em->persist($shipStatusDetails);
                $em->flush();
            }

            $lastId = $shipStatusDetails->getId();

            $statusValue = $em->createQueryBuilder()
                ->select('a.status')
                ->from('InitialShippingBundle:ShipStatusDetails', 'a')
                ->where('a.id = :status_id')
                ->setParameter('status_id', $lastId)
                ->getQuery()
                ->getResult();
            $shipNameQuery = $em->createQueryBuilder()
                ->select('a.shipName')
                ->from('InitialShippingBundle:ShipDetails', 'a')
                ->where('a.id = :ship_id')
                ->setParameter('ship_id', $id)
                ->getQuery()
                ->getResult();

            $response = new JsonResponse();
            $response->setData(array(
                'status' => $statusValue,
                'shipName' => $shipNameQuery[0]['shipName']
            ));
            return $response;
        } else {
            return $this->redirectToRoute('fos_user_security_login');
        }

    }

    /**
     * Finds and displays a KpiDetails entity.
     *
     * @Route("/ajax_status_show", name="shipdetails_ajax_status_show")
     */
    public function ajax_status_showAction(Request $request)
    {
        $user = $this->getUser();
        if ($user != null) {
            $em = $this->getDoctrine()->getManager();
            $id = $request->request->get('Id');

            $activeIndex = 0;
            $inactiveIndex = 0;
            $activeShipDetails = array();
            $activeShipType = array();
            $inactiveShipDetails = array();
            $inactiveShipType = array();

            $ship_id_value = $em->createQueryBuilder()
                ->select('identity(a.shipDetailsId)')
                ->from('InitialShippingBundle:ShipStatusDetails', 'a')
                ->where('a.status = :status')
                ->setParameter('status', 1)
                ->distinct()
                ->getQuery()
                ->getResult();
            $index = count($ship_id_value);

            for ($i = 0; $i < $index; $i++) {
                $findShipActiveQuery = $em->createQueryBuilder()
                    ->select('a.id', 'a.status')
                    ->from('InitialShippingBundle:ShipStatusDetails', 'a')
                    ->where('a.shipDetailsId = :ship_id')
                    ->setParameter('ship_id', $ship_id_value[$i][1])
                    ->orderby('a.id')
                    ->getQuery()
                    ->getResult();

                $shipCount = count($findShipActiveQuery) - 1;
                $shipStatusValue = $findShipActiveQuery[$shipCount]['status'];

                $ship_detail_query = $em->createQueryBuilder()
                    ->select('a.id', 'a.shipName', 'a.description', 'a.location', 'a.size', 'a.built', 'a.gt', 'a.manufacturingYear', 'a.imoNumber', 'identity(a.shipType)')
                    ->from('InitialShippingBundle:ShipDetails', 'a')
                    ->where('a.id = :ship_id')
                    ->setParameter('ship_id', $ship_id_value[$i][1])
                    ->getQuery()
                    ->getResult();
                $ship_details[$i] = $ship_detail_query;

                $shipType = $em->createQueryBuilder()
                    ->select('a.shipType')
                    ->from('InitialShippingBundle:ShipTypes', 'a')
                    ->where('a.id = :shipType_id')
                    ->setParameter('shipType_id', $ship_detail_query[0][1])
                    ->getQuery()
                    ->getResult();
                $ship_types[$i] = $shipType;

                if ($shipStatusValue == 1) {
                    $activeShipDetails[$activeIndex] = $ship_detail_query;
                    $activeShipType[$activeIndex] = $shipType;
                    $activeIndex++;
                } else {
                    $inactiveShipDetails[$inactiveIndex] = $ship_detail_query;
                    $inactiveShipType[$inactiveIndex] = $shipType;
                    $inactiveIndex++;
                }
            }
            $response = new JsonResponse();

            if ($id == 1) {
                $response->setData(array(
                    'ship_details' => $activeShipDetails,
                    'ship_type' => $activeShipType
                ));
            } else if ($id == 0) {
                $response->setData(array(
                    'ship_details' => $inactiveShipDetails,
                    'ship_type' => $inactiveShipType
                ));
            }

            return $response;
        } else {
            return $this->redirectToRoute('fos_user_security_login');
        }

    }


    /**
     * Finds and displays a ShipDetails entity.
     *
     * @Route("/{id}", name="shipdetails_show")
     * @Method("GET")
     */
    public function showAction(ShipDetails $shipDetail)
    {
        $deleteForm = $this->createDeleteForm($shipDetail);

        return $this->render('shipdetails/show.html.twig', array(
            'shipDetail' => $shipDetail,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing ShipDetails entity.
     *
     * @Route("/{id}/edit", name="shipdetails_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, ShipDetails $shipDetail)
    {
        $deleteForm = $this->createDeleteForm($shipDetail);
        $editForm = $this->createForm('Initial\ShippingBundle\Form\ShipDetailsType', $shipDetail);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($shipDetail);
            $em->flush();

            return $this->redirectToRoute('shipdetails_select1', array('id' => $shipDetail->getId()));
        }

        return $this->render('shipdetails/edit.html.twig', array(
            'shipDetail' => $shipDetail,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing ShipDetails entity.
     *
     * @Route("/edit", name="shipdetails_edit1")
     * @Method({"GET", "POST"})
     */
    public function edit1Action(Request $request)
    {
        $params = $request->request->get('ship_details');
        $shipName = $params['shipName'];
        $shipType = $params['shipType'];
        $imoNumber = $params['imoNumber'];
        $country = $params['country'];
        $built = $params['built'];
        $size = $params['size'];
        $gt = $params['gt'];
        $location = $params['location'];
        $description = $params['description'];
        $manufacturingYear = $params['manufacturingYear'];
        $companyDetailsId = $params['companyDetailsId'];

        $em = $this->getDoctrine()->getManager();

        $ship_id_array = $em->createQueryBuilder()
            ->select('a.id')
            ->from('InitialShippingBundle:ShipDetails', 'a')
            ->where('a.shipName = :ship_name')
            ->setParameter('ship_name', $shipName)
            ->getQuery()
            ->getResult();

        for ($j = 0; $j < count($ship_id_array); $j++) {
            $entity = $em->getRepository('InitialShippingBundle:ShipDetails')->find($ship_id_array[$j]['id']);
            $shipType_ob = $em->getRepository('InitialShippingBundle:ShipDetails')->find($shipType);
            $country_obj = $em->getRepository('InitialShippingBundle:ShipDetails')->find($country);
            $company_obj = $em->getRepository('InitialShippingBundle:ShipDetails')->find($companyDetailsId);

            $shipDetail = new ShipDetails();
            $entity->setshipName($shipName);
            $entity->setShipType($shipType_ob);
            $entity->setImoNumber($imoNumber);
            $entity->setCompanyDetailsId($company_obj);
            $entity->setCountry($country_obj);
            $entity->setDescription($description);
            $entity->setManufacturingYear($manufacturingYear);
            $entity->setBuilt($built);
            $entity->setSize($size);
            $entity->setGt($gt);
            $entity->setLocation($location);
            $em->flush();
        }

        return $this->redirectToRoute('shipdetails_select1');
    }

    /**
     * Deletes a ShipDetails entity.
     *
     * @Route("/{id}", name="shipdetails_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, ShipDetails $shipDetail)
    {
        $form = $this->createDeleteForm($shipDetail);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($shipDetail);
            $em->flush();
        }

        return $this->redirectToRoute('shipdetails_select1');
    }

    /**
     * Creates a form to delete a ShipDetails entity.
     *
     * @param ShipDetails $shipDetail The ShipDetails entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(ShipDetails $shipDetail)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('shipdetails_delete', array('id' => $shipDetail->getId())))
            ->setMethod('DELETE')
            ->getForm();
    }
}