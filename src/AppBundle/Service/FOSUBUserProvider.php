<?php
/**
 * Created by PhpStorm.
 * User: eimantas
 * Date: 16.11.22
 * Time: 21.45
 */

namespace AppBundle\Service;

use AppBundle\Entity\User;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider as BaseClass;
use Symfony\Component\Security\Core\User\UserInterface;

class FOSUBUserProvider extends BaseClass
{

    /**
     * {@inheritDoc}
     */
    public function connect(UserInterface $user, UserResponseInterface $response)
    {
        $property       = $this->getProperty($response);
        $username       = $response->getUsername();

        $service        = $response->getResourceOwner()->getName();
        $setter         = 'set'.ucfirst($service);
        $setter_id      = $setter.'Id';
        $setter_token   = $setter.'AccessToken';

        if (null !== $previousUser = $this->userManager->findUserBy(array($property => $username))) {
            $previousUser->$setter_id(null);
            $previousUser->$setter_token(null);
            $this->userManager->updateUser($previousUser);
        }

        $user->$setter_id($username);
        $user->$setter_token($response->getAccessToken());
        $this->userManager->updateUser($user);
    }
    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $username   = $response->getUsername();
        $user       = $this->userManager->findUserBy(array($this->getProperty($response) => $username));

        if (null === $user) {
            $service        = $response->getResourceOwner()->getName();
            $setter         = 'set'.ucfirst($service);
            $setter_id      = $setter.'Id';
            $setter_token   = $setter.'AccessToken';

            $user = new User();
            $user->$setter_id($username)
                ->$setter_token($response->getAccessToken())
                ->setUsername($response->getUsername())
                ->setEmail($response->getEmail())
                ->setPassword(md5((uniqid())))
                ->setEnabled(true)
                ->setName($response->getFirstName())
                ->setSurname($response->getLastName());
            
            if ($service === 'facebook') {
                $user->setAvatar('https://graph.facebook.com/'.$user->getUsername().'/picture?type=large');
            } else {
                $user->setAvatar($response->getProfilePicture());
            }

            $this->userManager->updateUser($user);
            return $user;
        }
        //if user exists - go with the HWIOAuth way
        $user           = parent::loadUserByOAuthUserResponse($response);
        $serviceName    = $response->getResourceOwner()->getName();
        $setter         = 'set' . ucfirst($serviceName) . 'AccessToken';
        //update access token
        $user->$setter($response->getAccessToken());
        return $user;
    }
}