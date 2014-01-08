<?php
namespace Index\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Feed\Writer\Feed;
use Zend\View\Model\FeedModel;

class FeedController extends AbstractActionController
{
    public function rssAction()
    {
        $feed = new Feed();
        $feed->setTitle($this->project['name']);
        $feed->setFeedLink('http://ourdomain.com/rss', 'atom');
        $feed->addAuthor(array(
            'name'  => 'admin',
            'email' => $this->project['email'],
            'uri'   => $this->project['url'],
             ));
        $feed->setDescription($this->project['description']);
		if (isset($this->project['url'])) {
        	$feed->setLink($this->project['url']);
		} else {
        	$feed->setLink('localhost');
		}
		$feed->setDateModified(time());
        $topics = $this->db->query('
                    SELECT t.*
                    FROM Topic t
                    WHERE t.project_id=:project and t.hide!=1 and t.content!=""
                    LIMIT 10'
                    )->execute(array(
                        'project' => $this->project['id']
                        ));

        $data = array();
        foreach ($topics as $topic) {
            $data[] = array(
                'title' => $topic['name'],
                'content' => $topic['content'],
                'link' => $this->project['url'] . $this->url()->fromRoute('index', array('controller'=>'index', 'action'=>'topic', 'id'=>$topic['id'])),
                'date_created' => date(DATE_RSS, $topic['created']),
            );
        }

        foreach($data as $row)
        {
            $entry = $feed->createEntry();
            $entry->setTitle($row['title']);
            $entry->setLink($row['link']);
            $entry->setDescription($row['content']);

            $entry->setDateModified(strtotime($row['date_created']));
            $entry->setDateCreated(strtotime($row['date_created']));

            $feed->addEntry($entry);
        }

        $feed->export('rss');

        $feedmodel = new FeedModel();
        $feedmodel->setFeed($feed);

        return $feedmodel;
    }
    public function rssProductAction()
    {
        $feed = new Feed();
        $feed->setTitle($this->project['name']);
        $feed->setFeedLink('http://ourdomain.com/rss', 'atom');
        $feed->addAuthor(array(
            'name'  => 'admin',
            'email' => $this->project['email'],
            'uri'   => $this->project['url'],
             ));
        $feed->setDescription($this->project['description']);
        if (isset($this->project['url'])) {
            $feed->setLink($this->project['url']);
        } else {
            $feed->setLink('localhost');
        }
        $feed->setDateModified(time());
        $products = $this->db->query('
                    SELECT p.*
                    FROM Product p
                    WHERE t.hide!=1
                    LIMIT 10'
                    )->execute(array(
                        'project' => $this->project['id']
                        ));

        $data = array();
        foreach ($products as $product) {
            $data[] = array(
                'title'   => $product['name'],
                'content' => $product['description'],
                'link'    => $this->project['url'] . $this->url()->fromRoute('index', array('controller'=>'index', 'action'=>'product', 'id'=>$product['id'])),
                'date_created' => date(DATE_RSS, $product['created']),
            );
        }

        foreach($data as $row)
        {
            $entry = $feed->createEntry();
            $entry->setTitle($row['title']);
            $entry->setLink($row['link']);
            $entry->setDescription($row['content']);

            $entry->setDateModified(strtotime($row['date_created']));
            $entry->setDateCreated(strtotime($row['date_created']));

            $feed->addEntry($entry);
        }

        $feed->export('rss');

        $feedmodel = new FeedModel();
        $feedmodel->setFeed($feed);

        return $feedmodel;
    }
}
