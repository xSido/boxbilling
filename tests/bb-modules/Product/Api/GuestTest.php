<?php

namespace Box\Mod\Product\Api;

class GuestTest extends \BBTestCase
{
    /**
     * @var \Box\Mod\Product\Api\Guest
     */
    protected $api = null;

    public function setup(): void
    {
        $this->api = new \Box\Mod\Product\Api\Guest();
    }

    public function testgetDi()
    {
        $di = new \Box_Di();
        $this->api->setDi($di);
        $getDi = $this->api->getDi();
        $this->assertEquals($di, $getDi);
    }

    public function testget_list()
    {
        $serviceMock = $this->getMockBuilder(
            "\Box\Mod\Product\Service"
        )->getMock();
        $serviceMock
            ->expects($this->atLeastOnce())
            ->method("getProductSearchQuery")
            ->will($this->returnValue(["sqlString", []]));

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

        $this->api->setService($serviceMock);
        $this->api->setDi($di);
        $result = $this->api->get_list([]);
        $this->assertIsArray($result);
    }

    public function testget_pairs()
    {
        $serviceMock = $this->getMockBuilder(
            "\Box\Mod\Product\Service"
        )->getMock();

        $serviceMock
            ->expects($this->atLeastOnce())
            ->method("getPairs")
            ->will($this->returnValue([]));

        $this->api->setService($serviceMock);
        $result = $this->api->get_pairs([]);
        $this->assertIsArray($result);
    }

    public function testgetMissingRequiredParams()
    {
        $data = [];

        $this->expectException(\Box_Exception::class);
        $this->expectExceptionMessage("Product ID or slug is missing");
        $this->api->get($data);
    }

    public function testgetWithSetId()
    {
        $data = [
            "id" => 1,
        ];

        $model = new \Model_Product();

        $serviceMock = $this->getMockBuilder(
            "\Box\Mod\Product\Service"
        )->getMock();
        $serviceMock
            ->expects($this->atLeastOnce())
            ->method("findOneActiveById")
            ->will($this->returnValue($model));
        $serviceMock
            ->expects($this->atLeastOnce())
            ->method("toApiArray")
            ->will($this->returnValue([]));

        $di = new \Box_Di();
        $di["array_get"] = $di->protect(function (
            array $array,
            $key,
            $default = null
        ) use ($di) {
            return isset($array[$key]) ? $array[$key] : $default;
        });
        $this->api->setDi($di);
        $this->api->setService($serviceMock);
        $result = $this->api->get($data);
        $this->assertIsArray($result);
    }

    public function testgetWithSetSlug()
    {
        $data = [
            "slug" => "product/1",
        ];

        $model = new \Model_Product();

        $serviceMock = $this->getMockBuilder(
            "\Box\Mod\Product\Service"
        )->getMock();
        $serviceMock
            ->expects($this->atLeastOnce())
            ->method("findOneActiveBySlug")
            ->will($this->returnValue($model));
        $serviceMock
            ->expects($this->atLeastOnce())
            ->method("toApiArray")
            ->will($this->returnValue([]));

        $di = new \Box_Di();
        $di["array_get"] = $di->protect(function (
            array $array,
            $key,
            $default = null
        ) use ($di) {
            return isset($array[$key]) ? $array[$key] : $default;
        });
        $this->api->setDi($di);
        $this->api->setService($serviceMock);
        $result = $this->api->get($data);
        $this->assertIsArray($result);
    }

    public function testgetProductNotFound()
    {
        $data = [
            "slug" => "product/1",
        ];

        $model = null;

        $serviceMock = $this->getMockBuilder(
            "\Box\Mod\Product\Service"
        )->getMock();
        $serviceMock
            ->expects($this->atLeastOnce())
            ->method("findOneActiveBySlug")
            ->will($this->returnValue($model));
        $di = new \Box_Di();
        $di["array_get"] = $di->protect(function (
            array $array,
            $key,
            $default = null
        ) use ($di) {
            return isset($array[$key]) ? $array[$key] : $default;
        });
        $this->api->setDi($di);
        $this->api->setService($serviceMock);

        $this->expectException(\Box_Exception::class);
        $this->expectExceptionMessage("Product not found");
        $this->api->get($data);
    }

