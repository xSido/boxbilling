<?php
namespace Box\Tests\Mod\Email\Api;

class Api_ClientTest extends \BBTestCase
{
    public function testGet_list()
    {
        $clientApi = new \Box\Mod\Email\Api\Client();
        $emailService = new \Box\Mod\Email\Service();

        $willReturn = [
            "list" => [
                "id" => 1,
            ],
        ];
        $pager = $this->getMockBuilder("Box_Pagination")->getMock();
        $pager
            ->expects($this->atLeastOnce())
            ->method("getSimpleResultSet")
            ->will($this->returnValue($willReturn));

        $di = new \Box_Di();
        $di["pager"] = $pager;
        $di["array_get"] = $di->protect(function (
            array $array,
            $key,
            $default = null
        ) use ($di) {
            return isset($array[$key]) ? $array[$key] : $default;
        });
        $clientApi->setDi($di);
        $emailService->setDi($di);

        $service = $emailService;
        $clientApi->setService($service);

        $client = new \Model_Client();
        $client->loadBean(new \RedBeanPHP\OODBBean());
        $client->id = rand(1, 100);
        $clientApi->setIdentity($client);

        $result = $clientApi->get_list([]);
        $this->assertIsArray($result);

        $this->assertArrayHasKey("list", $result);
        $this->assertIsArray($result["list"]);
    }

    public function testGet()
    {
        $clientApi = new \Box\Mod\Email\Api\Client();

        $model = new \Model_ActivityClientEmail();
        $model->loadBean(new \RedBeanPHP\OODBBean());
        $service = $this->getMockBuilder("Box\Mod\Email\Service")
            ->setMethods(["findOneForClientById", "toApiArray"])
            ->getMock();
        $service
            ->expects($this->atLeastOnce())
            ->method("findOneForClientById")
            ->will($this->returnValue($model));
        $service
            ->expects($this->atLeastOnce())
            ->method("toApiArray")
            ->will($this->returnValue([]));
        $clientApi->setService($service);

        $validatorMock = $this->getMockBuilder("\Box_Validate")
            ->disableOriginalConstructor()
            ->getMock();
        $validatorMock
            ->expects($this->atLeastOnce())
            ->method("checkRequiredParamsForArray")
            ->will($this->returnValue(null));
        $di["validator"] = $validatorMock;
        $clientApi->setDi($di);

        $client = new \Model_Client();
        $client->loadBean(new \RedBeanPHP\OODBBean());
        $client->id = rand(1, 100);
        $clientApi->setIdentity($client);

        $result = $clientApi->get(["id" => 1]);
        $this->assertIsArray($result);
    }

    public function testGetNotFoundException()
    {
        $clientApi = new \Box\Mod\Email\Api\Client();

        $service = $this->getMockBuilder("Box\Mod\Email\Service")
            ->setMethods(["findOneForClientById"])
            ->getMock();
        $service
            ->expects($this->atLeastOnce())
            ->method("findOneForClientById")
            ->will($this->returnValue(false));

        $client = new \Model_Client();
        $client->loadBean(new \RedBeanPHP\OODBBean());
        $client->id = 5;
        $clientApi->setIdentity($client);

        $validatorMock = $this->getMockBuilder("\Box_Validate")
            ->disableOriginalConstructor()
            ->getMock();
        $validatorMock
            ->expects($this->atLeastOnce())
            ->method("checkRequiredParamsForArray")
            ->will($this->returnValue(null));
        $di["validator"] = $validatorMock;
        $clientApi->setDi($di);

        $clientApi->setService($service);

        $this->expectException(\Box_Exception::class);
        $result = $clientApi->get(["id" => 1]);
        $this->assertIsArray($result);
    }

