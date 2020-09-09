<?php

namespace Multifields\Elements\Row;

use Multifields\Base\Core;

class Row extends \Multifields\Base\Elements
{
    protected $styles = 'view/css/row.css';
    protected $scripts = 'view/js/row.js';

    protected $actions = [
        'add',
        'move',
        'del',
        'resize',
    ];

    protected $template = '
        <div id="[+id+]" class="col mf-row [+class+]" data-type="row" data-name="[+name+]" [+attr+]>
            [+title+]
            [+templates+]
            [+value+]
            [+actions+]
            <div class="mf-items [+items.class+]">
                [+items+]
            </div>
        </div>';

    protected function setAttr()
    {
        if (!empty(self::$params['autoincrement'])) {
            self::$params['attr'] .= ' data-autoincrement="' . self::$params['autoincrement'] . '"';
        }

        if (!empty(self::$params['mf.col'])) {
            self::$params['class'] = trim(preg_replace('/col-[\d|auto]+/', '', self::$params['class']) . ' col-' . self::$params['mf.col']);
        }

        if (!empty(self::$params['mf.offset'])) {
            self::$params['class'] = trim(preg_replace('/offset-[\d|auto]+/', '', self::$params['class']) . ' offset-' . self::$params['mf.offset']);
        }

        parent::setAttr();
    }

    protected function setTemplates()
    {
        $out = '';

        if (!empty(Core::getConfig('templates')) && isset(self::$params['templates']) && (self::$params['templates'] === true || is_array(self::$params['templates']))) {
            $i = 0;
            foreach (Core::getConfig('templates') as $k => $v) {
                if ((empty($v['hidden']) && empty(self::$params['templates'])) || (self::$params['templates'] === true || (is_array(self::$params['templates']) && (isset(self::$params['templates'][$k]) || in_array($k, self::$params['templates']))))) {
                    $v['label'] = isset($v['label']) ? $v['label'] : $k;
                    $v['icon'] = isset($v['icon']) ? $v['icon'] : '';

                    $out .= '<div class="mf-option" onclick="Multifields.elements.row.setTemplate(\'' . $k . '\');" data-template-name="' . $k . '">' . self::setIcon($v['icon']) . $v['label'] . '</div>';
                    $i++;
                }
            }

            if (!empty($out)) {
                self::$params['class'] .= ' mf-row-group';

                $out = '<div id="mf-templates-' . self::$params['id'] . '" class="mf-templates' . ($i > 1 ? '' : ' mf-hidden') . ' contextMenu">' . $out . '</div>';
            }
        }

        self::$params['templates'] = $out;
    }

    /**
     * @return string
     */
    public function render()
    {
        $this->setTemplates();

        parent::setActions();
        parent::setValue();

        return parent::render();
    }

    /**
     * @param $action
     * @param $type
     * @return string
     */
    protected function renderAction($action, $type)
    {
        if ($action == 'resize') {
            return '
                <i class="mf-actions-' . $action . '-offset fa"></i>
                <i class="mf-actions-' . $action . '-col fa"></i>';
        }

        return parent::renderAction($action, $type);
    }
}
