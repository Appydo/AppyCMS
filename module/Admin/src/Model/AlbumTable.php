<?php
namespace Album\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\AbstractTableGateway;

class AlbumTable
{
    protected $table ='album';

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;

        $this->initialize();
    }

    public function fetchAll()
    {
        $this->adapter->query('SELECT ');
        $resultSet = $this->select();
        return $resultSet;
    }

    public function getAlbum($id)
    {
        $id  = (int) $id;

        $rowset = $this->select(array(
            'id' => $id,
        ));

        $row = $rowset->current();

        if (!$row) {
            throw new \Exception("Could not find row $id");
        }

        return $row;
    }

    public function saveAlbum(Album $album)
    {
        $data = array(
            'artist' => $album->artist,
            'title'  => $album->title,
        );

        $id = (int) $album->id;

        if ($id == 0) {
            $this->insert($data);
        } elseif ($this->getAlbum($id)) {
            $this->update(
                $data,
                array(
                    'id' => $id,
                )
            );
        } else {
            throw new \Exception('Form id does not exist');
        }
    }

    public function deleteAlbum($id)
    {
        $this->delete(array(
            'id' => $id,
        ));
    }
}