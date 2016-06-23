<?php

use ApiFoo\Adapters\Cake\V3_2\CakeOrm;

/**
 * Integration tests for CakeORM version 3.2
 */
class CakeOrmTest extends \PHPUnit_Framework_TestCase
{
    private $table;
    private $query;

    public function setUp()
    {
        $connection = new \Cake\Database\Connection([
            'driver' => '\Cake\Database\Driver\Sqlite',
            'database' => ':memory:',
            'init' => [
                'CREATE TABLE IF NOT EXISTS table_name (id INT, name VARCHAR)',
                'INSERT INTO table_name (id, name) VALUES(1,"test1"),(1,"test2")',
            ],
        ]);

        $this->table = new \Cake\ORM\Table([
            'connection' => $connection,
            'table' => 'table_name',
        ]);

        $this->query = $this->table->query();
    }

    public function testGetCountQuery_ReturnsDistinctCountSql()
    {
        $orm = new CakeOrm($this->table);

        $query = $orm->getCountQuery();
        $this->assertEquals('SELECT DISTINCT (COUNT(root.id)) AS "count" FROM table_name root', $query->sql());
    }

    public function testGetIdsQuery_ReturnsDistinctIdSql()
    {
        $orm = new CakeOrm($this->table);

        $query = $orm->getIdsQuery();
        $this->assertEquals('SELECT DISTINCT root.id AS "root__id" FROM table_name root', $query->sql());
    }

    public function testGetListQuery_ReturnsWhereIdInSql()
    {
        $orm = new CakeOrm($this->table);
        $query = $this->table->query()
            ->select(['root.id']);

        $query = $orm->getListQuery($query, [1,2]);
        $this->assertEquals('SELECT root.id AS "root__id" FROM table_name root WHERE root.id IN (:c0,:c1)', $query->sql());
    }

    public function testGetItemQuery_ReturnsWhereEqualsIdSql()
    {
        $orm = new CakeOrm($this->table);
        $query = $this->table->query()
            ->select(['root.id'])
            ->where(['id' => 1]);

        $query = $orm->getItemQuery($query);
        $this->assertEquals('SELECT root.id AS "root__id" FROM table_name root WHERE id = :c0', $query->sql());
    }

    public function testExecuteQuery_WithoutHydration_ReturnsArray()
    {
        $orm = new CakeOrm($this->table);
        $query = $this->table->query()
            ->select(['root.id'])
            ->hydrate(false)
            ->limit(2);

        $result = $orm->executeQuery($query);
        $this->assertInternalType('array', $result);
        $this->assertCount(2, $result);
    }

    public function testGetRepositoryFromQuery_ReturnsTable()
    {
        $orm = new CakeOrm($this->table);
        $query = $this->table->query();

        $result = $orm->getRepositoryFromQuery($query);
        $this->assertInstanceOf('\Cake\ORM\Table', $result);
    }

    public function testSetPagination_AddsClauses()
    {
        $orm = new CakeOrm($this->table);
        $query = $this->table->query();

        $orm->setPagination($query, 5, 2);
        $this->assertEquals(5, $query->clause('limit'));
        $this->assertEquals(5, $query->clause('offset'));
    }
}