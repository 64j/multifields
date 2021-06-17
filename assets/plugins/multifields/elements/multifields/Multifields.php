<?php

namespace Multifields\Elements\Multifields;

class Multifields extends \Multifields\Base\Elements
{
    protected $styles = 'view/css/multifields.css';
    protected $scripts = 'view/js/multifields.js';
    protected $tpl = 'view/multifields.tpl';

    protected $actions = [
        'add'
    ];

    protected $toolbar;
    protected $settings = [
        'view' => [
            'icons'
        ],
        'toolbar' => [
            'breakpoints' => [
                [
                    'name' => '',
                    'value' => 0,
                    'label' => 'Default',
                    'icon' => 'fa fa-tv'
                ],
                [
                    'name' => 'xl',
                    'value' => 1200,
                    'label' => 'Desktop (xl) - 1200 px',
                    'icon' => 'fa fa-desktop',
                ],
                [
                    'name' => 'lg',
                    'value' => 992,
                    'label' => 'Laptop (lg) - 992 px',
                    'icon' => 'fa fa-laptop',
                ],
                [
                    'name' => 'md',
                    'value' => 768,
                    'label' => 'Tablet (md) - 768 px',
                    'icon' => 'fa fa-tablet-alt',
                ],
                [
                    'name' => 'sm',
                    'value' => 576,
                    'label' => 'Mobile (sm) - 576 px',
                    'icon' => 'fa fa-mobile-alt fa-rotate-270',
                ],
                [
                    'name' => 'xs',
                    'value' => 320,
                    'label' => 'Mobile (xs) - 320 px',
                    'icon' => 'fa fa-mobile-alt',
                ],
            ],
            'export' => [
                'name' => 'export',
                'value' => '',
                'label' => 'Export',
                'icon' => 'fa fa-upload'
            ],
            'import' => [
                'name' => 'import',
                'value' => '',
                'label' => 'Import',
                'icon' => 'fa fa-download'
            ],
            'fullscreen' => [
                'name' => 'fullscreen',
                'value' => '',
                'label' => 'Fullscreen',
                'icon' => 'fa fa-expand-arrows-alt'
            ],
            'save' => true,
        ],
    ];

