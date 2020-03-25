<?php

namespace Multifields\Elements;

class Image extends \Multifields\Base\Elements
{
    protected $styles = 'image.css';
    protected $scripts = 'image.js';
    protected $tpl = 'image.tpl';

    public function render($params = [], $data = [])
    {
        $params['onchange'] = '';
        $params['onclick'] = '';

        if (!empty($params['thumb'])) {
            $thumb = is_array($params['thumb']) ? implode(',', $params['thumb']) : $params['thumb'];
            $params['attr'] .= ' data-thumb="' . $thumb . '"';
            $params['onchange'] = 'Multifields.elements.image.setValue(event);';
        }

        if (!empty($params['multi'])) {
            $params['attr'] .= ' data-multi="' . $params['multi'] . '"';
            $params['onclick'] = 'Multifields.elements.image.MultiBrowseServer(event)';
            $params['button.class'] = 'far fa-images';
        } else {
            $params['button.class'] = 'far fa-image';
        }

        return parent::render($params, $data);
    }
}