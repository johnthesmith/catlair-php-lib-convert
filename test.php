<?php

namespace catlair;

require_once "mermaid.php";



$log = Log::create();
$m = Mermaid::create( $log );

$r = $m -> buldFlowchart
(
    [
        'elements' =>
        [
            'first'  => [ 'label' => 'first!', 'shape' => 'lin-cyl' ],
            'second' => [ 'label' => 'second name', 'shape' => 'docs' ],
            'third'  => [ 'label' => 'third name', 'shape' => 'rect', 'url' => 'https://google.com' ],
            'forth'  => [ 'label' => 'forth name', 'shape' => 'main-circle' ],
            'un'     => [ 'label' => 'c1', 'shape' => 'main-container' ],
            'bug'    => [ 'label' => 'c2', 'shape' => 'main-container' ],
            'lag'    => [ 'label' => 'lag', 'shape' => 'main-stadium' ],
        ],
        'hierachy' =>
        [
            'un' => [ 'second', 'third' ],
            'bug' => [ 'forth' ]
        ],
        'links' =>
        [
            [
                'from' => 'first',
                'to' => 'second',
                'line' => Mermaid::LINK_DOT,
                'end' => Mermaid::POINT_CROSS,
                'label' => 'he "llo" dsdf'
            ],
            [
                'from' => 'second',
                'to' => 'first',
                'end' => Mermaid::POINT_DOT,
            ],
            [
                'from' => 'un',
                'to' => 'bug',
            ],
            [
                'from' => 'third',
                'to' => 'lag',
            ]
        ]
    ]
);

$log -> dump( $m -> getResultAsArray() );
$log -> prn( $r );
