<?php

namespace Multifields\Elements\Date;

class Date extends \Multifields\Base\Elements
{
    //protected $disabled = true;

    protected $template = '
        <div class="col [+class+]" data-type="date" data-name="[+name+]" [+attr+]>
            [+title+]
            <input type="text" id="tv[+id+]" class="form-control DatePicker [+item.class+]" name="tv[+id+]" value="[+value+]" placeholder="[+placeholder+]" [+item.attr+]>
            <a onclick="document.forms[\'mutate\'].elements[\'tv[+id+]\'].value=\'\';document.forms[\'mutate\'].elements[\'tv[+id+]\'].blur(); return true;" onmouseover="window.status=\'clear the date\'; return true;" onmouseout="window.status=\'\'; return true;" style="cursor:pointer;"><i class="fa fa-calendar-times-o"></i></a>
        </div>';
}
