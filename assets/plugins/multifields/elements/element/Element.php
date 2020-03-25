<?php

namespace Multifields\Elements;

class Element extends \Multifields\Base\Elements
{
    protected $tpl = 'element.tpl';

    public function render($params = [], $data = [])
    {
        if (!isset($params['tag'])) {
            $params['tag'] = 'div';
        }

        if (!isset($params['id'])) {
            $params['id'] = '';
        }

        return parent::render($params, $data);
    }
}