    protected function setToolbar()
    {
        $this->toolbar = !empty(mfc()->getConfig('settings')['toolbar']) ? mfc()->getConfig('settings')['toolbar'] : [];

        $this->params['toolbar'] = '';
        $this->params['grid'] = '';

        if (!empty($this->toolbar)) {
            if (!empty($this->toolbar['breakpoints'])) {
                $breakpoints = $this->toolbar['breakpoints'];

                if (is_bool($breakpoints)) {
                    $breakpoints = $this->settings['toolbar']['breakpoints'];
                } elseif (!is_array($breakpoints)) {
                    $breakpoints = false;
                }

                if (!empty($breakpoints)) {
                    $cookie_breakpoint = !empty($_COOKIE['mf-breakpoint-' . mfc()->getParams('tv')['id']]) ? $_COOKIE['mf-breakpoint-' . mfc()->getParams('tv')['id']] : '';
                    foreach ($breakpoints as &$v) {
                        if (!is_array($v)) {
                            $v = $this->settings['toolbar']['breakpoints'][array_search($v, array_column($this->settings['toolbar']['breakpoints'], 'name'))];
                        }

                        $v['icon'] = isset($v['icon']) ? $v['icon'] : '';

                        $active = '';

                        if (($v['value'] == 0 && !$cookie_breakpoint) || ($cookie_breakpoint && $cookie_breakpoint == $v['name'])) {
                            $active = ' active';
                            if ($v['value']) {
                                $this->params['items.attr'] .= ' style="max-width: ' . $v['value'] . 'px;"';
                                $this->params['attr'] .= ' data-mf-breakpoint="' . $v['name'] . '"';
                            }
                        }

                        $data_breakpoint = $v['name'] ? '[data-mf-breakpoint="' . $v['name'] . '"]' : ':not([data-mf-breakpoint])';
                        $data_col = $v['name'] ? '-' . $v['name'] : '';

                        $this->params['css'] .= '#' . $this->params['id'] . '.multifields' . $data_breakpoint . ' [data-mf-col' . $data_col . '="auto"]:not([data-mf-disable-col]) { flex: 0 0 auto; max-width: none; width: auto; }';
                        $this->params['css'] .= '#' . $this->params['id'] . '.multifields' . $data_breakpoint . ' [data-mf-col' . $data_col . '=""]:not([data-mf-disable-col]) { flex-basis: 0; flex-grow: 1; }';
                        for ($i = 1; $i <= 12; $i++) {
                            $n = (float)number_format(100 / 12 * $i, 6);
                            $this->params['css'] .= '#' . $this->params['id'] . '.multifields' . $data_breakpoint . ' [data-mf-col' . $data_col . '="' . $i . '"]:not([data-mf-disable-col]) { flex: 0 0 ' . $n . '%; max-width: ' . $n . '%; }';
                            if ($i < 12) {
                                $this->params['css'] .= '#' . $this->params['id'] . '.multifields' . $data_breakpoint . ' [data-mf-offset' . $data_col . '="' . $i . '"]:not([data-mf-disable-offset]) { margin-left: ' . $n . '%; }';
                            }
                        }

                        if ($v['value']) {
                            $this->params['grid'] .= '<div style="max-width: ' . $v['value'] . 'px;"></div>';
                        }

                        $v = '
                        <a href="javascript:;" class="mf-breakpoint mf-btn' . $active . '" title="' . $v['label'] . '" onclick="Multifields.elements.multifields.actionToolbarBreakpoint.call(this, \'' . $v['value'] . '\');" data-breakpoint-key="' . $v['value'] . '" data-breakpoint-name="' . $v['name'] . '">
                            ' . $this->setIcon($v['icon']) . '
                        </a>';
                    }

                    $this->params['toolbar'] .= '<div class="mf-breakpoints">' . implode($breakpoints) . '</div>';
                }
            }

            if (!empty($this->toolbar['export'])) {
                $this->params['toolbar'] .= '
                    <a href="javascript:;" class="mf-btn mf-btn-toolbar-' . $this->settings['toolbar']['export']['name'] . '" title="' . $this->settings['toolbar']['export']['label'] . '" onclick="Multifields.elements.multifields.actionToolbarExport.call(this);">
                        <span>' . $this->setIcon($this->settings['toolbar']['export']['icon']). '</span>
                    </a>';
            }

            if (!empty($this->toolbar['import'])) {
                $this->params['toolbar'] .= '
                    <a href="javascript:;" class="mf-btn mf-btn-toolbar-' . $this->settings['toolbar']['import']['name'] . '" title="' . $this->settings['toolbar']['import']['label'] . '" onclick="Multifields.elements.multifields.actionToolbarImport.call(this);">
                        <span>' . $this->setIcon($this->settings['toolbar']['import']['icon']) . '</span>
                    </a>';
            }

            if ((!isset($this->toolbar['save']) && $this->settings['toolbar']['save']) || !empty($this->toolbar['save'])) {
                $this->params['toolbar'] .= '
                <a href="javascript:;" class="mf-btn mf-btn-toolbar-save" onclick="actions.save();">
                    <i class="mf-icon fa fa-floppy-o"></i>
                </a>';
            }

            if (!empty($this->toolbar['fullscreen'])) {
                $active = '';
                if (!empty($_COOKIE['mf-fullscreen-' . mfc()->getParams('tv')['id']])) {
                    $this->params['attr'] .= ' data-mf-fullscreen';
                    $active = ' active';
                }
                $this->params['toolbar'] .= '
                    <a href="javascript:;" class="mf-btn mf-btn-toolbar-' . $this->settings['toolbar']['fullscreen']['name'] . $active . '" title="' . $this->settings['toolbar']['fullscreen']['label'] . '" onclick="Multifields.elements.multifields.actionToolbarFullscreen.call(this);">
                        <span>' . $this->setIcon($this->settings['toolbar']['fullscreen']['icon']) . '</span>
                    </a>';
            }

            if ($this->params['toolbar']) {
                $this->params['toolbar'] = '<div class="mf-toolbar">' . $this->params['toolbar'] . '</div>';
            }

            if ($this->params['grid']) {
                $this->params['grid'] = '<div class="mf-grid">' . $this->params['grid'] . '</div>';
            }
        }
    }

    protected function setTemplates()
    {
        $out = '';

        if (!empty(mfc()->getConfig('templates'))) {
            $i = 0;
            foreach (mfc()->getConfig('templates') as $k => $v) {
                if (empty($v['hidden'])) {
                    $v['label'] = isset($v['label']) ? $v['label'] : $k;
                    $v['icon'] = isset($v['icon']) ? $v['icon'] : '';

                    $out .= '<div class="mf-option" onclick="Multifields.setTemplate(\'' . $k . '\');" data-template-name="' . $k . '">' . $this->setIcon($v['icon']) . $v['label'] . '</div>';
                    $i++;
                }
            }

            if (!empty($out)) {
                $class = '';
                $this->params['class'] .= ' mf-row-group';
                if (!empty(mfc()->getConfig('settings')['view']) && in_array(mfc()->getConfig('settings')['view'], $this->settings['view'])) {
                    $this->params['class'] .= ' mf-view-' . mfc()->getConfig('settings')['view'];
                } else {
                    $class = ' contextMenu';
                }
                $out = '<div id="mf-templates-' . $this->params['id'] . '" class="mf-templates' . ($i > 1 ? '' : ' mf-hidden') . $class . '">' . $out . '</div>';
            }
        }

        $this->params['templates'] = $out;
    }

    /**
     * @return string
     */
    public function render()
    {
        $this->params['css'] = '';

        $this->setToolbar();
        $this->setActions();
        $this->setTemplates();

        if (!empty($this->params['css'])) {
            $this->params['css'] = '<style>' . $this->params['css'] . '</style>';
        }


        return parent::render();
    }
}
