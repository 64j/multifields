<?php

namespace Multifields\Elements\Text;

class Text extends \Multifields\Base\Elements
{
    protected $template = '
        <div class="col [+class+]" data-type="text" data-name="[+name+]" [+attr+]>
            [+title+]
            <input type="text" id="[+id+]" class="form-control" name="[+id+]" value="[+value+]" placeholder="[+placeholder+]" onchange="documentDirty=true;" [+item.attr+]>
        </div>';

    public function render($params = [], $data = [])
    {
        if ($params['title'] != '') {
            $params['title'] = '<label for="tv' . $params['id'] . '">' . $params['title'] . '</label>';
        }

        return parent::render($params, $data);
    }
}
