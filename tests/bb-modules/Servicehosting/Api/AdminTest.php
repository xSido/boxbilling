<?php

namespace Box\Mod\Servicehosting\Api;

class AdminTest extends \BBTestCase
{
    /**
     * @var \Box\Mod\Servicehosting\Api\Admin
     */
    protected $api = null;

    public function setup(): void
    {
        $this->api = new \Box\Mod\Servicehosting\Api\Admin();
    }

    public function testgetDi()
    {
        $di = new \Box_Di();
        $this->api->setDi($di);
        $getDi = $this->api->getDi();
        $this->assertEquals($di, $getDi);
    }

    public function testchange_plan()
    {
        $data = [
            "plan_id" => 1,
        ];

        $getServiceReturnValue = [
            new \Model_ClientOrder(),
            new \Model_ServiceHosting(),
        ];
        $apiMock = $this->getMockBuilder("\Box\Mod\Servicehosting\Api\Admin")
            ->setMethods(["_getService"])
            ->getMock();

        $apiMock
            ->expects($this->atLeastOnce())
            ->method("_getService")
            ->will($this->returnValue($getServiceReturnValue));

        $serviceMock = $this->getMockBuilder(
            "\Box\Mod\Servicehosting\Service"
        )->getMock();
        $serviceMock
            ->expects($this->atLeastOnce())
            ->method("changeAccountPlan")
            ->will($this->returnValue(true));

        $dbMock = $this->getMockBuilder("\Box_Database")->getMock();
        $dbMock
            ->expects($this->atLeastOnce())
            ->method("getExistingModelById")
            ->will($this->returnValue(new \Model_ServiceHostingHp()));

        $di = new \Box_Di();
        $di["db"] = $dbMock;

        $apiMock->setDi($di);
        $apiMock->setService($serviceMock);

        $result = $apiMock->change_plan($data);
        $this->assertIsBool($result);
        $this->assertTrue($result);
    }

    public function testchange_planMissingPlanId()
    {
        $data = [];

        $this->expectException(\Box_Exception::class);
        $this->expectExceptionMessage("plan_id is missing");
        $this->api->change_plan($data);
    }

    public function testchange_username()
    {
        $getServiceReturnValue = [
            new \Model_ClientOrder(),
            new \Model_ServiceHosting(),
        ];
        $apiMock = $this->getMockBuilder("\Box\Mod\Servicehosting\Api\Admin")
            ->setMethods(["_getService"])
            ->getMock();

        $apiMock
            ->expects($this->atLeastOnce())
            ->method("_getService")
            ->will($this->returnValue($getServiceReturnValue));

        $serviceMock = $this->getMockBuilder(
            "\Box\Mod\Servicehosting\Service"
        )->getMock();
        $serviceMock
            ->expects($this->atLeastOnce())
            ->method("changeAccountUsername")
            ->will($this->returnValue(true));

        $apiMock->setService($serviceMock);

        $result = $apiMock->change_username([]);
        $this->assertIsBool($result);
        $this->assertTrue($result);
    }

    public function testchange_ip()
    {
        $getServiceReturnValue = [
            new \Model_ClientOrder(),
            new \Model_ServiceHosting(),
        ];
        $apiMock = $this->getMockBuilder("\Box\Mod\Servicehosting\Api\Admin")
            ->setMethods(["_getService"])
            ->getMock();

        $apiMock
            ->expects($this->atLeastOnce())
            ->method("_getService")
            ->will($this->returnValue($getServiceReturnValue));

        $serviceMock = $this->getMockBuilder(
            "\Box\Mod\Servicehosting\Service"
        )->getMock();
        $serviceMock
            ->expects($this->atLeastOnce())
            ->method("changeAccountIp")
            ->will($this->returnValue(true));

        $apiMock->setService($serviceMock);

        $result = $apiMock->change_ip([]);
        $this->assertIsBool($result);
        $this->assertTrue($result);
    }

