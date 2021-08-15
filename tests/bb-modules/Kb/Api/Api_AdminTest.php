<?php
namespace Box\Tests\Mod\Kb\Api;

class Api_AdminTest extends \BBTestCase
{
    public function testArticle_get_list()
    {
        $di = new \Box_Di();
        $di["array_get"] = $di->protect(function (
            array $array,
            $key,
            $default = null
        ) use ($di) {
            return isset($array[$key]) ? $array[$key] : $default;
        });
        $adminApi = new \Box\Mod\Kb\Api\Admin();
        $adminApi->setDi($di);

        $data = [
            "status" => "status",
            "search" => "search",
            "cat" => "category",
        ];

        $kbService = $this->getMockBuilder("Box\Mod\Kb\Service")
            ->setMethods(["searchArticles"])
            ->getMock();
        $kbService
            ->expects($this->atLeastOnce())
            ->method("searchArticles")
            ->will($this->returnValue(["list" => []]));

        $adminApi->setService($kbService);

        $result = $adminApi->article_get_list($data);
        $this->assertIsArray($result);
    }

    public function testArticle_get()
    {
        $adminApi = new \Box\Mod\Kb\Api\Admin();

        $data = [
            "id" => rand(1, 100),
        ];

        $db = $this->getMockBuilder("Box_Database")->getMock();
        $db->expects($this->atLeastOnce())
            ->method("findOne")
            ->will($this->returnValue(new \Model_KbArticle()));

        $admin = new \Model_Admin();
        $admin->loadBean(new \RedBeanPHP\OODBBean());

        $admin->id = 5;

        $di = new \Box_Di();
        $di["loggedin_admin"] = $admin;
        $di["db"] = $db;
        $validatorMock = $this->getMockBuilder("\Box_Validate")
            ->disableOriginalConstructor()
            ->getMock();
        $validatorMock
            ->expects($this->atLeastOnce())
            ->method("checkRequiredParamsForArray")
            ->will($this->returnValue(null));
        $di["validator"] = $validatorMock;
        $adminApi->setDi($di);

        $kbService = $this->getMockBuilder("Box\Mod\Kb\Service")
            ->setMethods(["toApiArray"])
            ->getMock();
        $kbService
            ->expects($this->atLeastOnce())
            ->method("toApiArray")
            ->will($this->returnValue([]));
        $adminApi->setService($kbService);

        $result = $adminApi->article_get($data);
        $this->assertIsArray($result);
    }

    public function testArticle_getNotFoundException()
    {
        $adminApi = new \Box\Mod\Kb\Api\Admin();

        $data = [
            "id" => rand(1, 100),
        ];

        $db = $this->getMockBuilder("Box_Database")->getMock();
        $db->expects($this->atLeastOnce())
            ->method("findOne")
            ->will($this->returnValue(false));

        $di = new \Box_Di();
        $di["db"] = $db;
        $validatorMock = $this->getMockBuilder("\Box_Validate")
            ->disableOriginalConstructor()
            ->getMock();
        $validatorMock
            ->expects($this->atLeastOnce())
            ->method("checkRequiredParamsForArray")
            ->will($this->returnValue(null));
        $di["validator"] = $validatorMock;
        $adminApi->setDi($di);
        $this->expectException(\Box_Exception::class);
        $adminApi->article_get($data);
    }

    public function testArticle_create()
    {
        $adminApi = new \Box\Mod\Kb\Api\Admin();

        $data = [
            "kb_article_category_id" => rand(1, 100),
            "title" => "Title",
        ];

        $id = rand(1, 100);

        $di = new \Box_Di();
        $di["array_get"] = $di->protect(function (
            array $array,
            $key,
            $default = null
        ) use ($di) {
            return isset($array[$key]) ? $array[$key] : $default;
        });

        $kbService = $this->getMockBuilder("Box\Mod\Kb\Service")
            ->setMethods(["createArticle"])
            ->getMock();
        $kbService
            ->expects($this->atLeastOnce())
            ->method("createArticle")
            ->will($this->returnValue($id));
        $adminApi->setService($kbService);
        $validatorMock = $this->getMockBuilder("\Box_Validate")
            ->disableOriginalConstructor()
            ->getMock();
        $validatorMock
            ->expects($this->atLeastOnce())
            ->method("checkRequiredParamsForArray")
            ->will($this->returnValue(null));
        $di["validator"] = $validatorMock;
        $adminApi->setDi($di);

        $result = $adminApi->article_create($data);
        $this->assertIsInt($result);
        $this->assertEquals($result, $id);
    }

