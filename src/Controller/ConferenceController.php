<?php

namespace App\Controller;

use App\Entity\Conference;
use App\Repository\CommentRepository;
use App\Repository\ConferenceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class ConferenceController extends AbstractController
{
    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }
    /**
     * @Route("/", name="homepage")
     */
    public function index(ConferenceRepository $repo): Response
    {
        return new Response($this->twig->render('conference/index.html.twig', [
            'conferences' => $repo->findAll(),
        ]));
    }

    /**
     * @Route("/conference/{id}", name="conference")
     */
    public function show(Request $req, Conference $conference, CommentRepository $repo ): Response
    {
        $offset = max(0, $req->query->getInt('offset', 0));
        $paginator = $repo->getCommentPaginator($conference, $offset);
        return new Response($this->twig->render('conference/show.html.twig', [
            'conference' => $conference,
            'comments' => $paginator,
            'previous' => $offset - $repo::PAGINATOR_PER_PAGE,
            'next' => min(count($paginator), $offset + $repo::PAGINATOR_PER_PAGE),
        ]));
    }
}
