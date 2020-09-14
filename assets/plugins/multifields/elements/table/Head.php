<?php

namespace Multifields\Elements\Table;

class Head extends \Multifields\Base\Elements
{
    protected $template = '<thead data-name="[+name+]">[+items+]</thead>';

    public function render()
    {
        return parent::view();
    }
}
