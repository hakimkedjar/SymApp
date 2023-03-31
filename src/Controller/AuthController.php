<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\RegisterValidation;
use Symfony\Component\Security\Core\Security;
use Doctrine\Persistence\ManagerRegistry;

class AuthController extends AbstractController
{   
    private $userRepository;
    private $serializer;
    private $security;

    public function __construct(
        UserRepository $userRepository,
        SerializerInterface $serializer,
        Security $security

    ) {
        $this->userRepository = $userRepository;
        $this->serializer = $serializer;
        $this->security = $security;
    }

    /**
     * @Route("/register", methods={"POST"}, name="register")
     * 
     */
    public function register(Request $request, ValidatorInterface $validator, RegisterValidation $userValidation): JsonResponse
    {   
        $jsonData = json_decode($request->getContent(), true);
        $constraint = $userValidation->registerConstraints();

        $errors = $validator->validate($jsonData, $constraint);

        if (count($errors) > 0) {
            // Il y a des erreurs de validation, renvoie une réponse avec les erreurs
            $errorMessages = [];
    
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()][] = $error->getMessage();
            }
            $response = new JsonResponse(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
            return $response;
        }
        

        $user = $this->userRepository->addUser($jsonData);
        $serializer = $this->serializer->serialize($user, 'json');

        $response = new JsonResponse([
            'message' => 'Register success',
            'user'    => $serializer,
        ], Response::HTTP_CREATED);

        return $response;
    }

    /**
     * @Route("/user", name="user_info", methods={"GET"})
     */
    public function userInfo()
    {
        $user = $this->security->getUser();

        dd($this->getUser());

        if (!$user) {
            $response = new JsonResponse([
                'message' => 'User not found',
            ], Response::HTTP_NOT_FOUND);
            return $response;
        }

        $userId = $user->getId();
        $email = $user->getEmail();
        $nom = $user->getNom();
        $prenom = $user->getPrenom();

        $response = new JsonResponse([
            'id'     => $userId,
            'email'  => $email,
            'nom'    => $nom,
            'prenom' => $prenom
        ], Response::HTTP_OK);
        
        return $response;
    }

    // /**
    //  * @Route("/user", methods={"GET"})
    //  * Get user informations
    //  */
    // public function getUserInfo(ManagerRegistry $doctrine): Response
    // {
    //     $user = $this->getUser();
    //     dd($user);
    //     return $this->json($users);
    // }

    /**
     * @Route("/logout")
     * 
     */
    public function logout(): Response
    {
        // Récupérez vos données depuis une source quelconque
        $myData = [
            'message'    => 'logout success',
        ];

        // Créez une réponse JSON
        $response = new JsonResponse($myData, Response::HTTP_OK);

        return $response;
    }
}