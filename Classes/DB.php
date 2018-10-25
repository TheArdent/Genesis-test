<?php
/**
 * Created by PhpStorm.
 * User: theardent
 * Date: 24.10.18
 * Time: 15:01
 */

namespace Classes;


use PDO;

class DB
{

    private static $instance;
    private        $pdo;

    private function __construct()
    {
        $config = EnvConfig::getConfig();

        $dsn = $config->get('DB_DRIVER').':host='.$config->get('DB_HOST').';dbname='.$config->get('DB_NAME').';charset=utf8';

        try {
            $this->pdo = new PDO($dsn, $config->get('DB_USER'), $config->get('DB_PASSWORD'),
                [
                    PDO::ATTR_ERRMODE    => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_PERSISTENT => true
                ]);
        } catch (\Exception $e) {
            Log::error($e);
            die();
        }
    }

    /**
     * @return DB
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @param string $sql
     * @param array $parameters
     * @return array
     * @throws \Exception
     */
    public function select(string $sql, array $parameters = [])
    {
        $rows = [];

        $stmt = $this->pdo->prepare($sql);

        $errors = $this->pdo->errorInfo();
        if ($this->pdo->errorCode() != 0000) {
            throw new \Exception($errors[2], $errors[1]);
        }

        if ($stmt->execute($parameters)) {
            while ($row = $stmt->fetch()) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /**
     * @param string $table
     * @param array $entity
     * @return int
     * @throws \Exception
     */
    public function insert(string $table, array $entity)
    {
        $prepare_values = [];

        foreach ($entity as $key => $value) {
            $prepare_values[] = ":{$key}";
        }
        $sql = "INSERT INTO {$table} (".implode(', ', array_keys($entity)).') '.
            'VALUES ('.implode(', ', $prepare_values).')';

        $stmt = $this->pdo->prepare($sql);

        foreach ($entity as $key => $value) {
            $stmt->bindParam(":{$key}", $entity[$key], PDO::PARAM_STR);
        }

        $stmt->execute();

        $errors = $this->pdo->errorInfo();
        if ($this->pdo->errorCode() != 0000) {
            throw new \Exception($errors[2], $errors[1]);
        }

        return $this->pdo->lastInsertId();
    }

    /**
     * @param string $table
     * @param array $entity
     * @param string $where
     * @return bool
     * @throws \Exception
     */
    public function update(string $table, array $entity, string $where)
    {

        $prepare_values = [];

        foreach ($entity as $key => $value) {
            $prepare_values[] = "{$key} = :{$key}";
        }

        $sql = "UPDATE {$table} SET ".implode(', ', $prepare_values).' WHERE '.$where;

        $stmt = $this->pdo->prepare($sql);

        foreach ($entity as $key => $value) {
            $stmt->bindParam(":{$key}", $entity[$key], PDO::PARAM_STR);
        }

        $result = $stmt->execute();

        $errors = $this->pdo->errorInfo();
        if ($this->pdo->errorCode() != 0000) {
            throw new \Exception($errors[2], $errors[1]);
        }

        return $result;
    }

    /**
     * @param string $table
     * @param string $where
     * @return int
     * @throws \Exception
     */
    public function delete(string $table, string $where)
    {
        $result = $this->pdo->exec("DELETE FROM {$table} WHERE {$where}");
        $errors = $this->pdo->errorInfo();
        if ($this->pdo->errorCode() != 0000) {
            throw new \Exception($errors[2], $errors[1]);
        }

        return $result;
    }
}