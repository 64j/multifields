<?php

namespace Multifields\Elements\Element;

class Element extends \Multifields\Base\Elements
{
    protected $tpl = 'element.tpl';

    protected $template = '<[+name+] [+attr+]>[+items+]</[+name+]>';

    public function render($params = [], $data = [])
    {
        if (!isset($params['name'])) {
            $params['name'] = 'div';
        }

        if (isset($params['id']) && $params['id'] != '') {
            $params['attr'] = ' id="' . $params['id'] . '"';
        }

        if (isset($params['class']) && $params['class'] != '') {
            $params['attr'] .= ' class="' . $params['class'] . '"';
        }

        return parent::render($params, $data);
    }
}
