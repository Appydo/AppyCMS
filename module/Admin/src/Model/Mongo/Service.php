<?php
namespace Admin\Model\Mongo;

use Zend\Db\Adapter\Adapter;

class Service
{
    public $collection = 'service';
    
    public function __construct($adapter) {
        echo '?';
        $this->adapter = new \Mongo('mongodb://appydo:132v9h2a@ds039467.mongolab.com:39467/test');
        echo 'ok';
    }
    
    public function getAll() {
        $collection = $this->adapter->collection;
        return $this->adapter->$table->find();
    }
    
    public function get($id) {
        $collection = $this->collection;
        $cursor = $this->adapter->$table->find(array('id'=>$id));
        $cursor->next();
        return $cursor->current();
    }
    
    public function getOne($search = array(), $options = array()) {
        $collection = $this->collection;
        $cursor = $this->adapter->$table->find($search, $options);
        $cursor->next();
        return $cursor->current();
    }
}