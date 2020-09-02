<?php

namespace Multifields\Elements\Text;

class Text extends \Multifields\Base\Elements
{
    protected $template = '
        <div class="col [+class+]" data-type="text" data-name="[+name+]" [+attr+]>
            [+label+]
            <input type="text" id="tv[+id+]" class="form-control" name="tv[+id+]" value="[+value+]" placeholder="[+placeholder+]" onchange="documentDirty=true;" [+item.attr+]>
        </div>';

    public function render($params = [], $data = [])
    {
        if ($params['label'] != '') {
            $params['label'] = '<label for="tv' . $params['id'] . '" ' . $params['label.attr'] . '>' . $params['label'] . '</label>';
        }

        return parent::render($params, $data);
    }
}
