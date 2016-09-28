<?php

namespace MJanssen\Provider;

use MJanssen\Route\Methods;

class MethodsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_throws_if_method_is_invalid()
    {
        $this->expectException(\InvalidArgumentException::class);
        new Methods(['INVALID']);
    }

    /**
     * @test
     */
    public function it_exposes_methods()
    {
        $methods = new Methods(Methods::ALLOWED_METHODS);

        $this->assertSame(
            Methods::ALLOWED_METHODS,
            $methods->toArray()
        );
    }

}