    public function testchange_domain()
    {
        $getServiceReturnValue = [
            new \Model_ClientOrder(),
            new \Model_ServiceHosting(),
        ];
        $apiMock = $this->getMockBuilder("\Box\Mod\Servicehosting\Api\Admin")
            ->setMethods(["_getService"])
            ->getMock();

        $apiMock
            ->expects($this->atLeastOnce())
            ->method("_getService")
            ->will($this->returnValue($getServiceReturnValue));

        $serviceMock = $this->getMockBuilder(
            "\Box\Mod\Servicehosting\Service"
        )->getMock();
        $serviceMock
            ->expects($this->atLeastOnce())
            ->method("changeAccountDomain")
            ->will($this->returnValue(true));

        $apiMock->setService($serviceMock);

        $result = $apiMock->change_domain([]);
        $this->assertIsBool($result);
        $this->assertTrue($result);
    }

    public function testchange_password()
    {
        $getServiceReturnValue = [
            new \Model_ClientOrder(),
            new \Model_ServiceHosting(),
        ];
        $apiMock = $this->getMockBuilder("\Box\Mod\Servicehosting\Api\Admin")
            ->setMethods(["_getService"])
            ->getMock();

        $apiMock
            ->expects($this->atLeastOnce())
            ->method("_getService")
            ->will($this->returnValue($getServiceReturnValue));

        $serviceMock = $this->getMockBuilder(
            "\Box\Mod\Servicehosting\Service"
        )->getMock();
        $serviceMock
            ->expects($this->atLeastOnce())
            ->method("changeAccountPassword")
            ->will($this->returnValue(true));

        $apiMock->setService($serviceMock);

        $result = $apiMock->change_password([]);
        $this->assertIsBool($result);
        $this->assertTrue($result);
    }

    public function testsync()
    {
        $getServiceReturnValue = [
            new \Model_ClientOrder(),
            new \Model_ServiceHosting(),
        ];
        $apiMock = $this->getMockBuilder("\Box\Mod\Servicehosting\Api\Admin")
            ->setMethods(["_getService"])
            ->getMock();

        $apiMock
            ->expects($this->atLeastOnce())
            ->method("_getService")
            ->will($this->returnValue($getServiceReturnValue));

        $serviceMock = $this->getMockBuilder(
            "\Box\Mod\Servicehosting\Service"
        )->getMock();
        $serviceMock
            ->expects($this->atLeastOnce())
            ->method("sync")
            ->will($this->returnValue(true));

        $apiMock->setService($serviceMock);

        $result = $apiMock->sync([]);
        $this->assertIsBool($result);
        $this->assertTrue($result);
    }

    public function testupdate()
    {
        $getServiceReturnValue = [
            new \Model_ClientOrder(),
            new \Model_ServiceHosting(),
        ];
        $apiMock = $this->getMockBuilder("\Box\Mod\Servicehosting\Api\Admin")
            ->setMethods(["_getService"])
            ->getMock();

        $apiMock
            ->expects($this->atLeastOnce())
            ->method("_getService")
            ->will($this->returnValue($getServiceReturnValue));

        $serviceMock = $this->getMockBuilder(
            "\Box\Mod\Servicehosting\Service"
        )->getMock();
        $serviceMock
            ->expects($this->atLeastOnce())
            ->method("update")
            ->will($this->returnValue(true));

        $apiMock->setService($serviceMock);

        $result = $apiMock->update([]);
        $this->assertIsBool($result);
        $this->assertTrue($result);
    }

    public function testmanager_get_pairs()
    {
        $serviceMock = $this->getMockBuilder(
            "\Box\Mod\Servicehosting\Service"
        )->getMock();
        $serviceMock
            ->expects($this->atLeastOnce())
            ->method("getServerManagers")
            ->will($this->returnValue([]));

        $this->api->setService($serviceMock);

        $result = $this->api->manager_get_pairs([]);
        $this->assertIsArray($result);
    }

