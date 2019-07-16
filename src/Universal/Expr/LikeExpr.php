<?php

namespace SQLBuilder\Universal\Expr;

use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Criteria;
use SQLBuilder\ArgumentArray;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\Bind;

class LikeExpr implements ToSqlInterface
{
    public $pat;

    public $criteria;

    public function __construct($exprStr, $pat, $criteria = Criteria::CONTAINS)
    {
        $this->exprStr = $exprStr;
        $this->pat = $pat;
        $this->criteria = $criteria;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args)
    {
        // XXX: $pat can be a Bind object
        $isBind = $this->pat instanceof Bind;

        $pat = $isBind ? $this->pat->getValue() : $this->pat;

        switch ($this->criteria) {
        case Criteria::CONTAINS:
            $pat = '%'.$pat.'%';
            break;
        case Criteria::STARTS_WITH:
            $pat = $pat.'%';
            break;

        case Criteria::ENDS_WITH:
            $pat = '%'.$pat;
            break;

        case Criteria::EXACT:
            break;

        default:
            $pat = '%'.$pat.'%';
            break;
        }


        if ($isBind) {
            $this->pat->setForQuery($pat);
            $queryPat = $this->pat;
        } else {
            $queryPat = $pat;
        }

        return $this->exprStr.' LIKE '.$driver->deflate($queryPat, $args);
    }

    public static function __set_state(array $array)
    {
        return new self($array['exprStr'], $array['pat'], $array['criteria']);
    }
}
