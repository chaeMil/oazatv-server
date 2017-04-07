<?php

namespace App;

use Drahak\Restful\Application\IResourceRouter;
use Drahak\Restful\Application\Routes\ResourceRoute;
use Nette,
	Nette\Application\Routers\RouteList,
	Nette\Application\Routers\Route;


class RouterFactory {

	/**
	 * @return Nette\Application\IRouter
	 */
	public static function createRouter() {
		$router = new RouteList();

        $router[] = new ResourceRoute('api/v3/archive/[<page>]/[<lang>]', array(
            'module' => 'RestApi',
            'presenter' => 'Archive',
            'action' => array(
                IResourceRouter::GET => 'page',
            )
        ), IResourceRouter::GET);

        $router[] = new Route('files/video/<hash>/<format>/', array(
            'module' => 'Files',
            'presenter' => 'Video',
            'action' => 'getVideoFile',
            'hash' => NULL,
            'format' => NULL
        ));
		
        //json api links
        $router[] = new Route('[<locale=cs cs|en>/]api/v2/<presenter>/<id>/<action>', array(
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
        $router[] = new Route('<presenter>/<action>/<id>/<attr>/', array(
            'module' => 'Front',
            'presenter' => 'Main',
            'action' => 'default',
            'id' => NULL,
            'attr' => NULL
            ));
                               
                
		return $router;
	}

}
