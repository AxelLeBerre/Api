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
    #[Route('/register', name: 'app_register', methods:['POST'])]
    public function register(Request $request, UserRepository $userRepository, UserPasswordHasherInterface $hasher, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(),true);

        // Vérifier si les données sont fournies
        if (!$data || !isset($data['email']) || !isset($data['password'])) {
            return new JsonResponse(['error' => 'Données manquantes.'], 400);
        }

        // Validation de l'email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse(['error' => "L'adresse email n'est pas valide."], 400);
        }

        // Vérification de la longueur du mot de passe
        if (strlen($data['password']) < 8) {
            return new JsonResponse(['error' => 'Le mot de passe doit contenir au moins 8 caractères.'], 400);
        }

        // Vérifier si l'utilisateur existe déjà dans la base de données
        $userExist = $userRepository->findOneBy(['email'=>$data['email']]);
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
            'message' => 'Utilisateur créé avec succès!',
        ]);
    }

    #[Route('/login', name: 'app_auth', methods:['POST'])]
    public function login(Request $request, UserPasswordHasherInterface $hasher, JWTTokenManagerInterface $JWT, UserRepository $userRepository): JsonResponse
    {
        $data = json_decode($request->getContent(),true);

        // Vérifier si les données sont fournies
        if (!$data || !isset($data['email']) || !isset($data['password'])) {
            return new JsonResponse(['error' => 'Données manquantes.'], 400);
        }
        
        // Validation de l'email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse(['error' => "L'adresse email n'est pas valide."], 400);
        }

        // Vérifier si l'utilisateur existe
        $userExist = $userRepository->findOneBy(['email'=>$data['email']]);

        if(!$userExist){
            return new JsonResponse(['error'=>'Utilisateur non trouvé.'],400);
        }

        // Vérifier si le mot de passe est correct
        if (!$hasher->isPasswordValid($userExist, $data['password'])) {
            return new JsonResponse(['error' => 'Mot de passe incorrect.'], 400);
        }
        
        $jwtToken = $JWT->create($userExist);

        return $this->json([
            'Token' => $jwtToken,
        ]);
    }

}
