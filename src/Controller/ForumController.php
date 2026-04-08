<?php

namespace App\Controller;

use App\Entity\Thread;
use App\Entity\Response as ForumResponse;
use App\Entity\User;
use App\Form\ResponseType;
use App\Form\ThreadType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/thread')]
class ForumController extends AbstractController
{
    #[Route('/', name: 'thread_index')]
    public function index(Request $request, EntityManagerInterface $em, SessionInterface $session): Response
    {
        $userRole = $session->get('user_role');

        if (!$session->get('user_id') || !in_array($userRole, ['AGRICULTEUR', 'EXPERT'])) {
            $this->addFlash('error', 'Accès réservé aux agriculteurs et experts.');
            return $this->redirectToRoute('app_home');
        }

        $title = $request->query->get('title');
        $category = $request->query->get('category');

        $qb = $em->getRepository(Thread::class)
            ->createQueryBuilder('t')
            ->orderBy('t.createdAt', 'DESC');

        if ($title) {
            $qb->andWhere('t.title LIKE :title')
               ->setParameter('title', "%$title%");
        }

        if ($category && $category !== 'Toutes les catégories') {
            $qb->andWhere('t.category = :category')
               ->setParameter('category', $category);
        }

        return $this->render('forum/index.html.twig', [
            'threads' => $qb->getQuery()->getResult(),
            'current_title' => $title,
            'current_category' => $category,
        ]);
    }

    #[Route('/new', name: 'thread_new')]
    public function new(Request $request, EntityManagerInterface $em, SessionInterface $session): Response
    {
        $userRole = $session->get('user_role');

        if (!$session->get('user_id') || !in_array($userRole, ['AGRICULTEUR', 'EXPERT'])) {
            $this->addFlash('error', 'Accès réservé.');
            return $this->redirectToRoute('app_home');
        }

        $user = $em->getRepository(User::class)->find($session->get('user_id'));

        $thread = new Thread();
        $form = $this->createForm(ThreadType::class, $thread)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $thread->setUser($user);

            $em->persist($thread);
            $em->flush();

            $this->addFlash('success', 'Sujet publié.');
            return $this->redirectToRoute('thread_index');
        }

        return $this->render('forum/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'thread_show', methods: ['GET', 'POST'])]
    public function show(int $id, Request $request, EntityManagerInterface $em, SessionInterface $session): Response
    {
        $thread = $em->getRepository(Thread::class)->find($id);

        if (!$thread) {
            throw $this->createNotFoundException();
        }

        $user = $em->getRepository(User::class)->find($session->get('user_id'));

        $responseEntity = new ForumResponse();
        $form = $this->createForm(ResponseType::class, $responseEntity)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $responseEntity->setUser($user);
            $responseEntity->setThread($thread);

            if ($parentId = $request->request->get('parent_id')) {
                $parent = $em->getRepository(ForumResponse::class)->find($parentId);
                if ($parent) {
                    $responseEntity->setParentResponse($parent);
                }
            }

            $em->persist($responseEntity);
            $em->flush();

            return $this->redirectToRoute('thread_show', ['id' => $id]);
        }

        return $this->render('forum/show.html.twig', [
            'thread' => $thread,
            'form' => $form,
        ]);
    }

    #[Route('/edit/{id}', name: 'thread_edit')]
    public function edit(int $id, Request $request, EntityManagerInterface $em, SessionInterface $session): Response
    {
        $thread = $em->getRepository(Thread::class)->find($id);

        if (!$thread || $thread->getUser()->getUserId() !== $session->get('user_id')) {
            return $this->redirectToRoute('thread_index');
        }

        $form = $this->createForm(ThreadType::class, $thread)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            return $this->redirectToRoute('thread_show', ['id' => $id]);
        }

        return $this->render('forum/new.html.twig', [
            'form' => $form,
            'edit_mode' => true,
        ]);
    }

    #[Route('/delete/{id}', name: 'thread_delete', methods: ['POST'])]
    public function delete(int $id, Request $request, EntityManagerInterface $em, SessionInterface $session): Response
    {
        $thread = $em->getRepository(Thread::class)->find($id);

        if (!$thread) {
            return $this->redirectToRoute('thread_index');
        }

        if ($this->isCsrfTokenValid('delete'.$id, $request->request->get('_token'))) {
            $em->remove($thread);
            $em->flush();
        }

        return $this->redirectToRoute('thread_index');
    }

    #[Route('/like/{id}', name: 'thread_like', methods: ['POST'])]
    public function like(int $id, EntityManagerInterface $em): Response
    {
        $thread = $em->getRepository(Thread::class)->find($id);

        if (!$thread) {
            return $this->json(['error' => 'Not found'], 404);
        }

        $thread->setLikeCount($thread->getLikeCount() + 1);
        $em->flush();

        return $this->json(['likes' => $thread->getLikeCount()]);
    }
}