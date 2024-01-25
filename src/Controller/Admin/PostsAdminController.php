<?php

namespace App\Controller\Admin;

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

#[Route('/admin/posts')]
class PostsAdminController extends AbstractController
{

    #[Route('/list/{type?}/{id?}', name: 'admin_posts_index', methods: ['GET'])]
    public function index(
        PostsRepository $postsRepository,
                        $type,
                        $id
    ): Response
    {
        if($type !== null) {
            $fkType = ($type == 'user') ? 'fk_user' : 'fk_team';
        }
        if($id !== null && $type !== null) {
            $posts = $postsRepository->findBy([$fkType => $id]);
        } elseif($id === null && $type !== null) {
            $posts = $postsRepository->findAllByType($fkType);
        } elseif($id === null && $type === null) {
            $posts = $postsRepository->findAll();
        }
        return $this->render('admin/posts/index.html.twig', [
            'posts' => $posts,
            'sidebar' => 'redac'
        ]);
    }

    #[Route('/new', name: 'admin_posts_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        FileUploaderService $fileUploader,
        Security $security,
    ): Response
    {
        $post = new Posts();
        $form = $this->createForm(PostsType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form['picture']->getData();
            if ($file) {
                $fileName = $fileUploader->upload($file);
                if (null !== $fileName) {
                    $post->setPicture($fileName);
                }
            }
            $post->setFkTeam($security->getUser());

            $entityManager->persist($post);
            $entityManager->flush();

            return $this->redirectToRoute('admin_posts_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/posts/new.html.twig', [
            'post' => $post,
            'form' => $form,
            'sidebar' => 'redac'
        ]);
    }

    #[Route('/{id}', name: 'admin_posts_show', methods: ['GET'])]
    public function show(Posts $post): Response
    {
        return $this->render('admin/posts/show.html.twig', [
            'post' => $post,
            'sidebar' => 'redac'
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_posts_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Posts $post, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PostsType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('admin_posts_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('posts/edit.html.twig', [
            'post' => $post,
            'form' => $form,
            'sidebar' => 'redac'
        ]);
    }

    #[Route('/{id}', name: 'admin_posts_delete', methods: ['POST'])]
    public function delete(Request $request, Posts $post, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $entityManager->remove($post);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_posts_index', [], Response::HTTP_SEE_OTHER);
    }
}