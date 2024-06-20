<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class OrderTest extends WebTestCase
{
    //
    // При открытии формы заказа неавторизованным пользователем отображается страница с ошибкой
    //
    public function testForbidden(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/order');

        $this->assertResponseStatusCodeSame(401, 'Страница заказа должна быть доступна только авторизованным пользователям');
        $this->assertSelectorTextContains('h1', '401', 'Заголовок сообщения об ошибке доступа должен содержать "401"');
    }
}
