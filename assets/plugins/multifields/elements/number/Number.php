<?php

namespace Multifields\Elements\Number;

class Number extends \Multifields\Base\Elements
{
    protected $template = '
        <div class="col [+class+]" data-type="number" data-name="[+name+]" [+attr+]>
            [+label+]
            <input type="number" id="[+id+]" class="form-control" name="[+id+]" value="[+value+]" placeholder="[+placeholder+]" onchange="documentDirty=true;" [+item.attr+]>
        </div>';

    public function render($params = [], $data = [])
    {
        if ($params['label'] != '') {
            $params['label'] = '<label for="tv' . $params['id'] . '" ' . $params['label.attr'] . '>' . $params['label'] . '</label>';
        }

        return parent::render($params, $data);
    }
}
