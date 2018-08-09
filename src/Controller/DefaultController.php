<?php
// src/Controller/DefaultController.php
namespace App\Controller;


use http\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Firebase\JWT\JWT;
use App\Entity\User;

use Symfony\Component\HttpFoundation\Response;

class DefaultController extends AbstractController
{
    /**
     * @Route("/loginoauth")
     */
    public function login(Request $request){

        $mail = $request->query->get('mail');
        $entityManager = $this->getDoctrine()->getManager();
        $userRepository = $this->getDoctrine()->getRepository(User::class);
        $user =  $userRepository->loadUserByUsername($mail);
        $key = "example_key";


        if($user){
            $token = ['token' => $user->getToken()];
        } else {
            $newToken = array(
                "name" => $mail,
                "role" => "ROLE_API"
            );
            $token = ['token' => JWT::encode($newToken, $key)];
            $newUser = new User();
            $newUser->setUserName($mail);
            $newUser->setEmail($mail);
            $newUser->seToken($token['token']);
            $entityManager->persist($newUser);
            $entityManager->flush();

        }

        return $this->json($token);



    }
    /**
     * @Route("/api")
     */
    public function apiExample()
    {
        return new Response('hola');

        return $this->json([
            'name' => 'gon',
            'symfony' => 'rocks',
        ]);
    }
    /**
     * @Route("/admin")
     */
    public function api() {
        $key = "example_key";
        $token = array(
            "iss" => "http://example.org",
            "aud" => "http://example.com",
            "iat" => 1356999524,
            "nbf" => 1357000000
        );

        /**
         * IMPORTANT:
         * You must specify supported algorithms for your application. See
         * https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40
         * for a list of spec-compliant algorithms.
         */
        $jwt = JWT::encode($token, $key);
        return $this->json($jwt);


        $product = $this->getDoctrine()
        ->getRepository(User::class)
        ->find(1);
        return $this->json($product);
    }
}