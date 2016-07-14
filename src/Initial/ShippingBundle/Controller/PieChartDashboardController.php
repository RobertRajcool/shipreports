<?php

namespace Initial\ShippingBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PieChartDashboardController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('', array('name' => $name));
    }
}