    public function testcategory_get_list()
    {
        $serviceMock = $this->getMockBuilder(
            "\Box\Mod\Product\Service"
        )->getMock();
        $serviceMock
            ->expects($this->atLeastOnce())
            ->method("getProductCategorySearchQuery")
            ->will($this->returnValue(["sqlString", []]));
        $serviceMock
            ->expects($this->atLeastOnce())
            ->method("toProductCategoryApiArray")
            ->will($this->returnValue([]));

        $pager = [
            "list" => [
                0 => ["id" => 1],
            ],
        ];
        $pagerMock = $this->getMockBuilder("\Box_Pagination")->getMock();
        $pagerMock
            ->expects($this->atLeastOnce())
            ->method("getAdvancedResultSet")
            ->will($this->returnValue($pager));

        $modelProductCategory = new \Model_ProductCategory();
        $modelProductCategory->loadBean(new \RedBeanPHP\OODBBean());
        $dbMock = $this->getMockBuilder("\Box_Database")->getMock();
        $dbMock
            ->expects($this->atLeastOnce())
            ->method("getExistingModelById")
            ->will($this->returnValue($modelProductCategory));

        $di = new \Box_Di();
        $di["db"] = $dbMock;
        $di["pager"] = $pagerMock;
        $di["array_get"] = $di->protect(function (
            array $array,
            $key,
            $default = null
        ) use ($di) {
            return isset($array[$key]) ? $array[$key] : $default;
        });

        $this->api->setService($serviceMock);
        $this->api->setDi($di);
        $result = $this->api->category_get_list([]);
        $this->assertIsArray($result);
    }

    public function testcategory_get_pairs()
    {
        $serviceMock = $this->getMockBuilder(
            "\Box\Mod\Product\Service"
        )->getMock();
        $serviceMock
            ->expects($this->atLeastOnce())
            ->method("getProductCategoryPairs")
            ->will($this->returnValue([]));

        $this->api->setService($serviceMock);
        $result = $this->api->category_get_pairs([]);
        $this->assertIsArray($result);
    }

    public function testget_sliderEmptyList()
    {
        $dbMock = $this->getMockBuilder("\Box_Database")->getMock();
        $dbMock
            ->expects($this->atLeastOnce())
            ->method("find")
            ->will($this->returnValue([]));

        $di = new \Box_Di();
        $di["db"] = $dbMock;
        $di["array_get"] = $di->protect(function (
            array $array,
            $key,
            $default = null
        ) use ($di) {
            return isset($array[$key]) ? $array[$key] : $default;
        });

        $this->api->setDi($di);

        $result = $this->api->get_slider([]);
        $this->assertIsArray($result);
        $this->assertEquals([], $result);
    }

    public function testget_slider()
    {
        $productModel = new \Model_Product();
        $productModel->loadBean(new \RedBeanPHP\OODBBean());
        $dbMock = $this->getMockBuilder("\Box_Database")->getMock();
        $dbMock
            ->expects($this->atLeastOnce())
            ->method("find")
            ->will($this->returnValue([$productModel]));

        $di = new \Box_Di();
        $di["db"] = $dbMock;
        $di["array_get"] = $di->protect(function (
            array $array,
            $key,
            $default = null
        ) use ($di) {
            return isset($array[$key]) ? $array[$key] : $default;
        });
        $this->api->setDi($di);

        $arr = [
            "id" => 1,
            "slug" => "/",
            "title" => "New Item",
            "pricing" => "1W",
            "config" => [],
        ];
        $serviceMock = $this->getMockBuilder(
            "\Box\Mod\Product\Service"
        )->getMock();
        $serviceMock
            ->expects($this->atLeastOnce())
            ->method("toApiArray")
            ->will($this->returnValue($arr));

        $this->api->setService($serviceMock);
        $result = $this->api->get_slider([]);
        $this->assertIsArray($result);
    }

    public function testget_sliderJsonFormat()
    {
        $productModel = new \Model_Product();
        $productModel->loadBean(new \RedBeanPHP\OODBBean());
        $dbMock = $this->getMockBuilder("\Box_Database")->getMock();
        $dbMock
            ->expects($this->atLeastOnce())
            ->method("find")
            ->will($this->returnValue([$productModel]));

        $di = new \Box_Di();
        $di["db"] = $dbMock;
        $di["array_get"] = $di->protect(function (
            array $array,
            $key,
            $default = null
        ) use ($di) {
            return isset($array[$key]) ? $array[$key] : $default;
        });
        $this->api->setDi($di);

        $arr = [
            "id" => 1,
            "slug" => "/",
            "title" => "New Item",
            "pricing" => "1W",
            "config" => [],
        ];
        $serviceMock = $this->getMockBuilder(
            "\Box\Mod\Product\Service"
        )->getMock();
        $serviceMock
            ->expects($this->atLeastOnce())
            ->method("toApiArray")
            ->will($this->returnValue($arr));

        $this->api->setService($serviceMock);
        $result = $this->api->get_slider([]);
        $this->assertIsArray($result);

        $result = $this->api->get_slider(["format" => "json"]);
        $this->assertIsString($result);
        $this->assertIsArray(json_decode($result, 1));
    }
}
