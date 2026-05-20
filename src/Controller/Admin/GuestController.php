<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\AdminGuestType;
use App\Repository\UserRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class GuestController extends AbstractController
{
    #[Route('/admin/guest', name: 'admin_guest_index')]
    public function index(UserRepository $userRepository): Response
    {
        $guests = $userRepository->findBy(['admin' => false], ['email' => 'ASC']);

        return $this->render('admin/guest/index.html.twig', [
            'guests' => $guests,
        ]);
    }

    #[Route('/admin/guest/add', name: 'admin_guest_add')]
    public function add(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
    ): Response {
        $guest = new User();
        $form = $this->createForm(AdminGuestType::class, $guest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $guest->setAdmin(false);
                $guest->setRoles([]);
                $plain = (string) $form->get('plainPassword')->getData();
                $guest->setPassword($passwordHasher->hashPassword($guest, $plain));
                $entityManager->persist($guest);
                $entityManager->flush();
            } catch (UniqueConstraintViolationException) {
                $this->addFlash('danger', 'Un compte avec cet email existe déjà.');

                return $this->render('admin/guest/add.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            $this->addFlash('success', 'Invité créé.');

            return $this->redirectToRoute('admin_guest_index');
        }

        return $this->render('admin/guest/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/guest/update/{id}', name: 'admin_guest_update')]
    public function update(
        Request $request,
        int $id,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
    ): Response {
        $guest = $this->getGuestOr404($id, $userRepository);
        $form = $this->createForm(AdminGuestType::class, $guest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $guest->setAdmin(false);
                $guest->setRoles([]);
                $plain = $form->get('plainPassword')->getData();
                if (\is_string($plain) && '' !== $plain) {
                    $guest->setPassword($passwordHasher->hashPassword($guest, $plain));
                }
                $entityManager->flush();
            } catch (UniqueConstraintViolationException) {
                $this->addFlash('danger', 'Un compte avec cet email existe déjà.');

                return $this->render('admin/guest/update.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            $this->addFlash('success', 'Invité modifié.');

            return $this->redirectToRoute('admin_guest_index');
        }

        return $this->render('admin/guest/update.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/guest/delete/{id}', name: 'admin_guest_delete')]
    public function delete(int $id, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $guest = $this->getGuestOr404($id, $userRepository);

        $paths = [];
        foreach ($guest->getMedias()->toArray() as $media) {
            $paths[] = $media->getPath();
            $entityManager->remove($media);
        }

        $mediaCount = \count($paths);
        $entityManager->remove($guest);
        $entityManager->flush();

        foreach ($paths as $path) {
            if (is_file($path)) {
                @unlink($path);
            }
        }

        if ($mediaCount > 0) {
            $this->addFlash('success', sprintf(
                'Invité supprimé. %d média(s) associé(s) ont également été supprimé(s).',
                $mediaCount,
            ));
        } else {
            $this->addFlash('success', 'Invité supprimé.');
        }

        return $this->redirectToRoute('admin_guest_index');
    }

    private function getGuestOr404(int $id, UserRepository $userRepository): User
    {
        $user = $userRepository->find($id);
        if (!$user instanceof User || $user->isAdmin()) {
            throw $this->createNotFoundException();
        }

        return $user;
    }
}
