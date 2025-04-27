<?php
/*
    Catlair PHP Copyright (C) 2021 https://itserv.ru

    This program (or part of program) is free software: you can redistribute
    it and/or modify it under the terms of the GNU Aferro General
    Public License as published by the Free Software Foundation,
    either version 3 of the License, or (at your option) any later version.

    This program (or part of program) is distributed in the hope that
    it will be useful, but WITHOUT ANY WARRANTY; without even the implied
    warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
    See the GNU Aferro General Public License for more details.
    You should have received a copy of the GNU Aferror General Public License
    along with this program. If not, see <https://www.gnu.org/licenses/>.

*/

/*
    Построитель кода mermaid диаграмм

    Репозитории
        2025-04-27
            https://github.com/johnthesmith/catlair-php-lib-web
*/
namespace catlair;



/* Core libraries */
require_once '../core/result.php';
require_once '../core/log.php';



/*
    Mermaid class definition
*/
class Mermaid extends Result
{
    /* Flowchart directions */
    const DIRECTION_TOP_DOWN    = 'TD';
    const DIRECTION_TOP_BOTTOM  = 'TB';
    const DIRECTION_BOTTOM_TOP  = 'BT';
    const DIRECTION_RIGHT_LEFT  = 'RL';
    const DIRECTION_LEFT_RIGHT  = 'LR';

    /* Line style */
    const LINK_BOLD         = 'bold';
    const LINK_LINE         = 'line';
    const LINK_DOT          = 'dot';
    const LINK_HIDDEN       = 'hidden';

    /* Begin of line */
    const POINT_ARROW        = 'arrow';
    const POINT_CROSS        = 'cross';
    const POINT_DOT          = 'dot';
    const POINT_NONE         = 'none';


    /* Log object */
    private $log = null;



    /*
        Create mermaid object
    */
    static public function create
    (
        /* Log object */
        Log $aLog
    )
    {
        $result = new Mermaid();
        $result -> log = $aLog;
        return $result;
    }



    /*
        Builds flowchart diagram

        elements:
        - first:
            label: string
            shape: https://mermaid.js.org/syntax/flowchart.html#expanded-node-shapes-in-mermaid-flowcharts-v11-3-0
        - second:
          ...
        - third
          ...

        heracly:
          third
            first:
            second:

        links:
        -
          from: first
          to: second
    */
    public function buldFlowchart
    (
        /* Incoming array */
        array $aData,
        /* Direction */
        string $aDirection = self::DIRECTION_LEFT_RIGHT
    )
    : string
    {
        $result = [];
        $result[] = '%% FLowchatd diagramm';

        /* Begin of diagram */
        $result[] = 'flowchart ' . $aDirection;

        $elements = clValueFromObject( $aData, 'elements', [] );
        $hierachy = clValueFromObject( $aData, 'hierachy', [] );
        $links = clValueFromObject( $aData, 'links', [] );

        $result[] = '';
        $result[] = '%% Elements';

        /* Добавление элементов */
        foreach( $elements as $id => $element )
        {
            $url = clValueFromObject( $element, 'url', null );

            $label = str_replace
            (
                [ '"' ],
                [ '\'\'' ],
                clValueFromObject( $element, 'label', $id )
            );

            if( !empty( $url ))
            {
                $label = '<a href="' . $url . '">' . $label . '</a>';
            }

            $shape = clValueFromObject( $element, 'shape', '' );
            if( $shape != 'container' )
            {
                $result[] = $id
                . '@{ shape: '
                . $shape
                . ', label: "'
                . $label
                . '" }';
            }
            else
            {
                $result[] = $id;
            }
        }

        $result[] = '';
        $result[] = '%% Hierarch';

        $subgraphs = function( $hierachy )
        use ( &$subgraphs, &$result, &$aData )
        {
            foreach( $hierachy as $key => $value)
            {
                if( is_array( $value ))
                {
                    $label = clValueFromObject
                    (
                        $aData,
                        [ 'elements', $key, 'label' ],
                        ''
                    );
                    $result[] = 'subgraph '
                    . $key
                    . ( empty( $label ) ? '' : ( '[' . $label . ']' ));
                    $subgraphs( $value );
                    $result[] = 'end';
                }
                else
                {
                    $result[] = $value;
                }
            }
        };
        $subgraphs( $hierachy );


        $result[] = '';
        $result[] = '%% Links';

        /* Добавление связей */
        foreach( $links as $link )
        {
            /* Извлечение from */
            $from = clValueFromObject( $link, 'from' );
            $to = clValueFromObject( $link, 'to' );

            $type = $this -> getLink
            (
                clValueFromObject( $link, 'line', self::LINK_LINE ),
                clValueFromObject( $link, 'begin', self::POINT_NONE ),
                clValueFromObject( $link, 'end', self::POINT_ARROW )
            );

            /* Извлечение метки */
            $label = str_replace
            (
                [ '"', '|' ],
                [ '\'\'', '-' ],
                clValueFromObject( $link, 'label', '' )
            );

            $this -> validate
            (
                !array_key_exists( $from, $elements ),
                'unknown_element_from',
                [ 'id'=> $from, 'link' => $link ]
            );

            $this -> validate
            (
                !array_key_exists( $to, $elements ),
                'unknown_element_to',
                [ 'id' => $to, 'link' => $link ]
            );

            if( $this -> isOk() )
            {
                $result[] = $from
                . ' '
                . $type
                .
                (
                    empty( $label )
                    ? ' '
                    : ' |' . $label . '| '
                )
                . $to;
            }
            else break;
        }

        return $this -> isOk() ? implode( PHP_EOL, $result ) .  PHP_EOL : '';
    }



    /*
        Setters and getters
    */

    /*
        Returns the log object
    */
    public function getLog() : Log
    {
        return $this -> log;
    }



    /*
        Return link
    */
    public function getLink
    (
        /* Line type LINK_* */
        string $line,
        string $begin,
        string $end
    )
    {
        $result = '-->';

        switch( $begin )
        {
            case self::POINT_CROSS: $b = 'x'; break;
            case self::POINT_DOT:   $b - 'o'; break;
            case self::POINT_ARROW: $b = '<'; break;
        }

        switch( $end )
        {
            case self::POINT_CROSS: $e = 'x'; break;
            case self::POINT_DOT:   $e = 'o'; break;
            case self::POINT_ARROW: $e = '>'; break;
        }

        switch( $line )
        {
            case self::LINK_LINE:
                switch( $begin )
                {
                    case self::POINT_NONE:  $b = '-'; break;
                }
                switch( $end )
                {
                    case self::POINT_NONE:  $e = '-'; break;
                }
                $result = $b . '-' . $e;
            break;
            case self::LINK_BOLD:
                switch( $begin )
                {
                    case self::POINT_NONE:  $b = '='; break;
                }
                switch( $end )
                {
                    case self::POINT_NONE:  $e = '='; break;
                }
                $result = $b . '=' . $e;
            break;
            case self::LINK_DOT:
                switch( $begin )
                {
                    case self::POINT_NONE:  $b = '-'; break;
                }
                switch( $end )
                {
                    case self::POINT_NONE:  $e = '-'; break;
                    case self::POINT_ARROW: $e = '->'; break;
                    case self::POINT_CROSS: $e = '-x'; break;
                    case self::POINT_DOT:   $e = '-o'; break;
                }
                $result = $b . '.' . $e;
            break;
            case self::LINK_HIDDENT:
                $result = '~~~';
            break;
        }
        return $result;
    }

}
