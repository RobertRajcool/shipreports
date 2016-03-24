<?php

namespace Initial\ShippingBundle\Controller;

use Initial\ShippingBundle\Entity\EmailGroup;
use Initial\ShippingBundle\Entity\EmailUsers;
use Initial\ShippingBundle\Entity\Mailing;
use Initial\ShippingBundle\Entity\MailingGroup;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;


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
            ->select('c.groupname','c.id','b.useremailid')
            ->from('InitialShippingBundle:EmailGroup','c')
            ->join('InitialShippingBundle:EmailUsers','b', 'WITH', 'b.groupid = c.id')
            ->where('c.companyid = :companyid')
            ->groupby('c.groupname')
            ->setParameter('companyid',$companyid)
            ->getQuery()
            ->getResult();

        //Finding Company for Login user Ends Here//
        return $this->render('InitialShippingBundle:Mailing:creategroup.html.twig',
            array('listofuser'=>$listofuser,'usercount'=>count($listofuser)));
    }
    /**
     * ajaxviewemailgroup.
     *
     * @Route("/ajaxviewemailgroup", name="ajaxviewemailgroup")
     */
    public function ajaxviewemailgroupAction(Request $request)
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
        $emailgroupid = $request->request->get('emailgroupid');
        $grouobject = $em->getRepository('InitialShippingBundle:EmailGroup')->findOneBy(array('id'=>$emailgroupid));
        $groupname=$grouobject->getGroupname();
        $newcompanyid = $em->getRepository('InitialShippingBundle:CompanyDetails')->findOneBy(array('id'=>$companyid));

        $groupofemaild = $em->createQueryBuilder()
            ->select('b.useremailid')
            ->from('InitialShippingBundle:EmailGroup','c')
            ->join('InitialShippingBundle:EmailUsers','b', 'WITH', 'b.groupid = c.id')
            ->where('c.id = :id')
            ->andwhere('c.companyid = :companyid')
            ->setParameter('id',$emailgroupid)
            ->setParameter('companyid',$newcompanyid)
            ->getQuery()
            ->getResult();

        $response = new JsonResponse();
        $response->setData(array('groupofemailid' => $groupofemaild,'groupname'=>$groupname,'groupid'=>$emailgroupid));
        return $response;
    }
    /**
     * updatemailgroup.
     *
     * @Route("/updatemailgroup", name="updatemailgroup")
     */
    public function updatemailgroupAction(Request $request)
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
        $listofemail = $request->request->get('listofemail');
        $groupname = $request->request->get('groupnameintextbox');
        $viewgroupid = $request->request->get('viewgroupid');
        $newcompanyid = $em->getRepository('InitialShippingBundle:CompanyDetails')->findOneBy(array('id'=>$companyid));
        $entity = $em->getRepository('InitialShippingBundle:EmailGroup')->find($viewgroupid);
        $entity->setGroupname($groupname);
        $em->flush();

        $groupuserid = $em->createQueryBuilder()
            ->select('c.id')
            ->from('InitialShippingBundle:EmailUsers','c')
            ->where('c.groupid = :groupid')
            ->setParameter('groupid',$viewgroupid)
            ->getQuery()
            ->getResult();
        for($k=0;$k<count($groupuserid);$k++)
        {
            $userentity = $em->getRepository('InitialShippingBundle:EmailUsers')->find($groupuserid[$k]['id']);
            $userentity->setUseremailid($listofemail[$k]);
            $em->flush();
        }

        $response = new JsonResponse();
        $response->setData(array('updatemsg'=>"Group Detail Updated"));
        return $response;
    }
    /**
     * Add Account.
     *
     * @Route("/emailgroup", name="emailgroup")
     * @Method("Post")
     */
    public function emailgroupAction(Request $request)
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

        $groupname = $request->request->get('groupnameintextbox');
        $listofemail = $request->request->get('listofemail');
        $newcompanyid = $em->getRepository('InitialShippingBundle:CompanyDetails')->findOneBy(array('id'=>$companyid));
        $groupobject=new EmailGroup();
        $groupobject->setGroupname($groupname);
        $groupobject->setCompanyid($newcompanyid);
        $em->persist($groupobject);
        $em->flush();
        $lastid= $groupobject->getId();
        for($i=0;$i<count($listofemail);$i++)
        {
            $emailusers=new EmailUsers();
            $emailref = $em->getRepository('InitialShippingBundle:EmailGroup')->findOneBy(array('id'=>$lastid));
            $emailusers->setGroupid($emailref);
            $emailusers->setUseremailid($listofemail[$i]);
            $em->persist($emailusers);
            $em->flush();
        }

        //Insertion Prcess Ends Here//

        $response = new JsonResponse();
        $response->setData(array('savemsg'=>"Group Detail Saved"));
        return $response;

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

        for($i=0;$i<count($groupemailids);$i++)
        {
            $mailgroupobject=new MailingGroup();
            $emailref = $em->getRepository('InitialShippingBundle:Mailing')->findOneBy(array('id'=>$groupemailids[$i]));
            $mailgroupobject->setGroupname($groupname);
            $mailgroupobject->setEmailreferenceid($emailref);
            $em->persist($mailgroupobject);
            $em->flush();
        }

        //Insertion Prcess Ends Here//

        return $this->redirectToRoute('mailinghome');

    }
    /**
     * Lists all ReadingKpiValues entities.
     *
     * @Route("/listgroup", name="listmailgroup")
     * @Method("GET")
     */
    public function listgroupAction(/*$page*/)
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
        $newcompanyid = $em->getRepository('InitialShippingBundle:CompanyDetails')->findOneBy(array('id'=>$companyid));
        $listallgroup = $em->createQueryBuilder()
            ->select('a.id','a.emailid','a.username','b.groupname')
            ->from('InitialShippingBundle:Mailing','a')
            ->join('InitialShippingBundle:MailingGroup','b', 'WITH', 'b.emailreferenceid = a.id')
            ->where('a.companyid = :companyid')
            ->setParameter('companyid',$newcompanyid)
            ->getQuery()
            ->getResult();

        return $this->render('InitialShippingBundle:Mailing:listallgroup.html.twig', array(
            'listallgroup' => $listallgroup

        ));
    }

}
