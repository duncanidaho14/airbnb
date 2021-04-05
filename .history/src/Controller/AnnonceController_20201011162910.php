<?php

namespace App\Controller;

use App\Entity\Annonce;
use App\Entity\Image;
use App\Form\AnnonceType;
use App\Repository\AnnonceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AnnonceController extends AbstractController
{
    /**
     * @Route("/annonces", name="annonces_index")
     */
    public function index(AnnonceRepository $annonce)
    {
        return $this->render('annonce/index.html.twig', [
            'ads' => $annonce->findAll(),
        ]);
    }

    /**
     * Permet de créer une annonce
     * 
     * @Route("/annonces/new", name="annonces_create")
     * @IsGranted("ROLE_USER")
     *
     * @return void
     */
    public function create(Request $request, EntityManagerInterface $manager)
    {
        $annonce = new Annonce();
        $form = $this->createForm(AnnonceType::class, $annonce);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach($annonce->getImages() as $image) {
                $image->setAnnonce($annonce);
                $manager->persist($image);
            }
            
            $annonce->setAuthor($this->getUser());

            $manager->persist($annonce);
            $manager->flush();
            
            $this->addFlash(
                'success',
                "L'annonce <strong>{$annonce->getTitle()}</strong> a bien été enregistrée !"
            );

            return $this->redirectToRoute("annonces_show", [
                'slug' => $annonce->getSlug()
            ]);
        }
        return $this->render('annonce/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * Permet d'afficher le formulaire d'édition
     * 
     * @Route("/annonces/{slug}/edit", name="annonces_edit")
     * @Security("is_granted('ROLE_USER') and user === ad.getAuthor()", message="Cette annonce ne vous appartient pas, vous ne pouvez pas la modifier")
     * 
     * @return Response
     */
    public function edit(Annonce $ad, Request $request, EntityManagerInterface $manager)
    {

        $form = $this->createForm(AnnonceType::class, $ad);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer l'ajout de multiples images avec collectiontype
            foreach ($ad->getImages() as $image) {
                $image->setAnnonce($ad);
                $manager->persist($image);
            }
            $manager->persist($ad);
            $manager->flush();

            $this->addFlash(
                'success',
                "Les modifications de l'annonce <strong>{$ad->getTitle()}</strong> ont bien été enregistrées ! "
            );

            return $this->redirectToRoute('annonces_show', [
                'slug' => $ad->getSlug()
            ]);
        }

        return $this->render('annonce/edit.html.twig', [
            'form' => $form->createView(),
            'annonce' => $ad
        ]);
    }

    /**
     * Permet d'afficher une seule annonce
     * @Route("/annonces/{slug}", name="annonces_show")
     * 
     * @return Response
     */
    public function show(AnnonceRepository $repo, $slug):Response
    {
        // Je récupére l'annonce qui correspond au slug
        $ad = $repo->findOneBySlug($slug);

        return $this->render('annonce/show.html.twig', [
            'ad' => $ad
        ]);

    }

    /**
     * Permet de supprimer une annonce
     * 
     * @Route("/ads/{slug}/delete", name="annonces_delete")
     * @Security("is_granted('ROLE_USER') and user == ad.getAuthor()", message="Vous n'avez pas le droit d'accéder à cette ressource")
     *
     * @param Annonce $ad
     * @param ObjectManager $manager
     * @return Response
     */
    public function delete(Annonce $ad, EntityManagerInterface $manager) {
        $manager->remove($ad);
        $manager->flush();

        $this->addFlash(
            'success',
            "L'annonce <strong>{$ad->getTitle()}</strong> a bien été supprimée !"
        );

        return $this->redirectToRoute("annonces_index");
    }

}
    

