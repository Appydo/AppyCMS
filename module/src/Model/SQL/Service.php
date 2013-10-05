<?php
namespace Admin\Model\SQL;

use Zend\Db\Adapter\Adapter;

class Service
{
    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }

    public function getAll() {
        $query = $this->adapter->query('SELECT s.* FROM Service s');
        return $query->execute();
    }

    public function get($id) {
        $query = $this->adapter->query('SELECT s.* FROM Service s');
        return $query->execute();
    }

}