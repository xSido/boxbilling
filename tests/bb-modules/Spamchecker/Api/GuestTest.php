<?php

namespace Box\Mod\Spamchecker\Api;

class GuestTest extends \BBTestCase
{
    /**
     * @var \Box\Mod\Spamchecker\Api\Guest
     */
    protected $api = null;

    public function setup(): void
    {
        $this->api = new \Box\Mod\Spamchecker\Api\Guest();
    }

    public function testgetDi()
    {
        $di = new \Box_Di();
        $this->api->setDi($di);
        $getDi = $this->api->getDi();
        $this->assertEquals($di, $getDi);
    }

    public function datarecaptchaConfig()
    {
        return [
            [
                [
                    "captcha_recaptcha_publickey" => 1234,
                    "captcha_enabled" => true,
                ],
                [
                    "publickey" => 1234,
                    "enabled" => true,
                    "version" => null,
                ],
            ],
            [
                [
                    "captcha_enabled" => true,
                ],
                [
                    "publickey" => null,
                    "enabled" => true,
                    "version" => null,
                ],
            ],
            [
                [
                    "captcha_recaptcha_publickey" => 1234,
                    "captcha_enabled" => false,
                    "captcha_version" => 2,
                ],
                [
                    "publickey" => 1234,
                    "enabled" => false,
                    "version" => 2,
                ],
            ],
            [
                [
                    "captcha_enabled" => false,
                ],
                [
                    "publickey" => null,
                    "enabled" => false,
                    "version" => null,
                ],
            ],
        ];
    }

    /**
     * @dataProvider datarecaptchaConfig
     */
    public function testrecaptcha($config, $expected)
    {
        $di = new \Box_Di();
        $di["mod_config"] = $di->protect(function () use ($config) {
            return $config;
        });
        $di["array_get"] = $di->protect(function (
            array $array,
            $key,
            $default = null
        ) use ($di) {
            return isset($array[$key]) ? $array[$key] : $default;
        });

        $this->api->setDi($di);
        $result = $this->api->recaptcha([]);

        $this->assertEquals($expected, $result);
    }
}
