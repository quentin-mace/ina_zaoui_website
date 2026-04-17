<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Page « Qui suis-je ? » (/about) : accès et contenu principal.
 *
 * Prérequis : {@see \App\DataFixtures\UserFixtures} chargées sur la base de test.
 */
class AboutPageTest extends WebTestCase
{
    private const FIXTURE_EMAIL_GUEST_WITH_ACCESS = 'invite+1@example.com';

    private const FIXTURE_PASSWORD = 'password';

    public function testAboutPageIsReachableAndShowsTitleAndMainMessage(): void
    {
        $client = static::createClient();
        $this->logInAsFixtureGuestWithAccess($client);

        $client->request('GET', '/about');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2.about-title', 'Qui suis-je ?');
        self::assertSelectorTextContains(
            'p.about-description',
            'Ina Zaoui est une photographe globe-trotteuse, réputée pour son engagement à explorer les paysages du monde entier'
        );
        self::assertSelectorExists('img.about-img[alt="Ina Zaoui"]');
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
