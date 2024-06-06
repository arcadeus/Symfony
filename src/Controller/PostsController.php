<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\Persistence\ManagerRegistry;

class PostsController extends AbstractController
{
    /** @var PostRepository $postRepository */
    private $postRepository;
    private ManagerRegistry $doctrine;

    public function __construct(PostRepository $postRepository,  ManagerRegistry $doctrine)
    {
        $this->postRepository = $postRepository;
        $this->doctrine = $doctrine;
    }

    #[Route('/posts', name: 'app_posts')]
    public function index(): Response
    {
        $posts = $this->postRepository->findAll();

        return $this->render('posts/index.html.twig', [
            'posts' => $posts
        ]);
    }

    #[Route("/posts/new", name: "new_blog_post")]
    public function addPost(Request $request): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $em = $this->doctrine->getManager();
            $em->persist($post);
            $em->flush();

            return $this->redirectToRoute('app_posts');
        }
        return $this->render('posts/new.html.twig', [
            'page_title' => ('Добавить Post'),
            'form' => $form->createView()
        ]);
    }

    #[Route("/posts/{id}", name: "blog_show")]
    public function post(Post $post): Response
    {
        return $this->render('posts/show.html.twig', [
            'post' => $post
        ]);
    }

    #[Route("/posts/{id}/edit", name: "blog_post_edit")]
    public function edit(Post $post, Request $request)
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $em = $this->doctrine->getManager();
            $em->flush();

            return $this->redirectToRoute('blog_show', [
                'id' => $post->getId()
            ]);
        }

        return $this->render('posts/new.html.twig', [
            'page_title' => ('Редактировать Post #' . $post->getId()),
            'form' => $form->createView()
        ]);
    }
}
