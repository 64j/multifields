<?php

namespace Multifields\Elements\Image;

class Image extends \Multifields\Base\Elements
{
    protected $styles = 'image.css';
    protected $scripts = 'image.js';

    protected $template = '
        <div class="col p-1 [+class+]" data-type="image" data-name="[+name+]" [+attr+]>
            [+title+]
            <input type="text" id="[+id+]" class="form-control" name="[+id+]" value="[+value+]" placeholder="[+placeholder+]" onchange="documentDirty=true;[+onchange+]">
            <i class="[+button.class+]" onclick="BrowseServer(\'[+id+]\');[+onclick+]"></i>
        </div>';

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
