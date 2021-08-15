<?php

namespace Box\Mod\Product;

class ServiceTest extends \BBTestCase
{
    /**
     * @var \Box\Mod\Product\Service
     */
    protected $service = null;

    public function setup(): void
    {
        $this->service = new \Box\Mod\Product\Service();
    }

    public function testgetDi()
    {
        $di = new \Box_Di();
        $this->service->setDi($di);
        $getDi = $this->service->getDi();
        $this->assertEquals($di, $getDi);
    }

    public function testgetPairs()
    {
        $data = [
            "type" => "domain",
            "products_only" => true,
            "active_only" => true,
        ];

        $execArray = [
            [
                "id" => 1,
                "title" => "title4test",
            ],
        ];

        $expectArray = [
            "1" => "title4test",
        ];

        $dbMock = $this->getMockBuilder("\Box_Database")->getMock();
        $dbMock
            ->expects($this->atLeastOnce())
            ->method("getAll")
            ->will($this->returnValue($execArray));

        $di = new \Box_Di();
        $di["db"] = $dbMock;
        $di["array_get"] = $di->protect(function (
            array $array,
            $key,
            $default = null
        ) use ($di) {
            return isset($array[$key]) ? $array[$key] : $default;
        });

        $this->service->setDi($di);

        $result = $this->service->getPairs($data);
        $this->assertIsArray($result);
        $this->assertEquals($expectArray, $result);
    }

    public function testtoApiArray()
    {
        $serviceMock = $this->getMockBuilder("\Box\Mod\Product\Service")
            ->setMethods([
                "getStartingFromPrice",
                "getUpgradablePairs",
                "toProductPaymentApiArray",
            ])
            ->getMock();

        $serviceMock
            ->expects($this->atLeastOnce())
            ->method("getStartingFromPrice");
        $serviceMock
            ->expects($this->atLeastOnce())
            ->method("getUpgradablePairs");
        $productPaymentArray = [
            "type" => "free",
            \Model_ProductPayment::FREE => ["price" => 0, "setup" => 0],
            \Model_ProductPayment::ONCE => ["price" => 1, "setup" => 10],
            \Model_ProductPayment::RECURRENT => [],
        ];
        $serviceMock
            ->expects($this->atLeastOnce())
            ->method("toProductPaymentApiArray")
            ->will($this->returnValue($productPaymentArray));

        $model = new \Model_Product();
        $model->loadBean(new \RedBeanPHP\OODBBean());
        $model->product_category_id = 1;
        $model->product_payment_id = 2;
        $model->config = "{}";

        $modelProductCategory = new \Model_ProductCategory();
        $modelProductCategory->loadBean(new \RedBeanPHP\OODBBean());
        $modelProductCategory->type = "free";

        $modelProductPayment = new \Model_ProductPayment();

        $dbMock = $this->getMockBuilder("\Box_Database")->getMock();
        $dbMock
            ->expects($this->atLeastOnce())
            ->method("load")
            ->will(
                $this->onConsecutiveCalls(
                    $modelProductPayment,
                    $modelProductCategory
                )
            );

        $toolsMock = $this->getMockBuilder("\Box_Tools")->getMock();
        $toolsMock
            ->expects($this->atLeastOnce())
            ->method("decodeJ")
            ->will($this->returnValue([]));

        $di = new \Box_Di();
        $di["db"] = $dbMock;
        $di["tools"] = $toolsMock;
        $di["mod_service"] = $di->protect(function () use ($serviceMock) {
            return $serviceMock;
        });

        $model->setDi($di);
        $serviceMock->setDi($di);

        $result = $serviceMock->toApiArray($model, true, new \Model_Admin());
        $this->assertIsArray($result);
    }

    public function testgetTypes()
    {
        $modArray = ["servicecustomtest"];

        $expectedArray = [
            "custom" => "Custom",
            "license" => "License",
            "downloadable" => "Downloadable",
            "hosting" => "Hosting",
            "domain" => "Domain",
        ];

        $expectedArray["customtest"] = "Customtest";

        $extensionServiceMock = $this->getMockBuilder(
            "\Box\Mod\Extension\Service"
        )->getMock();
        $extensionServiceMock
            ->expects($this->atLeastOnce())
            ->method("getInstalledMods")
            ->will($this->returnValue($modArray));

        $di = new \Box_Di();
        $di["mod_service"] = $di->protect(function () use (
            $extensionServiceMock
        ) {
            return $extensionServiceMock;
        });

        $this->service->setDi($di);
        $result = $this->service->getTypes();
        $this->assertIsArray($result);
        $this->assertEquals($expectedArray, $result);
    }

