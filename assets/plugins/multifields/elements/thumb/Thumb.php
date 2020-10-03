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
        preg_match('/style="(.*)"/', $this->params['attr'], $matches);
        $this->params['attr'] = preg_replace('/style="(.*)"/', '', $this->params['attr']);
        $this->params['attr'] .= 'style="background-image: url(\'../' . $this->params['value'] . '\');' . (!empty($matches[1]) ? $matches[1] : '') . '"';

        if (!empty($this->params['multi'])) {
            $this->params['attr'] .= ' data-multi="' . $this->params['multi'] . '"';
        }

        if (!empty($this->params['image'])) {
            $this->params['attr'] .= ' data-image="' . $this->params['image'] . '"';
        }

        if (!empty($this->params['items'])) {
            $this->params['class'] .= ' mf-group';
        }

        parent::setAttr();
    }

    /**
     * @return string
     */
    public function render()
    {
        $this->setActions();

        return parent::render();
    }
}
