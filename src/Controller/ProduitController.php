<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class ProduitController extends AbstractController
{

    #[Route('/produits', name: 'app_produits')]
    public function produits(ManagerRegistry $doctrine): Response
    {
        // on récupere les articles
        $produits = $doctrine->getRepository(Produit::class)->findAll();

        // on verifie si on a bien tous les articles
        //dd($articles);
        
        return $this->render('produit/produits.html.twig', [
            'produits' => $produits
        ]);
    }

    #[Route('/produit_{id<\d+>}', name: 'app_produit')]

    public function produit(ManagerRegistry $doctrine , $id, Request $request): Response
    {
  // on récuper l'produit dont l'id est celui dans l'url
  $produit = $doctrine->getRepository(Produit::class)->find($id);

  //on verifie si je recupere bien un produit
//   dd($produit);

  /************** traitement des commentaires ***********************/
    //   $commentaire = new Commentaire();

    //   $form = $this->createForm(CommentaireType::class, $commentaire);

    //   $form->handleRequest($request);

    //   if( $form->isSubmitted() && $form->isValid())
    //   {
    //       // on affecte la date de cretation automatiquement 
    //       $commentaire->setDateDeCreation(new Datetime('now'))
    //                   // on lie le commentaire à l'produit en cours
    //                   ->setproduit($produit);
    //        // on envoie en bdd avec le manager
    //       $manager = $doctrine->getManager();
    //       $manager->persist($commentaire);
    //       $manager->flush();
          // on redirige vers la même page en lui indiquant le même id
          return $this->redirectToRoute('app_produit', ['id' => $id]);
      }
    


  /**************************** fin commentaires */

    //   return $this->render('produit/produit.html.twig', [
    //       'produit' => $produit,
    //       'formCommentaire' => $form->createView()
    //   ]);}



    #[Route('/produit_ajout', name: 'app_produit_ajout')]
    public function ajout(Request $request, ManagerRegistry $doctrine, SluggerInterface $slugger)
    {
        // on crée un objet produit
        $produit = new Produit(); // une instanciation de class

        // on lie produitType avec l'objet crée
        $form = $this->createForm(ProduitType::class, $produit);

        $form->handleRequest($request);

        if( $form->isSubmitted() && $form->isValid())
        {

            if($form->get('file')->getData())
            {    
                // on recupere la donnée du champ file du formulaire
                $file = $form->get('file')->getData();

                // le slug permet de transformer une chaine de caracteres ex : ('le mot clé' => 'le-mot-cle')
                // on modifie le nom de l'image en y mettant le titre sous forme de slug (sans espaces, accents...) puis un id generé tout en gardant l'extension de l'image
                $fileName = $slugger->slug($produit->getTitre()) . uniqid() . '.' . $file->guessExtension();

                try{
                    // on deplace notre image dans le dossier parametré dans config/services.yaml dans la partie parameters
                    $file->move($this->getParameter('produit_image'), $fileName );

                }catch(FileException $e)
                {
                    // gérer les exceptions en cas d'erreur durant l'upload
                }

                // on affecte fileName à l'produit pour l'enregistrer an bdd
                $produit->setImage($fileName);
            }
            
            // on affecte la date car elle ne s'ajoute pas depuis le formulaire
          
            // on recupere le manager de doctrine
            $manager = $doctrine->getManager();

            $manager->persist($produit);

            $manager->flush();
                $this->addFlash("success","le produit a bien été ajouté !");


            return $this->redirectToRoute('app_produit');
        }
    
        return $this->render('produit/formulaire.html.twig', [
            'formProduit' => $form->createView()
        ]);
    }
}



