<?php
/**
 * module.config.php - Tag Config
 *
 * Main Config File for Tag Module
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

use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Laminas\ServiceManager\Factory\InvokableFactory;

return [
    # Tag Module - Routes
    'router' => [
        'routes' => [
            # Module Basic Route
            'tag' => [
                'type'    => Segment::class,
                'options' => [
                    'route' => '/tag[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\TagController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'tag-api' => [
                'type'    => Segment::class,
                'options' => [
                    'route' => '/tag/api[/:action[/:filter]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'filter' => '[a-zA-Z][a-zA-Z0-9_-]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\ApiController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'tag-entity' => [
                'type'    => Segment::class,
                'options' => [
                    'route' => '/tag/entity[/:action[/:filter]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'filter' => '[a-zA-Z0-9_-]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\EntityController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'tag-api-list' => [
                'type'    => Segment::class,
                'options' => [
                    'route' => '/tag/api/list[/:form[/:tagtype]]',
                    'constraints' => [
                        'form' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'tagtype' => '[a-zA-Z][a-zA-Z0-9_-]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\ApiController::class,
                        'action'     => 'list',
                    ],
                ],
            ],
        ],
    ],

    # View Settings
    'view_manager' => [
        'template_path_stack' => [
            'tag' => __DIR__ . '/../view',
        ],
    ],
];
