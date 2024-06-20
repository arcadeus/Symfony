<?php

namespace App\Tests\Controller;

use App\Entity\Order;
use App\Entity\Service;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\User\InMemoryUser;

class OrderTest extends WebTestCase
{
    private ?EntityManager $m_EntityManager;

    //
    private function login()
    {
        $client = static::createClient();
        $user = new InMemoryUser('user', '123', ['ROLE_USER']);
        $client->loginUser($user);
        return $client;
    }

    private function setupDB(): void
    {
        $kernel = self::bootKernel();
        $this->m_EntityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    function random_str($length)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyz';
        $charsLength = strlen($chars);
        $res = '';
        for ($i = 0; $i < $length; $i++)
        {
            $res .= $chars[rand(0, $charsLength - 1)];
        }
        return $res;
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

    //
    // При подтверждении заказа с незаполненной электронной почтой или не выбранной услугой
    // пользователю отображается та же самая форма заказа с текстом ошибки в произвольном месте
    //
    public function testErrorMessage(): void
    {
        $client = $this->login();

        $client->request('GET', '/order');

        $client->followRedirects();
        $crawler = $client->submitForm('Подтвердить');
        $this->assertSelectorTextContains('div.error', 'Укажите электронную почту');
    }

    //
    // При отправке авторизованным пользователем формы с заполненными полями
    // в хранилище должен появиться новый заказ с данными, соответствующими форме
    //
    public function testSubmit(): void
    {
        $client = $this->login();

        $this->setupDB();

        // Select random Service
        $services = $this->m_EntityManager
            ->getRepository(Service::class)
            ->findAll();

        $cServises = count($services);
        if (!$cServises)
            die('No services found in test DB');

        $nService = rand(0, $cServises - 1);
        $nService = $services[$nService]->getId();

        // Build random pseudo-EMail
        $email = $this->random_str(8) . '@' . $this->random_str(4) . '.' . $this->random_str(2);

        // Submit Order form
        $client->request('GET', '/order');

        $client->followRedirects();
        $client->submitForm(
            'Подтвердить',
            [
                'order[service]' => $nService,
                'order[email]'   => $email,
            ]
        );

        // Look for the INSERTed Order in DB
        $orders = $this->m_EntityManager
            ->getRepository(Order::class)
            ->findBy([
                'service' => $nService,
                'email'   => $email
              ]);

        // 8 + 4 + 2 = 14 random letters are most likely unique
        $this->assertSame(1, count($orders));
    }
}
