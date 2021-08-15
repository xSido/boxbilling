<?php
namespace Box\Mod\System;

class ServiceTest extends \BBTestCase
{
    /**
     * @var \Box\Mod\System\Service
     */
    protected $service = null;

    public function setup(): void
    {
        $this->service = new \Box\Mod\System\Service();
    }

    public function testgetParamValueMissingKeyParam()
    {
        $param = [];
        $this->expectException(\Box_Exception::class);
        $this->expectExceptionMessage("Parameter key is missing");

        $this->service->getParamValue($param);
    }

    public function testgetCompany()
    {
        $config = ["url" => "www.boxbilling.com"];
        $expected = [
            "www" => $config["url"],
            "name" => "Inc. Test",
            "email" => "work@example.eu",
            "tel" => null,
            "signature" => null,
            "logo_url" => null,
            "address_1" => null,
            "address_2" => null,
            "address_3" => null,
            "account_number" => null,
            "number" => null,
            "note" => null,
            "privacy_policy" => null,
            "tos" => null,
            "vat_number" => null,
        ];

        $multParamsResults = [
            [
                "param" => "company_name",
                "value" => "Inc. Test",
            ],
            [
                "param" => "company_email",
                "value" => "work@example.eu",
            ],
        ];
        $dbMock = $this->getMockBuilder("\Box_Database")->getMock();
        $dbMock
            ->expects($this->atLeastOnce())
            ->method("getAll")
            ->will($this->returnValue($multParamsResults));

        $di = new \Box_Di();
        $di["db"] = $dbMock;
        $di["config"] = $config;
        $di["array_get"] = $di->protect(function (
            array $array,
            $key,
            $default = null
        ) use ($di) {
            return isset($array[$key]) ? $array[$key] : $default;
        });
        $this->service->setDi($di);

        $result = $this->service->getCompany();
        $this->assertIsArray($result);
        $this->assertEquals($expected, $result);
    }

    public function testgetLanguages()
    {
        $expected = [
            [
                "locale" => "en_US",
                "title" => "English (United States)",
            ],
        ];

        $result = $this->service->getLanguages(true);
        $this->assertIsArray($result);
        $this->assertEquals($expected, $result);
    }

    public function testgetParams()
    {
        $expected = [
            "company_name" => "Inc. Test",
            "company_email" => "work@example.eu",
        ];
        $multParamsResults = [
            [
                "param" => "company_name",
                "value" => "Inc. Test",
            ],
            [
                "param" => "company_email",
                "value" => "work@example.eu",
            ],
        ];
        $dbMock = $this->getMockBuilder("\Box_Database")->getMock();
        $dbMock
            ->expects($this->atLeastOnce())
            ->method("getAll")
            ->will($this->returnValue($multParamsResults));

        $di = new \Box_Di();
        $di["db"] = $dbMock;

        $this->service->setDi($di);

        $result = $this->service->getParams([]);
        $this->assertIsArray($result);
        $this->assertEquals($expected, $result);
    }

    public function testupdateParams()
    {
        $data = [
            "company_name" => "newValue",
        ];

        $eventMock = $this->getMockBuilder("\Box_EventManager")->getMock();
        $eventMock->expects($this->atLeastOnce())->method("fire");

        $logMock = $this->getMockBuilder("\Box_Log")->getMock();

        $systemServiceMock = $this->getMockBuilder("\Box\Mod\System\Service")
            ->setMethods(["setParamValue"])
            ->getMock();
        $systemServiceMock
            ->expects($this->atLeastOnce())
            ->method("setParamValue")
            ->will($this->returnValue(true));

        $di = new \Box_Di();
        $di["events_manager"] = $eventMock;
        $di["logger"] = $logMock;

        $systemServiceMock->setDi($di);
        $result = $systemServiceMock->updateParams($data);
        $this->assertIsBool($result);
        $this->assertTrue($result);
    }

    public function testgetMessages()
    {
        $latestVersion = "1.0.0";
        $type = "info";

        $systemServiceMock = $this->getMockBuilder("\Box\Mod\System\Service")
            ->setMethods(["getParamValue"])
            ->getMock();
        $systemServiceMock
            ->expects($this->atLeastOnce())
            ->method("getParamValue")
            ->will($this->returnValue(false));

        $updaterMock = $this->getMockBuilder("\Box_Update")->getMock();
        $updaterMock
            ->expects($this->atLeastOnce())
            ->method("getCanUpdate")
            ->will($this->returnValue(true));
        $updaterMock
            ->expects($this->atLeastOnce())
            ->method("getLatestVersion")
            ->will($this->returnValue($latestVersion));

        $toolsMock = $this->getMockBuilder("\Box_Tools")->getMock();
        $toolsMock
            ->expects($this->atLeastOnce())
            ->method("fileExists")
            ->will($this->returnValue(true));

        $di = new \Box_Di();
        $di["updater"] = $updaterMock;
        $di["mod_service"] = $di->protect(function () use ($systemServiceMock) {
            return $systemServiceMock;
        });
        $di["tools"] = $toolsMock;
        $di["array_get"] = $di->protect(function (
            array $array,
            $key,
            $default = null
        ) use ($di) {
            return isset($array[$key]) ? $array[$key] : $default;
        });

        $systemServiceMock->setDi($di);

        $result = $systemServiceMock->getMessages($type);
        $this->assertIsArray($result);
    }

