<?php

namespace Multifields\Elements\Richtext;

class Richtext extends \Multifields\Base\Elements
{
    protected $styles = 'richtext.css';
    protected $scripts = 'richtext.js';
    protected $tpl = 'richtext.tpl';

    protected $disabled = false;

    public function render()
    {
        if (!empty($this->params['inline'])) {
            $this->params['class'] .= ' mf-richtext-inline';
        }

        if (!empty($this->params['mf.options'])) {
            $this->params['mf.options'] = is_array($this->params['mf.options']) ? htmlspecialchars(json_encode($this->params['mf.options'])) : htmlspecialchars($this->params['mf.options']);
        }

        return parent::render();
    }

    protected function preFillData(&$item = [], $config = [], $find = [])
    {
        if (!empty($find['mf.options'])) {
            $item['mf.options'] = $find['mf.options'];
            if (is_array($item['mf.options'])) {
                if (!empty($item['mf.options']['init'])) {
                    $item['inline'] = true;
                } elseif (!empty($item['mf.options']['inline'])) {
                    unset($item['mf.options']['inline']);
                    $item['mf.options']['init'] = true;
                    $item['inline'] = true;
                } elseif (isset($item['inline'])) {
                    unset($item['inline']);
                }
            }
        } elseif (isset($item['mf.options'])) {
            unset($item['mf.options']);
        }
    }

    /**
     * @param array $params
     * @return string
     */
    public function actionDisplay($params = [])
    {
        $evo = evolutionCMS();
        $this->template = file_get_contents(__DIR__ . '/editor.tpl');

        $which_editor = $evo->getConfig('which_editor');

        define($which_editor . '_INIT_INTROTEXT', 1);

        $options = [
            'theme' => 'custom',
            'width' => '100%',
            'height' => '100%',
            'block_formats' => 'Paragraph=p;Header 1=h1;Header 2=h2;Header 3=h3;Header 4=h4;Header 5=h5;Header 6=h6;Div=div'
        ];

        if (!empty($params['mf-options'])) {
            $params['mf-options'] = json_decode(base64_decode($params['mf-options']), true);
            if (is_array($params['mf-options'])) {
                $options = array_merge($options, $params['mf-options']);
            }
        }

        $which_editor_config = [
            'editor' => $which_editor,
            'elements' => ['ta'],
            'options' => [
                'ta' => $options
            ]
        ];

        $body_class = '';
        $theme_modes = array('', 'lightness', 'light', 'dark', 'darkness');
        $theme_mode = isset($_COOKIE['MODX_themeMode']) ? $_COOKIE['MODX_themeMode'] : '';
        $manager_theme_mode = $evo->getConfig('manager_theme_mode');
        if (!empty($theme_modes[$theme_mode])) {
            $body_class .= ' ' . $theme_modes[$theme_mode];
        } elseif (!empty($theme_modes[$manager_theme_mode])) {
            $body_class .= ' ' . $theme_modes[$manager_theme_mode];
        }

        // invoke OnRichTextEditorInit event
        $evtOut = $evo->invokeEvent('OnRichTextEditorInit', $which_editor_config);
        if (is_array($evtOut)) {
            $evtOut = implode('', $evtOut);
        } else {
            $evtOut = '';
        }

        return $this->view([
            'lang' => $evo->getConfig('lang_code'),
            'MODX_SITE_URL' => MODX_SITE_URL,
            'MGR_DIR' => MGR_DIR,
            'manager_theme' => $evo->getConfig('manager_theme'),
            'body_class' => $body_class,
            'evtOut' => $evtOut
        ]);
    }
}
