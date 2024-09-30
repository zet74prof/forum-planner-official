<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserRolesType;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class ProfileSecurityController extends AbstractController
{
    private GoogleAuthenticatorInterface $googleAuthenticator;

    public function __construct(GoogleAuthenticatorInterface $googleAuthenticator)
    {
        $this->googleAuthenticator = $googleAuthenticator;
    }

    #[Route('/profil', name: 'app_profilesecurity')]
    public function profileSecurity(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        $fa = $user->getEnable2fa();

        $isverified = $user->getIsVerified();

        return $this->render('profile/security.html.twig', [
            '2fa' => $fa,
            'user' => $user,
            'isVerified' => $isverified
        ]);
    }

}