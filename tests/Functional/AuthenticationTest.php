<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Parcours d’authentification front (formulaire /login), sans couvrir l’admin.
 *
 * Données attendues : {@see \App\DataFixtures\UserFixtures} (même mot de passe pour tous les comptes).
 * Prérequis : base de test à jour + `doctrine:fixtures:load` (ou équivalent) sur cette base.
 */
class AuthenticationTest extends WebTestCase
{
    private const FIXTURE_PASSWORD = 'password';

    private const FIXTURE_EMAIL_ADMIN = 'ina@zaoui.com';

    /** Invité impair dans UserFixtures : accès conservé (hasAccess par défaut). */
    private const FIXTURE_EMAIL_GUEST_WITH_ACCESS = 'invite+1@example.com';

    /** Invité pair (indice 0) dans UserFixtures : hasAccess false. */
    private const FIXTURE_EMAIL_GUEST_NO_ACCESS = 'invite+0@example.com';

    public function testAdminCanLogInAndReachFrontHome(): void
    {
        $client = static::createClient();

        $this->submitLoginForm($client, self::FIXTURE_EMAIL_ADMIN, self::FIXTURE_PASSWORD);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2.home-title', 'Photographe');
        self::assertSame('/', $client->getRequest()->getPathInfo());
    }

    public function testGuestWithAccessCanLogInAndReachFrontHome(): void
    {
        $client = static::createClient();

        $this->submitLoginForm($client, self::FIXTURE_EMAIL_GUEST_WITH_ACCESS, self::FIXTURE_PASSWORD);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2.home-title', 'Photographe');
    }

    public function testGuestWithoutAccessSeesDisabledAccountMessage(): void
    {
        $client = static::createClient();

        $this->submitLoginForm($client, self::FIXTURE_EMAIL_GUEST_NO_ACCESS, self::FIXTURE_PASSWORD);

        self::assertResponseIsSuccessful();
        self::assertStringContainsString(
            'Ce compte a été désactivé',
            $client->getResponse()->getContent() ?: ''
        );
    }

    public function testUnknownEmailShowsAuthenticationError(): void
    {
        $client = static::createClient();

        $this->submitLoginForm(
            $client,
            'aucun-compte-associe@example.test',
            self::FIXTURE_PASSWORD
        );

        self::assertResponseIsSuccessful();
        self::assertGreaterThan(0, $client->getCrawler()->filter('.alert-danger')->count());
    }

    public function testWrongPasswordShowsAuthenticationError(): void
    {
        $client = static::createClient();

        $this->submitLoginForm(
            $client,
            self::FIXTURE_EMAIL_GUEST_WITH_ACCESS,
            'not-the-fixture-password'
        );

        self::assertResponseIsSuccessful();
        self::assertGreaterThan(0, $client->getCrawler()->filter('.alert-danger')->count());
    }

    private function submitLoginForm(KernelBrowser $client, string $email, string $password): void
    {
        $client->followRedirects(true);

        $crawler = $client->request('GET', '/login');
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Connexion')->form([
            '_username' => $email,
            '_password' => $password,
        ]);
        $client->submit($form);
    }
}