    public function testtemplateExists()
    {
        $getThemeResults = [
            "paths" => ["\home", '\var'],
        ];
        $systemServiceMock = $this->getMockBuilder("\Box\Mod\Theme\Service")
            ->setMethods(["getThemeConfig"])
            ->getMock();
        $systemServiceMock
            ->expects($this->atLeastOnce())
            ->method("getThemeConfig")
            ->will($this->returnValue($getThemeResults));

        $toolsMock = $this->getMockBuilder("\Box_Tools")->getMock();
        $toolsMock
            ->expects($this->atLeastOnce())
            ->method("fileExists")
            ->will($this->onConsecutiveCalls(false, true));

        $di = new \Box_Di();
        $di["tools"] = $toolsMock;
        $di["mod_service"] = $di->protect(function () use ($systemServiceMock) {
            return $systemServiceMock;
        });

        $this->service->setDi($di);

        $result = $this->service->templateExists("defaultFile.cp");
        $this->assertIsBool($result);
        $this->assertTrue($result);
    }

    public function testtemplateExistsEmptyPaths()
    {
        $getThemeResults = ["paths" => []];
        $systemServiceMock = $this->getMockBuilder("\Box\Mod\System\Service")
            ->setMethods(["getThemeConfig"])
            ->getMock();
        $systemServiceMock
            ->expects($this->atLeastOnce())
            ->method("getThemeConfig")
            ->will($this->returnValue($getThemeResults));

        $di = new \Box_Di();
        $di["mod_service"] = $di->protect(function () use ($systemServiceMock) {
            return $systemServiceMock;
        });
        $this->service->setDi($di);

        $result = $this->service->templateExists("defaultFile.cp");
        $this->assertIsBool($result);
        $this->assertFalse($result);
    }

    public function testrenderStringTemplateException()
    {
        $vars = [
            "_client_id" => 1,
        ];

        $this->getMockBuilder("Drupal\Core\Template\TwigEnvironment")
            ->disableOriginalConstructor()
            ->getMock();

        $twigMock = $this->getMockBuilder("\Twig\Environment")
            ->disableOriginalConstructor()
            ->getMock();
        $twigMock->expects($this->atLeastOnce())->method("addGlobal");
        $twigMock
            ->method("createTemplate")
            ->will($this->throwException(new \Error("Error")));

        $dbMock = $this->getMockBuilder("\Box_Database")->getMock();
        $dbMock
            ->expects($this->atLeastOnce())
            ->method("load")
            ->will($this->returnValue(new \Model_Client()));

        $di = new \Box_Di();
        $di["db"] = $dbMock;
        $di["twig"] = $twigMock;
        $di["api_client"] = new \Model_Client();
        $this->service->setDi($di);

        $this->expectException(\Error::class);
        $this->service->renderString("test", false, $vars);
    }

    public function testrenderStringTemplate()
    {
        $vars = [
            "_client_id" => 1,
        ];
        $twigMock = $this->getMockBuilder("\Twig\Environment")
            ->disableOriginalConstructor()
            ->getMock();
        $twigMock->expects($this->atLeastOnce())->method("addGlobal");
        $twigMock
            ->method("createTemplate")
            ->willReturn(new \FakeTemplateWrapper("test"));
        $twigMock->method("load")->willReturn(new \FakeTemplateWrapper("test"));

        $dbMock = $this->getMockBuilder("\Box_Database")->getMock();
        $dbMock
            ->expects($this->atLeastOnce())
            ->method("load")
            ->will($this->returnValue(new \Model_Client()));

        $di = new \Box_Di();
        $di["db"] = $dbMock;
        $di["twig"] = $twigMock;
        $di["api_client"] = new \Model_Client();
        $this->service->setDi($di);

        $string = $this->service->renderString("test", true, $vars);
        $this->assertEquals($string, "test");
    }

    public function testclearCache()
    {
        $toolsMock = $this->getMockBuilder("\Box_Tools")->getMock();
        $toolsMock
            ->expects($this->atLeastOnce())
            ->method("emptyFolder")
            ->will($this->returnValue(true));

        $di = new \Box_Di();
        $di["tools"] = $toolsMock;

        $this->service->setDi($di);

        $result = $this->service->clearCache();
        $this->assertIsBool($result);
        $this->assertTrue($result);
    }

