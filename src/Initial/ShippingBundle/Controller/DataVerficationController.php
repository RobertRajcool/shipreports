<?php

namespace Initial\ShippingBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Initial\ShippingBundle\Entity\ReadingKpiValues;
use Symfony\Component\HttpFoundation\Session\Session;


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
     * @Route("/add_data", name="adddata")
     */
    public function findnumofshipsAction($mode='')
    {
        $em = $this->getDoctrine()->getManager();
        //Finding Company for Login user Starts Here//
        $user = $this->getUser();
        if($user==null)
        {
            return $this->redirectToRoute('fos_user_security_login');
        }
        else
        {
            $userId = $user->getId();
            $username = $user->getUsername();
            if($this->container->get('security.context')->isGranted('ROLE_ADMIN'))
            {
                $query = $em->createQueryBuilder()
                    ->select('a.shipName','a.id')
                    ->from('InitialShippingBundle:ShipDetails','a')
                    ->join('InitialShippingBundle:CompanyDetails','b', 'WITH', 'b.id = a.companyDetailsId')
                    ->where('b.adminName = :username')
                    ->setParameter('username',$username)
                    ->getQuery();
            }
            else
            {
                $query = $em->createQueryBuilder()
                    ->select('a.shipName','a.id')
                    ->from('InitialShippingBundle:ShipDetails','a')
                    ->leftjoin('InitialShippingBundle:User','b', 'WITH', 'b.companyid = a.companyDetailsId')
                    ->where('b.id = :userId')
                    ->setParameter('userId',$userId)
                    ->getQuery();
            }


            $listallshipforcompany = $query->getResult();
            if($mode=='listships')
            {
                return $listallshipforcompany;
            }

            //Finding Company for Login user Ends Here//
            return $this->render('InitialShippingBundle:DataVerficationScoreCorad:home.html.twig',
                array('listofships'=>$listallshipforcompany,'shipcount'=>count($listallshipforcompany)));
        }

        }
    /**
     * Element and Kpi for Particular Ships.
     *
     * @Route("/{shipid}/{monthdetail}/shipskpielment", name="shipskpielment")
     */
    public function shipskpielmentAction(Request $request,$shipid,$monthdetail,$mode='')
    {
        $session = new Session();
        $em=$this->getDoctrine()->getManager();
        $mydate='01-'.$monthdetail;
        $time = strtotime($mydate);
        $newformat = date('Y-m-d',$time);
        $new_date=new \DateTime($newformat);
        $new_date->modify('last day of this month');
        $resularray = $em->createQueryBuilder()
            ->select('b.value')
            ->from('InitialShippingBundle:ReadingKpiValues', 'b')
            ->where('b.shipDetailsId = :shipdetailsid')
            ->andWhere('b.monthdetail =:dataofmonth')
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
        $maxelementcount=0;

        $returnarray=array();
        $sessionkpielementid=array();
        $k=0;
        for($i=0;$i<count($ids);$i++)
        {
            $kpiid=$ids[$i]['id'];
            $kpiname=$ids[$i]['kpiName'];

            $query = $em->createQueryBuilder()
                ->select('b.elementName', 'b.id')
                ->from('InitialShippingBundle:ElementDetails', 'b')
                ->where('b.kpiDetailsId = :kpidetailsid')
                ->setParameter('kpidetailsid', $kpiid)
                ->add('orderBy', 'b.id  ASC ')
                ->getQuery();
            $elementids = $query->getResult();
            if(count($elementids)==0)
            {
                $query1 = $em->createQueryBuilder()
                    ->select('b.kpiName', 'b.id')
                    ->from('InitialShippingBundle:KpiDetails', 'b')
                    ->where('b.kpiName = :kpiName')
                    ->setParameter('kpiName', $kpiname)
                    ->add('orderBy', 'b.id  ASC ')
                    ->groupby('b.kpiName')
                    ->getQuery();

                $ids1 = $query1->getResult();
                $newkpiid=$ids1[0]['id'];
                $newkpiname=$ids1[0]['kpiName'];
                $query = $em->createQueryBuilder()
                    ->select('b.elementName', 'b.id')
                    ->from('InitialShippingBundle:ElementDetails', 'b')
                    ->where('b.kpiDetailsId = :kpidetailsid')
                    ->setParameter('kpidetailsid', $newkpiid)
                    ->add('orderBy', 'b.id  ASC ')
                    ->getQuery();
                $elementids = $query->getResult();
                if($maxelementcount<count($elementids))
                {
                    $maxelementcount=count($elementids);
                }
                for($j=0;$j<count($elementids);$j++)
                {
                    $sessionkpielementid[$newkpiid][$j] = $elementids[$j]['id'];
                    $returnarray[$newkpiname][$j] = $elementids[$j]['elementName'];


                }
            }
            else
            {
                if($maxelementcount<count($elementids))
                {
                    $maxelementcount=count($elementids);
                }

                for($j=0;$j<count($elementids);$j++)
                {
                    $sessionkpielementid[$kpiid][$j] = $elementids[$j]['id'];
                    $returnarray[$kpiname][$j] = $elementids[$j]['elementName'];

                }
            }




        }
        $elementvalues=array();
        for($kkk=0;$kkk<count($resularray);$kkk++)
        {
            array_push($elementvalues,$resularray[$kkk]['value']);
        }
        if($mode=='listkpielement')
        {
            return array('returnarray'=>$returnarray,'elementcount'=>$maxelementcount,'elementvalues'=>$elementvalues);
        }

        $session->remove('sessionkpielementid');
        $session->set('sessionkpielementid', $sessionkpielementid);
        $response = new JsonResponse();
        $response->setData(array('kpiNameArray' => $returnarray,'elementcount'=>$maxelementcount,'elementvalues'=>$elementvalues));
        return $response;
    }
    /**
     * Adding Kpi Values.
     *
     * @Route("/{buttonid}/addkpivalues", name="addkpivaluesname")
     * @Method("Post")
     */
    public function addkpivaluesAction(Request $request,$buttonid)
    {
        $session = new Session();
        $kpiandelementids= $session->get('sessionkpielementid');
        $shipid = $request->request->get('shipid');
        $elementvalues=$request->request->get('newelemetvalues');
        $dataofmonth = $request->request->get('dataofmonth');
        $mydate='01-'.$dataofmonth;
        $time = strtotime($mydate);
        $newformat = date('Y-m-d',$time);
        $em = $this->getDoctrine()->getManager();
        $new_date=new \DateTime($newformat);
        $new_date->modify('last day of this month');
        $k=0;
        $returnmsg='';
        $newshipid = $em->getRepository('InitialShippingBundle:ShipDetails')->findOneBy(array('id'=>$shipid));
        if($buttonid=='updatebuttonid')
        {

            $returnarrayids = $em->createQueryBuilder()
                ->select('b.id')
                ->from('InitialShippingBundle:ReadingKpiValues', 'b')
                ->where('b.shipDetailsId = :shipdetailsid')
                ->andWhere('b.monthdetail =:dataofmonth')
                ->setParameter('shipdetailsid', $shipid)
                ->setParameter('dataofmonth', $new_date)
                ->getQuery()
                ->getResult();
            for($kkk=0;$kkk<count($returnarrayids);$kkk++)
            {
                $entityobject = $em->getRepository('InitialShippingBundle:ReadingKpiValues')->find($returnarrayids[$kkk]['id']);
                $entityobject->setValue($elementvalues[$kkk]);
                //$entityobject->setFilename($pdffilenamearray[0].'.pdf');
                $em->flush();
                $returnmsg=' Data Updated...';
            }


        }
        if($buttonid=='savebuttonid')
        {
        foreach($kpiandelementids as $kpikey => $kpipvalue)
        {


            $newkpiid = $em->getRepository('InitialShippingBundle:KpiDetails')->findOneBy(array('id'=>$kpikey));
            foreach($kpipvalue as $elementkey => $elementvalue)
            {
                $newelementid = $em->getRepository('InitialShippingBundle:ElementDetails')->findOneBy(array('id'=>$elementvalue));

                $readingkpivalue=new ReadingKpiValues();
                $readingkpivalue->setKpiDetailsId($newkpiid);
                $readingkpivalue->setElementDetailsId($newelementid);
                $readingkpivalue->setShipDetailsId($newshipid);
                $readingkpivalue->setMonthdetail($new_date);
                $readingkpivalue->setValue($elementvalues[$k]);
                $em->persist($readingkpivalue);
                $em->flush();
                $k++;

            }
        }
            $returnmsg=' Data Saved...';
        }

        $session->remove('sessionkpielementid');
        $shipname=$newshipid->getShipName();
        $listships = $this->findnumofshipsAction('listships');
        $nextshipid=0;
        $nextshipname='';
        for($m=0;$m<count($listships);$m++)
        {
         if($listships[$m]['id']==$shipid)
         {
             $nextshipid=$listships[$m+1]['id'];
             break;
         }
        }


        if($nextshipid!=0)
        {
            $kpielementarray= $this->shipskpielmentAction($request,$nextshipid,$dataofmonth,'listkpielement');
            $newnextshipid = $em->getRepository('InitialShippingBundle:ShipDetails')->findOneBy(array('id'=>$nextshipid));
            $nextshipname=$newnextshipid->getShipName();
        }

        $response = new JsonResponse();
        $response->setData(array('returnmsg'=>$shipname.$returnmsg,'shipname'=>$nextshipname,'shipid'=>$nextshipid,
            'kpiNameArray' => $kpielementarray['returnarray'],'elementcount'=>$kpielementarray['elementcount'],'elementvalues'=>$kpielementarray['elementvalues']));
        return $response;

    }


}
