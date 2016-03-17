<?php

namespace Initial\ShippingBundle\Controller;

use Initial\ShippingBundle\Entity\Excel_file_details;
use Initial\ShippingBundle\Entity\Mailing;
use Initial\ShippingBundle\Entity\MailingGroup;
use Initial\ShippingBundle\Entity\ShipDetails;
use Initial\ShippingBundle\Entity\KpiRules;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Ob\HighchartsBundle\Highcharts\Highchart;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * DashboradController.
 *
 * @Route("/mailing")
 */
class MailingController extends Controller
{
    /**
     * Mailing Home.
     *
     * @Route("/", name="mailinghome")
     */
    public function indexAction()
    {
        return $this->render('InitialShippingBundle:Mailing:home.html.twig');
    }
    /**
     * createaccount.
     *
     * @Route("/createaccount", name="createaccount")
     */
    public function createaccountAction()
    {
        return $this->render('InitialShippingBundle:Mailing:createaccount.html.twig');
    }
    /**
     * creategroup.
     *
     * @Route("/creategroup", name="creategroup")
     */
    public function creatgroupAction()
    {
        $em = $this->getDoctrine()->getManager();
        //Finding Company for Login user Starts Here//
        $user = $this->getUser();
        $userId = $user->getId();
        $username = $user->getUsername();

        if($this->container->get('security.context')->isGranted('ROLE_ADMIN'))
        {
            $query = $em->createQueryBuilder()
                ->select('a.id')
                ->from('InitialShippingBundle:CompanyDetails','a')
                ->join('InitialShippingBundle:User','b', 'WITH', 'b.username = a.adminName')
                ->where('b.username = :username')
                ->setParameter('username',$username)
                ->getQuery();
        }
        else
        {
            $query = $em->createQueryBuilder()
                ->select('a.companyid')
                ->from('InitialShippingBundle:User','a')
                ->where('a.id = :userId')
                ->setParameter('userId',$userId)
                ->getQuery();
        }
        $companyid=$query->getSingleScalarResult();
        $listofuser = $em->createQueryBuilder()
            ->select('c.id','c.username','c.emailid')
            ->from('InitialShippingBundle:Mailing','c')
            ->where('c.companyid = :companyid')
            ->setParameter('companyid',$companyid)
            ->getQuery()
            ->getResult();

        //Finding Company for Login user Ends Here//
        return $this->render('InitialShippingBundle:Mailing:creategroup.html.twig',array('listofuser'=>$listofuser));
    }
    /**
     * Add Account.
     *
     * @Route("/readingaccount", name="readinginaccount")
     * @Method("Post")
     */
    public function readingaccountAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        //Finding Company for Login user Starts Here//
        $user = $this->getUser();
        $userId = $user->getId();
        $username = $user->getUsername();

        if($this->container->get('security.context')->isGranted('ROLE_ADMIN'))
        {
            $query = $em->createQueryBuilder()
                ->select('a.id')
                ->from('InitialShippingBundle:CompanyDetails','a')
                ->join('InitialShippingBundle:User','b', 'WITH', 'b.username = a.adminName')
                ->where('b.username = :username')
                ->setParameter('username',$username)
                ->getQuery();
        }
        else
        {
            $query = $em->createQueryBuilder()
                ->select('a.companyid')
                ->from('InitialShippingBundle:User','a')
                ->where('a.id = :userId')
                ->setParameter('userId',$userId)
                ->getQuery();
        }
        $companyid=$query->getSingleScalarResult();
        //Finding Company for Login user Ends Here//
        //Insertion Prcess Starts Here//
        $mailingvalues = $request->request->get('mailing');
        $clinetusername = $mailingvalues['username'];
        $emailid = $mailingvalues['emailid'];
        $newcompanyid = $em->getRepository('InitialShippingBundle:CompanyDetails')->findOneBy(array('id'=>$companyid));
        $mailobject=new Mailing();
        $mailobject->setCompanyid($newcompanyid);
        $mailobject->setEmailid($emailid);
        $mailobject->setUsername($clinetusername);
        $em->persist($mailobject);
        $em->flush();
        //Insertion Prcess Ends Here//

        return $this->redirectToRoute('mailinghome');

    }
    /**
     * Add Account.
     *
     * @Route("/readgroup", name="readgroup")
     * @Method("Post")
     */
    public function readgroupAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        //Finding Company for Login user Starts Here//
        $user = $this->getUser();
        $userId = $user->getId();
        $username = $user->getUsername();

        if($this->container->get('security.context')->isGranted('ROLE_ADMIN'))
        {
            $query = $em->createQueryBuilder()
                ->select('a.id')
                ->from('InitialShippingBundle:CompanyDetails','a')
                ->join('InitialShippingBundle:User','b', 'WITH', 'b.username = a.adminName')
                ->where('b.username = :username')
                ->setParameter('username',$username)
                ->getQuery();
        }
        else
        {
            $query = $em->createQueryBuilder()
                ->select('a.companyid')
                ->from('InitialShippingBundle:User','a')
                ->where('a.id = :userId')
                ->setParameter('userId',$userId)
                ->getQuery();
        }
        $companyid=$query->getSingleScalarResult();
        //Finding Company for Login user Ends Here//
        //Insertion Prcess Starts Here//
        $mailing_group = $request->request->get('mailing_group');
        $groupname = $mailing_group['groupname'];
        $groupemailids = $request->request->get('emailreferenceid');
        $mailgroupobject=new MailingGroup();
        for($i=0;$i<count($groupemailids);$i++)
        {
            $emailref = $em->getRepository('InitialShippingBundle:MailingGroup')->findOneBy(array('id'=>$groupemailids[$i]));
            $mailgroupobject->setGroupname($groupname);
            $mailgroupobject->setEmailreferenceid($emailref);
            $em->persist($mailgroupobject);
            $em->flush();
        }

        //Insertion Prcess Ends Here//

        return $this->redirectToRoute('mailinghome');

    }
}