    public function testgetCurrentUrl()
    {
        $requestMock = $this->getMockBuilder("\Box_Request")->getMock();
        $requestMock
            ->expects($this->atLeastOnce())
            ->method("getScheme")
            ->will($this->returnValue("https"));
        $requestMock
            ->expects($this->atLeastOnce())
            ->method("getURI")
            ->will($this->returnValue("?page=1"));

        $requestMock
            ->expects($this->atLeastOnce())
            ->method("getServer")
            ->will(
                $this->returnCallback(function () {
                    $arg = func_get_arg(0);
                    if ($arg == "SERVER_PORT") {
                        return "80";
                    }
                    if ($arg == "SERVER_NAME") {
                        return "localhost";
                    }
                    return false;
                })
            );

        $di = new \Box_Di();
        $di["request"] = $requestMock;

        $this->service->setDi($di);
        $result = $this->service->getCurrentUrl();
        $this->assertIsString($result);
    }

    public function testgetPeriod()
    {
        $code = "1W";
        $expexted = "Every week";
        $result = $this->service->getPeriod($code);

        $this->assertIsString($result);
        $this->assertEquals($expexted, $result);
    }

    public function testgetCountries()
    {
        $modMock = $this->getMockBuilder("\Box_Mod")
            ->disableOriginalConstructor()
            ->getMock();
        $modMock
            ->expects($this->atLeastOnce())
            ->method("getConfig")
            ->will($this->returnValue(["countries" => "US"]));

        $di = new \Box_Di();
        $di["mod"] = $di->protect(function () use ($modMock) {
            return $modMock;
        });

        $this->service->setDi($di);
        $result = $this->service->getCountries();
        $this->assertIsArray($result);
    }

    public function testgetEuCountries()
    {
        $modMock = $this->getMockBuilder("\Box_Mod")
            ->disableOriginalConstructor()
            ->getMock();
        $modMock
            ->expects($this->atLeastOnce())
            ->method("getConfig")
            ->will($this->returnValue(["countries" => "US"]));

        $di = new \Box_Di();
        $di["mod"] = $di->protect(function () use ($modMock) {
            return $modMock;
        });

        $this->service->setDi($di);
        $result = $this->service->getEuCountries();
        $this->assertIsArray($result);
    }

    public function testgetStates()
    {
        $result = $this->service->getStates();
        $this->assertIsArray($result);
    }

    public function testgetPhoneCodes()
    {
        $data = [];
        $result = $this->service->getPhoneCodes($data);
        $this->assertIsArray($result);
    }

    public function testgetVersion()
    {
        $result = $this->service->getVersion();
        $this->assertIsString($result);
        $this->assertEquals(\Box_Version::VERSION, $result);
    }

    public function testgetPendingMessages()
    {
        $di = new \Box_Di();

        $sessionMock = $this->getMockBuilder("\Box_Session")
            ->disableOriginalConstructor()
            ->getMock();
        $sessionMock
            ->expects($this->atLeastOnce())
            ->method("get")
            ->with("pending_messages")
            ->willReturn([]);

        $di["session"] = $sessionMock;

        $this->service->setDi($di);
        $result = $this->service->getPendingMessages();
        $this->assertIsArray($result);
    }

    public function testgetPendingMessages_GetReturnsNotArray()
    {
        $di = new \Box_Di();

        $sessionMock = $this->getMockBuilder("\Box_Session")
            ->disableOriginalConstructor()
            ->getMock();
        $sessionMock
            ->expects($this->atLeastOnce())
            ->method("get")
            ->with("pending_messages")
            ->willReturn(null);

        $di["session"] = $sessionMock;

        $this->service->setDi($di);
        $result = $this->service->getPendingMessages();
        $this->assertIsArray($result);
    }

    public function testsetPendingMessage()
    {
        $serviceMock = $this->getMockBuilder("\Box\Mod\System\Service")
            ->setMethods(["getPendingMessages"])
            ->getMock();
        $serviceMock
            ->expects($this->atLeastOnce())
            ->method("getPendingMessages")
            ->willReturn([]);

        $di = new \Box_Di();

        $sessionMock = $this->getMockBuilder("\Box_Session")
            ->disableOriginalConstructor()
            ->getMock();
        $sessionMock
            ->expects($this->atLeastOnce())
            ->method("set")
            ->with("pending_messages");

        $di["session"] = $sessionMock;

        $serviceMock->setDi($di);

        $message = "Important Message";
        $result = $serviceMock->setPendingMessage($message);
        $this->assertTrue($result);
    }

    public function testclearPendingMessages()
    {
        $di = new \Box_Di();

        $sessionMock = $this->getMockBuilder("\Box_Session")
            ->disableOriginalConstructor()
            ->getMock();
        $sessionMock
            ->expects($this->atLeastOnce())
            ->method("delete")
            ->with("pending_messages");
        $di["session"] = $sessionMock;
        $this->service->setDi($di);
        $result = $this->service->clearPendingMessages();
        $this->assertTrue($result);
    }
}
