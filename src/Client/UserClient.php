<?php

namespace Src\Client;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class UserClient
{
    private const
        URI_BASE = 'https://testapi.ru',
        URI_AUTH = '/auth',
        URI_GET_USER = '/get-user',
        URI_SET_USER = '/user';

    public const
        RESPONSE_STATUS_OK = 'OK',
        RESPONSE_STATUS_NOT_FOUND = 'Not found',
        RESPONSE_STATUS_ERROR = 'Error';

    /**
     * @var ClientInterface
     */
    private ClientInterface $_httpClient;

    /**
     * @var string
     */
    private string $_token;

    /**
     * UserClient constructor.
     *
     * @param ClientInterface $httpClient
     */
    public function __construct(ClientInterface $httpClient)
    {
        $this->_httpClient = $httpClient;

        $response = $this->makeRequest('GET', self::URI_BASE . self::URI_AUTH . '?login=' . $_ENV['TESTAPI_LOGIN'] . '&pass=' . $_ENV['TESTAPI_PASS'], );

        $authData = $this->parseResponse($response);

        if(isset($authData['token']))
        {
            $this->_token = $authData['token'];
        }
        else
        {
            throw new ClientException('Неверный ответ от сервера: не возвращен токен');
        }

    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $options
     *
     * @return ResponseInterface
     *
     * @throws ClientException
     */
    private function makeRequest(string $method, $uri = '', array $options = []): ResponseInterface
    {
        try
        {
            return $this->_httpClient->request($method, $uri, $options);
        }
        catch (GuzzleException $exception)
        {
            if($exception instanceof BadResponseException)
            {
                if ($exception->getResponse()->getStatusCode() === '401')
                {
                    throw new ClientException('Неверный токен или логин/пароль');
                }

                throw new ClientException('Неверный ответ от сервера: BadResponseException ' . $exception->getCode() . ' ' . $exception->getMessage());
            }

            throw new ClientException('Сетевая ошибка: GuzzleException '  . $exception->getCode() . ' ' . $exception->getMessage());
        }
    }

    /**
     * @param ResponseInterface $response
     *
     * @return mixed
     *
     * @throws ClientException
     */
    private function parseResponse(ResponseInterface $response)
    {
        $data = json_decode($response->getBody(), true);

        if(!isset($data['status']))
        {
            throw new ClientException('Неверный ответ от сервера' );
        }

        if($data['status'] !== self::RESPONSE_STATUS_OK)
        {
            throw new ClientException('Неверный статус ответа от сервера: ' . $data['status']);
        }

        return $data;
    }

    /**
     * @param string $userName
     *
     * @return array
     *
     * @throws ClientException
     */
    public function getUser(string $userName): array
    {
        $response = $this->makeRequest('GET', self::URI_BASE . self::URI_GET_USER . '/' . $userName . '?token=' . $this->_token);
        return $this->parseResponse($response);
    }

    /**
     * @param array $user
     *
     * @throws ClientException
     */
    public function setUser(array $user): void
    {
        $response = $this->makeRequest('POST', self::URI_BASE . self::URI_SET_USER . '/' . $user['id'] . '?token=' . $this->_token, [
            'json' => $user
        ]);
        $this->parseResponse($response);
    }
}