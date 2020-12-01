<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Conference;
use App\Form\CommentTypeFormType;
use App\Message\CommentMessage;
use App\Repository\CommentRepository;
use App\Repository\ConferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ConferenceController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var MessageBusInterface
     */
    private $bus;

    /**
     * @var NotifierInterface
     */
    private $notifier;

    /**
     * ConferenceController constructor.
     */
    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger, MessageBusInterface $bus, NotifierInterface $notifier)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->bus = $bus;
        $this->notifier = $notifier;
    }

    /**
     * @Route("/", name="homepage")
     */
    public function index(ConferenceRepository $conferenceRepository): Response
    {
        $response = $this->render('conference/index.html.twig', [
            'conferences' => $conferenceRepository->findAll(),
        ]);
        $response->setSharedMaxAge(3600);

        return $response;
    }

    /**
     * @Route("/conference_header", name="conference_header")
     */
    public function conferenceHeader(): Response
    {
        $response = $this->render('conference/header.html.twig', [
            'conferences' => $this->getDoctrine()->getRepository(Conference::class)->findAll(),
        ]);
        $response->setPublic();
        $response->setSharedMaxAge(3600);

        return $response;
    }

    /**
     * @Route("/conference/{slug}", name="conference")
     *
     * @throws \Exception
     */
    public function show(Conference $conference, Request $request, CommentRepository $commentRepository, string $photoDir): Response
    {
        $offset = max(0, $request->query->getInt('offset', 0));
        $comments = $commentRepository->getCommentPaginator($conference, $offset);

        $comment = new Comment();
        $form = $this->createForm(CommentTypeFormType::class, $comment);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setConference($conference);

            if ($photo = $form['photo']->getData()) {
                $filename = bin2hex(random_bytes(6)).'.'.$photo->guessExtension();
                try {
                    $photo->move($photoDir, $filename);
                    $comment->setPhotoFilename($filename);
                } catch (FileException $exception) {
                    $this->addFlash('error', $exception->getMessage());
                    $this->logger->error($exception->getMessage());
                }
            }

            $this->entityManager->persist($comment);
            $this->entityManager->flush();

            $context = [
                'user_ip' => $request->getClientIp(),
                'user_agent' => $request->headers->get('user-agent'),
                'referrer' => $request->headers->get('referer'),
                'permalink' => $request->getUri(),
            ];

            $this->notifier->send(new Notification(
                'Thank you for the feedback; your comment will be posted after moderation',
                ['browser']
            ));

            $reviewUrl = $this->generateUrl(
                'review_comment',
                ['id' => $comment->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $this->bus->dispatch(new CommentMessage($comment->getId(), $reviewUrl, $context));

            return $this->redirectToRoute('conference', [
                'slug' => $conference->getSlug(),
            ]);
        }

        if ($form->isSubmitted()) {
            $this->notifier->send(new Notification(
                'Can you check your submission ? There are some problems with it.',
                ['browser']
            ));
        }

        return $this->render('conference/show.html.twig', [
            'conference' => $conference,
            'comments' => $comments,
            'previous' => $offset - CommentRepository::PAGINATOR_PER_PAGE,
            'next' => min(count($comments), $offset + CommentRepository::PAGINATOR_PER_PAGE),
            'comment_form' => $form->createView(),
        ]);
    }
}
