<?php

namespace Box\Mod\Invoice;

class ServiceSubscriptionTest extends \BBTestCase
{
    /**
     * @var \Box\Mod\Invoice\ServiceSubscription
     */
    protected $service = null;

    public function setup(): void
    {
        $this->service = new \Box\Mod\Invoice\ServiceSubscription();
    }

    public function testgetDi()
    {
        $di = new \Box_Di();
        $this->service->setDi($di);
        $getDi = $this->service->getDi();
        $this->assertEquals($di, $getDi);
    }

    public function testcreate()
    {
        $subscriptionModel = new \Model_Subscription();
        $subscriptionModel->loadBean(new \RedBeanPHP\OODBBean());
        $newId = 10;

        $dbMock = $this->getMockBuilder("\Box_Database")->getMock();
        $dbMock
            ->expects($this->atLeastOnce())
            ->method("dispense")
            ->will($this->returnValue($subscriptionModel));
        $dbMock
            ->expects($this->atLeastOnce())
            ->method("store")
            ->will($this->returnValue($newId));

        $eventsMock = $this->getMockBuilder("\Box_EventManager")->getMock();
        $eventsMock->expects($this->atLeastOnce())->method("fire");

        $di = new \Box_Di();
        $di["db"] = $dbMock;
        $di["logger"] = new \Box_Log();
        $di["events_manager"] = $eventsMock;
        $di["array_get"] = $di->protect(function (
            array $array,
            $key,
            $default = null
        ) use ($di) {
            return isset($array[$key]) ? $array[$key] : $default;
        });
        $this->service->setDi($di);

        $data = [
            "client_id" => 1,
            "gateway_id" => 2,
        ];

        $result = $this->service->create(
            new \Model_Client(),
            new \Model_PayGateway(),
            $data
        );
        $this->assertIsInt($result);
        $this->assertEquals($newId, $result);
    }

    public function testupdate()
    {
        $subscriptionModel = new \Model_Subscription();
        $subscriptionModel->loadBean(new \RedBeanPHP\OODBBean());
        $data = [
            "status" => "",
            "sid" => "",
            "period" => "",
            "amount" => "",
            "currency" => "",
        ];

        $dbMock = $this->getMockBuilder("\Box_Database")->getMock();
        $dbMock->expects($this->atLeastOnce())->method("store");

        $di = new \Box_Di();
        $di["db"] = $dbMock;
        $di["logger"] = new \Box_Log();
        $di["array_get"] = $di->protect(function (
            array $array,
            $key,
            $default = null
        ) use ($di) {
            return isset($array[$key]) ? $array[$key] : $default;
        });
        $this->service->setDi($di);

        $result = $this->service->update($subscriptionModel, $data);
        $this->assertTrue($result);
    }

    public function testtoApiArray()
    {
        $subscriptionModel = new \Model_Subscription();
        $subscriptionModel->loadBean(new \RedBeanPHP\OODBBean());

        $clientModel = new \Model_Client();
        $clientModel->loadBean(new \RedBeanPHP\OODBBean());

        $gatewayModel = new \Model_PayGateway();
        $gatewayModel->loadBean(new \RedBeanPHP\OODBBean());

        $dbMock = $this->getMockBuilder("\Box_Database")->getMock();
        $dbMock
            ->expects($this->atLeastOnce())
            ->method("load")
            ->will($this->onConsecutiveCalls($clientModel, $gatewayModel));

        $clientServiceMock = $this->getMockBuilder(
            "\Box\Mod\Client\Service"
        )->getMock();
        $clientServiceMock
            ->expects($this->atLeastOnce())
            ->method("toApiArray")
            ->will($this->returnValue([]));

        $payGatewayService = $this->getMockBuilder(
            "\Box\Mod\Invoice\ServicePayGateway"
        )->getMock();
        $payGatewayService
            ->expects($this->atLeastOnce())
            ->method("toApiArray")
            ->will($this->returnValue([]));

        $di = new \Box_Di();
        $di["mod_service"] = $di->protect(function (
            $serviceName,
            $sub = ""
        ) use ($clientServiceMock, $payGatewayService) {
            if ($serviceName == "Client") {
                return $clientServiceMock;
            }
            if ($sub == "PayGateway") {
                return $payGatewayService;
            }
        });
        $di["db"] = $dbMock;
        $this->service->setDi($di);

        $expected = [
            "id" => "",
            "sid" => "",
            "period" => "",
            "amount" => "",
            "currency" => "",
            "status" => "",
            "created_at" => "",
            "updated_at" => "",
            "client" => [],
            "gateway" => [],
        ];

        $result = $this->service->toApiArray($subscriptionModel);
        $this->assertIsArray($result);
        $this->assertIsArray($result["client"]);
        $this->assertIsArray($result["gateway"]);
        $this->assertEquals($expected, $result);
    }