    public function testArticle_update()
    {
        $adminApi = new \Box\Mod\Kb\Api\Admin();

        $data = [
            "id" => rand(1, 100),
            "kb_article_category_id" => rand(1, 100),
            "title" => "Title",
            "slug" => "article-slug",
            "status" => "active",
            "content" => "Content",
            "views" => rand(1, 100),
        ];

        $kbService = $this->getMockBuilder("Box\Mod\Kb\Service")
            ->setMethods(["updateArticle"])
            ->getMock();
        $kbService
            ->expects($this->atLeastOnce())
            ->method("updateArticle")
            ->will($this->returnValue(true));
        $di = new \Box_Di();
        $di["array_get"] = $di->protect(function (
            array $array,
            $key,
            $default = null
        ) use ($di) {
            return isset($array[$key]) ? $array[$key] : $default;
        });

        $validatorMock = $this->getMockBuilder("\Box_Validate")
            ->disableOriginalConstructor()
            ->getMock();
        $validatorMock
            ->expects($this->atLeastOnce())
            ->method("checkRequiredParamsForArray")
            ->will($this->returnValue(null));
        $di["validator"] = $validatorMock;

        $adminApi->setDi($di);

        $adminApi->setService($kbService);

        $result = $adminApi->article_update($data);
        $this->assertTrue($result);
    }

    public function testArticle_delete()
    {
        $adminApi = new \Box\Mod\Kb\Api\Admin();

        $data = [
            "id" => rand(1, 100),
        ];

        $db = $this->getMockBuilder("Box_Database")->getMock();
        $db->expects($this->atLeastOnce())
            ->method("findOne")
            ->will($this->returnValue(new \Model_KbArticle()));

        $di = new \Box_Di();
        $di["db"] = $db;

        $validatorMock = $this->getMockBuilder("\Box_Validate")
            ->disableOriginalConstructor()
            ->getMock();
        $validatorMock
            ->expects($this->atLeastOnce())
            ->method("checkRequiredParamsForArray")
            ->will($this->returnValue(null));
        $di["validator"] = $validatorMock;

        $adminApi->setDi($di);

        $kbService = $this->getMockBuilder("Box\Mod\Kb\Service")
            ->setMethods(["rm"])
            ->getMock();
        $kbService
            ->expects($this->atLeastOnce())
            ->method("rm")
            ->will($this->returnValue(true));
        $adminApi->setService($kbService);

        $result = $adminApi->article_delete($data);
        $this->assertTrue($result);
    }

    public function testArticle_deleteNotFoundException()
    {
        $adminApi = new \Box\Mod\Kb\Api\Admin();

        $db = $this->getMockBuilder("Box_Database")->getMock();
        $db->expects($this->atLeastOnce())
            ->method("findOne")
            ->will($this->returnValue(false));

        $di = new \Box_Di();
        $di["db"] = $db;

        $validatorMock = $this->getMockBuilder("\Box_Validate")
            ->disableOriginalConstructor()
            ->getMock();
        $validatorMock
            ->expects($this->atLeastOnce())
            ->method("checkRequiredParamsForArray")
            ->will($this->returnValue(null));
        $di["validator"] = $validatorMock;
        $adminApi->setDi($di);

        $kbService = $this->getMockBuilder("Box\Mod\Kb\Service")
            ->setMethods(["rm"])
            ->getMock();
        $kbService
            ->expects($this->never())
            ->method("rm")
            ->will($this->returnValue(true));
        $adminApi->setService($kbService);

        $this->expectException(\Box_Exception::class);
        $result = $adminApi->article_delete(["id" => rand(1, 100)]);
        $this->assertTrue($result);
    }

    public function testCategory_get_list()
    {
        $adminApi = new \Box\Mod\Kb\Api\Admin();

        $willReturn = [
            "pages" => 5,
            "page" => 2,
            "per_page" => 2,
            "total" => 10,
            "list" => [],
        ];

        $pager = $this->getMockBuilder("Box_Pagination")->getMock();
        $pager
            ->expects($this->atLeastOnce())
            ->method("getAdvancedResultSet")
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
        $adminApi->setDi($di);

        $kbService = $this->getMockBuilder("Box\Mod\Kb\Service")
            ->setMethods(["categoryGetSearchQuery"])
            ->getMock();
        $kbService
            ->expects($this->atLeastOnce())
            ->method("categoryGetSearchQuery")
            ->will($this->returnValue(true));
        $adminApi->setService($kbService);

        $result = $adminApi->category_get_list([]);
        $this->assertIsArray($result);
        $this->assertEquals($result, $willReturn);
    }

