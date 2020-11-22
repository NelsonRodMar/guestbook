<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Conference;
use App\Form\CommentTypeFormType;
use App\Repository\CommentRepository;
use App\Repository\ConferenceRepository;
use App\Service\SpamChecker;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ConferenceController extends AbstractController
{
    /**
     * @var EntityManagerInterface $entityManger
     */
    private $entityManager;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * ConferenceController constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param LoggerInterface        $logger
     */
    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

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

    function inc($people, $count)
    {
        $people += $count;
    }


    /**
     * @Route("/conference/{slug}", name="conference")
     *
     * @param Conference        $conference
     * @param Request           $request
     * @param CommentRepository $commentRepository
     * @param string            $photoDir
     * @param SpamChecker       $spamChecker
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function show(Conference $conference, Request $request, CommentRepository $commentRepository, string $photoDir, SpamChecker $spamChecker): Response
    {
        $offset = max(0, $request->query->getInt('offset', 0));
        $comments = $commentRepository->getCommentPaginator($conference, $offset);

        $comment = new Comment();
        $form = $this->createForm(CommentTypeFormType::class, $comment);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setConference($conference);

            if ($photo = $form['photo']->getData()) {
                $filename = bin2hex(random_bytes(6).'.'.$photo->guessExtension());
                try {
                    $photo->move($photoDir, $filename);
                    $comment->setPhotoFilename($filename);
                } catch (FileException $exception) {
                    $this->addFlash('error', $exception->getMessage());
                    $this->logger->error($exception->getMessage());
                }
            }

            $this->entityManager->persist($comment);

            $context = [
                'user_ip' => $request->getClientIp(),
                'user_agent' => $request->headers->get('user-agent'),
                'referrer' => $request->headers->get('referer'),
                'permalink' => $request->getUri(),
            ];

            if (2 === $spamChecker->getSpamScore($comment, $context)) {
                throw new \RuntimeException('Blatant spam, go away !!!');
            }

            $this->entityManager->flush();

            return $this->redirectToRoute('conference', [
                'slug' => $conference->getSlug()
            ]);
        }

        return $this->render('conference/show.html.twig',[
            'conference' => $conference,
            'comments' => $comments,
            'previous' => $offset - CommentRepository::PAGINATOR_PER_PAGE,
            'next' => min(count($comments), $offset + CommentRepository::PAGINATOR_PER_PAGE),
            'comment_form' => $form->createView(),
        ]);
    }
}