    public function testgetMainDomainProduct()
    {
        $model = new \Model_Product();

        $dbMock = $this->getMockBuilder("\Box_Database")->getMock();
        $dbMock
            ->expects($this->atLeastOnce())
            ->method("findOne")
            ->will($this->returnValue($model));

        $di = new \Box_Di();
        $di["db"] = $dbMock;

        $this->service->setDi($di);

        $result = $this->service->getMainDomainProduct();
        $this->assertInstanceOf("\Model_Product", $result);
    }

    public function testgetPaymentTypes()
    {
        $expected = [
            "free" => "Free",
            "once" => "One time",
            "recurrent" => "Recurrent",
        ];

        $result = $this->service->getPaymentTypes();
        $this->assertIsArray($result);
        $this->assertEquals($expected, $result);
    }

    public function testcreateProduct()
    {
        $systemServiceMock = $this->getMockBuilder(
            "\Box\Mod\System\Service"
        )->getMock();
        $systemServiceMock
            ->expects($this->atLeastOnce())
            ->method("checkLimits");

        $modelPayment = new \Model_ProductPayment();
        $modelPayment->loadBean(new \RedBeanPHP\OODBBean());

        $modelProduct = new \Model_Product();
        $modelProduct->loadBean(new \RedBeanPHP\OODBBean());

        $newProductId = 1;

        $dbMock = $this->getMockBuilder("\Box_Database")->getMock();
        $dbMock
            ->expects($this->atLeastOnce())
            ->method("getCell")
            ->will($this->returnValue(0));

        $dbMock
            ->expects($this->atLeastOnce())
            ->method("dispense")
            ->will($this->onConsecutiveCalls($modelPayment, $modelProduct));

        $dbMock
            ->expects($this->atLeastOnce())
            ->method("store")
            ->will($this->returnValue($newProductId));

        $toolMock = $this->getMockBuilder("\Box_Tools")->getMock();
        $toolMock->expects($this->atLeastOnce())->method("slug");

        $di = new \Box_Di();
        $di["mod_service"] = $di->protect(function () use ($systemServiceMock) {
            return $systemServiceMock;
        });
        $di["db"] = $dbMock;
        $di["tools"] = $toolMock;
        $di["logger"] = new \Box_Log();

        $this->service->setDi($di);
        $result = $this->service->createProduct("title", "domain");
        $this->assertIsInt($result);
        $this->assertEquals($newProductId, $result);
    }

    public function testupdateProductMissngPricingType()
    {
        $serviceMock = $this->getMockBuilder("\Box\Mod\Product\Service")
            ->setMethods(["getPaymentTypes"])
            ->getMock();

        $typesArr = [
            "free" => "Free",
            "once" => "One time",
            "recurrent" => "Recurrent",
        ];
        $serviceMock
            ->expects($this->atLeastOnce())
            ->method("getPaymentTypes")
            ->will($this->returnValue($typesArr));

        $data = [
            "pricing" => [],
        ];

        $modelProduct = new \Model_Product();
        $modelProduct->loadBean(new \RedBeanPHP\OODBBean());

        $this->expectException(\Box_Exception::class);
        $this->expectExceptionMessage("Pricing type is required");
        $serviceMock->updateProduct($modelProduct, $data);
    }

    public function testupdateProduct()
    {
        $modelProduct = new \Model_Product();
        $modelProduct->loadBean(new \RedBeanPHP\OODBBean());

        $serviceMock = $this->getMockBuilder("\Box\Mod\Product\Service")
            ->setMethods(["getPaymentTypes"])
            ->getMock();

        $typesArr = [
            "free" => "Free",
            "once" => "One time",
            "recurrent" => "Recurrent",
        ];
        $serviceMock
            ->expects($this->atLeastOnce())
            ->method("getPaymentTypes")
            ->will($this->returnValue($typesArr));

        $data = [
            "pricing" => [
                "type" => \Model_ProductPayment::RECURRENT,
                \Model_ProductPayment::RECURRENT => [
                    [
                        "1W" => [
                            "setup" => "",
                            "price" => "",
                            "enabled" => true,
                        ],
                    ],
                ],
            ],
            "config" => [],
            "product_category_id" => 1,
            "form_id" => 10,
            "icon_url" => "http://www.google.com",
            "status" => false,
            "hidden" => 0,
            "slug" => "product/0",
            "setup" => "test",
            "upgrades" => [],
            "addons" => [],
            "title" => "new Title",
            "stock_control" => false,
            "allow_quantity_select" => false,
            "quantity_in_stock" => 0,
            "description" => "Product description",
            "plugin" => "plug in",
        ];

        $modelProductPayment = new \Model_ProductPayment();
        $modelProductPayment->loadBean(new \RedBeanPHP\OODBBean());

        $dbMock = $this->getMockBuilder("\Box_Database")->getMock();
        $dbMock
            ->expects($this->atLeastOnce())
            ->method("getExistingModelById")
            ->will($this->returnValue($modelProductPayment));

        $dbMock
            ->expects($this->atLeastOnce())
            ->method("store")
            ->will($this->returnValue(1));

        $toolMock = $this->getMockBuilder("\Box_Tools")->getMock();
        $toolMock
            ->expects($this->atLeastOnce())
            ->method("decodeJ")
            ->will($this->returnValue([]));

        $di = new \Box_Di();
        $di["db"] = $dbMock;
        $di["tools"] = $toolMock;
        $di["logger"] = new \Box_Log();
        $di["array_get"] = $di->protect(function (
            array $array,
            $key,
            $default = null
        ) use ($di) {
            return isset($array[$key]) ? $array[$key] : $default;
        });

        $serviceMock->setDi($di);

        $result = $serviceMock->updateProduct($modelProduct, $data);
        $this->assertTrue($result);
    }