    public function testCategory_get()
    {
        $adminApi = new \Box\Mod\Kb\Api\Admin();

        $db = $this->getMockBuilder("Box_Database")->getMock();
        $db->expects($this->atLeastOnce())
            ->method("findOne")
            ->will($this->returnValue(new \Model_KbArticleCategory()));

        $di = new \Box_Di();
        $di["db"] = $db;
        $validatorMock = $this->getMockBuilder("\Box_Validate")
            ->disableOriginalConstructor()
            ->getMock();
        $validatorMock
            ->expects($this->atLeastOnce())
            ->method("checkRequiredParamsForArray")
            ->will($this->returnValue(null));
        $di["validator"] = $validatorMock;
        $adminApi->setDi($di);

        $kbService = $this->getMockBuilder("Box\Mod\Kb\Service")
            ->setMethods(["categoryToApiArray"])
            ->getMock();
        $kbService
            ->expects($this->atLeastOnce())
            ->method("categoryToApiArray")
            ->will($this->returnValue([]));
        $adminApi->setService($kbService);

        $data = [
            "id" => rand(1, 100),
        ];
        $result = $adminApi->category_get($data);
        $this->assertIsArray($result);
    }

    public function testCategory_getIdNotSetException()
    {
        $adminApi = new \Box\Mod\Kb\Api\Admin();

        $db = $this->getMockBuilder("Box_Database")->getMock();
        $db->expects($this->never())
            ->method("findOne")
            ->will($this->returnValue(new \Model_KbArticleCategory()));

        $di = new \Box_Di();
        $di["db"] = $db;
        $validatorMock = $this->getMockBuilder("\Box_Validate")
            ->disableOriginalConstructor()
            ->getMock();
        $validatorMock
            ->expects($this->atLeastOnce())
            ->method("checkRequiredParamsForArray")
            ->willThrowException(new \Box_Exception("Category ID not passed"));
        $di["validator"] = $validatorMock;
        $adminApi->setDi($di);

        $kbService = $this->getMockBuilder("Box\Mod\Kb\Service")
            ->setMethods(["categoryToApiArray"])
            ->getMock();
        $kbService
            ->expects($this->never())
            ->method("categoryToApiArray")
            ->will($this->returnValue([]));
        $adminApi->setService($kbService);

        $this->expectException(\Box_Exception::class);
        $result = $adminApi->category_get([]);
        $this->assertIsArray($result);
    }

    public function testCategory_getNotFoundException()
    {
        $adminApi = new \Box\Mod\Kb\Api\Admin();

        $db = $this->getMockBuilder("Box_Database")->getMock();
        $db->expects($this->atLeastOnce())
            ->method("findOne")
            ->will($this->returnValue(false));

        $di = new \Box_Di();
        $validatorMock = $this->getMockBuilder("\Box_Validate")
            ->disableOriginalConstructor()
            ->getMock();
        $validatorMock
            ->expects($this->atLeastOnce())
            ->method("checkRequiredParamsForArray")
            ->will($this->returnValue(null));
        $di["validator"] = $validatorMock;
        $di["db"] = $db;
        $adminApi->setDi($di);

        $kbService = $this->getMockBuilder("Box\Mod\Kb\Service")
            ->setMethods(["categoryToApiArray"])
            ->getMock();
        $kbService
            ->expects($this->never())
            ->method("categoryToApiArray")
            ->will($this->returnValue([]));
        $adminApi->setService($kbService);

        $data = [
            "id" => rand(1, 100),
        ];

        $this->expectException(\Box_Exception::class);
        $result = $adminApi->category_get($data);
        $this->assertIsArray($result);
    }

