<?php
/**
 * Created by PhpStorm.
 * User: theardent
 * Date: 24.10.18
 * Time: 16:55
 */

namespace Classes;


class Entity
{

    /**
     * @var string
     */
    protected $table;

    /**
     * @var array
     */
    protected $params = [];

    /**
     * @var array
     */
    protected $columns = [];

    /**
     * Entity constructor.
     * @param array $params
     */
    public function __construct(array $params = [])
    {
        $this->params = $params;
    }

    /**
     * @param $params
     * @param string $column
     */
    static public function firstOrCreate(array $params)
    {
        $db = DB::getInstance();

        $object = new static();

        $condition = [];

        foreach ($params as $key => $value) {
            $condition[] = "{$key} = ?";
        }

        $condition = implode(' AND ', $condition);

        try {
            $entity = $db->select('SELECT * FROM '.$object->table.' WHERE '.$condition.' LIMIT 1',
                array_values($params));
        } catch (\Exception $e) {
            Log::error($e);
            die();
        }

        if (empty($entity)) {
            $object->params = $params;
            $id             = $object->create();

            $object->params = array_merge($params, ['id' => $id]);
        } else {
            $object->params = $entity[0];
        }

        return $object;
    }

    public function __get($name)
    {
        return $this->params[$name];
    }

    public function __set($name, $value)
    {
        $this->params[$name] = $value;
    }

    /**
     * @throws \Exception
     */
    public function save()
    {
        if (array_key_exists('id', $this->params)) {
            $this->update();
        } else {
            $this->create();
        }
    }

    /**
     * @throws \Exception
     */
    public function update()
    {
        $db = DB::getInstance();
        $db->update($this->table, $this->getColumnedParams(), 'id = '.$this->id);
    }

    /**
     * @return int
     */
    public function create()
    {
        $db = DB::getInstance();
        unset($this->params['id']);
        $this->params['created'] = date('Y-m-d H:i:s');

        try {
            return $db->insert($this->table, $this->getColumnedParams());
        } catch (\Exception $e) {
            Log::error($e);
            die();
        }
    }

    /**
     * @return array
     */
    protected function getColumnedParams()
    {
        return array_intersect_key($this->params, array_flip($this->columns));
    }

    /**
     * @return array
     */
    static public function getAll()
    {
        $db     = DB::getInstance();
        $entity = new static();

        try {
            $entities = $db->select('SELECT * FROM '.$entity->table);
        } catch (\Exception $e) {
            Log::error($e);
            die();
        }

        return array_map(function ($object) {
            return new static($object);
        }, $entities);
    }
}