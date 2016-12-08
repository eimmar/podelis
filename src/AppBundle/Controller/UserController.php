<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\EditUserType;
use AppBundle\Repository\NotificationRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class UserController extends Controller
{
    /**
     * @Security("has_role('ROLE_USER')")
     * @Route("/profile/edit", name="keistiDuomenis")
     */
    public function userEditAction(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(EditUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setUpdatedAt(new \DateTime('now'));
            $em = $this->getDoctrine()->getManager();

            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'Duomenys sėkmingai pakeisti!');
        }
        return $this->render('@App/User/editUser.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/profile", name="user")
     * @Security("has_role('ROLE_USER')")
     */
    public function userAction(Request $request)
    {
        return $this->render('@App/User/user.html.twig', []);
    }

    /**
     * @Route("/my-messages/{page}", name="messages")
     * @Security("has_role('ROLE_USER')")
     */
    public function messageListAction($page = 1)
    {
        $repository     = $this->getDoctrine()->getRepository('AppBundle:Notification');
        $notifications  = $repository->getNotifications($page, $this->getUser()->getId());
        $maxPages       = ceil($notifications->count() / NotificationRepository::MAX_RESULTS);

        if ($page > $maxPages) {
            return $this->render('@App/Home/404.html.twig');
        }
        return $this->render('@App/User/messages.html.twig', [
            'thisPage'      => $page,
            'maxPages'      => $maxPages,
            'notifications' => $notifications,
            'limit'         => NotificationRepository::MAX_RESULTS
        ]);
    }

    /**
     * @Route("/my-messages/message/{id}-{slug}", name="viewMessage")
     * @Security("has_role('ROLE_USER')")
     */
    public function messageAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $message = $em->getRepository('AppBundle:Notification')->findOneBy([
            'id'    => $id,
            'user'  => $this->getUser()->getId()
        ]);

        if ($message) {
            $message->setSeen(true);
            $em->persist($message);
            $em->flush();

            return $this->render('@App/User/viewMessage.html.twig', ['message' => $message]);
        }
        return $this->render('AppBundle:Home:404.html.twig');
    }

    /**
     * @Route("/api/user/data", name="user-data")
     */
    public function userDataAction(Request $request)
    {
        $securityContext = $this->get('security.authorization_checker');
        /** @var User $user */
        $user = $this->getUser();

        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY') ||
            $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')
        ) {
            $repository = $this->getDoctrine()->getRepository('AppBundle:Test');

            //$cat = $repository->bestResultFromEachCategory($user);

            $res=["100","10","20","30","40","50","60","70","80"];
            $data = "var data = google.visualization.arrayToDataTable([
                            ['Categories', 'Results', { role: 'style' }, { role: 'annotation' } ],
                            ['', $res[0], 'color: gray', 'PHP Basics'],
                            ['', $res[1], 'color: #76A7FA', 'Functions and Arrays'],
                            ['', $res[2], '', 'OOP'],
                            ['', $res[3], 'stroke-color: #703593; fill-color: #C5A5CF', 'Security'],
                            ['', $res[4], 'stroke-color: #871B47; fill-color: #BC5679; ', 'Data format and Types'],
                            ['', $res[5], 'gold', 'String and Patterns'],
                            ['', $res[6], 'color: #76A7FA', 'Database and SQL'],
                            ['', $res[7], 'silver', 'Web features'],
                            ['', $res[8], 'stroke-color: #703593; fill-color: #C5A5CF', 'INPUT and OUTPUT'],
                        ]);";
            return new JsonResponse(json_encode(['data' => $data]));
        } else {
            return $this->redirectToRoute('homepage');
        }
    }

    /**
     * @Route("/api/user/category/{id}", name="category-data")
     */
    public function userCategoryResultsAction($id)
    {
        $securityContext    = $this->get('security.authorization_checker');
        /** @var User $user */
        $user               = $this->getUser();

        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY') ||
            $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')
        ) {
            $repository = $this->getDoctrine()->getRepository('AppBundle:Test');
            $cat        = $repository->categoryResults($user, $id);
            $data       = ["100", "10", "20", "30", "40", "50", "60", "70", "80"];
            return new JsonResponse(json_encode(['data' => $data, 'id' => $id, 'cat' => $cat]));
        } else {
            return $this->redirectToRoute('homepage');
        }
    }
}
