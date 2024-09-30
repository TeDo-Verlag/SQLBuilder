<?php
use SQLBuilder\Universal\Query\CreateTableQuery;
use SQLBuilder\Universal\Query\DropTableQuery;
use SQLBuilder\Testing\PDOQueryTestCase;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Raw;

class DropTableQueryTest extends PDOQueryTestCase
{
    public $driverType = 'MySQL';

    // public $schema = array( 'tests/schema/member_mysql.sql' );

    public function createDriver() {
        return new MySQLDriver;
    }

    public function setUp(): void
    {
        parent::setUp();

        // Clean up
        foreach(array('groups','users','points') as $table) {
            $dropQuery = new DropTableQuery($table);
            $dropQuery->IfExists();
            $this->assertQuery($dropQuery);
        }
    }

    public function tearDown(): void
    {
        foreach(array('groups','users', 'points') as $table) {
            $dropQuery = new DropTableQuery($table);
            $dropQuery->IfExists();
            $this->assertQuery($dropQuery);
        }
    }

    public function testDropTemporaryTable()
    {
        $q = new CreateTableQuery('points');
        $q->temporary();
        $q->column('x')->float(10,2);
        $q->column('y')->float(10,2);
        $this->assertQuery($q);

        $q = new DropTableQuery('points');
        $q->temporary();
        $this->assertSql('DROP TEMPORARY TABLE `points`', $q);
        $this->assertQuery($q);
    }

    public function testDropTable() 
    {
        $q = new CreateTableQuery('points');
        $q->column('x')->float(10,2);
        $q->column('y')->float(10,2);
        $this->assertQuery($q);

        $q = new DropTableQuery('points');
        $q->drop('users');
        $q->drop('books');
        $q->ifExists();
        $this->assertQuery($q);
    }

    public function testDropMultipleTable() 
    {
        $q = new CreateTableQuery('points');
        $q->column('id')->int();
        $this->assertQuery($q);

        $q = new CreateTableQuery('users');
        $q->column('id')->int();
        $this->assertQuery($q);

        $q = new DropTableQuery(['points','users']);
        $this->assertQuery($q);
    }
}