    public function testupdatePriority()
    {
        $data = [
            "priority" => [
                1 => 10,
                5 => 1,
            ],
        ];

        $modelProduct = new \Model_Product();
        $modelProduct->loadBean(new \RedBeanPHP\OODBBean());

        $dbMock = $this->getMockBuilder("\Box_Database")->getMock();
        $dbMock
            ->expects($this->atLeastOnce())
            ->method("load")
            ->will($this->returnValue($modelProduct));

        $dbMock->expects($this->atLeastOnce())->method("store");

        $di = new \Box_Di();
        $di["db"] = $dbMock;
        $di["logger"] = new \Box_Log();

        $this->service->setDi($di);

        $result = $this->service->updatePriority($data);
        $this->assertTrue($result);
    }

    public function testupdateConfig()
    {
        $modelProduct = new \Model_Product();
        $modelProduct->loadBean(new \RedBeanPHP\OODBBean());
        $modelProduct->config = '{"settings":5,"max":"10"}';

        $data = [
            "config" => [
                "settings" => 3,
                "max" => "",
            ],
            "new_config_name" => "newParam",
            "new_config_value" => "newValue",
        ];

        $dbMock = $this->getMockBuilder("\Box_Database")->getMock();

        $dbMock->expects($this->atLeastOnce())->method("store");

        $di = new \Box_Di();
        $di["db"] = $dbMock;
        $di["logger"] = new \Box_Log();

        $this->service->setDi($di);

        $result = $this->service->updateConfig($modelProduct, $data);
        $this->assertTrue($result);
    }

    public function testgetAddons()
    {
        $addonsRows = [
            [
                "id" => 1,
                "title" => "testTitle",
            ],
        ];

        $expected = [
            1 => "testTitle",
        ];

        $dbMock = $this->getMockBuilder("\Box_Database")->getMock();
        $dbMock
            ->expects($this->atLeastOnce())
            ->method("getAll")
            ->will($this->returnValue($addonsRows));

        $di = new \Box_Di();
        $di["db"] = $dbMock;
        $di["logger"] = new \Box_Log();

        $this->service->setDi($di);

        $result = $this->service->getAddons();
        $this->assertIsArray($result);
        $this->assertEquals($expected, $result);
    }

    public function testcreateAddon()
    {
        $newProductId = 1;

        $modelPayment = new \Model_ProductPayment();
        $modelPayment->loadBean(new \RedBeanPHP\OODBBean());

        $modelProduct = new \Model_Product();
        $modelProduct->loadBean(new \RedBeanPHP\OODBBean());

        $dbMock = $this->getMockBuilder("\Box_Database")->getMock();
        $dbMock
            ->expects($this->atLeastOnce())
            ->method("store")
            ->will($this->returnValue($newProductId));
        $dbMock
            ->expects($this->atLeastOnce())
            ->method("dispense")
            ->will($this->onConsecutiveCalls($modelPayment, $modelProduct));

        $toolMock = $this->getMockBuilder("\Box_Tools")->getMock();
        $toolMock->expects($this->atLeastOnce())->method("slug");

        $di = new \Box_Di();
        $di["db"] = $dbMock;
        $di["logger"] = new \Box_Log();
        $di["tools"] = $toolMock;

        $this->service->setDi($di);

        $result = $this->service->createAddon("title");
        $this->assertIsInt($result);
        $this->assertEquals($newProductId, $result);
    }

