<?php

namespace App\Controller;

use App\Entity\Annonce;
use App\Form\AnnonceType;
use App\Repository\AnnonceRepository;
use App\Service\Pagination;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminAnnonceController extends AbstractController
{
    /**
     * @Route("/admin/annonces/{page<\d+>?1}", name="admin_annonces_index")
     */
    public function index(AnnonceRepository $repo, $page, Pagination $pagination)
    {
        $pagination->setEntityClass(Annonce::class)
                    ->setPage($page);
        return $this->render('admin/annonce/index.html.twig', [
            'pagination' => $pagination
        ]);
    }

    /**
     * Permet d'afficher le formulaire d'édition de la zone d'administration
     * 
     * @Route("/admin/annonces/{id}/edit", name="admin_annonces_edit") 
     * 
     * @param Annonce $ad
     * @return Response
     */
    public function edit(Annonce $ad, Request $request, EntityManagerInterface $manager)
    {
        $form = $this->createForm(AnnonceType::class, $ad);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($ad);
            $manager->flush();

            $this->addFlash(
                'success',
                "L'annonce <strong>{$ad->getTitle()}</strong> a bien été enregistrée ! "
            );
        }
        return $this->render('admin/annonce/edit.html.twig', [
            'ad' => $ad,
            'form' => $form->createView()
        ]);
    }

    /**
     * Permet de supprimer une annonce
     * 
     * @Route("/admin/annonces/{id}/delete", name="admin_annonces_delete")
     *
     * @param Annonce $ad
     * @param EntityManagerInterface $manager
     * @return void
     **/
    public function delete(Annonce $ad, EntityManagerInterface $manager)
    {
        if(count($ad->getBookings()) > 0)
        {
            $this->addFlash(
                'warning',
                "Vous ne pouvez pas supprimer l'annonce <strong>{$ad->getTitle()}</strong> car elle possède déjà des réservations !"
            );
        } else {
            $manager->remove($ad);
            $manager->flush();

            $this->addFlash(
                    'success',
                    "L'annonce <strong>{$ad->getTitle()}</strong> a bien été supprimée !"
            );

        }
        
        return $this->redirectToRoute('admin_annonces_index');
    }
}
