<?php
class Box_ConfigTest extends PHPUnit\Framework\TestCase
{
    public function testConfig()
    {
        $data = [
            "test" => "value",
            "baz" => [
                "foo" => "1",
                "bar" => "2",
            ],
        ];

        $config = new Box_Config($data);
        $this->assertEquals("value", $config["test"]);
        $this->assertEquals("1", $config["baz"]["foo"]);
        $this->assertEquals("2", $config["baz"]["bar"]);
    }
}
