<?php

namespace Initial\ShippingBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class HighchartController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('', array('name' => $name));
    }
}
