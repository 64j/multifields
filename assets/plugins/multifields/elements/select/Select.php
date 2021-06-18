<?php

namespace Multifields\Elements\Select;

class Select extends \Multifields\Base\Elements
{
    protected $template = '
        <div id="[+id+]" class="col [+class+]" data-type="[+type+]" data-name="[+name+]" [+attr+]>
            [+title+]
            <select id="tv[+id+]" class="form-control [+item.class+]" name="tv[+id+]" placeholder="[+placeholder+]" onchange="documentDirty=true;" [+item.attr+]>
            [+elements+]
            </select>
        </div>';

    protected function setOptions()
    {
        if (!empty($this->params['elements'])) {
            if (!is_array($this->params['elements'])) {
                $this->params['elements'] = array_map('trim', explode('||', $this->params['elements']));
            }

            foreach ($this->params['elements'] as &$element) {
                list($value, $key) = explode('==', $element . '==');
                if(strlen($key) == 0) {
                    $key = $value;
                }
                $selected = $key == $this->params['value'] ? ' selected="selected"' : '';
                $element = '<option value="' . htmlspecialchars($key) . '"' . $selected . '>' . htmlspecialchars($value) . '</option>';
            }

            $this->params['elements'] = implode($this->params['elements']);
        }
    }

    public function render()
    {
        $this->setOptions();

        return parent::render();
    }
}
