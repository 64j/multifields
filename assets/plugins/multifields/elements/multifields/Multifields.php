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
                    'icon' => '<svg width="18" height="13" viewBox="0 0 18 13" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M16.9811 12.24H1.01887C0.456113 12.24 0 11.7419 0 11.1273V1.11273C0 0.498131 0.456113 0 1.01887 0H16.9811C17.5439 0 18 0.498131 18 1.11273V11.1273C18 11.7419 17.5439 12.24 16.9811 12.24ZM16.9811 1.11273H1.01887V11.1273H16.9811V1.11273Z" fill="#C4C4C4"/></svg>'
                ],
                [
                    'name' => 'xl',
                    'value' => 1200,
                    'label' => 'Desktop (xl) - 1200 px',
                    'icon' => '<svg width="18" height="15" viewBox="0 0 18 15" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M16.5405 11.4207C16.5405 11.4207 13.8323 11.4207 10.711 11.4207C10.2245 11.4207 12.1738 14.4 11.6757 14.4C11.6197 14.4 6.86676 14.4 6.81081 14.4C6.38805 14.4 8.2153 11.4207 7.79741 11.4207C4.46789 11.4207 1.45946 11.4207 1.45946 11.4207C0.653351 11.4207 0 10.7538 0 9.93103V1.48966C0 0.666869 0.653351 0 1.45946 0H16.5405C17.3466 0 18 0.666869 18 1.48966V9.93103C18 10.7538 17.3466 11.4207 16.5405 11.4207ZM16.5405 1.48966H1.45946V9.93103H16.5405V1.48966Z" fill="#C4C4C4"/></svg>',
                ],
                [
                    'name' => 'lg',
                    'value' => 992,
                    'label' => 'Laptop (lg) - 992 px',
                    'icon' => '<svg width="18" height="11" viewBox="0 0 18 11" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M17.5385 10.8H0.461538C0.206769 10.8 0 10.5896 0 10.3304V9.86087C0 9.60167 0.206769 9.3913 0.461538 9.3913H2.20569C1.98462 9.1415 1.84615 8.81468 1.84615 8.45217V1.4087C1.84615 0.630626 2.466 0 3.23077 0H14.7692C15.534 0 16.1538 0.630626 16.1538 1.4087V8.45217C16.1538 8.81468 16.0154 9.1415 15.7943 9.3913H17.5385C17.7932 9.3913 18 9.60167 18 9.86087V10.3304C18 10.5896 17.7932 10.8 17.5385 10.8ZM14.7692 1.4087H3.23077V8.45217H14.7692V1.4087Z" fill="#C4C4C4"/></svg>',
                ],
                [
                    'name' => 'md',
                    'value' => 768,
                    'label' => 'Tablet (md) - 768 px',
                    'icon' => '<svg width="14" height="18" viewBox="0 0 14 18" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M11.8957 18H1.78435C0.798793 18 0 17.1942 0 16.2V1.8C0 0.8058 0.798793 0 1.78435 0H11.8957C12.8812 0 13.68 0.8058 13.68 1.8V16.2C13.68 17.1942 12.8812 18 11.8957 18ZM6.54261 16.8H7.13739V16.2H6.54261V16.8ZM12.4904 1.2H1.18957V15H12.4904V1.2Z" fill="#C4C4C4"/></svg>',
                ],
                [
                    'name' => 'sm',
                    'value' => 576,
                    'label' => 'Mobile (sm) - 576 px',
                    'icon' => '<svg width="18" height="10" viewBox="0 0 18 10" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M18 2.48V7.52C18 8.71304 17.033 9.68 15.84 9.68L2.16 9.68C0.96696 9.68 2.72455e-07 8.71304 2.20305e-07 7.52L0 2.48C-5.21494e-08 1.28696 0.96696 0.32 2.16 0.32L15.84 0.32C17.033 0.32 18 1.28696 18 2.48ZM16.56 5.36V4.64H15.84V5.36H16.56ZM1.44 1.76L1.44 8.24L14.4 8.24V1.76L1.44 1.76Z" fill="#C4C4C4"/></svg>',
                ],
                [
                    'name' => 'xs',
                    'value' => 320,
                    'label' => 'Mobile (xs) - 320 px',
                    'icon' => '<svg width="10" height="18" viewBox="0 0 10 18" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7.2 18H2.16C0.96696 18 0 17.033 0 15.84V2.16C0 0.96696 0.96696 0 2.16 0H7.2C8.39304 0 9.36 0.96696 9.36 2.16V15.84C9.36 17.033 8.39304 18 7.2 18ZM4.32 16.56H5.04V15.84H4.32V16.56ZM7.92 1.44H1.44V14.4H7.92V1.44Z" fill="#C4C4C4"/></svg>',
                ],
            ],
            'export' => [
                'name' => 'export',
                'value' => '',
                'label' => 'Export',
                'icon' => '<svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10.1076 6.70909H13.5955L9.00001 0L4.40449 6.70909H7.89062V13.455H10.1076V6.70909Z" fill="#C4C4C4"/><path d="M18 18H0V10.925H1.23796V16.7589H16.762V10.925H18V18Z" fill="#C4C4C4"/></svg>'
            ],
            'import' => [
                'name' => 'import',
                'value' => '',
                'label' => 'Import',
                'icon' => '<svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7.89245 6.74586H4.40449L9.00001 13.455L13.5955 6.74586H10.1094L10.1094 0L7.89245 1.94009e-07L7.89245 6.74586Z" fill="#C4C4C4"/><path d="M18 18H0V10.925H1.23796V16.7589H16.762V10.925H18V18Z" fill="#C4C4C4"/></svg>'
            ],
            'fullscreen' => [
                'name' => 'fullscreen',
                'value' => '',
                'label' => 'Fullscreen',
                'icon' => '<svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M2.40061 1.40625H5.80078V0H0V5.80078H1.40625V2.40061L6.22266 7.21702L7.21702 6.22266L2.40061 1.40625ZM7.21702 11.7773L6.22266 10.783L1.40625 15.5994V12.1992H0V18H5.80078V16.5938H2.40061L7.21702 11.7773ZM12.1992 0V1.40625H15.5994L10.783 6.22266L11.7773 7.21702L16.5938 2.40061V5.80078H18V0H12.1992ZM16.5938 12.1992V15.5994L11.7773 10.783L10.783 11.7773L15.5994 16.5938H12.1992V18H18V12.1992H16.5938Z" fill="#C4C4C4"/></svg>'
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
                        <span>' . $this->settings['toolbar']['export']['icon'] . '</span>
                    </a>';
            }

            if (!empty($this->toolbar['import'])) {
                $this->params['toolbar'] .= '
                    <a href="javascript:;" class="mf-btn mf-btn-toolbar-' . $this->settings['toolbar']['import']['name'] . '" title="' . $this->settings['toolbar']['import']['label'] . '" onclick="Multifields.elements.multifields.actionToolbarImport.call(this);">
                        <span>' . $this->settings['toolbar']['import']['icon'] . '</span>
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
                        <span>' . $this->settings['toolbar']['fullscreen']['icon'] . '</span>
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

                    $out .= '<div class="mf-option" onclick="Multifields.elements.multifields.setTemplate(\'' . $k . '\');" data-template-name="' . $k . '">' . $this->setIcon($v['icon']) . $v['label'] . '</div>';
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