    public function testResend()
    {
        $clientApi = new \Box\Mod\Email\Api\Client();

        $model = new \Model_ActivityClientEmail();
        $model->loadBean(new \RedBeanPHP\OODBBean());

        $service = $this->getMockBuilder("Box\Mod\Email\Service")
            ->setMethods(["findOneForClientById", "resend"])
            ->getMock();
        $service
            ->expects($this->atLeastOnce())
            ->method("findOneForClientById")
            ->will($this->returnValue($model));
        $service
            ->expects($this->atLeastOnce())
            ->method("resend")
            ->will($this->returnValue(true));

        $client = new \Model_Client();
        $client->loadBean(new \RedBeanPHP\OODBBean());
        $client->id = 5;
        $clientApi->setIdentity($client);

        $validatorMock = $this->getMockBuilder("\Box_Validate")
            ->disableOriginalConstructor()
            ->getMock();
        $validatorMock
            ->expects($this->atLeastOnce())
            ->method("checkRequiredParamsForArray")
            ->will($this->returnValue(null));
        $di["validator"] = $validatorMock;
        $clientApi->setDi($di);

        $clientApi->setService($service);

        $result = $clientApi->resend(["id" => 1]);
        $this->assertTrue($result);
    }

    public function testResendNotFoundException()
    {
        $clientApi = new \Box\Mod\Email\Api\Client();

        $service = $this->getMockBuilder("Box\Mod\Email\Service")
            ->setMethods(["findOneForClientById"])
            ->getMock();
        $service
            ->expects($this->atLeastOnce())
            ->method("findOneForClientById")
            ->will($this->returnValue(false));

        $client = new \Model_Client();
        $client->loadBean(new \RedBeanPHP\OODBBean());
        $client->id = 5;

        $clientApi->setIdentity($client);

        $validatorMock = $this->getMockBuilder("\Box_Validate")
            ->disableOriginalConstructor()
            ->getMock();
        $validatorMock
            ->expects($this->atLeastOnce())
            ->method("checkRequiredParamsForArray")
            ->will($this->returnValue(null));
        $di["validator"] = $validatorMock;
        $clientApi->setDi($di);

        $clientApi->setService($service);

        $this->expectException(\Box_Exception::class);
        $result = $clientApi->resend(["id" => 1]);
        $this->assertIsArray($result);
    }

    public function testDelete()
    {
        $clientApi = new \Box\Mod\Email\Api\Client();

        $di = new \Box_Di();

        $model = new \Model_ActivityClientEmail();
        $model->loadBean(new \RedBeanPHP\OODBBean());
        $service = $this->getMockBuilder("Box\Mod\Email\Service")
            ->setMethods(["findOneForClientById", "rm"])
            ->getMock();
        $service
            ->expects($this->atLeastOnce())
            ->method("findOneForClientById")
            ->will($this->returnValue($model));
        $service
            ->expects($this->atLeastOnce())
            ->method("rm")
            ->will($this->returnValue(true));

        $client = new \Model_Client();
        $client->loadBean(new \RedBeanPHP\OODBBean());
        $client->id = 5;
        $clientApi->setIdentity($client);

        $validatorMock = $this->getMockBuilder("\Box_Validate")
            ->disableOriginalConstructor()
            ->getMock();
        $validatorMock
            ->expects($this->atLeastOnce())
            ->method("checkRequiredParamsForArray")
            ->will($this->returnValue(null));
        $di["validator"] = $validatorMock;
        $clientApi->setDi($di);

        $clientApi->setService($service);

        $result = $clientApi->delete(["id" => 1]);
        $this->assertTrue($result);
    }

    public function testDeleteNotFoundException()
    {
        $clientApi = new \Box\Mod\Email\Api\Client();

        $service = $this->getMockBuilder("Box\Mod\Email\Service")
            ->setMethods(["findOneForClientById"])
            ->getMock();
        $service
            ->expects($this->atLeastOnce())
            ->method("findOneForClientById")
            ->will($this->returnValue(false));

        $client = new \Model_Client();
        $client->loadBean(new \RedBeanPHP\OODBBean());
        $client->id = 5;

        $clientApi->setIdentity($client);

        $validatorMock = $this->getMockBuilder("\Box_Validate")
            ->disableOriginalConstructor()
            ->getMock();
        $validatorMock
            ->expects($this->atLeastOnce())
            ->method("checkRequiredParamsForArray")
            ->will($this->returnValue(null));
        $di["validator"] = $validatorMock;
        $clientApi->setDi($di);

        $clientApi->setService($service);

        $this->expectException(\Box_Exception::class);
        $result = $clientApi->delete(["id" => 1]);
        $this->assertIsArray($result);
    }
}
