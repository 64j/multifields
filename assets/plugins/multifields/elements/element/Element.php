<?php

namespace Multifields\Elements\Element;

class Element extends \Multifields\Base\Elements
{
    protected $tpl = 'element.tpl';

    protected $template = '<[+tag+] [+attr+]>[+items+]</[+tag+]>';

    protected function setAttr()
    {
        if (isset($this->params['id']) && $this->params['id'] != '') {
            $this->params['attr'] .= ' id="' . $this->params['id'] . '"';
        }

        if (isset($this->params['class']) && $this->params['class'] != '') {
            $this->params['attr'] .= ' class="' . $this->params['class'] . '"';
        }
    }

    public function render()
    {
        if (empty($this->params['tag'])) {
            $this->params['tag'] = 'div';
        }

        return parent::render();
    }
}
