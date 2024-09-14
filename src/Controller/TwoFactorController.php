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
use OTPHP\TOTP;
use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\User;

class TwoFactorController extends AbstractController
{
    private $security;
    private GoogleAuthenticatorInterface $googleAuthenticator;
    private EntityManagerInterface $entityManager;

    public function __construct(GoogleAuthenticatorInterface $googleAuthenticator, EntityManagerInterface $entityManager, Security $security)
    {
        $this->googleAuthenticator = $googleAuthenticator;
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    #[Route('/code-login', name: 'app_code_login')]
    public function codeLogin(Request $request): Response
    {
        $user = $this->security->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('User not logged in.');
        }

        if (!$user->getGoogleAuthenticatorSecret()) {
            $secret = $this->googleAuthenticator->generateSecret();
            $user->setGoogleAuthenticatorSecret($secret);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        } else {
            $secret = $user->getGoogleAuthenticatorSecret();
        }

        // Débogage: Affiche le secret
        dump($secret);

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

        // Débogage: Affiche l'URL du QR code
        dump($qrCodeUrl);

        if ($request->isMethod('POST')) {
            $code = $request->request->get('auth_code');

            // Débogage: Affiche le code saisi
            dump($code);

            if ($this->googleAuthenticator->checkCode($user, $code)) {
                $user->setEnable2fa(true);
                $this->entityManager->persist($user);
                $this->entityManager->flush();
                $this->addFlash('success', '2FA activée avec succès!');
                return $this->redirectToRoute('app_user_index');
            } else {
                $this->addFlash('error', 'Le code est incorrect, veuillez réessayer.');
            }
        }

        return $this->render('security/codelogin.html.twig', [
            'qrCodeUrl' => $qrCodeUrl,
        ]);
    }

}
