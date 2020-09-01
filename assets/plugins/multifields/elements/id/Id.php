<?php

namespace Multifields\Elements\Id;

class Id extends \Multifields\Base\Elements
{
    protected $template = '
        <div class="col [+class+]" data-type="id" data-name="[+name+]" data-id="[+id+]" [+attr+]>
            <input type="text" id="tv[+id+]" class="form-control" name="tv[+id+]" value="[+value+]" placeholder="[+placeholder+]" readonly [+item.attr+]>
        </div>';
}
