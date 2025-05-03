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
require_once LIB . '/core/result.php';
require_once LIB . '/core/log.php';



/*
    Mermaid class definition
*/
class Mermaid extends Result
{
    const DIRECTION_TOP_DOWN    = 'TB';         /* Top bottom */
    const DIRECTION_TOP_BOTTOM  = 'TB';
    const DIRECTION_BOTTOM_TOP  = 'BT';
    const DIRECTION_RIGHT_LEFT  = 'RL';
    const DIRECTION_LEFT_RIGHT  = 'LR';

    /* Line style */
    const LINK_BOLD             = 'bold';       /* === */
    const LINK_LINE             = 'line';       /* --- */
    const LINK_DOT              = 'dot';        /* -.- */
    const LINK_HIDDEN           = 'hidden';     /* ~~~ */

    /* Begin of line */
    const POINT_ARROW           = 'arrow';      /* > */
    const POINT_CROSS           = 'cross';      /* x */
    const POINT_DOT             = 'dot';        /* o */
    const POINT_NONE            = 'none';       /* - */

    /*
        Shapes SHAPES_CONSTANTS
        https://mermaid.js.org/syntax/flowchart.html
    */
    const MAIN_CONTAINER        = 'main-container';        // subgraph
    const MAIN_ASYMMETRIC       = 'main-asymmetric';       // >text]
    const MAIN_CIRCLE           = 'main-circle';           // ((text))
    const MAIN_CIRCLE_DOUBLE    = 'main-circle-double';    // (((text)))
    const MAIN_CYLINDRER        = 'main-cylindrer';        // [(text)]
    const MAIN_STADIUM          = 'main-stadium';          // ([text])
    const MAIN_HEXAGON          = 'main-hexagon';          // {{text}}
    const MAIN_PARAL_LEFT       = 'main-paral-left';       // [\text\]
    const MAIN_PARAL_RIGHT      = 'main-paral-right';      // [/text/]
    const MAIN_RECT             = 'main-rect';             // [text]
    const MAIN_RECT_ROUNDED     = 'main-rect-rounded';     // (text)
    const MAIN_RHOMBUS          = 'main-rhombus';          // {text}
    const MAIN_SUBROUTINE       = 'main-subroutine';       // [[text]]
    const MAIN_TRAPEZOID_DOWN   = 'main-trapezoid-down';   // [/text\]
    const MAIN_TRAPEZOID_UP     = 'main-trapezoid-up';      // [\text/]
    /* Mindmap */
    const MAIN_BANG             = 'main-bang';              // ))text((
    const MAIN_CLOUD            = 'main-cloud';             // ((text))


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
        -
          first:
            label   : string
            shape   : SHAPES_CONSTANTS
            url     : url
        -
          second:
            ...

        heracly:
          third:
            first:
            second:

        links:
        -
          from  : string
          to    : string
          line  : LINE_
          begin : POINT_
          end   : POINT_*
          label : string
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
        $result[] = '%% Flowchatd diagramm';

        /* Begin of diagram */
        $result[] = 'flowchart ' . $aDirection;

        /* Create objects */
        $elements = clValueFromObject( $aData, 'elements', [] );
        $hierachy = clValueFromObject( $aData, 'hierachy', [] );
        $links = clValueFromObject( $aData, 'links', [] );

        /* Добавление элементов */
        $result[] = '';
        $result[] = '%% Elements';
        foreach( $elements as $id => $element )
        {
            $url = clValueFromObject( $element, 'url', null );

            $label = str_replace
            (
                [ '{}()[]|"' ],
                [ '______!\'\'' ],
                clValueFromObject( $element, 'label', $id )
            );

            if( !empty( $url ))
            {
                $label = '<a href="' . $url . '">' . $label . '</a>';
            }

            $result[] = $id . $this -> getFlowchartElement
            (
                $label,
                clValueFromObject( $element, 'shape', '' )
            );
        }

