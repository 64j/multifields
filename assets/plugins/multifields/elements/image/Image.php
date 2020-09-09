<?php

namespace Multifields\Elements\Image;

class Image extends \Multifields\Base\Elements
{
    protected $styles = 'image.css';
    protected $scripts = 'image.js';

    protected $template = '
        <div class="col [+class+]" data-type="image" data-name="[+name+]" [+attr+]>
            [+title+]
            <input type="text" id="tv[+id+]" class="form-control" name="tv[+id+]" value="[+value+]" placeholder="[+placeholder+]" onchange="documentDirty=true;[+onchange+]">
            <i class="[+button.class+]" onclick="BrowseServer(\'tv[+id+]\');[+onclick+]"></i>
        </div>';

    public function render()
    {
        self::$params['onchange'] = '';
        self::$params['onclick'] = '';

        if (!empty(self::$params['thumb'])) {
            $thumb = is_array(self::$params['thumb']) ? implode(',', self::$params['thumb']) : self::$params['thumb'];
            self::$params['attr'] .= ' data-thumb="' . $thumb . '"';
            self::$params['onchange'] = 'Multifields.elements.image.setValue(event);';
        }

        if (!empty(self::$params['multi'])) {
            self::$params['attr'] .= ' data-multi="' . self::$params['multi'] . '"';
            self::$params['onclick'] = 'Multifields.elements.image.MultiBrowseServer(event)';
            self::$params['button.class'] = 'far fa-images';
        } else {
            self::$params['button.class'] = 'far fa-image';
        }

        return parent::render();
    }
}
