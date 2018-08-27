<?php
// src/Controller/DefaultController.php
namespace App\Controller;


use Facebook\Facebook;
use http\Exception\InvalidArgumentException;
use http\Exception\UnexpectedValueException;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Firebase\JWT\JWT;
use App\Entity\User;

use Symfony\Component\HttpFoundation\Response;

class DefaultController extends AbstractController
{
    /**
     * @Route("/loginapp")
     * This is only for test the googleOAuth and FacebookOAuth
     */
    public function loginapp() {
        return $this->render('Default/loginapp.html.twig');
    }

    /**
     * @Route("/api", defaults={"_format"="json"})
     */
    public function apiExample()
    {
        //var_dump($this->getUser());exit;
        //return new Response($this->getUser());

        return $this->json([
            'name' => $this->getUser(),

        ]);
    }

    /**
     * @Route("/fblogin"  )
     */
    public function fblogin(Request $request)
    {
        $fbToken = $request->query->get('fbtoken');
        if(!$fbToken){
            throw new BadRequestHttpException('There is no user ');  //new BadRequestHttpException('There is no user');
        }

        $appId = $this->getParameter('fbAppId');
        $secret = $this->getParameter('fbSecretKey');
        $fb = new Facebook([
            'app_id' => "{$appId}",
            'app_secret' => "{$secret}",
            'default_graph_version' => 'v3.1',
        ]);

        try {
            // Returns a `Facebook\FacebookResponse` object
            $response = $fb->get('/me?fields=id,name,email', $fbToken);
        } catch(\Exception $e) {
            throw new UnexpectedValueException("No user authenticated");

        }
        $user = $response->getGraphUser();
        $token = $this->createToken($user['email']);

        return $this->json($token);
    }
    /**
     * @Route("/glogin")
     */
    public function googleLogin(Request $request)
    {
        $gToken = $request->query->get('gtoken');
        $client = new \Google_Client(['client_id' => $this->getParameter('googleClientId')]);
        $payload = $client->verifyIdToken($gToken);
        if(!$payload){
            throw $this->createAccessDeniedException('Invalid User');
        }
        $token = $this->createToken($payload['email']);

        return $this->json($token);
    }

    private function createToken($userName)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $userRepository = $this->getDoctrine()->getRepository(User::class);
        $user =  $userRepository->loadUserByUsername($userName);

        if($user){
            $token = ['token' => $user->getToken()];
        } else {
            $newToken = array(
                "name" => $userName,
                "role" => "ROLE_API"
            );
            $token = ['token' => JWT::encode($newToken, $this->getParameter('tokenkey'))];
            $newUser = new User();
            $newUser->setUserName($userName);
            $newUser->setEmail($userName);
            $newUser->seToken($token['token']);
            $entityManager->persist($newUser);
            $entityManager->flush();

        }

        return $token;
    }
}