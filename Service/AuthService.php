<?php

namespace Mrsuh\Service;

use Behat\Mink\Driver\GoutteDriver;
use Behat\Mink\Session;
use GuzzleHttp\Client;
use Mrsuh\Exception\AuthenticationException;
use Mrsuh\Exception\ParameterException;

class AuthService
{
    private $client;
    private $vk_params;
    private $file_path;

    /**
     * AuthService constructor.
     * @param array $vk_params
     */
    public function __construct(array $vk_params)
    {
        $this->checkParameters($vk_params);
        $this->vk_params = $vk_params;

        $this->file_path = __DIR__ . '/../' . $this->vk_params['username'];

        $this->token = file_exists($this->file_path) ? file_get_contents($this->file_path) : null;

        $this->client = new Client([
            'timeout'         => 3,
            'connect_timeout' => 3
        ]);
    }

    /**
     * @param array $params
     * @return bool
     * @throws ParameterException
     */
    private function checkParameters(array $params)
    {
        $missing_keys = [];
        foreach (['username', 'password', 'app_id', 'scope'] as $key) {
            if (!isset($params[$key])) {
                $missing_keys[] = $key;
            }
        }

        if (!empty($missing_keys)) {
            throw new ParameterException('Required parameters: ' . implode(', ', $missing_keys));
        }

        return true;
    }

    /**
     * @return null|string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param $token
     */
    private function setToken($token)
    {
        $this->token = $token;
        file_put_contents($this->file_path, $token);
    }

    /**
     * @throws AuthenticationException
     */
    public function auth()
    {
        try {

            $url_data = http_build_query([
                'client_id'     => $this->vk_params['app_id'],
                'scope'         => implode(',', $this->vk_params['scope']),
                'redirect_uri'  => 'http://oauth.vk.com/blank.html',
                'display'       => 'page',
                'response_type' => 'token',
            ]);

            $url = 'https://oauth.vk.com/authorize?' . $url_data;

            $driver  = new GoutteDriver();
            $session = new Session($driver);

            $session->start();
            $session->visit($url);

            $page = $session->getPage();

            if ($allow = $page->find('css', '#install_allow')) {
                $allow->click();
            }

            $email = $page->find('css', '[name="email"]');
            $pass  = $page->find('css', '[name="pass"]');
            $btn   = $page->find('css', '[type="submit"]');

            $email->setValue($this->vk_params['username']);
            $pass->setValue($this->vk_params['password']);

            $btn->click();

            if ($btn = $page->find('css', '[type="submit"]')) {
                $btn->click();
            }

            parse_str($session->getCurrentUrl(), $parse);

            if (!isset($parse['https://oauth_vk_com/blank_html#access_token'])) {
                throw new AuthenticationException('Invalid parse url');
            }

            $this->setToken($parse['https://oauth_vk_com/blank_html#access_token']);

        } catch (\Exception $e) {
            throw new AuthenticationException($e->getMessage());
        }

        return true;
    }
}