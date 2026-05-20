<?php

namespace App\Tests\Functional;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Liste des invités (/guests) : filtrage hasAccess, compteur de médias par ligne.
 *
 * Aligné sur {@see \App\DataFixtures\UserFixtures} (indices pairs sans accès) et
 * {@see \App\DataFixtures\MediaFixtures} (50 médias par invité).
 *
 * Prérequis : fixtures chargées sur la base de test.
 */
class GuestsListTest extends WebTestCase
{
    private const FIXTURE_EMAIL_GUEST_WITH_ACCESS = 'invite+1@example.com';

    private const FIXTURE_PASSWORD = 'password';

    /** Indice pair : hasAccess false dans UserFixtures, ne doit pas apparaître. */
    private const FIXTURE_DISABLED_GUEST_LINE = 'Invité 0 (';

    /** Indice impair avec accès, présent dans la liste. */
    private const FIXTURE_ACTIVE_GUEST_LINE_PREFIX = 'Invité 1 (';

    /** Médias par invité dans MediaFixtures::createGuestMedia (invités « Invité n »). */
    private const EXPECTED_MEDIA_COUNT_PER_FIXTURE_GUEST = 50;

    public function testGuestsPageListsOnlyActiveGuestsWithPerGuestMediaCount(): void
    {
        $client = static::createClient();
        $this->logInAsFixtureGuestWithAccess($client);

        $crawler = $client->request('GET', '/guests');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h3.mb-5', 'Invités');

        $content = $client->getResponse()->getContent() ?: '';
        self::assertStringNotContainsString(
            self::FIXTURE_DISABLED_GUEST_LINE,
            $content,
            'Un invité sans accès ne doit pas figurer dans la liste.'
        );
        self::assertStringContainsString(
            self::FIXTURE_ACTIVE_GUEST_LINE_PREFIX.self::EXPECTED_MEDIA_COUNT_PER_FIXTURE_GUEST.')',
            $content,
            'Un invité actif attendu (fixtures) doit apparaître avec son compteur de photos.'
        );

        $userRepository = static::getContainer()->get(UserRepository::class);
        $activeGuests = $userRepository->findBy(['admin' => false, 'hasAccess' => true]);

        $rows = $crawler->filter('.guests .guest');
        self::assertSame(
            \count($activeGuests),
            $rows->count(),
            'Une ligne par invité actif (même critère que HomeController::guests).'
        );

        $rows->each(function ($row) {
            $h4 = $row->filter('h4');
            self::assertSame(
                1,
                $h4->count(),
                'Chaque ligne d’invité doit avoir un seul titre (compteur par invité, pas de total global).'
            );
            $text = trim($h4->text());
            self::assertMatchesRegularExpression(
                '/^.+ \(\d+\)$/',
                $text,
                'Le titre de ligne doit être du type « Nom (nombre) ».'
            );
            if (str_contains($text, 'Invité ')) {
                self::assertStringContainsString(
                    '('.self::EXPECTED_MEDIA_COUNT_PER_FIXTURE_GUEST.')',
                    $text,
                    'MediaFixtures : 50 photos par invité nommé « Invité n ».'
                );
            }
        });
    }

    private function logInAsFixtureGuestWithAccess(KernelBrowser $client): void
    {
        $client->followRedirects(true);

        $crawler = $client->request('GET', '/login');
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Connexion')->form([
            '_username' => self::FIXTURE_EMAIL_GUEST_WITH_ACCESS,
            '_password' => self::FIXTURE_PASSWORD,
        ]);
        $client->submit($form);
        self::assertResponseIsSuccessful();
    }
}
