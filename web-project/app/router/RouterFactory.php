<?php

namespace App;

use Nette,
	Nette\Application\Routers\RouteList,
	Nette\Application\Routers\Route;


class RouterFactory
{

	/**
	 * @return Nette\Application\IRouter
	 */
	public static function createRouter()
	{
		$router = new RouteList();
		
                $router[] = new Route('api/v2/<presenter>/<action>/<id>', array(
                    'module' => 'Api',
                    'presenter' => 'Main',
                    'action' => 'default',
                    'id' => NULL
                ));
                
                $router[] = new Route('admin/<presenter>/<action>/<id>', array(
                    'module' => 'Admin',
                    'presenter' => 'Main',
                    'action' => 'default',
                    'id' => NULL
                ));

                $router[] = new Route('<presenter>/<action>/<id>', array(
                    'module' => 'Front',
                    'presenter' => 'Main',
                    'action' => 'default',
                    'id' => NULL
                ));
                
		return $router;
	}

}
