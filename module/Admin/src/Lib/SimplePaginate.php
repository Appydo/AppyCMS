<?php
namespace Admin\Lib;

class SimplePaginate {
	function public paginate($request)
	{
		if (isset($_GET['page'])) {
            $page = $_GET['page'];
        } else {
            $page  = 1;
        } 
        if (isset($_GET['move'])) {
            if ($_GET['move']=='next') {
                $page++;
            } elseif ($_GET['move']=='prev' and $page!=1) {
                $page--;
            }
        }
        if (empty($page)) $page = 0;
        $nb    = $this->table_row;
        $start = ($page * $nb) - $nb;
	}
}