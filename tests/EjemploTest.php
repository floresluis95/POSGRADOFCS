<?php

use PHPUnit\Framework\TestCase;

class EjemploTest extends TestCase
{
    public function testSumaDosNumeros()
    {
        $resultado = 2 + 3;

        $this->assertEquals(5, $resultado);
    }
}