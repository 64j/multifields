<?php

namespace Multifields\Elements\Number;

class Number extends \Multifields\Base\Elements
{
    protected $template = '
        <div class="col px-1 [+class+]" data-type="number" data-name="[+name+]" [+attr+]>
            [+title+]
            <input type="number" id="[+id+]" class="form-control" name="[+id+]" value="[+value+]" placeholder="[+placeholder+]" onchange="documentDirty=true;">
        </div>';
}
