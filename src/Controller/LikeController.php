<?php

namespace App\Controller;

use App\Entity\Likes;
use App\Entity\Movies;
use App\Entity\User;
use App\Entity\Comment;
use App\Repository\LikeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/likes')]
class LikeController extends AbstractController
{
    private $likeRepo;

    public function __construct(LikeRepository $likeRepo)
    {
        $this->likeRepo = $likeRepo;
    }

    // Afficher tous les likes
    #[Route('/', name: 'get_all_likes', methods: ['GET'])]
    public function getAllLikes(): JsonResponse
    {
        $likes = $this->likeRepo->findAll();
        return $this->json($likes);
    }

    // Afficher un like par ID
    #[Route('/{id}', name: 'get_like', methods: ['GET'])]
    public function getLike(int $id): JsonResponse
    {
        $like = $this->likeRepo->find($id);

        if (!$like) {
            return $this->json(['message' => 'Like non trouvé'], 404);
        }

        return $this->json($like);
    }

    // Ajouter un like
    #[Route('/', name: 'add_like', methods: ['POST'])]
public function addLike(Request $request, EntityManagerInterface $em): JsonResponse
{
    $data = json_decode($request->getContent(), true);

    // Vérifier que les données nécessaires sont présentes
    if (!isset($data['likes'])) {
        return $this->json(['message' => 'Données incomplètes'], 400);
    }

    // Récupérer l'utilisateur (une seule fois pour toute la requête)
    $user = $em->getRepository(User::class)->find($data['likes'][0]['userId']);
    if (!$user) {
        return $this->json(['message' => 'Utilisateur non trouvé'], 404);
    }

    $likes = [];

    foreach ($data['likes'] as $likeData) {
        $like = new Likes();

        // Vérifier le type (film ou commentaire)
        if ($likeData['type'] === 'movie') {
            // Récupérer le film
            $movie = $em->getRepository(Movies::class)->find($likeData['id']);
            if (!$movie) {
                return $this->json(['message' => 'Film non trouvé'], 404);
            }

            // Vérifie si un like existe déjà pour ce film
            if ($this->likeRepo->findExistingLikeForMovie($user, $movie)) {
                return $this->json(['message' => 'Vous avez déjà liké ce film'], 400);
            }

            $like->setMovie($movie);
            $like->setUser($user);
            $like->setComment(null);  // On s'assure que comment_id est nul dans le cas d'un like de film
        } elseif ($likeData['type'] === 'comment') {
            // Récupérer le commentaire
            $comment = $em->getRepository(Comment::class)->find($likeData['id']);
            if (!$comment) {
                return $this->json(['message' => 'Commentaire non trouvé'], 404);
            }

            // Vérifie si un like existe déjà pour ce commentaire
            if ($this->likeRepo->findExistingLikeForComment($user, $comment)) {
                return $this->json(['message' => 'Vous avez déjà liké ce commentaire'], 400);
            }

            $like->setComment($comment);
            $like->setUser($user);
            $like->setMovie(null);  // On s'assure que movie_id est nul dans le cas d'un like de commentaire
        } else {
            return $this->json(['message' => 'Type invalide'], 400);
        }

        // Ajouter la date de création
        $like->setCreatedAt(new \DateTime());

        // Ajouter le like dans un tableau pour être persisté plus tard
        $likes[] = $like;
    }

    // Sauvegarde des likes dans la base de données
    foreach ($likes as $like) {
        $em->persist($like);
    }

    $em->flush();

    return $this->json(['message' => 'Likes ajoutés avec succès!', 'likes' => count($likes)], 201);
}

    // Supprimer un like
    #[Route('/{id}', name: 'delete_like', methods: ['DELETE'])]
    public function deleteLike(int $id, EntityManagerInterface $em): JsonResponse
    {
        $like = $em->getRepository(Likes::class)->find($id);

        if (!$like) {
            return $this->json(['message' => 'Like non trouvé'], 404);
        }

        $em->remove($like);
        $em->flush();

        return $this->json(['message' => 'Like supprimé avec succès'], 200);
    }
}
