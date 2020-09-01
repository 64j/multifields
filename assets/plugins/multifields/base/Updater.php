<?php

namespace Multifields\Base;

class Updater
{
    const OLD_VERSION = '1.3.0';

    /**
     * Run updates
     */
    public function run()
    {
        if (version_compare($this->getVersion(), '2.0', '<')) {
            $this->setVersion((new \Multifields\Base\Updates\Updater200())->run());
        }

        if (version_compare($this->getVersion(), Core::VERSION, '<')) {
            $this->setVersion(Core::VERSION);
        }
    }

    /**
     * @return string
     */
    protected function getVersion()
    {
        $evo = evolutionCMS();
        $version = self::OLD_VERSION;

        if (is_file(dirname(__DIR__) . '/version.multifields.php')) {
            $version = file_get_contents(dirname(__DIR__) . '/version.multifields.php');
            unlink(dirname(__DIR__) . '/version.multifields.php');
        } else {
            if ($evo->getConfig('multifields_version')) {
                $version = $evo->getConfig('multifields_version');
            }
        }

        return $version;
    }

    /**
     * @param string $version
     * @return string
     */
    protected function setVersion($version = null)
    {
        $evo = evolutionCMS();

        if (empty($version)) {
            $version = self::OLD_VERSION;
        }

        if ($evo->getConfig('multifields_version')) {
            $evo->db->update([
                'setting_value' => $evo->db->escape($version)
            ], '[+prefix+]system_settings', 'setting_name = \'multifields_version\'');
        } else {
            $evo->db->insert([
                'setting_name' => 'multifields_version',
                'setting_value' => $evo->db->escape($version)
            ], '[+prefix+]system_settings');
        }

        return $version;
    }
}
