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
        $hashedPassword = $passwordHasher->hashPassword($user, $plaintextPassword);
        $user->setPassword($hashedPassword);

        $enable2fa = $form->get('enable_2fa')->getData();
        if ($enable2fa) {
            $secret = $this->googleAuthenticator->generateSecret();
            $user->setGoogleAuthenticatorSecret($secret);
            $user->setRoles(['ROLE_2FA']);
        }

        // Save user
        $entityManager->persist($user);
        $entityManager->flush();

        if ($enable2fa) {
            return $this->redirectToRoute('app_code_login', ['id' => $user->getId()]);
        }

        return $this->redirectToRoute('app_login');
    }

    return $this->render('security/register.html.twig', [
        'form' => $form->createView(),
    ]);
}
}
