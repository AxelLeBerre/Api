<?php

namespace App\Controller;

use App\Repository\MoviesRepository;
use App\Entity\Categories;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Movies; // Pour l'entité Movies
use Doctrine\ORM\EntityManagerInterface; // Pour l'EntityManager
use Symfony\Component\HttpFoundation\Request; // Pour gérer la requête HTTP


#[Route('/movies')]
class MoviesController extends AbstractController
{
    private $moviesRepo;
    private $serializer;
    public function __construct(MoviesRepository $moviesRepo, SerializerInterface $serializer)
    {
        $this->moviesRepo = $moviesRepo;
        $this->serializer = $serializer;
    }

    //Afficher tous les livres

    #[Route('/', name: 'get_movies', methods: ['GET'])]
    public function getAllMovies(): JsonResponse
    {
        $movies = $this->moviesRepo->findAll();
        $data = $this->serializer->normalize($movies);

        return $this->json($data);
    }

    
    //Afficher un livre

    #[Route('/{id}', name: 'get_movie', methods: ['GET'])]
    public function getMovie(int $id): JsonResponse
    {
        $movie = $this->moviesRepo->find($id);

        // Vérifie si le film existe
        if (!$movie) {
            return $this->json(['message' => 'Film non trouvé'], 404);
        }
    
        // Sérialisation des données
        $data = $this->serializer->normalize($movie);
    
        return $this->json($data);
    }


    //Ajouter un livre

    #[Route('/', name: 'add_movie', methods: ['POST'])]
    public function addMovie(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
    // Récupérer les données JSON envoyées dans le corps de la requête
        $data = json_decode($request->getContent(), true); // true pour convertir en tableau

    // Vérifier si toutes les données nécessaires sont présentes
        if (!isset($data['titre'], $data['dateSortie'], $data['description'], $data['realisateur'], $data['categories'])) {
            return new JsonResponse(['message' => 'Données incomplètes'], 400); // Code 400 pour mauvaise requête
        }

    // Créer un nouvel objet Movies et l'hydrater avec les données reçues
        $movie = new Movies();
        $movie->setTitre($data['titre']);
        $movie->setDateSortie(new \DateTime($data['dateSortie']));
        $movie->setDescription($data['description']);
        $movie->setRealisateur($data['realisateur']);

    // Ajouter les catégories
        foreach ($data['categories'] as $categoryId) {
            $category = $entityManager->getRepository(Categories::class)->find($categoryId);
            if ($category) {
                $movie->addCategory($category);
            }
        }

    // Persister et flush dans la base de données
        $entityManager->persist($movie);
        $entityManager->flush();

        return new JsonResponse([
            'message' => 'Film ajouté avec succès!',
            'id' => $movie->getId()
        ], 201); // Code 201 pour succès de la création
    }



    //Modifier un livre

    #[Route('/{id}', name: 'update_movie', methods: ['PUT'])]
    public function updateMovie(int $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        // Récupérer les données JSON envoyées dans la requête
        $data = json_decode($request->getContent(), true);

        // Vérifier si les données nécessaires sont présentes
        if (!isset($data['titre'], $data['dateSortie'], $data['description'], $data['realisateur'], $data['categories'])) {
            return new JsonResponse(['message' => 'Données incomplètes'], 400);
        }

        // Trouver le film dans la base de données par son ID
        $movie = $em->getRepository(Movies::class)->find($id);

        // Vérifier si le film existe
        if (!$movie) {
            return new JsonResponse(['message' => 'Film non trouvé'], 404);
        }

        // Mettre à jour les données du film
        $movie->setTitre($data['titre']);
        $movie->setDateSortie(new \DateTime($data['dateSortie']));
        $movie->setDescription($data['description']);
        $movie->setRealisateur($data['realisateur']);

        // Mettre à jour les catégories
        // On vide d'abord les catégories actuelles
        $movie->getCategories()->clear();

        foreach ($data['categories'] as $categoryId) {
            // Récupérer chaque catégorie par son ID
            $category = $em->getRepository(Categories::class)->find($categoryId);
            if ($category) {
                $movie->addCategory($category); // Ajouter la catégorie au film
            }
        }

        // Persister et enregistrer les changements dans la base de données
        $em->persist($movie);
        $em->flush();

        // Retourner une réponse JSON pour indiquer que la mise à jour a réussi
        return new JsonResponse([
            'message' => 'Film mis à jour avec succès',
            'id' => $movie->getId()
        ], 200); // Code 200 pour une mise à jour réussie
    }


    //Supprimer un film

    #[Route('/{id}', name: 'delete_movie', methods: ['DELETE'])]
    public function deleteMovie(int $id, EntityManagerInterface $em): JsonResponse
    {
        // Trouver le film par son ID dans la base de données
        $movie = $em->getRepository(Movies::class)->find($id);

        // Vérifier si le film existe
        if (!$movie) {
            return new JsonResponse(['message' => 'Film non trouvé'], 404); // Code 404 si le film n'est pas trouvé
        }

        // Supprimer le film
        $em->remove($movie);
        $em->flush();

        // Retourner une réponse JSON indiquant que le film a bien été supprimé
        return new JsonResponse(['message' => 'Film supprimé avec succès'], 200); // Code 200 pour une suppression réussie
    }
}