        /* Сборка иерархии */
        $result[] = '';
        $result[] = '%% Hierarch';
        $subgraphs = function( $hierachy )
        use ( &$subgraphs, &$result, &$aData )
        {
            foreach( $hierachy as $key => $value )
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

        /* Добавление связей */
        $result[] = '';
        $result[] = '%% Links';
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
                ( empty( $label ) ? ' ' : ' |' . $label . '| ' )
                . $to;
            }
            else break;
        }

        return $this -> isOk() ? implode( PHP_EOL, $result ) . PHP_EOL : '';
    }



    /*
        Builds flowchart diagram
    */
    public function buildMindmap
    (
        /*
            nodes:
            - first
                label:
                shape:

            hierarchy:
            - first:
              - second:
                ...
            - third:
              ...
        */
        array $a
    )
    :string
    {
        $result = [];
        $result[] = '%% Mindmap diagramm';

        /* Get elements */
        $elements = clValueFromObject( $a, 'nodes', [] );
        $hierarchy = clValueFromObject( $a, 'hierarchy', [] );


        /* Begin of diagram */
        $result[] = 'mindmap';

        /* Recursion for hierarchy */
        $loop = function( $tree, $depth )
        use ( &$result, &$loop, &$hierarchy, &$elements )
        {
            foreach( $tree as $key => $value )
            {
                $item = clValueFromObject( $elements, $key );
                if( $item )
                {
                    /* Ключ присутствует в списке элементов */
                    $label = $this -> getMindmapElement
                    (
                        clValueFromObject( $item, 'label', $key ),
                        clValueFromObject( $item, 'shape', self::MAIN_RECT )
                    );
                }
                else
                {
                    /* Используется идентификтаор элемента */
                    $label = $key;
                }
                /* Добавляем новый элемент */
                $result[] = str_repeat( ' ', $depth * 2) . $label;
                /* запуск рекурсии при наличии массива потомков */
                if( is_array( $value ))
                {
                    $loop( $value, $depth + 1 );
                }
            }
        };
        $loop( $hierarchy, 1 );

        return $this -> isOk() ? implode( PHP_EOL, $result ) .  PHP_EOL : '';
    }



    public function buldSequence
    (
        /* Incoming array */
        array $aData
    )
    : string
    {
        $result = '';
        return $result;
    }


    /**************************************************************************
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



    /*
        Convert shape in to label
    */
    public static function getFlowchartElement
    (
        /* Human readable label */
        string $label,
        /* Shape MAIN_* */
        $shape = self::MAIN_RECT
    )
    : string
    {
        switch( $shape )
        {
            case '':
            case null:
            case self::MAIN_RECT:           return "[$label]";
            case self::MAIN_ASYMMETRIC:     return ">$label]";
            case self::MAIN_BANG:           /* Mindmap */
            case self::MAIN_CLOUD:          /* Mindmap */
            case self::MAIN_CIRCLE:         return "(($label))";
            case self::MAIN_CIRCLE_DOUBLE:  return "((($label)))";
            case self::MAIN_CYLINDRER:      return "[($label)]";
            case self::MAIN_STADIUM:        return "([$label])";
            case self::MAIN_HEXAGON:        return "{{$label}}";
            case self::MAIN_PARAL_LEFT:     return "[\$label\]";
            case self::MAIN_PARAL_RIGHT:    return "[/$label/]";
            case self::MAIN_RECT_ROUNDED:   return "($label)";
            case self::MAIN_RHOMBUS:        return "{$label}";
            case self::MAIN_SUBROUTINE:     return "[[$label]]";
            case self::MAIN_TRAPEZOID_DOWN: return "[/$label\]";
            case self::MAIN_TRAPEZOID_UP:   return "[\$label/]";
            case self::MAIN_CONTAINER:      return '';
            default:
                return '@{ shape: ' . $shape . ', label: "' . $label . '" }';
        }
    }



    /*
        Convert shape in to label
    */
    public static function getMindmapElement
    (
        /* Human readable label */
        string $label,
        /* Shape MAIN_* */
        $shape = self::MAIN_RECT
    )
    : string
    {
        switch( $shape )
        {
            case '':
            case null:
            default:                        /* Other */
            case self::MAIN_RECT:           return "[$label]";
            case self::MAIN_CIRCLE:         return "(($label))";
            case self::MAIN_RECT_ROUNDED:   return "($label)";
            case self::MAIN_BANG:           return "))$label((";
            case self::MAIN_CLOUD:          return "($label)";
            case self::MAIN_HEXAGON:        return "{{$label}}";
        }
    }
}