    public function testserver_get_pairs()
    {
        $serviceMock = $this->getMockBuilder(
            "\Box\Mod\Servicehosting\Service"
        )->getMock();
        $serviceMock
            ->expects($this->atLeastOnce())
            ->method("getServerPairs")
            ->will($this->returnValue([]));

        $this->api->setService($serviceMock);

        $result = $this->api->server_get_pairs([]);
        $this->assertIsArray($result);
    }

    public function testserver_get_list()
    {
        $serviceMock = $this->getMockBuilder(
            "\Box\Mod\Servicehosting\Service"
        )->getMock();
        $serviceMock
            ->expects($this->atLeastOnce())
            ->method("getServersSearchQuery")
            ->will($this->returnValue(["SQLstring", []]));

        $pagerMock = $this->getMockBuilder("\Box_Pagination")->getMock();
        $pagerMock
            ->expects($this->atLeastOnce())
            ->method("getSimpleResultSet")
            ->will($this->returnValue(["list" => []]));

        $dbMock = $this->getMockBuilder("\Box_Database")->getMock();
        $dbMock
            ->expects($this->atLeastOnce())
            ->method("find")
            ->will($this->returnValue([]));

        $di = new \Box_Di();
        $di["pager"] = $pagerMock;
        $di["db"] = $dbMock;
        $di["array_get"] = $di->protect(function (
            array $array,
            $key,
            $default = null
        ) use ($di) {
            return isset($array[$key]) ? $array[$key] : $default;
        });

        $this->api->setDi($di);
        $this->api->setService($serviceMock);

        $result = $this->api->server_get_list([]);
        $this->assertIsArray($result);
    }

    public function testserver_create()
    {
        $data = [
            "name" => "test",
            "ip" => "1.1.1.1",
            "manager" => "ServerManagerCode",
        ];

        $newServerId = 1;

        $serviceMock = $this->getMockBuilder(
            "\Box\Mod\Servicehosting\Service"
        )->getMock();
        $serviceMock
            ->expects($this->atLeastOnce())
            ->method("createServer")
            ->will($this->returnValue($newServerId));

        $validatorMock = $this->getMockBuilder("\Box_Validate")
            ->disableOriginalConstructor()
            ->getMock();
        $validatorMock
            ->expects($this->atLeastOnce())
            ->method("checkRequiredParamsForArray")
            ->will($this->returnValue(null));

        $di = new \Box_Di();
        $di["validator"] = $validatorMock;
        $this->api->setDi($di);

        $this->api->setService($serviceMock);

        $result = $this->api->server_create($data);
        $this->assertIsInt($result);
        $this->assertEquals($newServerId, $result);
    }

    public function testserver_get()
    {
        $data["id"] = 1;

        $serviceMock = $this->getMockBuilder(
            "\Box\Mod\Servicehosting\Service"
        )->getMock();
        $serviceMock
            ->expects($this->atLeastOnce())
            ->method("toHostingServerApiArray")
            ->will($this->returnValue([]));

        $dbMock = $this->getMockBuilder("\Box_Database")->getMock();
        $dbMock
            ->expects($this->atLeastOnce())
            ->method("getExistingModelById")
            ->will($this->returnValue(new \Model_ServiceHostingServer()));

        $validatorMock = $this->getMockBuilder("\Box_Validate")
            ->disableOriginalConstructor()
            ->getMock();
        $validatorMock
            ->expects($this->atLeastOnce())
            ->method("checkRequiredParamsForArray")
            ->will($this->returnValue(null));

        $di = new \Box_Di();
        $di["db"] = $dbMock;
        $di["validator"] = $validatorMock;

        $this->api->setDi($di);
        $this->api->setService($serviceMock);

        $result = $this->api->server_get($data);
        $this->assertIsArray($result);
    }

