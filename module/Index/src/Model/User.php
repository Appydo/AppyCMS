<?php
namespace Index\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Topic
{
    protected $table ='topic';

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }
    
    public function fetch( $project_id )
    {
        $stmt = $this->adapter->createStatement('
                    SELECT t.*, u.username as author
                    FROM Topic t
                    LEFT JOIN users u on t.user_id=u.id
                    WHERE t.project_id=:project and t.topic_id is NULL and t.hide=0
                    ORDER BY t.id DESC');
        return $stmt->execute(array(
            'project' => $project_id
            ))->getResource()->fetchAll();
    }
}