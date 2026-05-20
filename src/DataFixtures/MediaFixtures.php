<?php

namespace App\DataFixtures;

use App\Entity\Media;
use App\Repository\AlbumRepository;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

class MediaFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private AlbumRepository $albumRepository,
        private UserRepository $userRepository,
        private EntityManagerInterface $manager,
    ) {
    }

    public function getDependencies(): array
    {
        return [
            AlbumFixtures::class,
            UserFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $this->createInaMedia();
        $this->createGuestMedia();

        $this->manager->flush();
    }

    private function createInaMedia(): void
    {
        $albums = $this->albumRepository->findAll();
        $ina = $this->userRepository->findOneBy(['name' => 'Ina Zaoui']);
        $counter = 0;

        foreach ($albums as $album) {
            for ($i = 0; $i < 10; ++$i) {
                $uploadNumber = str_pad(strval($counter + 1), 4, '0', STR_PAD_LEFT); // Make sure the number has four digits (ex: 0025)

                $media = new Media();
                $media->setUser($ina);
                $media->setAlbum($album);
                $media->setTitle("Titre $counter");
                $media->setPath("uploads/$uploadNumber.jpg");

                $this->manager->persist($media);
                ++$counter;
            }
        }
    }

    private function createGuestMedia(): void
    {
        $guests = $this->userRepository->findBy(['admin' => false]);
        $uploadCounter = 51;
        foreach ($guests as $guest) {
            for ($i = 0; $i < 50; ++$i) {
                $uploadNumber = str_pad(strval($uploadCounter), 4, '0', STR_PAD_LEFT); // Make sure the number has four digits (ex: 0063)

                $media = new Media();
                $media->setUser($guest);
                $media->setAlbum(null);
                $media->setTitle("Titre $i");
                $media->setPath("uploads/$uploadNumber.jpg");

                $this->manager->persist($media);
                ++$uploadCounter;
            }
        }
    }
}
