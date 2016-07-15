<?php

namespace Initial\ShippingBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Initial\ShippingBundle\Entity\ArchivedReport;

/**
 * ArchivedReport controller.
 *
 * @Route("/archivedreport")
 */
class ArchivedReportController extends Controller
{
    /**
     * Lists all ArchivedReport entities.
     *
     * @Route("/", name="archivedreport_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $user = $this->getUser();
        if ($user != null) {
            $em = $this->getDoctrine()->getManager();
            $userId = $user->getId();
            $userName = $user->getUsername();
            $role = $user->getRoles();
            $listAllShipForCompany = " ";
            if ($role[0] != 'ROLE_KPI_INFO_PROVIDER') {
                if ($this->container->get('security.context')->isGranted('ROLE_ADMIN')) {
                    $query = $em->createQueryBuilder()
                        ->select('a.shipName', 'a.id', 'a.manufacturingYear')
                        ->from('InitialShippingBundle:ShipDetails', 'a')
                        ->join('InitialShippingBundle:CompanyDetails', 'b', 'WITH', 'b.id = a.companyDetailsId')
                        ->where('b.adminName = :username')
                        ->setParameter('username', $userName)
                        ->getQuery();
                } else {
                    $query = $em->createQueryBuilder()
                        ->select('a.shipName', 'a.id', 'a.manufacturingYear')
                        ->from('InitialShippingBundle:ShipDetails', 'a')
                        ->leftjoin('InitialShippingBundle:User', 'b', 'WITH', 'b.companyid = a.companyDetailsId')
                        ->where('b.id = :userId')
                        ->setParameter('userId', $userId)
                        ->getQuery();
                }
                $listAllShipForCompany = $query->getResult();
            }
            return $this->render('archivedreport/index.html.twig', array(
                'vesselList' => $listAllShipForCompany,
            ));
        } else {
            return $this->redirectToRoute('fos_user_security_login');
        }
    }

    /**
     * Finds and displays a ArchivedReport entity.
     *
     * @Route("/{id}", name="archivedreport_show")
     * @Method("GET")
     */
    public function showAction(ArchivedReport $archivedReport)
    {

        return $this->render('archivedreport/show.html.twig', array(
            'archivedReport' => $archivedReport,
        ));
    }
}
