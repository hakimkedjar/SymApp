<?php
namespace App\Validator;

use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\UserRepository;

class RegisterValidation
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function registerConstraints()
    {
        return new Assert\Collection([
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
                new Assert\Email(),
                new Assert\Callback(function ($value, $context) {
                    $existingUser = $this->userRepository->findOneBy(['email' => $value]);
                    if ($existingUser) {
                        $context->addViolation('Email already exists');
                    }
                }),
            ],
            'password' => [
                new Assert\NotBlank(), 
                new Assert\Length(['min' => 8]),
                new Assert\Regex([
                    "pattern"   =>  '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/',
                    "message"   =>  'Password must contain at least one uppercase letter, one lowercase letter, one number and one special character'
                    ])
            ],
        ]);
    }
}