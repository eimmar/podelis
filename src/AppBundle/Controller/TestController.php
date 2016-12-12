<?php
/**
 * Created by PhpStorm.
 * User: eimantas
 * Date: 16.11.17
 * Time: 22.27
 */

namespace AppBundle\Controller;

use AppBundle\Entity\Answer;
use AppBundle\Entity\Question;
use AppBundle\Entity\QuestionReport;
use AppBundle\Form\QuestionReportType;
use AppBundle\Form\QuestionType;
use AppBundle\Form\TestQuestionType;
use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

class TestController extends Controller
{
    /**
     * @Route("/quick-test", name="quick_test")
     */
    public function quickTestAction()
    {
        $questions = $this->getDoctrine()->getRepository('AppBundle:Question')->getRandomQuestions(10);

        if (!$questions->isEmpty()) {
            $this->get('app.test_starter')->startTest($questions, '+2 minute,', false);
            return $this->redirectToRoute('question',
                [
                    'id'    => $questions->first()->getId(),
                    'slug'  => $questions->first()->slugify()
                ]);
        }
        return $this->render('@App/Home/404.html.twig');
    }

    /**
     * @Route("/categories", name="test_categories")
     */
    public function categoryListAction()
    {
        $categories = $this->getDoctrine()->getRepository('AppBundle:Book')->findAll();
        return $this->render('@App/TestPages/categories.html.twig', ['categories' => $categories]);
    }

    /**
     * @Route("/category-test/{id}-{slug}", name="categoryTest")
     */
    public function categoryTestAction($id)
    {
        /** @var ArrayCollection $questions */
        $questions = $this->getDoctrine()->getRepository('AppBundle:Question')->getCategoryQuestions($id);

        if (!$questions->isEmpty()) {
            $this->get('app.test_starter')->startTest($questions, '+1 minute,', true);
            return $this->redirectToRoute('question',
                [
                    'id'    => $questions->first()->getId(),
                    'slug'  => $questions->first()->slugify()
                ]);
        }
        return $this->render('@App/Home/404.html.twig');
    }

    /**
     * @Route("/question/{id}/{slug}", name="question")
     *
     */
    public function testAction(Request $request, $id, $slug)
    {

        $repository = $this->getDoctrine()->getRepository('AppBundle:Question');
        $testControl = $this->get('app.test_control');
        $question = $repository->findOneBy(['id' => $id]);
        $session = $this->get('session');

        if ($question && $testControl->questionInTest($question)) {
            if ($session->get('endsAt') <= new \DateTime('now')) {
                return $this->forward('testResults', [
                    'id' => $testControl->getQuestions()->first()->getId(),
                    'slug' => $testControl->getQuestions()->first()->slugify()
                ]);
            }
            $form = $this->createForm(TestQuestionType::class, [
                'question' => $question, 'answered' => $session->get('answered')
            ]);
            $form->handleRequest($request);

            if ($form->get('next')->isClicked()) {
                $testControl->addAnswer($id, $form['answers']->getData());
                return $this->forward('question', [
                    'id' => $testControl->getNext($question)->getId(),
                    'slug' => $testControl->getNext($question)->slugify()
                ]);
            }

            if ($form->get('previous')->isClicked()) {
                $testControl->addAnswer($id, $form['answers']->getData());
                return $this->forward('question', [
                    'id' => $testControl->getNext($question)->getId(),
                    'slug' => $testControl->getNext($question)->slugify()
                ]);
            }

            if ($form->get('submit')->isClicked()) {
                $testControl->addAnswer($id, $form['answers']->getData());

                return $this->redirectToRoute('testResults', [
                    'id' => $testControl->getQuestions()->first()->getId(),
                    'slug' => $testControl->getQuestions()->first()->slugify()
                ]);
            }

            return $this->render('@App/TestPages/question.html.twig', [
                'form' => $form->createView(),
                'current' => $question,
                'index' => $testControl->getCurrentIndex($question),
                'solved' => $testControl->isQuestionSolved($id)
            ]);
        }
        return $this->render('@App/Home/404.html.twig');
    }

