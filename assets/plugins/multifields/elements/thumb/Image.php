<?php

namespace Multifields\Elements\Thumb;

class Image extends \Multifields\Base\Elements
{
    protected $styles = 'view/css/thumb.image.css';
    protected $scripts = 'view/js/thumb.image.js';

    protected $actions = [
        'add',
        'move',
        'del',
        'edit'
    ];

    protected $template = '
        <div class="col mf-thumb mf-thumb-image [+class+]" data-type="thumb:image" data-name="[+name+]" [+attr+]>
            [+title+]
            [+actions+]
            <div class="mf-value mf-hidden">
                <input type="hidden" id="[+id+]_value" name="[+id+]_value" value="[+value+]">
            </div>
        </div>';

    protected function setAttr()
    {
        preg_match('/style="(.*)"/', self::$params['attr'], $matches);
        self::$params['attr'] = preg_replace('/style="(.*)"/', '', self::$params['attr']);
        self::$params['attr'] .= 'style="background-image: url(\'/' . self::$params['value'] . '\');' . (!empty($matches[1]) ? $matches[1] : '') . '"';

        if (!empty(self::$params['multi'])) {
            self::$params['attr'] .= ' data-multi="' . self::$params['multi'] . '"';
        }
    }

    /**
     * @return string
     */
    public function render()
    {
        $this->setAttr();

        if (isset(self::$params['items'])) {
            unset(self::$params['items']);
        }

        parent::setActions();

        return parent::render();
    }
}
