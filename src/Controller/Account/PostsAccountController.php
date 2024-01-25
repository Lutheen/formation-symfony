<?php

namespace App\Controller\Account;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\FileUploaderService;
use App\Repository\PostsRepository;
use App\Form\PostsType;
use App\Entity\Posts;

#[Route('/account/posts')]
class PostsAccountController extends AbstractController
{
    #[Route('/list', name: 'account_posts_index', methods: ['GET'])]
    public function index(
        PostsRepository $postsRepository,
        Security $security
    ): Response
    {
        return $this->render('account/posts/index.html.twig', [
            'posts' => $postsRepository->findBy(['fk_user' => $security->getUser()]),
        ]);
    }

    #[Route('/new', name: 'account_posts_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        FileUploaderService $fileUploader,
        Security $security,
    ): Response
    {
        $post = new Posts();
        $form = $this->createForm(PostsType::class, $post, [
            'date' => new \DateTime(date('Y-m-d'))
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form['picture']->getData();
            if ($file) {
                $fileName = $fileUploader->upload($file);
                if (null !== $fileName) {
                    $post->setPicture($fileName);
                }
            }
            $post->setFkUser($security->getUser());
            $entityManager->persist($post);
            $entityManager->flush();

            return $this->redirectToRoute('account_posts_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('account/posts/new.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'account_posts_show', methods: ['GET'])]
    public function show(Posts $post): Response
    {
        return $this->render('account/posts/show.html.twig', [
            'post' => $post,
        ]);
    }

    #[Route('/{id}/edit', name: 'account_posts_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Posts $post, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PostsType::class, $post, [
            'date' => $post->getDateAdd()
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('account_posts_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('account/posts/edit.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'account_posts_delete', methods: ['POST'])]
    public function delete(Request $request, Posts $post, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $entityManager->remove($post);
            $entityManager->flush();
        }

        return $this->redirectToRoute('account_posts_index', [], Response::HTTP_SEE_OTHER);
    }
}