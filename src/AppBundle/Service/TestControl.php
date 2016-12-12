<?php
/**
 * Created by PhpStorm.
 * User: eimantas
 * Date: 16.11.3
 * Time: 16.43
 */

namespace AppBundle\Service;

use AppBundle\Entity\Answer;
use AppBundle\Entity\Book;
use AppBundle\Entity\Question;
use AppBundle\Entity\Test;
use AppBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class TestControl
{
    private $questions;

    private $session;

    private $security;

    private $em;

    private $answers;

    /** @param Session $session
     * @param EntityManager $em
     * @param TokenStorage $security
     */
    public function __construct($session, $security, $em)
    {
        $this->session = $session;
        $this->security = $security;
        $this->answers = $session->get('answered');
        $this->em = $em;
        $this->questions = $session->get('questions');
    }


    public function getNext(Question $currentQ)
    {
        $index = $this->getQuestions()->indexOf($currentQ) + 1;
        return $this->getQuestions()->get($index) ? $this->getQuestions()->get($index) : $this->getQuestions()->first();
    }

    public function getPrevious(Question $currentQ)
    {
        $index = $this->getQuestions()->indexOf($currentQ) - 1;
        return $this->getQuestions()->get($index) ? $this->getQuestions()->get($index) : $this->getQuestions()->last();
    }

    public function questionInTest(Question $question)
    {
        return $this->getQuestions()->contains($question);
    }

    /**
     * @param int $questionId
     * @param Answer $answer
     */
    public function addAnswer($questionId, $answer)
    {
        if ($this->session->get('endsAt') >= new \DateTime()) {
            /** @var ArrayCollection $answered */
            $answered = $this->session->get('answered');
            $answered->set($questionId, $answer);
            $this->session->set('answered', $answered);
        }
    }

    public function getCurrentIndex(Question $currentQ)
    {
        return $this->getQuestions()->indexOf($currentQ);
    }

    public function arrayEqual($a, $b) {
        return (
            is_array($a) && is_array($b) &&
            count($a) == count($b) &&
            array_diff($a, $b) === array_diff($b, $a)
        );
    }

    public function checkAnswers()
    {
            $ended = new \DateTime();
            if ($this->session->get('endsAt') <= $ended) {
                $ended = $this->session->get('endsAt');
            }
            $started = $this->session->get('started');
            $this->session->set('timeSpent', date_diff($ended, $started));
            $this->session->set('endsAt', new \DateTime());

//            foreach ($this->questions as $question) {
//                $correctAns = $this->em->getRepository('AppBundle:Answer')
//                    ->findBy(['question' => $question, 'correct' => true]);
//                $pickedAnswers = (array_key_exists($question, $this->answers) ? $this->answers[$question] : null);
//                if (!is_array($pickedAnswers)) {
//                    $answer = $pickedAnswers;
//                    $pickedAnswers = [$answer];
//                }
//                $isCorrect = $this->session->get('isCorrect');
//                if ($this->array_equal($correctAns, $pickedAnswers) && !$this->isQuestionSolved($question)) {
//                    $isCorrect[$question] = true;
//                    $this->session->set('isCorrect', $isCorrect);
//                } else {
//                    $isCorrect[$question] = false;
//                    $this->session->set('isCorrect', $isCorrect);
//                }
//            }
            $this->getQuestions()->filter(function (Question $question) {
                $isCorrect = $this->session->get('isCorrect');
                $isCorrect->set($question->getId(), $this->isQuestionCorrect($question));
            });
            /** @var User $user */
            $user = $this->security->getToken()->getUser();
            if ($user != 'anon.' && $this->session->get('trackResults')) {
                /** @var Book $book */
                $book = $this->getQuestions()->first()->getBook();
                $test = new Test($user, $this->session->get('timeSpent'), $this->session->get('isCorrect'), $book);
                $user->updateStats($this->session->get('timeSpent'), $this->session->get('isCorrect'));

                $this->em->persist($test);
                $this->em->persist($user);
                $this->em->flush();
            }
    }

    /**
     * @param ArrayCollection $answered
     * @param Question $question
     * @return ArrayCollection|Answer|null
     */
    public function prepareSelectedOptions($answered, $question)
    {
      return $checkedAnswers = $answered->get($question->getId()) ? $answered->get($question->getId()) : null;

//        if ($checkedAnswers == null) {
//            return $checkedAnswers;
//        }
//        if (is_array($checkedAnswers)) {
//            foreach ($checkedAnswers as $key => $answer) {
//                $checkedAnswers[$key] = $this->em->merge($answer);
//            }
//            if ($question->getCheckboxAnswers()) {
//                return $checkedAnswers;
//            }
//            return count($checkedAnswers) > 1 ? $checkedAnswers : $checkedAnswers[0];
//        }
//        if ($checkedAnswers instanceof Answer) {
//            return $this->em->merge($checkedAnswers);
//        }
//
//        if ($checkedAnswers instanceof ArrayCollection) {
//            if ($checkedAnswers->count() != 0) {
//                return $checkedAnswers;
//            }
//            return $checkedAnswers[0];
//        }
//
//        return $this->em->merge($checkedAnswers[0]);
    }

    /**
     * @param Question $question
     * @return bool
     */
    private function isQuestionCorrect($question)
    {
        $pickedAns = $this->getAnswers()->get($question->getId()) ? $this->getAnswers()->get($question->getId()) : null;
        $correctAns = $question->getAnswers()->filter(function (Answer $answer) {
            if ($answer->getCorrect()) {
                return $answer;
            }
        });
        return $this->arrayEqual($correctAns->toArray(), $pickedAns->toArray()) && !$this->isQuestionSolved($question->getId());
    }

    /**
     * @param int $id
     * @return bool
     */
    public function isQuestionSolved($id)
    {
        /** @var ArrayCollection $solved */
        $solved = $this->session->get('solved');

        return $solved->contains($id);
    }

    /**
     * @return ArrayCollection
     */
    public function getQuestions()
    {
        return $this->questions;
    }

    /**
     * @param ArrayCollection $questions
     * @return TestControl
     */
    public function setQuestions($questions)
    {
        $this->questions = $questions;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getAnswers()
    {
        return $this->answers;
    }

    /**
     * @param ArrayCollection $answers
     * @return TestControl
     */
    public function setAnswers($answers)
    {
        $this->answers = $answers;
        return $this;
    }


}