<?php

namespace Multifields\Elements\Number;

class Number extends \Multifields\Base\Elements
{
    protected $template = '
        <div class="col [+class+]" data-type="number" data-name="[+name+]" [+attr+]>
            [+title+]
            <input type="number" id="[+id+]" class="form-control [+item.class+]" name="[+id+]" value="[+value+]" placeholder="[+placeholder+]" onchange="documentDirty=true;" [+item.attr+]>
        </div>';
}
