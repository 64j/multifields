<?php

namespace Multifields\Elements\Id;

class Id extends \Multifields\Base\Elements
{
    protected $template = '
        <div class="col [+class+]" data-type="id" data-name="[+name+]" [+attr+]>
            <input type="text" id="[+id+]" class="form-control" name="[+id+]" value="[+value+]" placeholder="[+placeholder+]" readonly [+item.attr+]>
        </div>';
}
