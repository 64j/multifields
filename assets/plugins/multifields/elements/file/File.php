<?php

namespace Multifields\Elements;

class File extends \Multifields\Base\Elements
{
    protected $styles = 'file.css';
    protected $scripts = 'file.js';
    protected $tpl = 'file.tpl';

    public function render($params = [], $data = [])
    {
        $params['button.class'] = 'far fa-file';

        return parent::render($params, $data);
    }
}