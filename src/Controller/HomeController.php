<?php

namespace App\Controller;

use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
use App\Repository\AlbumRepository;
use App\Repository\MediaRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class HomeController extends AbstractController
{
    public function __construct(
        private TagAwareCacheInterface $cache,
    ){
    }
    #[Route('/', name: 'home')]
    public function home(): Response
    {
        return $this->render('front/home.html.twig');
    }

    #[Route('/guests', name: 'guests')]
    public function guests(UserRepository $userRepository): Response
    {
        $guests = $this->cache->get('guests', function (ItemInterface $item) use ($userRepository) {
            $item->expiresAfter(3600);
            $item->tag('guests');

            return $userRepository->findAuthorizedGuests();
        });
        return $this->render('front/guests.html.twig', [
            'guests' => $guests
        ]);
    }

    #[Route('/guest/{id}', name: 'guest')]
    public function guest(int $id, UserRepository $userRepository): Response
    {
        $cacheItemName = 'guest_' . $id;
        $guest = $this->cache->get($cacheItemName, function (ItemInterface $item) use ($id, $userRepository) {
            $item->expiresAfter(3600);
            $item->tag('guests');

            return $userRepository->findWithAssociatedMedia($id);
        });

        return $this->render('front/guest.html.twig', [
            'guest' => $guest
        ]);
    }

    #[Route('/portfolio/{id}', name: 'portfolio')]
    public function portfolio(
        UserRepository $userRepository,
        AlbumRepository $albumRepository,
        MediaRepository $mediaRepository,
        ?int $id = null,
    ): Response {
        $albums = $albumRepository->findAll();
        $album = $id ? $albumRepository->find($id) : null;
        $user = $userRepository->findOneByAdmin(true);

        $inaAlbumCacheName = 'inaMedias_' . ($album?->getId() ?? 'global');

        $medias = $this->cache->get($inaAlbumCacheName, function (ItemInterface $item) use ($mediaRepository, $album, $user) {
            $item->expiresAfter(3600);
            $item->tag('ina');

            return $album
                ? $mediaRepository->findByAlbum($album)
                : $mediaRepository->findByUser($user);
        });

        return $this->render('front/portfolio.html.twig', [
            'albums' => $albums,
            'album' => $album,
            'medias' => $medias
        ]);
    }

    #[Route('/about', name: 'about')]
    public function about(): Response
    {
        return $this->render('front/about.html.twig');
    }
}
