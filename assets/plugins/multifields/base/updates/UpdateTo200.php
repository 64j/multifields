<?php

namespace Multifields\Base\Updates;

use Multifields\Base\Update;

class UpdateTo200 extends Update
{
    const VERSION = '2.0.0';

    protected $data = [];

    /**
     * @return string
     */
    public function run()
    {
        $this->update_config();

        $this->update_data_default();

        $this->update_data_files();

        $this->update_data_database();

        return self::VERSION;
    }

    protected function update_config()
    {
        // update config
        $files = glob(dirname(dirname(__DIR__)) . '/config/*.php');
        if (!empty($files)) {
            foreach ($files as $file) {
                // Skip example config files
                if (stripos(basename($file), 'example.') !== false) {
                    continue;
                }

                // copy old config
                copy($file, $file . '.bak');

                $config = file_get_contents($file);

                // fix thumb actions
                $config = str_replace("'type' => 'thumb'", "'type' => 'thumb'," . "\n" . "'actions' => false", $config);

                // fix type thumb:image
                $config = str_replace('thumb:image', 'thumb', $config);

                // fix type => group
                $config = str_replace("'type' => 'group'", "'type' => 'row'", $config);

                // fix php
                // remove comments
                $config = preg_replace(array('!/\*.*?\*/!s'), '', $config);
                $config = trim($config);

                $config = str_replace('<?php', '', $config);
                $config = trim($config);

                // remove return
                $config = substr($config, 6);

                // remove last ;
                $config = substr($config, 0, -1);

                $src = "<?php" . "\n";
                $src .= "return [" . "\n";
                $src .= "    'templates' => " . $config . "," . "\n";
                $src .= "];" . "\n";

                // save new config
                file_put_contents($file, $src);
            }
        }
    }

    protected function update_data_default()
    {
        $evo = evolutionCMS();

        $res = $evo->db->query('
            SELECT
            TV.*, TVC.id AS tvc_id, TVC.value
            FROM ' . $evo->getFullTableName('site_tmplvars') . ' AS TV
            LEFT JOIN ' . $evo->getFullTableName('site_tmplvar_contentvalues') . ' AS TVC ON TVC.tmplvarid = TV.id
            WHERE 1
            AND TV.type = "custom_tv:multifields"
            AND TVC.value <> ""
        ');

        if ($evo->db->getRecordCount($res)) {
            while ($row = $evo->db->getRow($res)) {
                $row['value'] = !empty($row['value']) ? $this->fillData(json_decode($row['value'], true)) : '';
                if (!empty($row['value'])) {
                    $row['value'] = json_encode($row['value'], JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
                    $evo->db->update([
                        'value' => $row['value']
                    ], $evo->getFullTableName('site_tmplvar_contentvalues'), 'id = ' . $row['tvc_id']);
                }
            }
        }
    }

    protected function update_data_files()
    {
        $files = glob(dirname(dirname(__DIR__)) . '/data/*.php');
        if (!empty($files)) {
            foreach ($files as $file) {
                copy($file, $file . '.bak');
                include $file;

                if (!empty($this->data)) {
                    $file_new = substr($file, 0, -3) . 'json';
                    $data = $this->fillData($this->data);
                    $data = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
                    file_put_contents($file_new, $data);
                }
            }
        }
    }

    protected function update_data_database()
    {

    }

    /**
     * @param array $data
     * @param int $parent
     * @param int $level
     * @return array
     */
    protected function fillData($data = [], $parent = 0, $level = 0)
    {
        $out = [];
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                if ($v['parent'] == $parent) {
                    unset($v['parent']);
                    if ($_ = $this->fillData($data, $k, $level)) {
                        $v['items'] = $_;
                    }
                    $out[] = $v;
                } else {
                    unset($data[$k]);
                }
            }
        } else {
            $out[] = $data;
        }

        return $out;
    }
}