<?php
namespace TodoWS\modeles;

/**
 * Class Modele
 * Les modèles doivent hérités de cette classe.
 * 
 * @author Jonathan Martel
 * @version 1.0
 * @update 2013-12-11
 * @license MIT
 * 
 */
class Modele {
	
    protected $_db;
	function __construct ()
	{
		$this->_db = \TodoWS\lib\MonSQL::getInstance();
	}
	
	function __destruct ()
	{
		
	}
}




?>