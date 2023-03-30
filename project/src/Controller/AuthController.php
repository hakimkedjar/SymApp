<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

class AuthController extends AbstractController
{   
    private $userRepository;
    private $serializer;
    private $security;

    public function __construct(
        UserRepository $userRepository,
        SerializerInterface $serializer,
        // SecurityInter $security,

    ) {
        $this->userRepository = $userRepository;
        $this->serializer = $serializer;
        // $this->security = $security;
    }

    /**
     * @Route("/register", methods={"POST"}, name="register")
     * 
     */
    public function register(Request $request, ValidatorInterface $validator): JsonResponse
    {   
        $jsonData = json_decode($request->getContent(), true);

        $constraint = new Assert\Collection([
            'nom' => [
                new Assert\NotBlank(), 
                new Assert\Length(['min' => 3])
            ],
            'prenom' => [
                new Assert\NotBlank(),
                new Assert\Length(['min' => 3])
            ],
            'email' => [
                new Assert\NotBlank(), 
                new Assert\Email()
            ],
            'password' => [
                new Assert\NotBlank(), 
                new Assert\Length(['min' => 6]),
                new Assert\Length(['min' => 8]),
                new Assert\Regex([
                    "pattern"   =>  '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/',
                    "message"   =>  'Password must contain at least one uppercase letter, one lowercase letter, one number and one special character'
                    ])
            ],
        ]);

        $errors = $validator->validate($jsonData, $constraint);

        if (count($errors) > 0) {
            // Il y a des erreurs de validation, renvoie une réponse avec les erreurs
            $errorMessages = [];
    
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()][] = $error->getMessage();
            }
    
            return new JsonResponse(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        // Check if email already exists
        $existingUser = $this->userRepository->findOneBy(['email' => $jsonData['email']]);
        
        if ($existingUser) {
            return new JsonResponse(['message' => 'Email already exists'], Response::HTTP_BAD_REQUEST);
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
     * @Route("/getUser", methods={"GET"}, name="getUser")
     * 
     */
    public function user(): Response
    {
        $user = $this->security->getUser();
        $dataUser = $this->serializer->serialize($user, 'json');

        // Créez une réponse JSON
        $response = new JsonResponse($dataUser, Response::HTTP_OK);

        return $response;
    }

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