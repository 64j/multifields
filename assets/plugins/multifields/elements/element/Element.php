<?php

namespace Multifields\Elements\Element;

class Element extends \Multifields\Base\Elements
{
    protected $tpl = 'element.tpl';

    protected $template = '<[+tag+] [+attr+]>[+items+]</[+tag+]>';

    protected static function setAttr()
    {
        if (isset(self::$params['id']) && self::$params['id'] != '') {
            self::$params['attr'] .= ' id="' . self::$params['id'] . '"';
        }

        if (isset(self::$params['class']) && self::$params['class'] != '') {
            self::$params['attr'] .= ' class="' . self::$params['class'] . '"';
        }
    }

    public function render()
    {
        if (empty(self::$params['tag'])) {
            self::$params['tag'] = 'div';
        }

        return parent::view();
    }
}
