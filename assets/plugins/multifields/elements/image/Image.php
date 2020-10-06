<?php

namespace Multifields\Elements\Image;

class Image extends \Multifields\Base\Elements
{
    protected $styles = 'image.css';
    protected $scripts = 'image.js';

    protected $template = '
        <div class="col [+class+]" data-type="image" data-name="[+name+]" [+attr+]>
            [+title+]
            <input type="text" id="tv[+id+]" class="form-control [+item.class+]" name="tv[+id+]" value="[+value+]" placeholder="[+placeholder+]" onchange="documentDirty=true;[+onchange+]" [+item.class+]>
            <i class="[+button.class+]" onclick="BrowseServer(\'tv[+id+]\');[+onclick+]"></i>
        </div>';

    public function render()
    {
        $this->params['onchange'] = '';
        $this->params['onclick'] = '';

        if (!empty($this->params['thumb'])) {
            $thumb = is_array($this->params['thumb']) ? implode(',', $this->params['thumb']) : $this->params['thumb'];
            $this->params['attr'] .= ' data-thumb="' . $thumb . '"';
            $this->params['onchange'] = 'Multifields.elements.image.setValue(event);';
        }

        if (!empty($this->params['multi'])) {
            $this->params['attr'] .= ' data-multi="' . $this->params['multi'] . '"';
            $this->params['onclick'] = 'Multifields.elements.image.MultiBrowseServer(event)';
            $this->params['button.class'] = 'far fa-images';
        } else {
            $this->params['button.class'] = 'far fa-image';
        }

        return parent::render();
    }
}
