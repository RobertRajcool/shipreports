<?php

namespace Initial\ShippingBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Initial\ShippingBundle\Form\ScorecardDataImportType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Response;
/**
 * PieChartDashboardController.
 *
 * @Route("/piechart")
 */
class PieChartDashboardController extends Controller
{
    /**
     * Lists all PieChartDashboard Elements.
     *
     * @Route("/{serialized}/listall", name="vessel_piechart")
     */
    public function indexAction($serialized)
    {
        return $this->render('InitialShippingBundle:DataVerficationRanking:backup.html.twig');
    }
}
