<?php

namespace App\Controller;

use App\Entity\Movies;
use App\Entity\User;
use App\Entity\Comment;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/comments')]

class CommentController extends AbstractController
{
    private $commentRepo;

    public function __construct(CommentRepository $commentRepo)
    {
        $this->commentRepo = $commentRepo;
    }

    // Afficher tous les commentaires
    #[Route('/', name: 'get_all_comments', methods: ['GET'])]
    public function getAllComments(): JsonResponse
    {
        $comments = $this->commentRepo->findAll();
        return $this->json($comments);
    }

    // Afficher un commentaire par ID
    #[Route('/{id}', name: 'get_comment', methods: ['GET'])]
    public function getComment(int $id): JsonResponse
    {
        $comment = $this->commentRepo->find($id);

        if (!$comment) {
            return $this->json(['message' => 'Commentaire non trouvé'], 404);
        }

        return $this->json($comment);
    }

    // Ajouter un commentaire
    #[Route('/', name: 'add_comment', methods: ['POST'])]
    public function addComment(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Vérifier que les données nécessaires sont présentes
        if (!isset($data['movieId'], $data['userId'], $data['commentaire'])) {
            return $this->json(['message' => 'Données incomplètes'], 400);
        }

        $movie = $em->getRepository(Movies::class)->find($data['movieId']);
        $user = $em->getRepository(User::class)->find($data['userId']);

        if (!$movie || !$user) {
            return $this->json(['message' => 'Film ou utilisateur non trouvé'], 404);
        }

        $comment = new Comment();
        $comment->setMovie($movie);
        $comment->setUser($user);
        $comment->setCommentaire($data['commentaire']);
        $comment->setDateCommentaire(new \DateTime());

        $em->persist($comment);
        $em->flush();

        return $this->json(['message' => 'Commentaire ajouté avec succès!', 'id' => $comment->getId()], 201);
    }

     // Supprimer un commentaire
     #[Route('/{id}', name: 'delete_comment', methods: ['DELETE'])]
     public function deleteComment(int $id, EntityManagerInterface $em): JsonResponse
     {
         $comment = $em->getRepository(Comment::class)->find($id);
 
         if (!$comment) {
             return $this->json(['message' => 'Commentaire non trouvé'], 404);
         }
 
         $em->remove($comment);
         $em->flush();
 
         return $this->json(['message' => 'Commentaire supprimé avec succès'], 200);
     }
}
