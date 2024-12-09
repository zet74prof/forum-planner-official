<?php

namespace App\Controller;

use App\Entity\Timeslot;
use App\Form\TimeslotType;
use App\Repository\TimeslotRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/timeslot')]
final class TimeslotController extends AbstractController
{
    #[Route(name: 'app_timeslot_index', methods: ['GET'])]
    public function index(TimeslotRepository $timeslotRepository): Response
    {
        return $this->render('timeslot/index.html.twig', [
            'timeslots' => $timeslotRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_timeslot_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $timeslot = new Timeslot();
        $form = $this->createForm(TimeslotType::class, $timeslot);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($timeslot);
            $entityManager->flush();

            return $this->redirectToRoute('app_timeslot_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('timeslot/new.html.twig', [
            'timeslot' => $timeslot,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_timeslot_show', methods: ['GET'])]
    public function show(Timeslot $timeslot): Response
    {
        return $this->render('timeslot/show.html.twig', [
            'timeslot' => $timeslot,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_timeslot_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Timeslot $timeslot, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TimeslotType::class, $timeslot);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_timeslot_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('timeslot/edit.html.twig', [
            'timeslot' => $timeslot,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_timeslot_delete', methods: ['POST'])]
    public function delete(Request $request, Timeslot $timeslot, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$timeslot->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($timeslot);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_timeslot_index', [], Response::HTTP_SEE_OTHER);
    }
}
