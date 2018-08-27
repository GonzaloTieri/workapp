<?php
// src/Security/ApiKeyUserProvider.php
namespace App\Security;

use App\Entity\User;
use Firebase\JWT\JWT;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Security\Core\User\UserProviderInterface;
//use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class ApiKeyUserProvider implements UserProviderInterface
{
    private $params ;
    private $em;

    public function __construct(ParameterBagInterface $params, EntityManagerInterface $em)
    {
        $this->params = $params;
        $this->em = $em;
    }

    public function getUsernameForApiKey($apiKey)
    {
        //var_dump($this->params->get('tokenkey'));exit;
        $tokenDecoded = array('token' => JWT::decode ($apiKey,$this->params->get('tokenkey'), array('HS256') ) );
        //($newToken, $this->getParameter('tokenkey')));
        // Look up the username based on the token in the database, via
        // an API call, or do something entirely different
        $username = $tokenDecoded['token']->name;

        return $username;
    }

    public function loadUserByUsername($username)
    {
        $userRepository = $this->em->getRepository(User::class);
        $user =  $userRepository->loadUserByUsername($username);

        return $user;
    }

    public function refreshUser(UserInterface $user)
    {
        // this is used for storing authentication in the session
        // but in this example, the token is sent in each request,
        // so authentication can be stateless. Throwing this exception
        // is proper to make things stateless
        throw new UnsupportedUserException();
    }

    public function supportsClass($class)
    {
        return User::class === $class;
    }
}