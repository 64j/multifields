<?php
/**
 * Multifields
 *
 * Custom fields for documents
 *
 * @author 64j
 */

namespace Multifields\Base;

class Core
{
    const VERSION = '2.0.1';

    private static $instance;
    private static $params = [];
    private static $config = [];
    private static $data = [];

    private function __construct($params = [])
    {
        $pluginParams = [];
        if (!empty(evolutionCMS()->pluginCache['multifieldsProps'])) {
            $pluginParams = json_decode(evolutionCMS()->pluginCache['multifieldsProps'], true);
        }

        self::setParams(array_merge([
            'basePath' => str_replace(DIRECTORY_SEPARATOR, '/', dirname(__DIR__)) . '/',
            'storage' => empty($pluginParams['multifields_storage']) ? 'files' : $pluginParams['multifields_storage'],
            'debug' => empty($pluginParams['multifields_debug']) ? false : ($pluginParams['multifields_debug'] == 'no' ? false : true),
        ], $params));

        require_once MODX_MANAGER_PATH . 'includes/tmplvars.inc.php';
        require_once MODX_MANAGER_PATH . 'includes/tmplvars.format.inc.php';
        require_once MODX_MANAGER_PATH . 'includes/tmplvars.commands.inc.php';
    }

    /**
     * @param array $params
     * @return static
     */
    public static function getInstance($params = [])
    {
        if (self::$instance === null) {
            self::$instance = new static($params);
        } else {
            self::setParams($params);
        }

        return self::$instance;
    }

    /**
     * @param int $id
     * @param array $row
     * @return string
     */
    public function render($id = 0, $row = [])
    {
        global $ResourceManagerLoaded;

        $tmp_ResourceManagerLoaded = $ResourceManagerLoaded;

        self::setParams([
            'id' => $id,
            'tv' => $row
        ]);

        self::setConfig(null);
        self::setData(null);

        if (empty(self::getConfig('templates'))) {
            if (self::getConfig()) {
                $out = 'Must be an array in file for id=' . self::getParams('tv')['id'];
            } else {
                $out = 'Not found config file for TV id=' . self::getParams('tv')['id'];
            }
        } else {
            $start = microtime(true);

            $values = '';

            if (!empty(self::getData())) {
                $values = htmlspecialchars(json_encode(self::getData(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8');
                self::setData(Elements::fillData(self::getData(), self::getConfig('templates')));
            }

            $out = Elements::renderData([
                [
                    'type' => 'multifields',
                    'name' => 'multifields',
                    'form.id' => self::getParams('storage') == 'files' ? '-mf-data[' . self::getParams('id') . '__' . self::getParams('tv')['id'] . ']' : self::getParams('tv')['id'],
                    'tv.id' => self::getParams('tv')['id'],
                    'tv.name' => self::getParams('tv')['name'],
                    'items' => self::getData(),
                    'values' => $values
                ]
            ]);

            if (self::getParams('debug')) {
                echo microtime(true) - $start . ' s.';
            }

            if (!empty($ResourceManagerLoaded)) {
                $ResourceManagerLoaded = $tmp_ResourceManagerLoaded;
            }
        }

        return $out;
    }

    /**
     *
     */
    public function saveData()
    {
        if (isset($_POST['tv-mf-data']) && self::getParams('storage') == 'files') {
            foreach ($_POST['tv-mf-data'] as $k => $data) {
                list($id, $tvId) = explode('__', $k);
                self::setParams([
                    'id' => $id,
                    'tv' => [
                        'id' => $tvId
                    ]
                ]);
                $data = evolutionCMS()->removeSanitizeSeed($data);
                $file = self::getParams('basePath') . 'data/' . $id . '__' . $tvId . '.json';
                if ($data == '') {
                    if (is_file($file)) {
                        unlink($file);
                    }
                } else {
                    file_put_contents($file, $data);
                }
            }
        }
    }

    /**
     *
     */
    public function deleteData()
    {

    }

    /**
     * @param array $data
     * @return array
     */
    public static function setParams($data = [])
    {
        if (empty(self::$params)) {
            self::$params = [];
        }

        return self::$params = array_merge(self::$params, $data);
    }

    /**
     * @param null $key
     * @return array
     */
    public static function getParams($key = null)
    {
        return isset(self::$params[$key]) ? self::$params[$key] : self::$params;
    }

    /**
     * @param array $data
     * @return array
     */
    public static function setConfig($data = [])
    {
        return self::$config = $data;
    }

    /**
     * @param null $key
     * @return array|mixed|null
     */
    public static function getConfig($key = null)
    {
        if (empty(self::$config)) {
            if (!is_dir(self::getParams('basePath') . 'config')) {
                mkdir(self::getParams('basePath') . 'config', 0755);
            }

            if (file_exists(self::getParams('basePath') . 'config/' . self::getParams('tv')['name'] . '.php')) {
                self::$config = require self::getParams('basePath') . 'config/' . self::getParams('tv')['name'] . '.php';
            } elseif (file_exists(self::getParams('basePath') . 'config/' . self::getParams('tv')['id'] . '.php')) {
                self::$config = require self::getParams('basePath') . 'config/' . self::getParams('tv')['id'] . '.php';
            }

            if (!is_array(self::$config)) {
                self::$config = null;
            } else {
                if (!isset(self::$config['settings'])) {
                    self::$config['settings'] = [];
                }
                if (!isset(self::$config['templates'])) {
                    self::$config['templates'] = [];
                }
                if (!isset(self::$config['items'])) {
                    self::$config['items'] = [];
                }
                self::$config['templates'] = self::configNormalize(self::$config['templates']);
            }
        }

        if (isset(self::$config[$key])) {
            return self::$config[$key];
        }

        return self::$config;
    }

    /**
     * @param array $data
     * @return array
     */
    private static function configNormalize($data = [])
    {
        foreach ($data as $k => &$v) {
            if (!is_array($v)) {
                if (isset(self::$config['templates'][$v])) {
                    $data[$v] = self::$config['templates'][$v];
                }
                unset($data[$k]);
            }
            if (!empty($v['templates'])) {
                $v['@templates'] = $v['templates'];
                $v['templates'] = self::configNormalize($v['templates']);
            }
        }

        return $data;
    }

    /**
     * @param array $data
     * @return array
     */
    public static function setData($data = [])
    {
        return self::$data = $data;
    }

    /**
     * @return array
     */
    public static function getData()
    {
        if (empty(self::$data)) {
            switch (self::getParams('storage')) {
                case 'files':
                    self::$data = self::fileData();
                    break;

                default:
                    self::$data = !empty(self::getParams('tv')['value']) ? json_decode(self::getParams('tv')['value'], true) : self::getConfig('items');
                    break;
            }
        }

        return self::$data;
    }

    /**
     * @param int $doc_id
     * @param null $tv_id
     * @return array
     */
    private static function fileData($doc_id = 0, $tv_id = null)
    {
        self::$data = [];

        if (!is_dir(self::getParams('basePath') . 'data')) {
            mkdir(self::getParams('basePath') . 'data', 0755);
        }

        if (empty($doc_id) && !empty(self::getParams('id'))) {
            $doc_id = self::getParams('id');
        }

        if (empty($tv_id) && isset(self::getParams('tv')['id'])) {
            $tv_id = self::getParams('tv')['id'];
        }

        $file = self::getParams('basePath') . 'data/' . $doc_id . '__' . $tv_id . '.json';

        if (file_exists($file)) {
            self::$data = file_get_contents($file);
            self::$data = json_decode(self::$data, true);
        }

        return self::$data;
    }
}
