<?php
namespace App\Model;

class Galleries{
	
	/** @var \DibiConnection */
	private $connection;

	public function __construct(\DibiConnection $connection){
		$this->connection = $connection;
		$this->connection->query("SET CHARSET utf8");
		$this->connection->query("SET NAMES utf8");
	}
	
	public function getList(){
		return $this->connection->select("*")->from("ski_galleries")->where("display = 1")->orderBy("order")->desc()->fetchAll();
	}
	
	public function getItem($id){
		return $this->connection->select("*")->from("ski_galleries")->where("id = %i", $id)->fetch();
	}
	
}
