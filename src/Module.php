<?php
/**
 * Module.php - Module Class
 *
 * Module Class File for Tag Module
 *
 * @category Config
 * @package Tag
 * @author Verein onePlace
 * @copyright (C) 2020  Verein onePlace <admin@1plc.ch>
 * @license https://opensource.org/licenses/BSD-3-Clause
 * @version 1.0.0
 * @since 1.0.0
 */

namespace OnePlace\Tag;

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Mvc\MvcEvent;
use Laminas\ModuleManager\ModuleManager;
use Laminas\Session\Config\StandardConfig;
use Laminas\Session\SessionManager;
use Laminas\Session\Container;
use Laminas\EventManager\EventInterface as Event;

class Module {
    /**
     * Module Version
     *
     * @since 1.0.0
     */
    const VERSION = '1.0.8';

    /**
     * Load module config file
     *
     * @since 1.0.0
     * @return array
     */
    public function getConfig() : array {
        return include __DIR__ . '/../config/module.config.php';
    }

    /**
     * Load Models
     */
    public function getServiceConfig() : array {
        return [
            'factories' => [
                # Tag Module - Base Model
                Model\TagTable::class => function($container) {
                    $tableGateway = $container->get(Model\TagTableGateway::class);
                    return new Model\TagTable($tableGateway,$container);
                },
                Model\TagTableGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\Tag($dbAdapter));
                    return new TableGateway('core_tag', $dbAdapter, null, $resultSetPrototype);
                },
                Model\EntityTagTable::class => function($container) {
                    $tableGateway = $container->get(Model\EntityTagTableGateway::class);
                    return new Model\EntityTagTable($tableGateway,$container);
                },
                Model\EntityTagTableGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\EntityTag($dbAdapter));
                    return new TableGateway('core_entity_tag', $dbAdapter, null, $resultSetPrototype);
                },
            ],
        ];
    }

    /**
     * Load Controllers
     */
    public function getControllerConfig() : array {
        return [
            'factories' => [
                # Tag Main Controller
                Controller\TagController::class => function($container) {
                    $oDbAdapter = $container->get(AdapterInterface::class);
                    return new Controller\TagController(
                        $oDbAdapter,
                        $container->get(Model\TagTable::class),
                        $container
                    );
                },
                # Entity Tag Controller
                Controller\EntityController::class => function($container) {
                    $oDbAdapter = $container->get(AdapterInterface::class);
                    return new Controller\EntityController(
                        $oDbAdapter,
                        $container->get(Model\EntityTagTable::class),
                        $container
                    );
                },
                # Api Controller
                Controller\ApiController::class => function($container) {
                    $oDbAdapter = $container->get(AdapterInterface::class);
                    return new Controller\ApiController(
                        $oDbAdapter,
                        $container->get(Model\EntityTagTable::class),
                        $container
                    );
                },
                # Api Controller
                Controller\InstallController::class => function($container) {
                    $oDbAdapter = $container->get(AdapterInterface::class);
                    return new Controller\InstallController(
                        $oDbAdapter,
                        $container->get(Model\EntityTagTable::class),
                        $container
                    );
                },
            ],
        ];
    }
}
