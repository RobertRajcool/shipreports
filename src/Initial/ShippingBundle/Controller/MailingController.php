<?php

namespace Initial\ShippingBundle\Controller;

use Initial\ShippingBundle\Entity\EmailGroup;
use Initial\ShippingBundle\Entity\EmailUsers;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;


/**
 * MailingController.
 *
 * @Route("/mailing")
 */
class MailingController extends Controller
{

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
            ->andwhere('c.groupstatus = :groupstatus')
            ->groupby('c.groupname')
            ->setParameter('companyid',$companyid)
            ->setParameter('groupstatus',1)
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
    public function ajaxviewemailgroupAction(Request $request,$mode='')
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
       //Finding List of user for particular group starts Here...
        $groupuserid = $em->createQueryBuilder()
            ->select('c.useremailid')
            ->from('InitialShippingBundle:EmailUsers','c')
            ->where('c.groupid = :groupid')
            ->setParameter('groupid',$viewgroupid)
            ->getQuery()
            ->getResult();
        $databaseemailarray=array();
        for($k=0;$k<count($groupuserid);$k++)
        {
           array_push($databaseemailarray,$groupuserid[$k]['useremailid']);
        }
        //Finding List of user for particular group Ends Here...
        //Update Email Group Users Starts Here.....
        for($j=0;$j<count($databaseemailarray);$j++)
        {
            if (!in_array($databaseemailarray[$j], $listofemail))
            {
                $qb = $em->createQueryBuilder()
                    ->delete('InitialShippingBundle:EmailUsers', 'd')
                    ->where('d.useremailid = :useremailid')
                    ->setParameter(':useremailid', $databaseemailarray[$j])
                    ->getQuery()
                    ->getResult();
            }
        }
        for($j=0;$j<count($listofemail);$j++)
        {
            if (!in_array($listofemail[$j], $databaseemailarray))
            {
                $emailusers=new EmailUsers();
                $emailref = $em->getRepository('InitialShippingBundle:EmailGroup')->findOneBy(array('id'=>$viewgroupid));
                $emailusers->setGroupid($emailref);
                $emailusers->setUseremailid($listofemail[$j]);
                $em->persist($emailusers);
                $em->flush();
            }
        }

        //Update Email Group Users Ends Here.....
        //Update Email Group  Starts Here.....
        $entity = $em->getRepository('InitialShippingBundle:EmailGroup')->find($viewgroupid);
        $entity->setGroupname($groupname);
        $em->flush();
        //Update Email Group  Ends Here.....

        $response = new JsonResponse();
        $response->setData(array('updatemsg'=>"Group Detail Updated"));
        return $response;
    }
    /**
     * archivegroup.
     *
     * @Route("/archivegroup", name="archivegroup")
     */
    public function archivegroupAction(Request $request)
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
        $groupid = $request->request->get('groupid');

        $newcompanyid = $em->getRepository('InitialShippingBundle:CompanyDetails')->findOneBy(array('id'=>$companyid));
        $entity = $em->getRepository('InitialShippingBundle:EmailGroup')->find($groupid);
        $entity->setGroupstatus(0);
        $em->flush();


        $response = new JsonResponse();
        $response->setData(array('archivemsg'=>"Group Archive Sucessfully.."));
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
     * @Route("/ajaxgroupchange", name="ajaxgroupchange")
     * @Method("Post")
     */
    public function ajaxgroupchangeAction(Request $request)
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

        $checkboxvalue = $request->request->get('checkboxvalue');
        if(count($checkboxvalue)==2)
        {
            $listofgroup = $em->createQueryBuilder()
                ->select('c.groupname','c.id','b.useremailid')
                ->from('InitialShippingBundle:EmailGroup','c')
                ->join('InitialShippingBundle:EmailUsers','b', 'WITH', 'b.groupid = c.id')
                ->where('c.companyid = :companyid')
                ->groupby('c.groupname')
                ->setParameter('companyid',$companyid)
                ->getQuery()
                ->getResult();
        }
        if(count($checkboxvalue)==1)
        {
            $listofgroup = $em->createQueryBuilder()
                ->select('c.groupname','c.id','b.useremailid')
                ->from('InitialShippingBundle:EmailGroup','c')
                ->join('InitialShippingBundle:EmailUsers','b', 'WITH', 'b.groupid = c.id')
                ->where('c.companyid = :companyid')
                ->andwhere('c.groupstatus = :groupstatus')
                ->groupby('c.groupname')
                ->setParameter('companyid',$companyid)
                ->setParameter('groupstatus',$checkboxvalue[0])
                ->getQuery()
                ->getResult();
        }

        $response = new JsonResponse();
        $response->setData(array('countofgroup'=>count($listofgroup),'listofgroup'=>$listofgroup));
        return $response;

    }


}