    public function testCategory_create()
    {
        $adminApi = new \Box\Mod\Kb\Api\Admin();

        $kbService = $this->getMockBuilder("Box\Mod\Kb\Service")
            ->setMethods(["createCategory"])
            ->getMock();
        $kbService
            ->expects($this->atLeastOnce())
            ->method("createCategory")
            ->will($this->returnValue([]));
        $adminApi->setService($kbService);

        $data = [
            "title" => "Title",
            "description" => "Description",
        ];

        $di = new \Box_Di();
        $di["array_get"] = $di->protect(function (
            array $array,
            $key,
            $default = null
        ) use ($di) {
            return isset($array[$key]) ? $array[$key] : $default;
        });
        $validatorMock = $this->getMockBuilder("\Box_Validate")
            ->disableOriginalConstructor()
            ->getMock();
        $validatorMock
            ->expects($this->atLeastOnce())
            ->method("checkRequiredParamsForArray")
            ->will($this->returnValue(null));
        $di["validator"] = $validatorMock;
        $adminApi->setDi($di);

        $result = $adminApi->category_create($data);
        $this->assertIsArray($result);
    }

    public function testCategory_update()
    {
        $adminApi = new \Box\Mod\Kb\Api\Admin();

        $kbService = $this->getMockBuilder("Box\Mod\Kb\Service")
            ->setMethods(["updateCategory"])
            ->getMock();
        $kbService
            ->expects($this->atLeastOnce())
            ->method("updateCategory")
            ->will($this->returnValue([]));
        $adminApi->setService($kbService);

        $db = $this->getMockBuilder("Box_Database")->getMock();
        $db->expects($this->atLeastOnce())
            ->method("findOne")
            ->will($this->returnValue(new \Model_KbArticleCategory()));

        $di = new \Box_Di();
        $di["db"] = $db;
        $di["array_get"] = $di->protect(function (
            array $array,
            $key,
            $default = null
        ) use ($di) {
            return isset($array[$key]) ? $array[$key] : $default;
        });

        $validatorMock = $this->getMockBuilder("\Box_Validate")
            ->disableOriginalConstructor()
            ->getMock();
        $validatorMock
            ->expects($this->atLeastOnce())
            ->method("checkRequiredParamsForArray")
            ->will($this->returnValue(null));
        $di["validator"] = $validatorMock;

        $adminApi->setDi($di);

        $data = [
            "id" => rand(1, 100),
            "title" => "Title",
            "slug" => "category-slug",
            "description" => "Description",
        ];

        $result = $adminApi->category_update($data);
        $this->assertIsArray($result);
    }

    public function testCategory_updateIdNotSet()
    {
        $adminApi = new \Box\Mod\Kb\Api\Admin();

        $kbService = $this->getMockBuilder("Box\Mod\Kb\Service")
            ->setMethods(["updateCategory"])
            ->getMock();
        $kbService
            ->expects($this->never())
            ->method("updateCategory")
            ->will($this->returnValue([]));
        $adminApi->setService($kbService);

        $db = $this->getMockBuilder("Box_Database")->getMock();
        $db->expects($this->never())
            ->method("findOne")
            ->will($this->returnValue(new \Model_KbArticleCategory()));

        $di = new \Box_Di();
        $di["db"] = $db;
        $validatorMock = $this->getMockBuilder("\Box_Validate")
            ->disableOriginalConstructor()
            ->getMock();
        $validatorMock
            ->expects($this->atLeastOnce())
            ->method("checkRequiredParamsForArray")
            ->willThrowException(new \Box_Exception("Category ID not passed"));
        $di["validator"] = $validatorMock;
        $adminApi->setDi($di);

        $data = [];

        $this->expectException(\Box_Exception::class);
        $result = $adminApi->category_update($data);
        $this->assertIsArray($result);
    }

    public function testCategory_updateNotFound()
    {
        $adminApi = new \Box\Mod\Kb\Api\Admin();

        $kbService = $this->getMockBuilder("Box\Mod\Kb\Service")
            ->setMethods(["updateCategory"])
            ->getMock();
        $kbService
            ->expects($this->never())
            ->method("updateCategory")
            ->will($this->returnValue([]));
        $adminApi->setService($kbService);

        $db = $this->getMockBuilder("Box_Database")->getMock();
        $db->expects($this->atLeastOnce())
            ->method("findOne")
            ->will($this->returnValue(false));

        $di = new \Box_Di();
        $di["db"] = $db;
        $validatorMock = $this->getMockBuilder("\Box_Validate")
            ->disableOriginalConstructor()
            ->getMock();
        $validatorMock
            ->expects($this->atLeastOnce())
            ->method("checkRequiredParamsForArray")
            ->will($this->returnValue(null));
        $di["validator"] = $validatorMock;
        $adminApi->setDi($di);

        $data = [
            "id" => rand(1, 100),
            "title" => "Title",
            "slug" => "category-slug",
            "description" => "Description",
        ];

        $this->expectException(\Box_Exception::class);
        $result = $adminApi->category_update($data);
        $this->assertIsArray($result);
    }

