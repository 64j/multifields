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
    }

    protected function setValue()
    {
        if (isset(self::$params['value']) && self::$params['value'] !== false) {
            if (is_bool(self::$params['value'])) {
                self::$params['value'] = '';
            }

            self::$params['value'] = '
                <div class="mf-value">
                    <input type="text" class="form-control" name="' . self::$params['id'] . '_value" value="' . stripcslashes(self::$params['value']) . '"' . (isset(self::$params['placeholder']) ? ' placeholder="' . self::$params['placeholder'] . '"' : '') . ' data-value>
                </div>';
        }
    }

    protected function setTemplates()
    {
        $out = '';

        if (!empty(Core::getConfig('templates')) && isset(self::$params['templates']) && (self::$params['templates'] === true || is_array(self::$params['templates']))) {
            $i = 0;
            foreach (Core::getConfig('templates') as $k => $v) {
                if ((empty($v['hidden']) && empty(self::$params['templates'])) || (self::$params['templates'] === true || (is_array(self::$params['templates']) && (isset(self::$params['templates'][$k]) || in_array($k, self::$params['templates']))))) {
                    $v['title'] = isset($v['title']) ? $v['title'] : $k;
                    $v['icon'] = isset($v['icon']) ? $v['icon'] : '';
                    $icon = '';
                    $icon_class = '';
                    $icon_image = '';

                    if (!empty($v['icon']) && $v['icon'][0] == '<') {
                        $icon = $v['icon'];
                        $icon_class = 'mf-icon mf-icon-image';
                    } elseif (stripos($v['icon'], '/') !== false) {
                        $icon_image = ' style="background-image: url(\'' . $v['icon'] . '\');"';
                        $icon_class = 'mf-icon mf-icon-image';
                    } elseif ($v['icon']) {
                        $icon_class = 'mf-icon ' . $v['icon'];
                    }

                    $out .= '
                    <div class="mf-option" onclick="Multifields.elements.row.template(\'' . $k . '\');" data-template-name="' . $k . '">
                        <div class="' . $icon_class . '"' . $icon_image . '>' . $icon . '</div>' . $v['title'] . '
                    </div>';
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
        $this->setAttr();
        $this->setValue();
        $this->setTemplates();

        parent::setActions();

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
