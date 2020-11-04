<?php

namespace Src\SomeOtherService;

use GuzzleHttp\Client;
use Src\Client\MockHttpClient;
use Src\Client\UserClient;
use Src\Client\ClientException;

class SomeOtherClass
{
    /**
     * @var UserClient
     */
    private UserClient $_userClient;

    public function __construct()
    {
        $this->_userClient = new UserClient(new Client());
    }

    public function manipulateWithUser(): void
    {
        try
        {
            $user = $this->_userClient->getUser('Ivanov Ivan');
            $user['name'] = 'Ivanov Ivan Ivanovich';
            $user['blocked'] = true;
            $user['permissions'][] = [
                'id' => 4,
                'permission' => 'send unblocking request'
            ];
            $this->_userClient->setUser($user);
        }
        catch (ClientException $exception)
        {
            echo 'Упс, произошла ошибка: ' . $exception->getCode() . ' ' . $exception->getMessage();
        }
    }
}