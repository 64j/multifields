<?php

namespace Multifields\Elements\Text;

class Text extends \Multifields\Base\Elements
{
    protected $template = '
        <div class="col [+class+]" data-type="text" data-name="[+name+]" [+attr+]>
            [+title+]
            <input type="text" id="tv[+id+]" class="form-control [+item.class+]" name="tv[+id+]" value="[+value+]" placeholder="[+placeholder+]" onchange="documentDirty=true;" [+item.attr+]>
        </div>';
}
