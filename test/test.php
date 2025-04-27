<?php

namespace catlair;

require_once "../mermaid.php";



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
            'forth'  => [ 'label' => 'forth name', 'shape' => 'rect' ],
            'un'     => [ 'label' => 'c1', 'shape' => 'container' ],
            'bug'    => [ 'label' => 'c2', 'shape' => 'container' ],
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
                'type' => '===>',
                'label' => 'he "llo" dsdf'
            ],
            [
                'from' => 'second',
                'to' => 'first',
                'type' => '===>',
                'label' => 'he "llo" dsdf'
            ],
            [
                'from' => 'un',
                'to' => 'bug',
                'type' => '===>',
                'label' => 'he "llo" dsdf'
            ]
        ]
    ]
);

$log -> dump( $m -> getResultAsArray() );
$log -> prn( $r );
