<?php
namespace SQLBuilder\Universal\Query;
use Exception;
use LogicException;
use SQLBuilder\Raw;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Driver\SQLiteDriver;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Bind;
use SQLBuilder\ParamMarker;
use SQLBuilder\Universal\Expr\SelectExpr;
use SQLBuilder\Universal\Syntax\Conditions;
use SQLBuilder\Universal\Syntax\Join;
use SQLBuilder\Universal\Syntax\IndexHint;
use SQLBuilder\Universal\Syntax\Paging;
use SQLBuilder\Universal\Traits\OrderByTrait;
use SQLBuilder\Universal\Traits\JoinTrait;
use SQLBuilder\Universal\Traits\OptionTrait;
use SQLBuilder\Universal\Traits\WhereTrait;

/**
 * Delete Statement Query
 *
 * @code
 *
 *  $query = new SQLBuilder\Universal\Query\DeleteQuery;
 *  $query->delete(array(
 *      'name' => 'foo',
 *      'values' => 'bar',
 *  ));
 *  $sql = $query->toSql($driver, $args);
 *
 * @code
 *
 * The fluent interface rules of Query objects
 *
 *    1. setters should return self, since there is no return value.
 *    2. getters should be just what they are.
 *    3. modifier can set / append data and return self

    DELETE [LOW_PRIORITY] [QUICK] [IGNORE] FROM tbl_name
        [PARTITION (partition_name,...)]
        [WHERE where_condition]
        [ORDER BY ...]
        [LIMIT row_count]

 */
class DeleteQuery implements ToSqlInterface
{
    use OptionTrait;
    use JoinTrait;
    use WhereTrait;

    protected $deleteTables = array();

    protected $limit;

    protected $partitions;

    /**
     * ->delete('posts', 'p')
     * ->delete('users', 'u')
     */
    public function delete($table, $alias = NULL) {
        if ($alias) {
            $this->deleteTables[$table] = $alias;
        } else {
            $this->deleteTables[] = $table;
        }
        return $this;
    }

    public function indexHintOn($tableRef) {
        $hint = new IndexHint;
        $this->indexHintOn[$tableRef] = $hint;
        return $hint;
    }

    public function partitions($partitions)
    {
        if (is_array($partitions)) {
            $this->partitions = new Partition($partitions);
        } else {
            $this->partitions = new Partition(func_get_args());
        }
        return $this;
    }


    /********************************************************
     * LIMIT clauses
     *******************************************************/
    public function limit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    /****************************************************************
     * Builders
     ***************************************************************/
    public function buildDeleteTableClause(BaseDriver $driver) {
        $tableRefs = array();
        foreach($this->deleteTables as $k => $v) {
            /* "column AS alias" OR just "column" */
            if (is_string($k)) {
                $sql = $driver->quoteTable($k) . ' AS ' . $v;
                if ($driver instanceof MySQLDriver && isset($this->indexHintOn[$k])) {
                    $sql .= $this->indexHintOn[$k]->toSql($driver, new ArgumentArray);
                }
                $tableRefs[] = $sql;
            } elseif ( is_integer($k) || is_numeric($k) ) {
                $sql = $driver->quoteTable($v);
                if ($driver instanceof MySQLDriver && isset($this->indexHintOn[$v])) {
                    $sql .= $this->indexHintOn[$v]->toSql($driver, NULL);
                }
                $tableRefs[] = $sql;
            }
        }
        if (!empty($tableRefs)) {
            return ' ' . join(', ', $tableRefs);
        }
        return '';
    }

    public function buildPartitionClause(BaseDriver $driver, ArgumentArray $args)
    {
        if ($this->partitions) {
            return $this->partitions->toSql($driver, $args);
        }
        return '';
    }

    public function buildLimitClause(BaseDriver $driver, ArgumentArray $args)
    {
        if ($this->limit) {
            return ' LIMIT ' . intval($this->limit);
        }
        return '';
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args) {
        $sql = 'DELETE'
            . $this->buildOptionClause()
            . $this->buildDeleteTableClause($driver)
            . $this->buildPartitionClause($driver, $args)
            . $this->buildJoinClause($driver, $args)
            . $this->buildJoinIndexHintClause($driver, $args)
            . $this->buildWhereClause($driver, $args)
            . $this->buildLimitClause($driver, $args)
            ;
        return $sql;
    }

    public function __clone() {
        $this->where = $this->where;
    }
}

