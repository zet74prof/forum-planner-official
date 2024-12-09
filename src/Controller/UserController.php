<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserRolesType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/user')]
final class UserController extends AbstractController
{
    private $googleAuthenticator;

    public function __construct(GoogleAuthenticatorInterface $googleAuthenticator)
    {
        $this->googleAuthenticator = $googleAuthenticator;
    }

    #[Route(name: 'app_user_index', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(UserRolesType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserRolesType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/roles', name: 'app_user_roles', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function roles(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $roleHierarchy = ['ROLE_USER', 'ROLE_ADMIN', 'ROLE_FORUM_ORGANIZER', 'ROLE_FORUM_ATTENDEE'];
        $allRoles = $roleHierarchy;

        $form = $this->createForm(UserRolesType::class, $user, [
            'roles' => $allRoles,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_user_index');
        }

        return $this->render('user/roles.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/update_roles', name: 'app_user_update_roles', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function updateRoles(Request $request, User $user, EntityManagerInterface $entityManager): Response
{
    $roles = $request->request->all('roles');
    $user->setRoles((array)$roles);
    $entityManager->flush();

    return $this->redirectToRoute('app_user_roles', ['id' => $user->getId()]);
}

    public function enableTwoFactorAuth(User $user): void
    {
        $secret = $this->googleAuthenticator->generateSecret();
        $user->setGoogleAuthenticatorSecret($secret);
    }

    public function showQrCode(User $user): Response
    {
        $qrCode = $this->googleAuthenticator->getQRCode($user);
        return $this->render('2fa_setup.html.twig', [
            'qrCode' => $qrCode,
        ]);
    }
}