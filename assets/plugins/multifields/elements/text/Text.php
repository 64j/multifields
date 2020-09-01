<?php

namespace Multifields\Elements\Text;

class Text extends \Multifields\Base\Elements
{
    protected $template = '
        <div class="col [+class+]" data-type="text" data-name="[+name+]" [+attr+]>
            [+title+]
            <input type="text" id="[+id+]" class="form-control" name="[+id+]" value="[+value+]" placeholder="[+placeholder+]" onchange="documentDirty=true;" [+item.attr+]>
        </div>';
}