    /**
     * @Route("/add-answer", name="questionChosen")
     */
    public function questionChosenAction(Request $request)
    {
        $session = $this->get('session');

        if ($request->isXmlHttpRequest() && $session->get('endsAt') >= new \DateTime()) {
            $repository = $this->getDoctrine()->getRepository('AppBundle:Answer');
            $questionId = $request->request->get('question');
            $answerIds = $request->request->get('answer');

            $answers = $repository->getAllChecked($answerIds);
            $this->get('app.test_control')->addAnswer($questionId, $answers);
        }
        return new Response();
    }

    /**
     * @Route("/solve-it", name="solveIt")
     */
    public function solveItAction(Request $request)
    {
        $session = $this->get('session');

        if ($request->isXmlHttpRequest() && $session->get('endsAt') >= new \DateTime()) {
            $id = $request->request->get('question');
            $answers = $this->getDoctrine()->getRepository('AppBundle:Answer')->getCorrectAnswers($id);
            /** @var Question $question */
            $question = $this->getDoctrine()->getRepository('AppBundle:Question')->findOneBy(['id' => $id]);
            $solved = $session->get('solved');
            $solved[$id] = true;
            $session->set('solved', $solved);

            return new JsonResponse(json_encode(['answers' => $answers, 'explanation' => $question->getExplanation()]));
        }
        return new Response();
    }

    /**
     * @Route("/results/{id}", name="testResults")
     */
    public function testResultsAction(Request $request, $id)
    {
        $session = $this->get('session');
        $testControl = $this->get('app.test_control');
        $testControl->checkAnswers();

        $repository = $this->getDoctrine()->getRepository('AppBundle:Question');
        $question = $repository->findOneBy(['id' => $id]);

        if ($question && $testControl->questionInTest($id)) {
            $form = $this->createForm(TestQuestionType::class, ['question' => $question,
                    'answered' => $session->get('answered')
                ]);

            $form->handleRequest($request);

            if ($form->get('next')->isClicked()) {
                return $this->forward('testResults', ['id' => $testControl->getNext($id)]);
            }

            if ($form->get('previous')->isClicked()) {
                return $this->forward('testResults', ['id' => $testControl->getPrevious($id)]);
            }

            if ($form->get('submit')->isClicked()) {
                $session->clear();
                return $this->redirectToRoute('homepage');
            }
            return $this->render('@App/TestPages/results.html.twig', [
                'form' => $form->createView(),
                'current' => $question,
                'index' => $testControl->getCurrentIndex($id),
                'solved' => $testControl->isQuestionSolved($id)
            ]);
        }
        return $this->render('@App/Home/404.html.twig');
    }

    /**
     * @Route("/report-submit", name="report")
     */
    public function questionReportAction(Request $request, $allow = false)
    {
        $report = new QuestionReport();
        $form = $this->createForm(QuestionReportType::class, $report);

        if (!$request->isXmlHttpRequest() && !$allow) {
            return new Response();
        }

        if ($request->isXmlHttpRequest() && $request->isMethod('POST')) {
            $form->handleRequest($request);
            $response = new JsonResponse();

            if ($form->isSubmitted() && $form->isValid()) {
                $question = $this->getDoctrine()
                    ->getRepository('AppBundle:Question')
                    ->find($request->request->get('questionId'));

                $report->setCreatedBy($this->getUser())
                    ->setQuestion($question)
                    ->setCreatedAt(new \DateTime())
                    ->setUpdatedAt(new \DateTime());

                $em = $this->getDoctrine()->getManager();
                $em->persist($report);
                $em->flush();

                $response->setStatusCode(200, 'success');
            } else {
                $response->setStatusCode(400, 'error');
            }
            return $response;
        }
        return $this->render('@App/TestPages/reportQuestion.html.twig', ['report' => $form->createView()]);
    }
}
