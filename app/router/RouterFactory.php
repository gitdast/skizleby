<?php

namespace Ski;

use Nette;
use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;


class RouterFactory {

	/**
	 * @return Nette\Application\IRouter
	 */
	public static function createRouter(){
		$router = new RouteList;
		
		$router[] = new Route('index.php', array(
						'presenter' => 'Homepage'
					), Route::ONE_WAY);
		
		$router[] = new Route('webkamera', 'Webcam:default', Route::ONE_WAY);		
		$router[] = new Route('<presenter>/<action>[/<id>]', 'Homepage:default');
		
	
		return $router;
	}

}
