<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class AuthController extends AbstractController
{
    #[Route('/register', name: 'app_auth', methods:['POST'])]
    public function register(Request $request, UserRepository $userRepository, UserPasswordHasherInterface $hasher, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(),true);

        $userExist = $userRepository->findOneBy(['email'=>$data['email']]);
        
        // Faire les vérifs !!!
        if($userExist){
            return new JsonResponse(['error'=>'Cet utilisateur existe deja'],400);
        }
        
        $user = new User(); 
        $user-> setEmail($data['email']);
        $user-> setPassword($hasher->hashPassword($user, $data['password']));
        $user-> setRoles(['ROLE_USER']);

        $em->persist($user);
        $em->flush();

        return $this->json([
            'message' => 'Welcome to your new controller!',
        ]);
    }

    #[Route('/login', name: 'app_auth', methods:['POST'])]
    public function login(Request $request, UserPasswordHasherInterface $hasher, JWTTokenManagerInterface $JWT, UserRepository $userRepository): JsonResponse
    {
        $data = json_decode($request->getContent(),true);

        // Faire les vérifs !!!
        $userExist = $userRepository->findOneBy(['email'=>$data['email']]);

        if(!$userExist){
            return new JsonResponse(['error'=>'Invalid'],400);
        }
        
        $jwtToken = $JWT->create($userExist);

        return $this->json([
            'Token' => $jwtToken,
        ]);
    }

}
