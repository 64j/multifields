<?php

namespace Multifields\Elements\Table;

class Body extends \Multifields\Base\Elements
{
    protected $template = '<tbody data-name="[+name+]">[+items+]</tbody>';

    public function render()
    {
        return parent::view();
    }
}
