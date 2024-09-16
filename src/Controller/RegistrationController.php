<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class RegistrationController extends AbstractController
{
    private GoogleAuthenticatorInterface $googleAuthenticator;

    public function __construct(GoogleAuthenticatorInterface $googleAuthenticator)
    {
        $this->googleAuthenticator = $googleAuthenticator;
    }

    #[Route('/register', name: 'app_registration_register')]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plaintextPassword = $user->getPassword();

            // Vérification du mot de passe avec regex
            if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $plaintextPassword)) {
                // Ajoute un message d'erreur et renvoie le formulaire
                $this->addFlash('error', 'Le mot de passe doit contenir au moins 8 caractères, dont une lettre majuscule, une lettre minuscule, un chiffre, et un caractère spécial.');
                return $this->render('security/register.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            // Hashage du mot de passe
            $hashedPassword = $passwordHasher->hashPassword($user, $plaintextPassword);
            $user->setPassword($hashedPassword);

            // Persist et flush de l'utilisateur dans la base de données
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

}