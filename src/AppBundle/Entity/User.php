<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use FOS\UserBundle\Model\User as BaseUser;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * User
 *
 * @ORM\Table(name="users")
 * @UniqueEntity(fields="username", message="Username already taken")
 * @UniqueEntity(fields="email", message="Email already taken")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 * @ORM\AttributeOverrides({
 *      @ORM\AttributeOverride(name="email", column=@ORM\Column(type="string", name="email", length=255, unique=false, nullable=true)),
 *      @ORM\AttributeOverride(name="emailCanonical", column=@ORM\Column(type="string", name="email_canonical", length=255, unique=false, nullable=true))
 * })
 */
class User extends BaseUser
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Length(min="3", max="30")
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="surname", type="string", length=255, nullable=true)
     */
    protected $surname;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    protected $updatedAt;

    /**
     * @var string
     *
     * @ORM\Column(name="facebook_id", type="string", length=255, nullable=true)
     */
    protected $facebook_id;

    /** @ORM\Column(name="facebook_access_token", type="string", length=255, nullable=true) */
    protected $facebook_access_token;

    /** @ORM\Column(name="google_id", type="string", length=255, nullable=true) */
    protected $google_id;

    /** @ORM\Column(name="google_access_token", type="string", length=255, nullable=true) */
    protected $google_access_token;

    /**
     * @var integer
     *
     * @ORM\Column(name="tests_taken", type="integer")
     */
    protected $testsTaken;

    /**
     * @var integer
     *
     * @ORM\Column(name="correct", type="integer")
     */
    protected $correct;

    /**
     * @var integer
     *
     * @ORM\Column(name="incorrect", type="integer")
     */
    protected $incorrect;

    /**
     * @var int
     *
     * @ORM\Column(name="time_spent", type="integer")
     */
    protected $timeSpent;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Test", mappedBy="userId")
     */
    protected $tests;

    /**
     * @ORM\Column(name="public_profile", type="boolean")
     */
    protected $publicProfile;
    /**
     * @var string
     *
     * @ORM\Column(name="avatar", type="string", nullable=true)
     */
    protected $avatar;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\QuestionReport", mappedBy="created_by")
     */
    protected $reports;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Question", mappedBy="created_by")
     */
    protected $questions_created;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Question", inversedBy="contributors", indexBy="id", cascade={"remove"})
     * @ORM\JoinTable(name="contributors")
     */
    protected $questionsContributed;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Notification", mappedBy="user", cascade={"remove"})
     */
    protected $notifications;

    public function __construct()
    {
        parent::__construct();

        $this->publicProfile = true;
        $this->createdAt = new \DateTime('now');
        $this->updatedAt = new \DateTime('now');
        $this->addRole('ROLE_USER');
        $this->correct = 0;
        $this->incorrect = 0;
        $this->testsTaken = 0;
        $this->timeSpent = 0;
        $this->avatar = 'http://www.iconsdb.com/icons/preview/black/guest-xxl.png';
        $this->questionsContributed = new ArrayCollection();
        $this->notifications = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return User
     */
    public function setName(string $name = null): User
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * @param string $avatar
     * @return User
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;
        return $this;
    }


    public function getSurname()
    {
        return $this->surname;
    }

    /**
     * @param string $surname
     * @return User
     */
    public function setSurname(string $surname): User
    {
        $this->surname = $surname;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getReports()
    {
        return $this->reports;
    }

    /**
     * @param mixed $reports
     * @return User
     */
    public function setReports($reports)
    {
        $this->reports = $reports;
        return $this;
    }



    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     * @return User
     */
    public function setCreatedAt(\DateTime $createdAt): User
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPublicProfile()
    {
        return $this->publicProfile;
    }

    /**
     * @param mixed $publicProfile
     */
    public function setPublicProfile($publicProfile)
    {
        $this->publicProfile = $publicProfile;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     * @return User
     */
    public function setUpdatedAt(\DateTime $updatedAt): User
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }


    /**
     * @return string
     */
    public function getFacebookId()
    {
        return $this->facebook_id;
    }

    /**
     * @param string $facebookId
     * @return User
     */
    public function setFacebookId($facebookId): User
    {
        $this->facebook_id = $facebookId;
        return $this;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return User
     */
    public function setId(int $id): User
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFacebookAccessToken()
    {
        return $this->facebook_access_token;
    }

    /**
     * @param mixed $facebook_access_token
     * @return User
     */
    public function setFacebookAccessToken($facebook_access_token)
    {
        $this->facebook_access_token = $facebook_access_token;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getGoogleId()
    {
        return $this->google_id;
    }

    /**
     * @param mixed $google_id
     * @return User
     */
    public function setGoogleId($google_id)
    {
        $this->google_id = $google_id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getGoogleAccessToken()
    {
        return $this->google_access_token;
    }

    /**
     * @param mixed $google_access_token
     * @return User
     */
    public function setGoogleAccessToken($google_access_token)
    {
        $this->google_access_token = $google_access_token;
        return $this;
    }


    /**
     * @return int
     */
    public function getTestsTaken(): int
    {
        return $this->testsTaken;
    }

    /**
     * @param int $testsTaken
     * @return User
     */
    public function setTestsTaken(int $testsTaken): User
    {
        $this->testsTaken = $testsTaken;
        return $this;
    }

    /**
     * @return int
     */
    public function getCorrect(): int
    {
        return $this->correct;
    }

    /**
     * @param int $correct
     * @return User
     */
    public function setCorrect(int $correct): User
    {
        $this->correct = $correct;
        return $this;
    }

    /**
     * @return int
     */
    public function getIncorrect(): int
    {
        return $this->incorrect;
    }

    /**
     * @param int $incorrect
     * @return User
     */
    public function setIncorrect(int $incorrect): User
    {
        $this->incorrect = $incorrect;
        return $this;
    }

    /**
     * @return integer
     */
    public function getTimeSpent()
    {
        return $this->timeSpent;
    }

    /**
     * @param integer $timeSpent
     * @return User
     */
    public function setTimeSpent($timeSpent): User
    {
        $this->timeSpent = $timeSpent;
        return $this;
    }

    public function getPercentage()
    {
        return $this->correct + $this->incorrect == 0 ? 0 : $this->correct / ($this->correct + $this->incorrect) * 100;
    }

    public function getFormatedTimeSpent()
    {
        $formated = "";
        $time = $this->timeSpent;

        $seconds = 31556926;
        if ($time >= $seconds) {
            $yr = floor($time / $seconds);
            $time -= $yr * $seconds;
            $formated .= sprintf('%d years ', $yr);
        }

        $seconds = 2629744;
        if ($time >= $seconds) {
            $mnt = floor($time / $seconds);
            $time -= $mnt * $seconds;
            $formated .= sprintf('%d months ', $mnt);
        }

        $seconds = 86400;
        if ($time >= $seconds) {
            $day = floor($time / $seconds);
            $time -= $day * $seconds;
            $formated .= sprintf('%d days ', $day);
        }

        $seconds = 3600;
        if ($time >= $seconds) {
            $hr = floor($time / $seconds);
            $time -= $hr * $seconds;
            $formated .= sprintf('%d hours ', $hr);
        }

        $seconds = 60;
        if ($time >= $seconds) {
            $min = floor($time / $seconds);
            $time -= $min * $seconds;
            $formated .= sprintf('%d minutes ', $min);
        }

        $formated .= sprintf('%d seconds', $time);

        return $formated;
    }

    public function updateStats($time, $answers)
    {
        $correct            = count(array_filter($answers, function ($answer) {
            return $answer;
        }));
        $this->correct      += $correct;
        $this->incorrect    += count($answers) - $correct;
        $this->timeSpent    += $time->s + $time->i * 60 + $time->h * 3600;
        $this->testsTaken++;
    }

    /**
     * @return mixed
     */
    public function getTests()
    {
        return $this->tests;
    }

    /**
     * @param mixed $tests
     * @return User
     */
    public function setTests($tests)
    {
        $this->tests = $tests;
        return $this;
    }

    
    /**
     * @return (Role|string)[] The user roles
     */
    public function getRoles()
    {
        return $this->roles;
    }

    public function addRole($role)
    {
        $this->roles[] = $role;
    }

    public function setRoles(array $roles)
    {
        $this->roles = $roles;
    }

    /**
     * @return string|null The salt
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * @return mixed
     */
    public function getQuestionsCreated()
    {
        return $this->questions_created;
    }

    /**
     * @param mixed $questions_created
     * @return User
     */
    public function setQuestionsCreated($questions_created)
    {
        $this->questions_created = $questions_created;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getQuestionsContributed()
    {
        return $this->questionsContributed;
    }

    /**
     * @param Question $questionsContributed
     * @return User
     */
    public function addQuestionsContributed($questionsContributed)
    {
        if (!$this->questionsContributed->contains($questionsContributed)) {
            $this->questionsContributed->add($questionsContributed);
        }
        return $this;
    }

    /**
     * @param Question $questionsContributed
     * @return User
     */
    public function removeQuestionsContributed($questionsContributed)
    {
        if ($this->questionsContributed->contains($questionsContributed)) {
            $this->questionsContributed->remove($questionsContributed);
        }
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getNotifications()
    {
        return $this->notifications;
    }

    /**
     * @param ArrayCollection $notifications
     * @return User
     */
    public function setNotifications($notifications)
    {
        $this->notifications = $notifications;
        return $this;
    }


    public function getFullName()
    {
        return $this->getSurname() ? $this->getName().' '.$this->getSurname() : $this->getName();
    }

    /**
     * @return int
     */
    public function getUnseenCount()
    {
        return $this->notifications->filter(function (Notification $notification) {
            return $notification->getSeen();
        })->count();
    }
}
