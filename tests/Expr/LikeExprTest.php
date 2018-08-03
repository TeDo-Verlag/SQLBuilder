<?php

use PHPUnit\Framework\TestCase;

use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Universal\Expr\LikeExpr;
use SQLBuilder\Criteria;
use SQLBuilder\ArgumentArray;

class LikeExprTest extends TestCase
{
    public function testFuncCall()
    {
        $driver = new MySQLDriver;
        $args = new ArgumentArray;
        $func = new LikeExpr('c', 'value', Criteria::CONTAINS);
        $sql = $func->toSql($driver, $args);

        $expected = "c LIKE '%value%'";
        $this->assertEquals($expected, $sql);
        // repeat
        $sql = $func->toSql($driver, $args);
        $this->assertEquals($expected, $sql);
    }
}
