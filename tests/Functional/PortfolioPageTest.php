<?php

namespace App\Tests\Functional;

use App\Entity\Album;
use App\Entity\User;
use App\Repository\AlbumRepository;
use App\Repository\MediaRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Portfolio : vue « toutes les photos » (médias d’Ina) vs filtre par album, navigation et cohérence du contenu.
 *
 * Prérequis : {@see \App\DataFixtures\AlbumFixtures}, {@see \App\DataFixtures\MediaFixtures} (createInaMedia).
 */
class PortfolioPageTest extends WebTestCase
{
    private const FIXTURE_EMAIL_VIEWER = 'invite+1@example.com';

    private const FIXTURE_PASSWORD = 'password';

    public function testGlobalViewShowsAllInaMediasAndHighlightsToutes(): void
    {
        $client = static::createClient();
        $this->logInAsFixtureGuest($client);

        $ina = $this->getInaUser();
        $mediaRepository = static::getContainer()->get(MediaRepository::class);
        $expectedMedias = $mediaRepository->findBy(['user' => $ina]);
        self::assertGreaterThan(0, \count($expectedMedias));

        $crawler = $client->request('GET', '/portfolio');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h3.mb-4', 'Portfolio');

        $this->assertPortfolioMediaCountMatches($crawler, \count($expectedMedias));

        $toutes = $crawler->selectLink('Toutes');
        self::assertCount(1, $toutes, 'Lien « Toutes les photos » présent.');
        self::assertStringContainsString('active', (string) $toutes->attr('class'), 'Vue globale : onglet « Toutes » actif.');

        $albumRepository = static::getContainer()->get(AlbumRepository::class);
        foreach ($albumRepository->findAll() as $album) {
            self::assertCount(1, $crawler->selectLink((string) $album->getName()), 'Chaque album est accessible depuis la barre de navigation.');
        }

        $html = $client->getResponse()->getContent() ?: '';
        foreach (array_slice($expectedMedias, 0, 3) as $media) {
            self::assertStringContainsString($media->getPath(), $html, 'Un média attendu (Ina) est rendu sur la vue globale.');
        }
    }

    public function testAlbumViewShowsOnlyAlbumMediasAndHighlightsThatAlbum(): void
    {
        $client = static::createClient();
        $this->logInAsFixtureGuest($client);

        $ina = $this->getInaUser();
        $albumRepository = static::getContainer()->get(AlbumRepository::class);
        $mediaRepository = static::getContainer()->get(MediaRepository::class);

        $album1 = $albumRepository->findOneBy(['name' => 'Album 1']);
        self::assertInstanceOf(Album::class, $album1);
        $album2 = $albumRepository->findOneBy(['name' => 'Album 2']);
        self::assertInstanceOf(Album::class, $album2);

        $album1Medias = $mediaRepository->findBy(['album' => $album1]);
        $album2Medias = $mediaRepository->findBy(['album' => $album2]);
        self::assertNotEmpty($album1Medias);
        self::assertNotEmpty($album2Medias);

        $foreign = $album2Medias[0];
        $inaGlobalCount = \count($mediaRepository->findBy(['user' => $ina]));

        $crawler = $client->request('GET', '/portfolio/'.$album1->getId());

        self::assertResponseIsSuccessful();

        $this->assertPortfolioMediaCountMatches($crawler, \count($album1Medias));

        $html = $client->getResponse()->getContent() ?: '';
        self::assertStringNotContainsString(
            $foreign->getPath(),
            $html,
            'Un média d’un autre album ne doit pas apparaître dans le contexte « Album 1 ».'
        );

        $toutes = $crawler->selectLink('Toutes');
        self::assertStringNotContainsString('active', (string) $toutes->attr('class'), 'Vue album : « Toutes » n’est pas l’onglet actif.');

        $album1Tab = $crawler->selectLink('Album 1');
        self::assertStringContainsString('active', (string) $album1Tab->attr('class'), 'L’album courant est mis en avant.');

        self::assertLessThan(
            $inaGlobalCount,
            \count($album1Medias),
            'Prérequis test : le filtre par album doit montrer moins de médias que la vue globale.'
        );
    }

    public function testNavigationBetweenGlobalAndAlbumContexts(): void
    {
        $client = static::createClient();
        $this->logInAsFixtureGuest($client);

        $albumRepository = static::getContainer()->get(AlbumRepository::class);
        $album3 = $albumRepository->findOneBy(['name' => 'Album 3']);
        self::assertInstanceOf(Album::class, $album3);

        $crawler = $client->request('GET', '/portfolio');
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('active', (string) $crawler->selectLink('Toutes')->attr('class'));

        $client->click($crawler->selectLink('Album 3')->link());
        self::assertResponseIsSuccessful();
        $crawler = $client->getCrawler();
        self::assertStringContainsString('active', (string) $crawler->selectLink('Album 3')->attr('class'));
        self::assertStringNotContainsString('active', (string) $crawler->selectLink('Toutes')->attr('class'));

        $client->click($crawler->selectLink('Toutes')->link());
        self::assertResponseIsSuccessful();
        $crawler = $client->getCrawler();
        self::assertStringContainsString('active', (string) $crawler->selectLink('Toutes')->attr('class'));
        self::assertStringNotContainsString('active', (string) $crawler->selectLink('Album 3')->attr('class'));
    }

    private function assertPortfolioMediaCountMatches(Crawler $crawler, int $expected): void
    {
        self::assertSame(
            $expected,
            $crawler->filter('.col-4.media img')->count(),
            'Nombre de vignettes portfolio aligné sur le contexte (sans imposer d’ordre d’affichage).'
        );
    }

    private function getInaUser(): User
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        $ina = $userRepository->findOneByAdmin(true);
        self::assertInstanceOf(User::class, $ina);

        return $ina;
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
