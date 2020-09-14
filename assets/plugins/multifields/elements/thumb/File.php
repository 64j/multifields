<?php

namespace Multifields\Elements\Thumb;

class File extends \Multifields\Base\Elements
{
    protected $styles = 'view/css/thumb.file.css';
    protected $scripts = 'view/js/thumb.file.js';

    protected $actions = [
        'add',
        'move',
        'del',
        'edit'
    ];

    protected $template = '
        <div class="col mf-thumb mf-thumb-file [+class+]" data-type="thumb:file" data-name="[+name+]" [+attr+]>
            [+title+]
            [+actions+]
            <div class="mf-value mf-hidden">
                <input type="hidden" id="[+id+]_value" name="[+id+]_value" value="[+value+]">
            </div>
        </div>';

    protected function setAttr()
    {
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
