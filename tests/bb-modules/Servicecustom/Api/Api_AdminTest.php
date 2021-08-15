<?php
namespace Box\Tests\Mod\Servicecustom\Api;

class Api_AdminTest extends \BBTestCase
{
    /**
     * @var \Box\Mod\Servicecustom\Api\Admin
     */
    protected $api = null;

    public function setup(): void
    {
        $this->api = new \Box\Mod\Servicecustom\Api\Admin();
    }

    public function testUpdate()
    {
        $serviceMock = $this->getMockBuilder("Box\Mod\Servicecustom\Service")
            ->setMethods(["updateConfig"])
            ->getMock();
        $serviceMock
            ->expects($this->atLeastOnce())
            ->method("updateConfig")
            ->will($this->returnValue(null));

        $data = [
            "order_id" => rand(1, 100),
            "config" => [
                "param1" => "value1",
            ],
        ];

        $this->api->setService($serviceMock);

        $this->api->update($data);
    }

    public function testUpdateOrderIdNotSetException()
    {
        $serviceMock = $this->getMockBuilder("Box\Mod\Servicecustom\Service")
            ->setMethods(["updateConfig"])
            ->getMock();
        $serviceMock
            ->expects($this->never())
            ->method("updateConfig")
            ->will($this->returnValue(null));

        $data = [
            "config" => [
                "param1" => "value1",
            ],
        ];

        $this->api->setService($serviceMock);
        $this->expectException(\Exception::class);
        $result = $this->api->update($data);

        $this->assertTrue($result);
    }

    public function testUpdateConfigNotSet()
    {
        $serviceMock = $this->getMockBuilder("Box\Mod\Servicecustom\Service")
            ->setMethods(["updateConfig"])
            ->getMock();
        $serviceMock
            ->expects($this->never())
            ->method("updateConfig")
            ->will($this->returnValue(null));

        $data = [
            "order_id" => rand(1, 100),
        ];

        $this->api->setService($serviceMock);

        $result = $this->api->update($data);
        $this->assertTrue($result);
    }

    public function testUpdateConfigIsNotArray()
    {
        $serviceMock = $this->getMockBuilder("Box\Mod\Servicecustom\Service")
            ->setMethods(["updateConfig"])
            ->getMock();
        $serviceMock
            ->expects($this->never())
            ->method("updateConfig")
            ->will($this->returnValue(null));

        $data = [
            "order_id" => rand(1, 100),
            "config" => "NotArray",
        ];

        $this->api->setService($serviceMock);

        $result = $this->api->update($data);
        $this->assertTrue($result);
    }

    public function testCall()
    {
        $serviceMock = $this->getMockBuilder(
            "Box\Mod\Servicecustom\Service"
        )->getMock();
        $serviceMock
            ->expects($this->atLeastOnce())
            ->method("getServiceCustomByOrderId")
            ->will($this->returnValue(new \Model_ServiceCustom()));
        $serviceMock
            ->expects($this->atLeastOnce())
            ->method("customCall")
            ->will($this->returnValue(null));

        $arguments = [
            0 => [
                "order_id" => rand(1, 100),
            ],
        ];

        $this->api->setService($serviceMock);

        $this->api->__call("delete", $arguments);
    }

    public function testCallArgumentsNotSetException()
    {
        $serviceMock = $this->getMockBuilder(
            "Box\Mod\Servicecustom\Service"
        )->getMock();
        $serviceMock
            ->expects($this->never())
            ->method("getServiceCustomByOrderId")
            ->will($this->returnValue(new \Model_ServiceCustom()));
        $serviceMock
            ->expects($this->never())
            ->method("customCall")
            ->will($this->returnValue(null));

        $arguments = [];

        $this->api->setService($serviceMock);
        $this->expectException(\Exception::class);
        $this->api->__call("delete", $arguments);
    }

    public function testCallOrderIdNotSetException()
    {
        $serviceMock = $this->getMockBuilder(
            "Box\Mod\Servicecustom\Service"
        )->getMock();
        $serviceMock
            ->expects($this->never())
            ->method("getServiceCustomByOrderId")
            ->will($this->returnValue(new \Model_ServiceCustom()));
        $serviceMock
            ->expects($this->never())
            ->method("customCall")
            ->will($this->returnValue(null));

        $arguments = [
            0 => [],
        ];

        $this->api->setService($serviceMock);
        $this->expectException(\Exception::class);
        $this->api->__call("delete", $arguments);
    }
}
