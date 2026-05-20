<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        private EntityManagerInterface $manager,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $this->createInaZaoui();
        $this->createGuests();

        $this->manager->flush();
    }

    private function createInaZaoui(): void
    {
        $ina = new User();
        $ina->setAdmin(true);
        $ina->setName('Ina Zaoui');
        $ina->setDescription(null);
        $ina->setEmail('ina@zaoui.com');
        $ina->setPassword($this->passwordHasher->hashPassword($ina, 'password'));
        $ina->setRoles(['ROLE_ADMIN']);

        $this->manager->persist($ina);
    }

    private function createGuests(): void
    {
        for ($i = 0; $i < 49; ++$i) {
            $guest = new User();
            $guest->setAdmin(false);
            $guest->setName("Invité $i");
            $guest->setDescription("Le maître de l'urbanité capturée, explore les méandres des cités avec un regard vif et impétueux, figeant l'énergie des rues dans des instants éblouissants. À travers une technique avant-gardiste, il métamorphose le béton et l'acier en toiles abstraites, révélant l'essence même de l'architecture moderne. Ses clichés transcendent les formes familières pour révéler des perspectives inattendues, offrant une vision nouvelle et captivante du monde urbain.");
            $guest->setEmail("invite+$i@example.com");
            $guest->setPassword($this->passwordHasher->hashPassword($guest, 'password'));
            if (0 === $i % 2) {
                $guest->setHasAccess(false);
            }
            $this->manager->persist($guest);
        }
    }
}