    public function testdelete()
    {
        $subscriptionModel = new \Model_Subscription();
        $subscriptionModel->loadBean(new \RedBeanPHP\OODBBean());

        $dbMock = $this->getMockBuilder("\Box_Database")->getMock();
        $dbMock->expects($this->atLeastOnce())->method("trash");

        $eventsMock = $this->getMockBuilder("\Box_EventManager")->getMock();
        $eventsMock->expects($this->atLeastOnce())->method("fire");

        $di = new \Box_Di();
        $di["db"] = $dbMock;
        $di["logger"] = new \Box_Log();
        $di["events_manager"] = $eventsMock;
        $this->service->setDi($di);

        $result = $this->service->delete($subscriptionModel);
        $this->assertTrue($result);
    }

    public function searchQueryData()
    {
        return [
            [[], "FROM subscription", []],
            [
                ["status" => "active"],
                "AND status = :status",
                ["status" => "active"],
            ],
            [
                ["invoice_id" => "1"],
                "AND invoice_id = :invoice_id",
                ["invoice_id" => "1"],
            ],
            [
                ["gateway_id" => "2"],
                "AND gateway_id = :gateway_id",
                ["gateway_id" => "2"],
            ],
            [
                ["client_id" => "3"],
                "AMD client_id  = :client_id",
                ["client_id" => "3"],
            ],
            [
                ["currency" => "EUR"],
                "AND currency =  :currency",
                ["currency" => "EUR"],
            ],
            [
                ["date_from" => "1234567"],
                "AND UNIX_TIMESTAMP(m.created_at) >= :date_from",
                ["date_from" => "1234567"],
            ],
            [
                ["date_to" => "1234567"],
                "AND UNIX_TIMESTAMP(m.created_at) <= :date_to",
                ["date_to" => "1234567"],
            ],
            [["id" => "10"], "AND id = :id", ["id" => "10"]],
            [["sid" => "10"], "AND sid = :sid", ["sid" => "10"]],
        ];
    }

    /**
     * @dataProvider searchQueryData
     */
    public function testgetSearchQuery($data, $expectedSqlPart, $expectedParams)
    {
        $di = new \Box_Di();
        $di["array_get"] = $di->protect(function (
            array $array,
            $key,
            $default = null
        ) use ($di) {
            return isset($array[$key]) ? $array[$key] : $default;
        });
        $this->service->setDi($di);
        $result = $this->service->getSearchQuery($data);

        $this->assertIsArray($result);
        $this->assertIsString($result[0]);
        $this->assertIsArray($result[1]);

        $this->assertEquals($expectedParams, $result[1]);
        $this->assertTrue(strpos($result[0], $expectedSqlPart) !== false);
    }

    public function testisSubscribableisNotSusbcribable()
    {
        $dbMock = $this->getMockBuilder("\Box_Database")->getMock();
        $dbMock
            ->expects($this->atLeastOnce())
            ->method("getCell")
            ->will($this->returnValue([""]));

        $di = new \Box_Di();
        $di["db"] = $dbMock;
        $this->service->setDi($di);

        $invoice_id = 2;
        $result = $this->service->isSubscribable($invoice_id);
        $this->assertIsBool($result);
        $this->assertFalse($result);
    }

    public function testisSubscribable()
    {
        $dbMock = $this->getMockBuilder("\Box_Database")->getMock();
        $dbMock
            ->expects($this->atLeastOnce())
            ->method("getCell")
            ->will($this->returnValue(null));

        $getAllResults = [
            0 => ["period" => "1W"],
        ];
        $dbMock
            ->expects($this->atLeastOnce())
            ->method("getAll")
            ->will($this->returnValue($getAllResults));

        $di = new \Box_Di();
        $di["db"] = $dbMock;
        $this->service->setDi($di);

        $invoice_id = 2;
        $result = $this->service->isSubscribable($invoice_id);
        $this->assertIsBool($result);
        $this->assertTrue($result);
    }

    public function testgetSubscriptionPeriod()
    {
        $serviceMock = $this->getMockBuilder(
            "\Box\Mod\Invoice\ServiceSubscription"
        )
            ->setMethods(["isSubscribable"])
            ->getMock();

        $serviceMock
            ->expects($this->atLeastOnce())
            ->method("isSubscribable")
            ->will($this->returnValue(true));

        $period = "1W";
        $dbMock = $this->getMockBuilder("\Box_Database")->getMock();
        $dbMock
            ->expects($this->atLeastOnce())
            ->method("getCell")
            ->will($this->returnValue($period));

        $di = new \Box_Di();
        $di["db"] = $dbMock;
        $serviceMock->setDi($di);

        $invoiceModel = new \Model_Invoice();
        $invoiceModel->loadBean(new \RedBeanPHP\OODBBean());

        $result = $serviceMock->getSubscriptionPeriod($invoiceModel);
        $this->assertIsString($result);
        $this->assertEquals($period, $result);
    }

    public function testunsubscribe()
    {
        $subscribtionModel = new \Model_Subscription();
        $subscribtionModel->loadBean(new \RedBeanPHP\OODBBean());
        $dbMock = $this->getMockBuilder("\Box_Database")->getMock();
        $dbMock->expects($this->atLeastOnce())->method("store");

        $di = new \Box_Di();
        $di["db"] = $dbMock;
        $this->service->setDi($di);

        $this->service->unsubscribe($subscribtionModel);
    }
}
