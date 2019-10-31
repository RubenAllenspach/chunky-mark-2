<?php

namespace Lib\SQLiteManager;

/**
 * Manager of SQLite connection
 *
 * All queries are prepared statements
 *
 * @author Ruben Allenspach <ruben.allenspach@solution.ch>
 */
class SQLiteManager
{
    /** @var \PDO $conn */
    private $conn;

    function __construct($path)
    {
        $this->conn = new \PDO($path);

        $this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->conn->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
    }

    /**
     * Get all results from query
     *
     * @param string $query
     * @param array  $params
     *
     * @return array
     */
    public function get($query, $params=[]): array
    {
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Get first result from query
     *
     * @param string $query
     * @param array  $params
     *
     * @return array
     */
    public function getOne($query, $params=[]): array
    {
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);

        return $stmt->fetch();
    }

    /**
     * Get first var from first row from query
     *
     * @param string $query
     * @param array  $params
     *
     * @return array
     */
    public function var($query, $params=[])
    {
        $result = $this->getOne($query, $params);
        \reset($result);

        return $result[\key($result)];
    }

    /**
     * Simpy execute a query
     *
     * @param string $query
     * @param array  $params
     *
     * @return bool
     */
    public function query($query, $params=[]): bool
    {
        $stmt = $this->conn->prepare($query);

        return $stmt->execute($params);
    }
}
