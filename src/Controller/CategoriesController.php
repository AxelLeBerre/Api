<?php

namespace App\Controller;

use App\Entity\Categories;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/categories')]
class CategoriesController extends AbstractController
{
    // Afficher toutes les catégories
    #[Route('/', name: 'get_all_categories', methods: ['GET'])]
    public function getAllCategories(EntityManagerInterface $entityManager): JsonResponse
    {
        $categories = $entityManager->getRepository(Categories::class)->findAll();
        return $this->json($categories);
    }

    // Afficher une catégorie par ID
    #[Route('/{id}', name: 'get_category', methods: ['GET'])]
    public function getCategory(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $category = $entityManager->getRepository(Categories::class)->find($id);

        if (!$category) {
            return $this->json(['message' => 'Catégorie non trouvée'], 404);
        }

        return $this->json($category);
    }

    // Ajouter une nouvelle catégorie
    #[Route('/', name: 'add_category', methods: ['POST'])]
    public function addCategory(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['name'])) {
            return $this->json(['message' => 'Données incomplètes'], 400);
        }

        $category = new Categories();
        $category->setTitre($data['name']);

        $entityManager->persist($category);
        $entityManager->flush();

        return $this->json(['message' => 'Catégorie ajoutée avec succès!', 'id' => $category->getId()], 201);
    }

    // Modifier une catégorie existante
    #[Route('/{id}', name: 'update_category', methods: ['PUT'])]
    public function updateCategory(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['name'])) {
            return $this->json(['message' => 'Données incomplètes'], 400);
        }

        $category = $entityManager->getRepository(Categories::class)->find($id);

        if (!$category) {
            return $this->json(['message' => 'Catégorie non trouvée'], 404);
        }

        $category->setTitre($data['name']);

        $entityManager->persist($category);
        $entityManager->flush();

        return $this->json(['message' => 'Catégorie mise à jour avec succès!', 'id' => $category->getId()], 200);
    }

    // Supprimer une catégorie
    #[Route('/{id}', name: 'delete_category', methods: ['DELETE'])]
    public function deleteCategory(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $category = $entityManager->getRepository(Categories::class)->find($id);

        if (!$category) {
            return $this->json(['message' => 'Catégorie non trouvée'], 404);
        }

        $entityManager->remove($category);
        $entityManager->flush();

        return $this->json(['message' => 'Catégorie supprimée avec succès'], 200);
    }
}
