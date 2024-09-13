<?php

namespace App\Controller;

use OTPHP\TOTP;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;

class CodeLoginController extends AbstractController
{
    #[Route('/code-login', name: 'app_code_login')]
    public function codeLogin(Request $request, Security $security): Response
    {

        $user = $security->getUser();

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
