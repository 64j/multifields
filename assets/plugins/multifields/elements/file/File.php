<?php

namespace Multifields\Elements\File;

class File extends \Multifields\Base\Elements
{
    protected $styles = 'file.css';
    protected $scripts = 'file.js';

    protected $template = '
        <div class="col [+class+]" data-type="file" data-name="[+name+]" [+attr+]>
            [+title+]
            <input type="text" id="[+id+]" class="form-control" name="[+id+]" value="[+value+]" placeholder="[+placeholder+]" onchange="documentDirty=true;">
            <i class="[+button.class+]" onclick="BrowseFileServer(\'[+id+]\');"></i>
        </div>';

    public function render($params = [], $data = [])
    {
        $params['button.class'] = 'far fa-file';

        return parent::render($params, $data);
    }
}
