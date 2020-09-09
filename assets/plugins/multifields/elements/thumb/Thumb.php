<?php

namespace Multifields\Elements\Thumb;

class Thumb extends \Multifields\Base\Elements
{
    protected $styles = 'view/css/thumb.css';
    protected $scripts = 'view/js/thumb.js';

    protected $actions = [
        'add',
        'move',
        'del',
        'edit'
    ];

    protected $template = '
        <div class="col mf-thumb [+class+]" data-type="thumb" data-name="[+name+]" [+attr+]>
            [+title+]
            [+actions+]
            <div class="mf-value mf-hidden">
                <input type="hidden" id="[+id+]_value" name="[+id+]_value" value="[+value+]">
            </div>
            <div class="row mx-0 mb-2 col-12 p-0 mf-items [+items.class+]">
                [+items+]
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

        if (!empty(self::$params['image'])) {
            self::$params['attr'] .= ' data-image="' . self::$params['image'] . '"';
        }

        if (!empty(self::$data) && !empty(self::$data['items'])) {
            self::$params['class'] .= ' mf-group';
        }
    }

    /**
     * @return string
     */
    public function render()
    {
        $this->setAttr();

        parent::setActions();

        return parent::render();
    }
}
