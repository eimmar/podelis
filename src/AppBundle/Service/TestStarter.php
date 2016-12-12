<?php
/**
 * Created by PhpStorm.
 * User: eimantas
 * Date: 16.11.6
 * Time: 17.27
 */

namespace AppBundle\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Session\Session;

class TestStarter
{
    private $session;

    /**@param Session $session */
    public function __construct($session)
    {
        $this->session = $session;
    }

    /**
     * @param bool $trackResults
     * @param ArrayCollection $questions
     * @param string $timePerQuestion
     */
    public function startTest($questions, $timePerQuestion, $trackResults)
    {
        $test = [
            'questions'    => $questions,
            'trackResults'      => $trackResults,
            'solved'            => new ArrayCollection(),
            'started'           => new \DateTime(),
            'endsAt'            => new \DateTime($this->setTimeLimit($timePerQuestion, $questions->count())),
            'answered'          => new ArrayCollection(),
            'isCorrect' => new ArrayCollection()
        ];

        $this->session->clear();
        $this->session->replace($test);
    }

    /**
     * @param string $timePerQuestion
     * @param int $numOfQuestions
     * @return string mixed
     * @throws \Exception
     */
    public function setTimeLimit($timePerQuestion, $numOfQuestions)
    {
        if (preg_match('#[0-9]+#', $timePerQuestion, $time)) {
            $time = intval($time);
            $time = $time * $numOfQuestions;

            return preg_replace('#[0-9]+#', $time, $timePerQuestion);
        }
        throw new \Exception('Invalid argument %s', $timePerQuestion);
    }
}
