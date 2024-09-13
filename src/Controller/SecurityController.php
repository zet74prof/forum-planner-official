<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use OTPHP\TOTP;

class SecurityController extends AbstractController
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, Security $security): Response
    {
        $user = $security->getUser();

        if ($user && $user->isGoogleAuthenticatorEnabled()) {
            return $this->redirectToRoute('app_code_login');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/code-login', name: 'app_code_login')]
    public function codeLogin(Request $request): Response
    {
        $user = $this->security->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('User not logged in.');
        }

        $secret = $user->getGoogleAuthenticatorSecret();

        if ($request->isMethod('POST')) {
            $code = $request->request->get('code');

            $totp = TOTP::create($secret);

            if ($totp->verify($code)) {
                return $this->redirectToRoute('home');
            } else {
                $this->addFlash('error', 'Invalid code.');
            }
        }

        return $this->render('security/codelogin.html.twig');
    }
}