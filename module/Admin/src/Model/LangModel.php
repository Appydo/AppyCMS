<?php
namespace Admin\Model;

use Zend\Db\Adapter\Adapter;

class Lang extends SimpleAdmin {
	public function __construct($db, $table)
    {
        $this->db    = $db;
        $this->table = $table;

        $this->initialize();
    }

    public function getAll($option)
    {
        $order_string = (!empty($result['order'])) ? 'ORDER BY '.$result['order'].' '.$result['sort'] : '';

        $entities = $this->db
            ->createStatement('SELECT '.$this->select.' FROM '.$this->table.' '.$where_string.' '.$order_string.' LIMIT '.$start.','.$nb)
            ->execute()
            ->getResource()
            ->fetchAll($option);

        return $entities;
    }

    public function get($id)
    {
        $id  = (int) $id;
        $row = $this->db
            ->query('SELECT * FROM '.$this->table.' WHERE id=:id')
            ->execute(array('id'=>$id))
            ->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function insert($data)
    {
        $insert = $this->db->query(
            "INSERT INTO ".$this->table." (".implode(",", array_keys($data)).")
            VALUES (:".implode(",:", array_keys($data)).")",
                $data
                );
        if ($insert) {
            return $this->db->getDriver()->getLastGeneratedValue();
        } else {
            throw new \Exception("Error during insert");
        }
    }

    public function update($data) {
        $update_set = array();
        foreach($data as $key=>$value) {
            $update_set[] = $key . '=:' . $key;
        }

        return $this->db->query('UPDATE '.$this->table.'
            SET '.implode(",", $update_set).'  WHERE '.$this->id.'=:'.$this->id,
                $data
                );

    }

    public function deleteAction($delete_ids) {
        $count = 0;
        foreach($delete_ids as $id) {
            $id = (int) $id;
            if(!empty($id)) {
                $delete = $this->db
                    ->query('DELETE FROM '.$this->table.' WHERE '.$this->id.'=:id')
                    ->execute(array('id' => $id));
                if (!$delete) {
                    throw new \Exception("Could not delete row $id");
                } else {
                    $count++;
                }
            }
        }
        return $count;
    }
}