    public function testdeleteProductActivaOrderException()
    {
        $model = new \Model_Product();

        $orderServiceMock = $this->getMockBuilder(
            "\Box\Mod\Order\Service"
        )->getMock();
        $orderServiceMock
            ->expects($this->atLeastOnce())
            ->method("productHasOrders")
            ->will($this->returnValue(true));

        $di = new \Box_Di();
        $di["mod_service"] = $di->protect(function () use ($orderServiceMock) {
            return $orderServiceMock;
        });

        $this->service->setDi($di);

        $this->expectException(\Box_Exception::class);
        $this->expectExceptionMessage(
            "Can not remove product which has active orders."
        );
        $this->service->deleteProduct($model);
    }

    public function testgetProductCategoryPairs()
    {
        $execArray = [
            [
                "id" => 1,
                "title" => "title4test",
            ],
        ];

        $expectArray = [
            "1" => "title4test",
        ];

        $dbMock = $this->getMockBuilder("\Box_Database")->getMock();
        $dbMock
            ->expects($this->atLeastOnce())
            ->method("getAll")
            ->will($this->returnValue($execArray));

        $di = new \Box_Di();
        $di["db"] = $dbMock;

        $this->service->setDi($di);
        $result = $this->service->getProductCategoryPairs();
        $this->assertIsArray($result);
        $this->assertEquals($expectArray, $result);
    }

    public function testupdateCategory()
    {
        $model = new \Model_ProductCategory();
        $model->loadBean(new \RedBeanPHP\OODBBean());

        $dbMock = $this->getMockBuilder("\Box_Database")->getMock();
        $dbMock
            ->expects($this->atLeastOnce())
            ->method("store")
            ->will($this->returnValue(1));

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

        $result = $this->service->updateCategory(
            $model,
            "title",
            "decription",
            "http://urltoimg.com/img.jpg"
        );
        $this->assertIsBool($result);
        $this->assertTrue($result);
    }

    public function testcreateCategory()
    {
        $newCategoryId = 1;

        $systemServiceMock = $this->getMockBuilder(
            "\Box\Mod\System\Service"
        )->getMock();
        $systemServiceMock
            ->expects($this->atLeastOnce())
            ->method("checkLimits");

        $model = new \Model_ProductCategory();
        $model->loadBean(new \RedBeanPHP\OODBBean());

        $dbMock = $this->getMockBuilder("\Box_Database")->getMock();
        $dbMock
            ->expects($this->atLeastOnce())
            ->method("dispense")
            ->will($this->returnValue($model));

        $dbMock
            ->expects($this->atLeastOnce())
            ->method("store")
            ->will($this->returnValue($newCategoryId));

        $di = new \Box_Di();
        $di["db"] = $dbMock;
        $di["mod_service"] = $di->protect(function () use ($systemServiceMock) {
            return $systemServiceMock;
        });
        $di["logger"] = new \Box_Log();

        $this->service->setDi($di);

        $result = $this->service->createCategory("title");

        $this->assertIsInt($result);
        $this->assertEquals($newCategoryId, $result);
    }

    public function testremoveProductCategoryCategoryHasProductsException()
    {
        $modelProductCategory = new \Model_ProductCategory();
        $modelProductCategory->loadBean(new \RedBeanPHP\OODBBean());

        $modelProduct = new \Model_Product();
        $modelProduct->loadBean(new \RedBeanPHP\OODBBean());

        $dbMock = $this->getMockBuilder("\Box_Database")->getMock();
        $dbMock
            ->expects($this->atLeastOnce())
            ->method("findOne")
            ->will($this->returnValue($modelProduct));

        $di = new \Box_Di();
        $di["db"] = $dbMock;

        $this->service->setDi($di);

        $this->expectException(\Box_Exception::class);
        $this->expectExceptionMessage(
            "Can not remove product category with products"
        );
        $this->service->removeProductCategory($modelProductCategory);
    }

    public function testremoveProductCategory()
    {
        $modelProductCategory = new \Model_ProductCategory();
        $modelProductCategory->loadBean(new \RedBeanPHP\OODBBean());

        $modelProduct = null;

        $dbMock = $this->getMockBuilder("\Box_Database")->getMock();
        $dbMock
            ->expects($this->atLeastOnce())
            ->method("findOne")
            ->will($this->returnValue($modelProduct));

        $dbMock->expects($this->atLeastOnce())->method("trash");

        $di = new \Box_Di();
        $di["db"] = $dbMock;
        $di["logger"] = new \Box_Log();

        $this->service->setDi($di);

        $result = $this->service->removeProductCategory($modelProductCategory);
        $this->assertIsBool($result);
        $this->assertTrue($result);
    }

