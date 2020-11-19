<?php

namespace App\Controller;

use App\Entity\Conference;
use App\Repository\CommentRepository;
use App\Repository\ConferenceRepository;
use PhpParser\Node\Expr\Clone_;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ConferenceController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     *
     * @param ConferenceRepository $conferenceRepository
     *
     * @return Response
     */
    public function index(ConferenceRepository $conferenceRepository): Response
    {
        return $this->render('conference/index.html.twig', [
            'conferences' => $conferenceRepository->findAll()
        ]);
    }

    /**
     * @Route("/conference/{id}", name="conference")
     *
     * @param Conference        $conference
     * @param Request           $request
     * @param CommentRepository $commentRepository
     *
     * @return Response
     */
    public function show(Conference $conference, Request $request, CommentRepository $commentRepository): Response
    {
        $offset = max(0, $request->query->getInt('offset', 0));
        $comments = $commentRepository->getCommentPaginator($conference, $offset);

        return $this->render('conference/show.html.twig',[
            'conference' => $conference,
            'comments' => $comments,
            'previous' => $offset - CommentRepository::PAGINATOR_PER_PAGE,
            'next' => min(count($comments), $offset + CommentRepository::PAGINATOR_PER_PAGE),
        ]);
    }
}
