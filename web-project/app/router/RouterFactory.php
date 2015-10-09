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
               
		
                //json api links
                $router[] = new Route('api/v2/<presenter>/<id>/<action>', array(
                    'module' => 'Api',
                    'presenter' => 'Main',
                    'action' => 'default',
                    'id' => NULL
                ));
                
                //admin links
                $router[] = new Route('admin/<presenter>/<action>/<id>', array(
                    'module' => 'Admin',
                    'presenter' => 'Main',
                    'action' => 'default',
                    'id' => NULL
                ));

                //frontend links
                $router[] = new Route('[<locale=cs cs|en>/]<presenter>/<action>/<id>', array(
                    'module' => 'Front',
                    'presenter' => 'Main',
                    'action' => 'default',
                    'id' => NULL
                ));
                               
                
		return $router;
	}

}