    public function testgetPromoSearchQuery()
    {
        $data = [
            "search" => "keyword",
            "id" => 1,
            "status" => "active",
        ];

        $di = new \Box_Di();
        $di["array_get"] = $di->protect(function (
            array $array,
            $key,
            $default = null
        ) use ($di) {
            return isset($array[$key]) ? $array[$key] : $default;
        });
        $this->service->setDi($di);

        [$sql, $params] = $this->service->getPromoSearchQuery($data);

        $this->assertIsString($sql);
        $this->assertIsArray($params);
    }

    public function testcreatePromo()
    {
        $systemServiceMock = $this->getMockBuilder(
            "\Box\Mod\System\Service"
        )->getMock();
        $systemServiceMock
            ->expects($this->atLeastOnce())
            ->method("checkLimits");

        $model = new \Model_Promo();
        $model->loadBean(new \RedBeanPHP\OODBBean());

        $newPromoId = 1;

        $dbMock = $this->getMockBuilder("\Box_Database")->getMock();
        $dbMock
            ->expects($this->atLeastOnce())
            ->method("dispense")
            ->will($this->returnValue($model));
        $dbMock
            ->expects($this->atLeastOnce())
            ->method("store")
            ->will($this->returnValue($newPromoId));

        $di = new \Box_Di();
        $di["db"] = $dbMock;
        $di["mod_service"] = $di->protect(function () use ($systemServiceMock) {
            return $systemServiceMock;
        });
        $di["logger"] = new \Box_Log();
        $di["array_get"] = $di->protect(function (
            array $array,
            $key,
            $default = null
        ) use ($di) {
            return isset($array[$key]) ? $array[$key] : $default;
        });

        $this->service->setDi($di);
        $result = $this->service->createPromo(
            "code",
            "percentage",
            50,
            [],
            [],
            [],
            []
        );
        $this->assertIsInt($result);
        $this->assertEquals($newPromoId, $result);
    }

    public function testtoPromoApiArray()
    {
        $model = new \Model_Promo();
        $model->loadBean(new \RedBeanPHP\OODBBean());
        $model->products = "{}";
        $model->periods = "{}";

        $dbMock = $this->getMockBuilder("\Box_Database")->getMock();
        $dbMock
            ->expects($this->atLeastOnce())
            ->method("toArray")
            ->will($this->returnValue([]));

        $di = new \Box_Di();
        $di["db"] = $dbMock;
        $di["tools"] = $this->getMockBuilder("\Box_Tools")->getMock();

        $this->service->setDi($di);

        $result = $this->service->toPromoApiArray($model);
        $this->assertIsArray($result);
    }