    public function testserver_delete()
    {
        $data["id"] = 1;

        $serviceMock = $this->getMockBuilder(
            "\Box\Mod\Servicehosting\Service"
        )->getMock();
        $serviceMock
            ->expects($this->atLeastOnce())
            ->method("deleteServer")
            ->will($this->returnValue(true));

        $dbMock = $this->getMockBuilder("\Box_Database")->getMock();
        $dbMock
            ->expects($this->atLeastOnce())
            ->method("getExistingModelById")
            ->will($this->returnValue(new \Model_ServiceHostingServer()));

        $validatorMock = $this->getMockBuilder("\Box_Validate")
            ->disableOriginalConstructor()
            ->getMock();
        $validatorMock
            ->expects($this->atLeastOnce())
            ->method("checkRequiredParamsForArray")
            ->will($this->returnValue(null));

        $di = new \Box_Di();
        $di["db"] = $dbMock;
        $di["validator"] = $validatorMock;

        $this->api->setDi($di);
        $this->api->setService($serviceMock);

        $result = $this->api->server_delete($data);
        $this->assertIsBool($result);
        $this->assertTrue($result);
    }

    public function testserver_update()
    {
        $data["id"] = 1;

        $serviceMock = $this->getMockBuilder(
            "\Box\Mod\Servicehosting\Service"
        )->getMock();
        $serviceMock
            ->expects($this->atLeastOnce())
            ->method("updateServer")
            ->will($this->returnValue(true));

        $dbMock = $this->getMockBuilder("\Box_Database")->getMock();
        $dbMock
            ->expects($this->atLeastOnce())
            ->method("getExistingModelById")
            ->will($this->returnValue(new \Model_ServiceHostingServer()));
        $validatorMock = $this->getMockBuilder("\Box_Validate")
            ->disableOriginalConstructor()
            ->getMock();
        $validatorMock
            ->expects($this->atLeastOnce())
            ->method("checkRequiredParamsForArray")
            ->will($this->returnValue(null));

        $di = new \Box_Di();
        $di["db"] = $dbMock;
        $di["validator"] = $validatorMock;

        $this->api->setDi($di);
        $this->api->setService($serviceMock);

        $result = $this->api->server_update($data);
        $this->assertIsBool($result);
        $this->assertTrue($result);
    }

    public function testserver_test_connection()
    {
        $data["id"] = 1;

        $serviceMock = $this->getMockBuilder(
            "\Box\Mod\Servicehosting\Service"
        )->getMock();
        $serviceMock
            ->expects($this->atLeastOnce())
            ->method("testConnection")
            ->will($this->returnValue(true));

        $dbMock = $this->getMockBuilder("\Box_Database")->getMock();
        $dbMock
            ->expects($this->atLeastOnce())
            ->method("getExistingModelById")
            ->will($this->returnValue(new \Model_ServiceHostingServer()));
        $validatorMock = $this->getMockBuilder("\Box_Validate")
            ->disableOriginalConstructor()
            ->getMock();
        $validatorMock
            ->expects($this->atLeastOnce())
            ->method("checkRequiredParamsForArray")
            ->will($this->returnValue(null));

        $di = new \Box_Di();
        $di["db"] = $dbMock;
        $di["validator"] = $validatorMock;

        $this->api->setDi($di);
        $this->api->setService($serviceMock);

        $result = $this->api->server_test_connection($data);
        $this->assertIsBool($result);
        $this->assertTrue($result);
    }

    public function testhp_get_pairs()
    {
        $serviceMock = $this->getMockBuilder(
            "\Box\Mod\Servicehosting\Service"
        )->getMock();
        $serviceMock
            ->expects($this->atLeastOnce())
            ->method("getHpPairs")
            ->will($this->returnValue([]));

        $this->api->setService($serviceMock);
        $result = $this->api->hp_get_pairs([]);
        $this->assertIsArray($result);
    }

