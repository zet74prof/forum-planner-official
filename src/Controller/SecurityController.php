<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    private GoogleAuthenticatorInterface $googleAuthenticator;
    private EntityManagerInterface $entityManager;
    private Security $security;

    public function __construct(GoogleAuthenticatorInterface $googleAuthenticator, EntityManagerInterface $entityManager, Security $security)
    {
        $this->googleAuthenticator = $googleAuthenticator;
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $user = $this->security->getUser();

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
        /** @var User $user */
        $user = $this->getUser();

        if (!$user->getGoogleAuthenticatorSecret()) {
            $secret = $this->googleAuthenticator->generateSecret();
            $user->setGoogleAuthenticatorSecret($secret);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        if ($request->isMethod('POST')) {
            $code = $request->request->get('auth_code');
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

        return $this->render('security/codelogin.html.twig', [
            'qrCodeUrl' => $qrCodeUrl,
        ]);
    }

    public function __invoke(Request $request): Response
    {
        return $this->codeLogin($request);
    }
}