<?php

namespace Src\Client;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class MockHttpClient implements ClientInterface
{
    public function request(string $method, $uri, array $options = []): ResponseInterface
    {
        preg_match('/:\/\/testapi.ru\/(?<route>[^\/?]+)\/?(?<param1>[^\/?]+)?\/?(?<param2>[^\/?]+)?(\?token=(?<token>.+)?)?(\?login=(?<login>[^\&]+)?)?(\&pass=(?<pass>.*)?)?/', $uri, $matches);

        try {
            if ($matches['route'] === 'auth' && $method === 'GET') {
                return $this->auth($matches['login'], $matches['pass']);
            }

            if ($matches['token'] !== 'dsfd79843r32d1d3dx23d32d') {
                $response = new Response('401', [], null,'1.1', null);
                throw new BadResponseException('Unauthorized', new Request($method, $uri), $response);
            }

            if($matches['route'] === 'get-user' && $method === 'GET') {
                return $this->getUser($matches['param1']);
            }

            if($matches['route'] === 'user' && $method === 'POST') {
                return $this->setUser($options['json']);
            }
        }
        catch (\Exception $exception)
        {
            $data = [
                'status' => 'Error'
            ];

            return new Response('200', [], json_encode($data),'1.1', null);
        }

        $data = [
            'status' => 'Not found',
        ];

        return new Response('200', [], json_encode($data),'1.1', null);
    }

    /**
     * @param $login
     * @param $password
     *
     * @return ResponseInterface
     */
    private function auth(string $login, string $password): ResponseInterface
    {
        if($login === 'test' && $password === '12345')
        {
            $data = [
                'status' => 'OK',
                'token' => 'dsfd79843r32d1d3dx23d32d'
            ];
        }
        else
        {
            $data = [
                'status' => 'Not found'
            ];
        }

        return new Response('200', [], json_encode($data),'1.1', null);
    }

    /**
     * @param string $username
     *
     * @return ResponseInterface
     */
    private function getUser(string $username): ResponseInterface
    {
        if($username === 'Ivanov Ivan')
        {
            $data = [
                'status' => 'OK',
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

            ];

            return new Response('200', [], json_encode($data),'1.1', null);
        }

        $data = [
            'status' => 'Not found',
        ];

        return new Response('200', [], json_encode($data),'1.1', null);
    }

    /**
     * @param $userData
     *
     * @return ResponseInterface
     */
    private function setUser(array $userData): ResponseInterface
    {
        if(empty($userData)) {
            $data = [
                'status' => 'Error'
            ];
        }
        else
        {
            $data = [
                'status' => 'OK'
            ];
        }

        return new Response('200', [], json_encode($data),'1.1', null);
    }

    public function requestAsync(string $method, $uri, array $options = []): PromiseInterface
    {
        throw new \RuntimeException('Не поддерживаемый метод');
    }

    public function getConfig(?string $option = null)
    {
        throw new \RuntimeException('Не поддерживаемый метод');
    }

    public function send(RequestInterface $request, array $options = []): ResponseInterface
    {
        throw new \RuntimeException('Не поддерживаемый метод');
    }

    public function sendAsync(RequestInterface $request, array $options = []): PromiseInterface
    {
        throw new \RuntimeException('Не поддерживаемый метод');
    }
}