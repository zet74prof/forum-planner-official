<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class TwoFactorController extends AbstractController
{
    private GoogleAuthenticatorInterface $googleAuthenticator;

    public function __construct(GoogleAuthenticatorInterface $googleAuthenticator)
    {
        $this->googleAuthenticator = $googleAuthenticator;
    }

    #[Route('/activate-2fa/{id}', name: 'activate_2fa')]
    public function activate2FA(int $id, EntityManagerInterface $entityManager): Response
    {
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        $qrCodeContent = $this->googleAuthenticator->getQRContent($user);

        $result = Builder::create()
            ->writer(new PngWriter())
            ->writerOptions([])
            ->data($qrCodeContent)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::High)
            ->size(200)
            ->margin(0)
            ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->build();

        $qrCodeImage = base64_encode($result->getString());

        return $this->render('security/qrcode.html.twig', [
            'qrCodeImage' => $qrCodeImage,
        ]);
    }

    #[Route('/2fa/enable-confirm', name: '2fa_enable_confirm')]
    public function enableConfirm(): Response
    {
        return $this->render('security/enable_confirm.html.twig');
    }
}