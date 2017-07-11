<?php

namespace App;

use Nette,
	Nette\Application\Routers\RouteList,
	Nette\Application\Routers\Route;


class RouterFactory {

	/**
	 * @return Nette\Application\IRouter
	 */
	public static function createRouter() {
		$router = new RouteList();

		        $secured = 0;
		        if (SECURED) {
		            $secured = Route::SECURED;
                }

                $securedApi = 0;
		        if (SECURED_API) {
		            $secured = Route::SECURED;
                }

                //json api links
                $router[] = new Route('[<locale=cs cs|en>/]api/v2/<presenter>/<id>/<action>', array(
                    'module' => 'Api',
                    'presenter' => 'Main',
                    'action' => 'default',
                    'id' => NULL
                ), $securedApi);
                
                //admin links
                $router[] = new Route('admin/<presenter>/<action>/<id>', array(
                    'module' => 'Admin',
                    'presenter' => 'Main',
                    'action' => 'default',
                    'id' => NULL
                ), $secured);

                //frontend links
                $router[] = new Route('<presenter>/<action>/<id>/<attr>/', array(
                    'module' => 'Front',
                    'presenter' => 'Main',
                    'action' => 'default',
                    'id' => NULL,
                    'attr' => NULL
                ), $secured);
                               
                
		return $router;
	}

}
