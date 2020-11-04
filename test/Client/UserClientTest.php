<?php

namespace Test\Client;

use PHPUnit\Framework\TestCase;
use Src\Client\ClientException;
use Src\Client\MockHttpClient;
use Src\Client\UserClient;

class UserClientTest extends TestCase
{
    public function testUserClientAuth(): void
    {
        $exception = null;

        try
        {
            new UserClient(new MockHttpClient());
        }
        catch (\Exception $exception) {}

        self::assertNull($exception);
    }

    public function testUserClientSetUser(): void
    {
        $userClient = new UserClient(new MockHttpClient());

        /* успех */
        $exception = null;
        try
        {
            $userClient->setUser([
                "active" => "1",
                "blocked" => false,
                "created_at" => 1587457590,
                "id" => 23,
                "name" => "Ivanov Ivan",
                "permissions" => [
                    [
                        "id" => 1,
                        "permission" => "comment"
                    ],
                    [
                        "id" => 2,
                        "permission" => "upload photo"
                    ],
                    [
                        "id" => 3,
                        "permission" => "add event"
                    ]
                ]
            ]);
        }
        catch (\Exception $exception) {}

        self::assertNull($exception);

        /* ошибка при пустом массиве */
        $exception = null;
        try
        {
            $userClient->setUser([]);
        }
        catch (\Exception $exception) {}

        self::assertInstanceOf(ClientException::class, $exception);
        self::assertEquals('Неверный статус ответа от сервера: ' . UserClient::RESPONSE_STATUS_ERROR, $exception->getMessage());
    }

    public function testUserClientGetUser(): void
    {
        $exception = null;
        $userClient = new UserClient(new MockHttpClient());

        /* проверяем получения правильного юзера */
        try
        {
            $user = $userClient->getUser('Ivanov Ivan');
        }
        catch (\Exception $exception) {}

        self::assertNull($exception);
        self::assertEquals([
            'status' => UserClient::RESPONSE_STATUS_OK,
            "active" => "1",
            "blocked" => false,
            "created_at" => 1587457590,
            "id" => 23,
            "name" => "Ivanov Ivan",
            "permissions" => [
                [
                    "id" => 1,
                    "permission" => "comment"
                ],
                [
                    "id" => 2,
                    "permission" => "upload photo"
                ],
                [
                    "id" => 3,
                    "permission" => "add event"
                ]
            ]

        ], $user);

        /* проверяем ошибку при попытке получения не существующего юзера */
        $exception = null;
        try
        {
            $userClient->getUser('Petrov Petr');
        }
        catch (\Exception $exception) {}

        self::assertInstanceOf(ClientException::class, $exception);
        self::assertEquals('Неверный статус ответа от сервера: ' . UserClient::RESPONSE_STATUS_NOT_FOUND, $exception->getMessage());
    }
}