<?php

namespace App\Tests\Functional;

use App\Entity\User;
use App\Repository\MediaRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Fiche invité (/guest/{id}) : médias alignés sur l’utilisateur affiché.
 *
 * Prérequis : {@see \App\DataFixtures\UserFixtures}, {@see \App\DataFixtures\MediaFixtures}.
 */
class GuestPageTest extends WebTestCase
{
    private const FIXTURE_EMAIL_VIEWER = 'invite+1@example.com';

    /** Invité dont on affiche la fiche. */
    private const FIXTURE_EMAIL_SUBJECT = 'invite+1@example.com';

    /** Autre invité actif : ses fichiers ne doivent pas apparaître sur la fiche du sujet. */
    private const FIXTURE_EMAIL_OTHER_GUEST = 'invite+3@example.com';

    private const FIXTURE_PASSWORD = 'password';

    public function testGuestDetailShowsTheirMediasOnlyWithCountAndVisibleMarkers(): void
    {
        $client = static::createClient();
        $this->logInAsFixtureGuest($client);

        $userRepository = static::getContainer()->get(UserRepository::class);
        $mediaRepository = static::getContainer()->get(MediaRepository::class);

        $guest = $userRepository->findOneBy(['email' => self::FIXTURE_EMAIL_SUBJECT]);
        self::assertInstanceOf(User::class, $guest);

        $otherGuest = $userRepository->findOneBy(['email' => self::FIXTURE_EMAIL_OTHER_GUEST]);
        self::assertInstanceOf(User::class, $otherGuest);

        $expectedCount = $mediaRepository->count(['user' => $guest]);
        self::assertGreaterThan(0, $expectedCount);

        $subjectMedias = $mediaRepository->findBy(['user' => $guest], ['id' => 'ASC']);
        self::assertCount($expectedCount, $subjectMedias);

        $foreignMedia = $mediaRepository->findOneBy(['user' => $otherGuest]);
        self::assertNotNull($foreignMedia);

        $crawler = $client->request('GET', '/guest/'.$guest->getId());

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h3.mb-4', (string) $guest->getName());
        self::assertSelectorTextContains('p.mb-5', "Le maître de l'urbanité");

        $mediaImages = $crawler->filter('.col-4.media img');
        self::assertSame(
            $expectedCount,
            $mediaImages->count(),
            'Nombre de vignettes = nombre de médias Doctrine pour cet invité.'
        );

        $html = $client->getResponse()->getContent() ?: '';

        $first = $subjectMedias[0];
        $last = $subjectMedias[\count($subjectMedias) - 1];
        foreach ([$first, $last] as $media) {
            self::assertSelectorExists(
                'img[alt="'.$media->getTitle().'"]',
                'Repère visuel : le titre du média est exposé en attribut alt.'
            );
            self::assertStringContainsString(
                $media->getPath(),
                $html,
                'Repère visuel : le chemin du fichier apparaît dans le src (asset).'
            );
        }

        self::assertStringNotContainsString(
            $foreignMedia->getPath(),
            $html,
            'Aucun média de l’invité « '.(string) $otherGuest->getEmail().' » ne doit figurer sur cette fiche.'
        );
    }

    private function logInAsFixtureGuest(KernelBrowser $client): void
    {
        $client->followRedirects(true);

        $crawler = $client->request('GET', '/login');
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Connexion')->form([
            '_username' => self::FIXTURE_EMAIL_VIEWER,
            '_password' => self::FIXTURE_PASSWORD,
        ]);
        $client->submit($form);
        self::assertResponseIsSuccessful();
    }
}
