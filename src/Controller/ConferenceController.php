<?php

namespace App\Controller;


use Twig\Environment;
use DateTimeInterface;
use App\Entity\Comment;
use App\Entity\Conference;
use App\Form\CommentFormType;
use App\Repository\CommentRepository;
use App\Repository\ConferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class ConferenceController extends AbstractController
{
    private $twig;
    private $em;
    public function __construct(Environment $twig, EntityManagerInterface $em)
    {
        $this->twig = $twig;
        $this->em = $em;
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
    public function show(Request $req, Conference $conference, ConferenceRepository $repo, CommentRepository $repo1, string $photoDir): Response
    {
        //-------formulaire
        $comment = new Comment;
        $form = $this->createForm(CommentFormType::class, $comment);
        $form->handleRequest($req);
        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setConference($conference);
            //------gestion de CreatedAt----------
            $datenow = new \DateTime('now');
            $comment->setCreatedAt($datenow);
            //-------gestion photo en front-------
            if ($photo =$form['photo']->getData()) {
                $filename = bin2hex(random_bytes(6)).'.'.$photo->guessExtension();
                try {
                    $photo->move($photoDir, $filename);
                } catch (FileException $e) {
                    //enable to upload the photo, give up
                }
                $comment->setPhotoFilename($filename);
            }
        
            $this->em->persist($comment);
            $this->em->flush();
            return $this->redirectToRoute('conference', ['slug' => $conference->getSlug()]);
        }

        //-------pagination
        $offset = max(0, $req->query->getInt('offset', 0));
        $paginator = $repo1->getCommentPaginator($conference, $offset);
        return new Response($this->twig->render('conference/show.html.twig', [
            'conferences' => $repo->findAll(),
            'conference' => $conference,
            //pagination
            'comments' => $paginator,
            'previous' => $offset - $repo1::PAGINATOR_PER_PAGE,
            'next' => min(count($paginator), $offset + $repo1::PAGINATOR_PER_PAGE),
            //formulaire
            'comment_form' => $form->createView(),
        ]));
    }
}
