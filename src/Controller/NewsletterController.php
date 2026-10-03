<?php
namespace App\Controller;
use App\Entity\AbonnementNewsletter;
use App\Repository\AbonnementNewsletterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class NewsletterController extends AbstractController
{
    #[Route('/newsletter/subscribe', name: 'app_newsletter_subscribe', methods: ['POST'])]
    public function subscribe(Request $request, AbonnementNewsletterRepository $repository, EntityManagerInterface $entityManager): JsonResponse
    {
        $email = trim((string) $request->request->get('email'));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->json(['success' => false, 'message' => 'Veuillez saisir une adresse email valide.'], 422);
        }
        if ($repository->findOneBy(['email' => mb_strtolower($email)]) === null) {
            $entityManager->persist((new AbonnementNewsletter())->setEmail($email));
            $entityManager->flush();
        }
        return $this->json(['success' => true, 'message' => 'Votre inscription a bien été enregistrée.']);
    }
}
