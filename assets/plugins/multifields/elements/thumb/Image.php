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
        preg_match('/style="(.*)"/', $this->params['attr'], $matches);
        $this->params['attr'] = preg_replace('/style="(.*)"/', '', $this->params['attr']);
        $this->params['attr'] .= 'style="background-image: url(\'../' . $this->params['value'] . '\');' . (!empty($matches[1]) ? $matches[1] : '') . '"';

        if (!empty($this->params['multi'])) {
            $this->params['attr'] .= ' data-multi="' . $this->params['multi'] . '"';
        }

        parent::setAttr();
    }

    /**
     * @return string
     */
    public function render()
    {
        if (isset($this->params['items'])) {
            unset($this->params['items']);
        }

        $this->setActions();

        return parent::render();
    }
}
