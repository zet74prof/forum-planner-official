<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TwoFactorController extends AbstractController
{

    public function __construct(GoogleAuthenticatorInterface $googleAuthenticator, EntityManagerInterface $entityManager)
    {
        $this->googleAuthenticator = $googleAuthenticator;
        $this->entityManager = $entityManager;
    }

    #[Route('/enable-2fa', name: 'app_2fa_setup')]
    public function setup2FA(Request $request): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('Utilisateur non authentifié.');
        }

        // Générer le secret si ce n'est pas déjà fait
        if (!$user->getGoogleAuthenticatorSecret()) {
            $secret = $this->googleAuthenticator->generateSecret();
            $user->setGoogleAuthenticatorSecret($secret);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        $qrCodeContent = $this->googleAuthenticator->getQRContent($user);

        $result = Builder::create()
            ->writer(new PngWriter())
            ->data($qrCodeContent)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::High)
            ->size(200)
            ->margin(10)
            ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->build();

        $qrCodeUrl = $result->getDataUri();

        return $this->render('security/2fa_setup.html.twig', [
            'qrCodeUrl' => $qrCodeUrl,
        ]);
    }

    #[Route('/disable-2fa', name: 'app_2fa_disable')]
    public function disable2FA(Request $request): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('Utilisateur non authentifié.');
        }

        $user->setGoogleAuthenticatorSecret(null);
        $user->setEnable2fa(false);
        $this->entityManager->flush();

        $this->addFlash('success', 'L\'authentification à deux facteurs a été désactivée.');

        return $this->redirectToRoute('app_profilesecurity');
    }

    #[Route('/check-2fa', name: 'app_2fa_check', methods: ['POST'])]
    public function check2FA(Request $request): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('Utilisateur non authentifié.');
        }

        $code = $request->request->get('auth_code');

        if (empty($code)) {
            $this->addFlash('error', 'Veuillez entrer le code de vérification.');
            return $this->redirectToRoute('app_2fa_setup');
        }

        if ($this->googleAuthenticator->checkCode($user, (string)$code)) {
            $user->setEnable2fa(true);
            $this->entityManager->flush();
            $this->addFlash('success', 'L\'authentification à deux facteurs a été activée avec succès.');
            return $this->redirectToRoute('app_profilesecurity');
        } else {
            $this->addFlash('error', 'Le code de vérification est incorrect.');
            return $this->redirectToRoute('app_2fa_setup');
        }
    }
}
