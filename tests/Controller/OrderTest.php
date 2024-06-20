<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\User\InMemoryUser;

class OrderTest extends WebTestCase
{
    //
    private function login()
    {
        $client = static::createClient();
        $user = new InMemoryUser('user', '123', ['ROLE_USER']);
        $client->loginUser($user);
        return $client;
    }

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

    //
    // Авторизованному пользователю отображается форма, проверить, что вывелись все поля и кнопка
    //
    public function testFields(): void
    {
        $client = $this->login();

        $crawler = $client->request('GET', '/order');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Заказать услугу оценки');

        $this->assertCount(
            1,
            $crawler->filter('select#order_service'),
            'Должно быть ровно одно поле "Услуга"'
        );
        $this->assertCount(
            1,
            $crawler->filter('input[type=email]#order_email'),
            'Должно быть ровно одно поле "Электонная почта"'
        );
        $this->assertCount(
            1,
            $crawler->filter('input[type=text][readonly=readonly]#price'),
            'Должно быть ровно одно поле "Стоимость" (только-чтение)'
        );

        $this->assertCount(
            1,
            $crawler->filter('input[type=submit]'),
            'Должно быть ровно одна кнопка "Подтвердить"'
        );
    }
}