    public function testhp_get_list()
    {
        $serviceMock = $this->getMockBuilder(
            "\Box\Mod\Servicehosting\Service"
        )->getMock();
        $serviceMock
            ->expects($this->atLeastOnce())
            ->method("getHpSearchQuery")
            ->will($this->returnValue(["SQLstring", []]));

        $pagerMock = $this->getMockBuilder("\Box_Pagination")->getMock();
        $pagerMock
            ->expects($this->atLeastOnce())
            ->method("getSimpleResultSet")
            ->will($this->returnValue(["list" => []]));

        $di = new \Box_Di();
        $di["pager"] = $pagerMock;
        $di["array_get"] = $di->protect(function (
            array $array,
            $key,
            $default = null
        ) use ($di) {
            return isset($array[$key]) ? $array[$key] : $default;
        });

        $this->api->setDi($di);
        $this->api->setService($serviceMock);

        $result = $this->api->hp_get_list([]);
        $this->assertIsArray($result);
    }

    public function testhp_delete()
    {
        $data = [
            "id" => 1,
        ];

        $model = new \Model_ServiceHostingHp();

        $serviceMock = $this->getMockBuilder(
            "\Box\Mod\Servicehosting\Service"
        )->getMock();
        $serviceMock
            ->expects($this->atLeastOnce())
            ->method("deleteHp")
            ->will($this->returnValue(true));

        $dbMock = $this->getMockBuilder("\Box_Database")->getMock();
        $dbMock
            ->expects($this->atLeastOnce())
            ->method("getExistingModelById")
            ->will($this->returnValue($model));
        $validatorMock = $this->getMockBuilder("\Box_Validate")
            ->disableOriginalConstructor()
            ->getMock();
        $validatorMock
            ->expects($this->atLeastOnce())
            ->method("checkRequiredParamsForArray")
            ->will($this->returnValue(null));

        $di = new \Box_Di();
        $di["db"] = $dbMock;
        $di["validator"] = $validatorMock;

        $this->api->setDi($di);
        $this->api->setService($serviceMock);

        $result = $this->api->hp_delete($data);
        $this->assertIsBool($result);
        $this->assertTrue($result);
    }

    public function testhp_get()
    {
        $data = [
            "id" => 1,
        ];

        $model = new \Model_ServiceHostingHp();

        $serviceMock = $this->getMockBuilder(
            "\Box\Mod\Servicehosting\Service"
        )->getMock();
        $serviceMock
            ->expects($this->atLeastOnce())
            ->method("toHostingHpApiArray")
            ->will($this->returnValue([]));

        $dbMock = $this->getMockBuilder("\Box_Database")->getMock();
        $dbMock
            ->expects($this->atLeastOnce())
            ->method("getExistingModelById")
            ->will($this->returnValue($model));

        $validatorMock = $this->getMockBuilder("\Box_Validate")
            ->disableOriginalConstructor()
            ->getMock();
        $validatorMock
            ->expects($this->atLeastOnce())
            ->method("checkRequiredParamsForArray")
            ->will($this->returnValue(null));

        $di = new \Box_Di();
        $di["db"] = $dbMock;
        $di["validator"] = $validatorMock;

        $this->api->setDi($di);
        $this->api->setService($serviceMock);

        $result = $this->api->hp_get($data);
        $this->assertIsArray($result);
    }

    public function testhp_update()
    {
        $data = [
            "id" => 1,
        ];

        $model = new \Model_ServiceHostingHp();

        $serviceMock = $this->getMockBuilder(
            "\Box\Mod\Servicehosting\Service"
        )->getMock();
        $serviceMock
            ->expects($this->atLeastOnce())
            ->method("updateHp")
            ->will($this->returnValue(true));

        $dbMock = $this->getMockBuilder("\Box_Database")->getMock();
        $dbMock
            ->expects($this->atLeastOnce())
            ->method("getExistingModelById")
            ->will($this->returnValue($model));

        $validatorMock = $this->getMockBuilder("\Box_Validate")
            ->disableOriginalConstructor()
            ->getMock();
        $validatorMock
            ->expects($this->atLeastOnce())
            ->method("checkRequiredParamsForArray")
            ->will($this->returnValue(null));

        $di = new \Box_Di();
        $di["db"] = $dbMock;
        $di["validator"] = $validatorMock;

        $this->api->setDi($di);
        $this->api->setService($serviceMock);

        $result = $this->api->hp_update($data);
        $this->assertIsBool($result);
        $this->assertTrue($result);
    }