    public function testupdatePromo()
    {
        $model = new \Model_Promo();
        $model->loadBean(new \RedBeanPHP\OODBBean());

        $data = [
            "code" => "GO",
            "type" => "absolute",
            "value" => 10,
            "active" => true,
            "freesetup" => true,
            "once_per_client" => true,
            "recurring" => false,
            "maxuses" => "1",
            "used" => "0",
            "start_at" => "2012-01-01",
            "end_at" => "2012-01-02",
            "products" => "domain",
            "periods" => [],
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
        $result = $this->service->updatePromo($model, $data);
        $this->assertIsBool($result);
        $this->assertTrue($result);
    }

    public function testdeletePromo()
    {
        $model = new \Model_Promo();
        $model->loadBean(new \RedBeanPHP\OODBBean());

        $dbMock = $this->getMockBuilder("\Box_Database")->getMock();
        $dbMock->expects($this->atLeastOnce())->method("exec");
        $dbMock->expects($this->atLeastOnce())->method("trash");

        $di = new \Box_Di();
        $di["db"] = $dbMock;
        $di["logger"] = new \Box_Log();

        $this->service->setDi($di);
        $result = $this->service->deletePromo($model);
        $this->assertIsBool($result);
        $this->assertTrue($result);
    }

    public function testgetProductSearchQuery()
    {
        $data = [
            "search" => "keyword",
            "type" => "domain",
            "status" => "active",
            "show_hidden" => true,
        ];

        $di = new \Box_Di();
        $di["array_get"] = $di->protect(function (
            array $array,
            $key,
            $default = null
        ) use ($di) {
            return isset($array[$key]) ? $array[$key] : $default;
        });
        $this->service->setDi($di);

        [$sql, $params] = $this->service->getProductSearchQuery($data);

        $this->assertIsString($sql);
        $this->assertIsArray($params);
    }

    public function testtoProductCategoryApiArray()
    {
        $model = new \Model_ProductCategory();
        $model->loadBean(new \RedBeanPHP\OODBBean());

        $modelProduct = new \Model_Product();
        $modelProduct->loadBean(new \RedBeanPHP\OODBBean());
        $modelProduct->type = "custom";
        $categoryProductsArr = [$modelProduct];

        $serviceMock = $this->getMockBuilder("\Box\Mod\Product\Service")
            ->setMethods(["getCategoryProducts", "toApiArray"])
            ->getMock();

        $serviceMock
            ->expects($this->atLeastOnce())
            ->method("getCategoryProducts")
            ->will($this->returnValue($categoryProductsArr));

        $apiArrayResult = [
            "price_starting_from" => 1,
        ];
        $serviceMock
            ->expects($this->atLeastOnce())
            ->method("toApiArray")
            ->will($this->returnValue($apiArrayResult));

        $dbMock = $this->getMockBuilder("\Box_Database")->getMock();
        $dbMock
            ->expects($this->atLeastOnce())
            ->method("toArray")
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

        $serviceMock->setDi($di);
        $result = $serviceMock->toProductCategoryApiArray($model);
        $this->assertIsArray($result);
    }

    public function testtoProductCategoryApiArray_StartingFromValue_NotZero()
    {
        $model = new \Model_ProductCategory();
        $model->loadBean(new \RedBeanPHP\OODBBean());

        $modelProduct = new \Model_Product();
        $modelProduct->loadBean(new \RedBeanPHP\OODBBean());
        $modelProduct->type = "custom";
        $categoryProductsArr = [
            $modelProduct,
            $modelProduct,
            $modelProduct,
            $modelProduct,
        ];

        $serviceMock = $this->getMockBuilder("\Box\Mod\Product\Service")
            ->setMethods(["getCategoryProducts", "toApiArray"])
            ->getMock();

        $serviceMock
            ->expects($this->atLeastOnce())
            ->method("getCategoryProducts")
            ->will($this->returnValue($categoryProductsArr));

        $min = 1;

        $serviceMock
            ->expects($this->atLeastOnce())
            ->method("toApiArray")
            ->willReturnOnConsecutiveCalls(
                [
                    "price_starting_from" => 4,
                ],
                [
                    "price_starting_from" => 5,
                ],
                [
                    "price_starting_from" => 2,
                ],
                [
                    "price_starting_from" => $min,
                ]
            );

        $dbMock = $this->getMockBuilder("\Box_Database")->getMock();
        $dbMock
            ->expects($this->atLeastOnce())
            ->method("toArray")
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

        $serviceMock->setDi($di);
        $result = $serviceMock->toProductCategoryApiArray($model);
        $this->assertIsArray($result);
        $this->assertEquals($min, $result["price_starting_from"]);
    }

    public function testfindOneActiveById()
    {
        $model = new \Model_Product();

        $dbMock = $this->getMockBuilder("\Box_Database")->getMock();
        $dbMock
            ->expects($this->atLeastOnce())
            ->method("findOne")
            ->will($this->returnValue($model));

        $di = new \Box_Di();
        $di["db"] = $dbMock;

        $this->service->setDi($di);
        $result = $this->service->findOneActiveById(1);
        $this->assertInstanceOf("\Model_Product", $result);
    }

    public function testfindOneActiveBySlug()
    {
        $model = new \Model_Product();

        $dbMock = $this->getMockBuilder("\Box_Database")->getMock();
        $dbMock
            ->expects($this->atLeastOnce())
            ->method("findOne")
            ->will($this->returnValue($model));

        $di = new \Box_Di();
        $di["db"] = $dbMock;

        $this->service->setDi($di);
        $result = $this->service->findOneActiveBySlug("product/1");
        $this->assertInstanceOf("\Model_Product", $result);
    }

    public function testgetProductCategorySearchQuery()
    {
        [$sql, $params] = $this->service->getProductCategorySearchQuery([]);

        $this->assertIsString($sql);
        $this->assertIsArray($params);
        $this->assertEquals([], $params);
    }

    public function testgetStartingFromPriceTypeFree()
    {
        $productModel = new \Model_Product();
        $productModel->loadBean(new \RedBeanPHP\OODBBean());
        $productModel->product_payment_id = 1;

        $productPaymentModel = new \Model_ProductPayment();
        $productPaymentModel->loadBean(new \RedBeanPHP\OODBBean());
        $productPaymentModel->type = "free";

        $dbMock = $this->getMockBuilder("\Box_Database")->getMock();
        $dbMock
            ->expects($this->atLeastOnce())
            ->method("load")
            ->will($this->returnValue($productPaymentModel));

        $di = new \Box_Di();
        $di["db"] = $dbMock;

        $this->service->setDi($di);
        $result = $this->service->getStartingFromPrice($productModel);

        $this->assertIsInt($result);
        $this->assertEquals("0", $result);
    }

    public function testgetStartingFromPricePaymentNotDefined()
    {
        $productModel = new \Model_Product();
        $productModel->loadBean(new \RedBeanPHP\OODBBean());

        $result = $this->service->getStartingFromPrice($productModel);

        $this->assertEquals(null, $result);
    }

    public function testgetStartingFromPrice_DomainType()
    {
        $productModel = new \Model_Product();
        $productModel->loadBean(new \RedBeanPHP\OODBBean());
        $productModel->type = Service::DOMAIN;
        $productModel->product_payment_id = 1;

        $serviceMock = $this->getMockBuilder("\Box\Mod\Product\Service")
            ->setMethods(["getStartingDomainPrice", "getStartingPrice"])
            ->getMock();
        $serviceMock
            ->expects($this->atLeastOnce())
            ->method("getStartingDomainPrice")
            ->willReturn("10.00");
        $serviceMock->expects($this->never())->method("getStartingPrice");

        $result = $serviceMock->getStartingFromPrice($productModel);
        $this->assertNotNull($result);
    }

    public function testgetUpgradablePairs()
    {
        $productModel = new \Model_Product();
        $productModel->loadBean(new \RedBeanPHP\OODBBean());
        $productModel->upgrades = "{}";

        $expected = [];

        $result = $this->service->getUpgradablePairs($productModel);
        $this->assertIsArray($result);
        $this->assertEquals($expected, $result);
    }

    public function testgetProductTitlesByIds()
    {
        $ids = ["1", "2"];

        $queryArr = [
            [
                "id" => "1",
                "titile" => "test",
            ],
            [
                "id" => "2",
                "titile" => "Another",
            ],
        ];

        $expected = [
            "1" => "test",
            "2" => "Another",
        ];

        $dbMock = $this->getMockBuilder("\Box_Database")->getMock();
        $dbMock
            ->expects($this->atLeastOnce())
            ->method("getAll")
            ->will($this->returnValue([]));

        $di = new \Box_Di();
        $di["db"] = $dbMock;

        $this->service->setDi($di);

        $result = $this->service->getProductTitlesByIds($ids);
        $this->assertIsArray($result);
    }

    public function testgetCategoryProducts()
    {
        $productCategoryModel = new \Model_ProductCategory();
        $productCategoryModel->loadBean(new \RedBeanPHP\OODBBean());

        $productModel = new \Model_Product();
        $productModel->loadBean(new \RedBeanPHP\OODBBean());

        $dbMock = $this->getMockBuilder("\Box_Database")->getMock();
        $dbMock
            ->expects($this->atLeastOnce())
            ->method("find")
            ->will($this->returnValue([$productModel]));

        $di = new \Box_Di();
        $di["db"] = $dbMock;

        $this->service->setDi($di);
        $result = $this->service->getCategoryProducts($productCategoryModel);
        $this->assertIsArray($result);
    }

    public function testtoProductPaymentApiArray()
    {
        $productPaymentModel = new \Model_ProductPayment();
        $productPaymentModel->loadBean(new \RedBeanPHP\OODBBean());

        $result = $this->service->toProductPaymentApiArray(
            $productPaymentModel
        );
        $this->assertIsArray($result);
    }

    public function testgetStartingPrice()
    {
        $productPaymentModel = new \Model_ProductPayment();
        $productPaymentModel->loadBean(new \RedBeanPHP\OODBBean());
        $productPaymentModel->type = "recurrent";

        $minPrice = 1;

        $productPaymentModel->w_enabled = true;
        $productPaymentModel->w_price = 2;
        $productPaymentModel->m_enabled = true;
        $productPaymentModel->m_price = 4;
        $productPaymentModel->q_enabled = true;
        $productPaymentModel->q_price = 8;
        $productPaymentModel->b_enabled = true;
        $productPaymentModel->b_price = $minPrice;
        $productPaymentModel->a_enabled = true;
        $productPaymentModel->a_price = 10;
        $productPaymentModel->bia_enabled = true;
        $productPaymentModel->bia_price = 12;
        $productPaymentModel->tria_enabled = true;
        $productPaymentModel->tria_price = 14;

        $result = $this->service->getStartingPrice($productPaymentModel);
        $this->assertIsInt($result);
        $this->assertEquals($minPrice, $result);
    }

    public function testgetSavePath()
    {
        $filename = "cfg.file";
        $config = ["path_data" => "/home"];
        $expected = $config["path_data"] . "/uploads/" . md5($filename);

        $di = new \Box_Di();
        $di["config"] = $config;

        $this->service->setDi($di);
        $result = $this->service->getSavePath($filename);
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
        $this->assertEquals($expected, $result);
    }

    public function testremoveOldFile()
    {
        $config = ["filename" => "test.cfg"];

        $toolMock = $this->getMockBuilder("\Box_Tools")->getMock();
        $toolMock
            ->expects($this->atLeastOnce())
            ->method("fileExists")
            ->will($this->returnValue(true));
        $toolMock->expects($this->atLeastOnce())->method("unlink");

        $di = new \Box_Di();
        $di["tools"] = $toolMock;
        $di["config"] = ["path_data" => "/home"];

        $this->service->setDi($di);
        $result = $this->service->removeOldFile($config);
        $this->assertIsBool($result);
        $this->assertTrue($result);
    }

    public function testremoveOldFileFileNotFound()
    {
        $config = ["filename" => "test.cfg"];

        $toolMock = $this->getMockBuilder("\Box_Tools")->getMock();
        $toolMock
            ->expects($this->atLeastOnce())
            ->method("fileExists")
            ->will($this->returnValue(false));

        $di = new \Box_Di();
        $di["tools"] = $toolMock;
        $di["config"] = ["path_data" => "/home"];

        $this->service->setDi($di);
        $result = $this->service->removeOldFile($config);
        $this->assertIsBool($result);
        $this->assertFalse($result);
    }

    public function testcanUpgradeTo_returnsTrue()
    {
        $serviceMock = $this->getMockBuilder("\Box\Mod\Product\Service")
            ->setMethods(["getUpgradablePairs"])
            ->getMock();
        $serviceMock
            ->expects($this->atLeastOnce())
            ->method("getUpgradablePairs")
            ->will($this->returnValue(["2" => "Hossting"]));

        $productModel = new \Model_Product();
        $productModel->loadBean(new \RedBeanPHP\OODBBean());
        $productModel->id = 1;

        $newProductModel = new \Model_Product();
        $newProductModel->loadBean(new \RedBeanPHP\OODBBean());
        $newProductModel->id = 2;

        $result = $serviceMock->canUpgradeTo($productModel, $newProductModel);
        $this->assertTrue($result);
    }

    public function testcanUpgradeTo_upgradeIsImposible()
    {
        $serviceMock = $this->getMockBuilder("\Box\Mod\Product\Service")
            ->setMethods(["getUpgradablePairs"])
            ->getMock();
        $serviceMock
            ->expects($this->atLeastOnce())
            ->method("getUpgradablePairs")
            ->will($this->returnValue(["4" => "Domain"]));

        $productModel = new \Model_Product();
        $productModel->loadBean(new \RedBeanPHP\OODBBean());
        $productModel->id = 1;

        $newProductModel = new \Model_Product();
        $newProductModel->loadBean(new \RedBeanPHP\OODBBean());
        $newProductModel->id = 2;

        $result = $serviceMock->canUpgradeTo($productModel, $newProductModel);
        $this->assertFalse($result);
    }

    public function testcanUpgradeTo_SameProducts()
    {
        $productModel = new \Model_Product();
        $productModel->loadBean(new \RedBeanPHP\OODBBean());
        $productModel->id = 1;

        $newProductModel = new \Model_Product();
        $newProductModel->loadBean(new \RedBeanPHP\OODBBean());
        $newProductModel->id = 1;

        $result = $this->service->canUpgradeTo($productModel, $newProductModel);
        $this->assertFalse($result);
    }

    public function testgetStartingDomainPrice()
    {
        $di = new \Box_Di();

        $dbMock = $this->getMockBuilder("\Box_Database")->getMock();
        $sqlQuery = 'SELECT min(price_registration)
                FROM tld
                WHERE active = 1';
        $amount = "10.00";
        $dbMock
            ->expects($this->atLeastOnce())
            ->method("getCell")
            ->with($sqlQuery)
            ->willReturn($amount);

        $di["db"] = $dbMock;
        $this->service->setDi($di);
        $result = $this->service->getStartingDomainPrice();
        $this->assertEquals($amount, $result);
    }

    public function testgetStartingDomainPrice_noActiveTld()
    {
        $di = new \Box_Di();

        $dbMock = $this->getMockBuilder("\Box_Database")->getMock();
        $sqlQuery = 'SELECT min(price_registration)
                FROM tld
                WHERE active = 1';
        $amount = null;
        $dbMock
            ->expects($this->atLeastOnce())
            ->method("getCell")
            ->with($sqlQuery)
            ->willReturn($amount);

        $di["db"] = $dbMock;
        $this->service->setDi($di);
        $result = $this->service->getStartingDomainPrice();
        $this->assertEquals((float) $amount, $result);
    }
}
