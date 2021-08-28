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
     * @Route("/conference/{slug}", name="conference")
     */
    public function show(Request $req, Conference $conference, ConferenceRepository $repo, CommentRepository $repo1 ): Response
    {
        $offset = max(0, $req->query->getInt('offset', 0));
        $paginator = $repo1->getCommentPaginator($conference, $offset);
        return new Response($this->twig->render('conference/show.html.twig', [
            'conferences' => $repo->findAll(),
            'conference' => $conference,
            'comments' => $paginator,
            'previous' => $offset - $repo1::PAGINATOR_PER_PAGE,
            'next' => min(count($paginator), $offset + $repo1::PAGINATOR_PER_PAGE),
        ]));
    }
}
