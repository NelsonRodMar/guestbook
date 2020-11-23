<?php


namespace App\Tests\Controller;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ConferenceControllerTest extends WebTestCase
{

    /**
     * Test index page
     *
     * @test
     */
    public function textIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Give your feedback');
    }

    /**
     * Test page conference
     *
     * @test
     */
    public function testConferencePage()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertCount(2, $crawler->filter('h4'));

        $client->clickLink('View');


        $this->assertPageTitleContains('Amsterdam');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Amsterdam 2019');
        $this->assertSelectorExists('div:contains("There are 1 comments")');
    }

    /**
     * Test comment insertion
     *
     * @test
     */
    public function testCommentSubmission()
    {
      $client = static::createClient();
      $client->request('GET', '/conference/amsterdam-2019');
      $client->submitForm('Submit',[
          'comment_type_form[author]' => 'Automated',
          'comment_type_form[text]' => 'Sombe feedback from an automated functionnal test',
          'comment_type_form[email]' => 'me@automat.ed',
          'comment_type_form[photo]' => dirname(__DIR__,2).'test/public/test.jpeg',
      ]);

      $this->assertResponseRedirects();
      $client->followRedirect();
      $this->assertSelectorExists('div:contains("There are 2 comments")');
    }
}