    public function testhp_create()
    {
        $data = [
            "name" => "test",
        ];

        $newHpId = 2;

        $serviceMock = $this->getMockBuilder(
            "\Box\Mod\Servicehosting\Service"
        )->getMock();
        $serviceMock
            ->expects($this->atLeastOnce())
            ->method("createHp")
            ->will($this->returnValue($newHpId));

        $validatorMock = $this->getMockBuilder("\Box_Validate")
            ->disableOriginalConstructor()
            ->getMock();
        $validatorMock
            ->expects($this->atLeastOnce())
            ->method("checkRequiredParamsForArray")
            ->will($this->returnValue(null));

        $di = new \Box_Di();
        $di["validator"] = $validatorMock;
        $this->api->setDi($di);

        $this->api->setService($serviceMock);

        $result = $this->api->hp_create($data);
        $this->assertIsInt($result);
        $this->assertEquals($newHpId, $result);
    }

    public function test_getService()
    {
        $data = [
            "order_id" => 1,
        ];

        $clientOrderModel = new \Model_ClientOrder();
        $dbMock = $this->getMockBuilder("\Box_Database")->getMock();
        $dbMock
            ->expects($this->atLeastOnce())
            ->method("getExistingModelById")
            ->will($this->returnValue($clientOrderModel));

        $model = new \Model_ServiceHosting();
        $orderServiceMock = $this->getMockBuilder(
            "\Box\Mod\Order\Service"
        )->getMock();
        $orderServiceMock
            ->expects($this->atLeastOnce())
            ->method("getOrderService")
            ->will($this->returnValue($model));
        $validatorMock = $this->getMockBuilder("\Box_Validate")
            ->disableOriginalConstructor()
            ->getMock();
        $validatorMock
            ->expects($this->atLeastOnce())
            ->method("checkRequiredParamsForArray")
            ->will($this->returnValue(null));

        $di = new \Box_Di();
        $di["mod_service"] = $di->protect(function () use ($orderServiceMock) {
            return $orderServiceMock;
        });
        $di["db"] = $dbMock;
        $di["validator"] = $validatorMock;

        $this->api->setDi($di);

        $result = $this->api->_getService($data);
        $this->assertIsArray($result);
        $this->assertInstanceOf("\Model_ClientOrder", $result[0]);
        $this->assertInstanceOf("\Model_ServiceHosting", $result[1]);
    }

    public function test_getServiceOrderNotActivated()
    {
        $data = [
            "order_id" => 1,
        ];

        $clientOrderModel = new \Model_ClientOrder();
        $dbMock = $this->getMockBuilder("\Box_Database")->getMock();
        $dbMock
            ->expects($this->atLeastOnce())
            ->method("getExistingModelById")
            ->will($this->returnValue($clientOrderModel));

        $model = null;
        $orderServiceMock = $this->getMockBuilder(
            "\Box\Mod\Order\Service"
        )->getMock();
        $orderServiceMock
            ->expects($this->atLeastOnce())
            ->method("getOrderService")
            ->will($this->returnValue($model));

        $validatorMock = $this->getMockBuilder("\Box_Validate")
            ->disableOriginalConstructor()
            ->getMock();
        $validatorMock
            ->expects($this->atLeastOnce())
            ->method("checkRequiredParamsForArray")
            ->will($this->returnValue(null));

        $di = new \Box_Di();
        $di["mod_service"] = $di->protect(function () use ($orderServiceMock) {
            return $orderServiceMock;
        });
        $di["db"] = $dbMock;
        $di["validator"] = $validatorMock;

        $this->api->setDi($di);

        $this->expectException(\Box_Exception::class);
        $this->expectExceptionMessage("Order is not activated");
        $this->api->_getService($data);
    }
}
