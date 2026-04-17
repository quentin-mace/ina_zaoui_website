<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Page d’accueil front (/) pour un utilisateur connecté avec accès.
 *
 * Prérequis : {@see \App\DataFixtures\UserFixtures} chargées sur la base de test.
 */
class HomeTest extends WebTestCase
{
    private const FIXTURE_EMAIL_GUEST_WITH_ACCESS = 'invite+1@example.com';

    private const FIXTURE_PASSWORD = 'password';

    public function testHomePageShowsMainContentWhenLoggedInWithAccess(): void
    {
        $client = static::createClient();
        $this->logInAsFixtureGuestWithAccess($client);

        $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2.home-title', 'Photographe');
        self::assertSelectorExists('p.home-description');
        self::assertSelectorExists('img.home-img[alt="Ina Zaoui"]');
        self::assertSelectorExists('a[href="/portfolio"]');
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
