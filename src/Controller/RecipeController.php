<?php

namespace App\Controller;

use App\Repository\RecipeRepository;
use App\Entity\Recipe;
use App\Form\RecipeType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class RecipeController extends AbstractController
{
    /**
     * This controller display all recipes
     *
     * @param RecipeRepository $repository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    #[Route('/recette', name: 'recipe', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(RecipeRepository $repository ,PaginatorInterface $paginator, Request $request): Response
    {
        $recipes = $paginator->paginate(
            $repository->findBy(['user' => $this->getUser()]),
            $request->query->getInt('page', 1),
            10
        );
        return $this->render('pages/recipe/index.html.twig', [
            'recipes' => $recipes,
        ]);
    }
    

    /**
     * This controller allow us to create a new recipe
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/recette/nouveau', name: 'recipe.new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $manager): Response
    {
        $recipe = new Recipe();
        $form = $this->createForm(RecipeType::class, $recipe);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $recipe = $form->getData();
            $recipe->setUser($this->getUser());
            $manager->persist($recipe);
            $manager->flush();
            $this->addFlash('success', 'La recette a bien été ajoutée');
            return $this->redirectToRoute('recipe');
        }

        return $this->render('pages/recipe/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    /**
     * Edit a Recipe
     *
     * @param Recipe $recipe
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response   
     */
    #[Security('is_granted("ROLE_USER") and user === recipe.getUser()')]
    #[Route('/recette/edition/{id}', name: 'recipe.edit', methods: ['GET', 'POST'])]

    public function edit(Recipe $recipe, Request $request, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(RecipeType::class, $recipe);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $recipe = $form->getData();
            $manager->persist($recipe);
            $manager->flush();
            $this->addFlash('success', 'La recette a bien été modifié');
            return $this->redirectToRoute('recipe');
        }
        return $this->render('pages/recipe/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }
    /**
     * Delete a recipe
     *
     * @param Recipe $recipe
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/recette/suppression/{id}', name: 'recipe.delete', methods: ['GET'])]
    public function delete(Recipe $recipe, EntityManagerInterface $manager): Response
    {
        if (!$recipe) {
            $this->addFlash('warning', 'La recette n\'existe pas');
            return $this->redirectToRoute('recipe');
        }
        $manager->remove($recipe);
        $manager->flush();
        $this->addFlash('success', 'La recette a bien été supprimé');
        return $this->redirectToRoute('recipe');
    }
}