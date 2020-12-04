<?php

namespace App\Tests\Controller;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Panther\PantherTestCase;

class ConferenceControllerTest extends PantherTestCase
{
    /**
     * Test index page.
     *
     * @test
     */
    public function textIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/en');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Give your feedback');
    }

    /**
     * Test page conference.
     *
     * @test
     */
    public function testConferencePage()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/en');

        $this->assertCount(2, $crawler->filter('h4'));

        $client->clickLink('View');

        $this->assertPageTitleContains('Amsterdam');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Amsterdam 2019');
        $this->assertSelectorExists('div:contains("1 comment.")');
    }

    /**
     * Test comment insertion.
     *
     * @test
     */
    public function testCommentSubmission()
    {
        $client = static::createClient();
        $client->request('GET', '/en/conference/amsterdam-2019');
        $client->submitForm('Submit', [
            'comment_type_form[author]' => 'Automated',
            'comment_type_form[text]' => 'Some feedback from an automated functionnal test',
            'comment_type_form[email]' => $email = 'me@automat.ed',
            'comment_type_form[photo]' => dirname(__DIR__, 2).'test/public/test.jpeg',
        ]);

        // simulate comment validation
        /** @var Comment $comment */
        $comment = self::$container->get(CommentRepository::class)->findOneByEmail($email);
        $comment->setState('published');
        self::$container->get(EntityManagerInterface::class)->flush();

        $this->assertResponseRedirects();
        $client->followRedirect();
        $this->assertSelectorExists('div:contains("2 comments.")');
    }
}