    public function testCategory_delete()
    {
        $adminApi = new \Box\Mod\Kb\Api\Admin();

        $kbService = $this->getMockBuilder("Box\Mod\Kb\Service")
            ->setMethods(["categoryRm"])
            ->getMock();
        $kbService
            ->expects($this->atLeastOnce())
            ->method("categoryRm")
            ->will($this->returnValue([]));
        $adminApi->setService($kbService);

        $db = $this->getMockBuilder("Box_Database")->getMock();
        $db->expects($this->atLeastOnce())
            ->method("findOne")
            ->will($this->returnValue(new \Model_KbArticleCategory()));

        $di = new \Box_Di();
        $di["db"] = $db;
        $validatorMock = $this->getMockBuilder("\Box_Validate")
            ->disableOriginalConstructor()
            ->getMock();
        $validatorMock
            ->expects($this->atLeastOnce())
            ->method("checkRequiredParamsForArray")
            ->will($this->returnValue(null));
        $di["validator"] = $validatorMock;
        $adminApi->setDi($di);

        $data = [
            "id" => rand(1, 100),
        ];
        $result = $adminApi->category_delete($data);
        $this->assertIsArray($result);
    }

    public function testCategory_deleteIdNotSet()
    {
        $adminApi = new \Box\Mod\Kb\Api\Admin();

        $kbService = $this->getMockBuilder("Box\Mod\Kb\Service")
            ->setMethods(["categoryRm"])
            ->getMock();
        $kbService
            ->expects($this->never())
            ->method("categoryRm")
            ->will($this->returnValue([]));
        $adminApi->setService($kbService);

        $db = $this->getMockBuilder("Box_Database")->getMock();
        $db->expects($this->never())
            ->method("findOne")
            ->will($this->returnValue(new \Model_KbArticleCategory()));

        $di = new \Box_Di();
        $di["db"] = $db;
        $validatorMock = $this->getMockBuilder("\Box_Validate")
            ->disableOriginalConstructor()
            ->getMock();
        $validatorMock
            ->expects($this->atLeastOnce())
            ->method("checkRequiredParamsForArray")
            ->willThrowException(new \Box_Exception("Category ID not passed"));
        $di["validator"] = $validatorMock;
        $adminApi->setDi($di);

        $data = [];
        $this->expectException(\Box_Exception::class);
        $result = $adminApi->category_delete($data);
        $this->assertIsArray($result);
    }

    public function testCategory_deleteNotFound()
    {
        $adminApi = new \Box\Mod\Kb\Api\Admin();

        $kbService = $this->getMockBuilder("Box\Mod\Kb\Service")
            ->setMethods(["categoryRm"])
            ->getMock();
        $kbService
            ->expects($this->never())
            ->method("categoryRm")
            ->will($this->returnValue([]));
        $adminApi->setService($kbService);

        $db = $this->getMockBuilder("Box_Database")->getMock();
        $db->expects($this->atLeastOnce())
            ->method("findOne")
            ->will($this->returnValue(false));

        $di = new \Box_Di();
        $di["db"] = $db;
        $validatorMock = $this->getMockBuilder("\Box_Validate")
            ->disableOriginalConstructor()
            ->getMock();
        $validatorMock
            ->expects($this->atLeastOnce())
            ->method("checkRequiredParamsForArray")
            ->will($this->returnValue(null));
        $di["validator"] = $validatorMock;
        $adminApi->setDi($di);

        $data = [
            "id" => rand(1, 100),
        ];

        $this->expectException(\Box_Exception::class);
        $result = $adminApi->category_delete($data);
        $this->assertIsArray($result);
    }

    public function testCategory_get_pairs()
    {
        $adminApi = new \Box\Mod\Kb\Api\Admin();

        $kbService = $this->getMockBuilder("Box\Mod\Kb\Service")
            ->setMethods(["categoryGetPairs"])
            ->getMock();
        $kbService
            ->expects($this->atLeastOnce())
            ->method("categoryGetPairs")
            ->will($this->returnValue([]));
        $adminApi->setService($kbService);

        $result = $adminApi->category_get_pairs([]);
        $this->assertIsArray($result);
    }
}
