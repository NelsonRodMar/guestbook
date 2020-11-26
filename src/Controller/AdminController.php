<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Message\CommentMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpCache\HttpCache;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Workflow\Registry;

/**
 * Class AdminController.
 *
 * @Route("/admin")
 */
class AdminController extends AbstractController
{
    /**
     * @var MessageBusInterface
     */
    private $bus;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(MessageBusInterface $bus, EntityManagerInterface $entityManager)
    {
        $this->bus = $bus;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/comment/review/{id}", name="review_comment")
     */
    public function reviewComment(Request $request, Comment $comment, Registry $registry): Response
    {
        $accepted = !$request->query->get('reject');

        $machine = $registry->get($comment);
        if ($machine->can($comment, 'publish')) {
            $transition = $accepted ? 'publish' : 'reject';
        } elseif ($machine->can($comment, 'publish_ham')) {
            $transition = $accepted ? 'publish_ham' : 'reject_ham';
        } else {
            return new Response('Comment already reviewed or not in the right stated.');
        }

        $machine->apply($comment, $transition);
        $this->entityManager->flush();

        if ($accepted) {
            $this->bus->dispatch(new CommentMessage($comment->getId()));
        }

        return $this->render('admin/review.html.twig', [
            'transition' => $transition,
            'comment' => $comment,
        ]);
    }

    /**
     * @Route("/http-cache/{uri<.*>}", methods={"PURGE"})
     *
     * @param KernelInterface $kernel
     * @param Request $request
     * @param string $uri
     *
     * @return Response
     */
    public function purgeHttpCache(KernelInterface $kernel, Request $request, string $uri): Response
    {
        if ('prod' === $kernel->getEnvironment()) {
            return new Response('KO', 400);
        }

        $store = (new class($kernel) extends HttpCache {
        })->getStore();
        $store->purge($request->getSchemeAndHttpHost().'/'.$uri);

        return new Response('Done');
    }
}
