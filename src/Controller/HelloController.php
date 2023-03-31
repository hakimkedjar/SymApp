<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class HelloController
{
    /**
     * @Route("/hello")
     * 
     */
    public function hello(): Response
    {
        // Récupérez vos données depuis une source quelconque
        $myData = [
            'name'    => 'Christian Doe',
            'email'   => 'john.doe@example.com',
            'age'     => 30,
        ];

        // Créez une réponse JSON
        $response = new JsonResponse($myData, Response::HTTP_OK);

        return $response;
    }
}