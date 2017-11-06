<?php

/**
 * @package Base
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\modules\base\models;

use gplcart\core\Container,
    gplcart\core\Config;
use gplcart\core\models\Language as LanguageModel;

/**
 * Manages basic behaviors and data related to Base model
 */
class Installer
{

    /**
     * Config class instance
     * @var \gplcart\core\Config $config
     */
    protected $config;

    /**
     * Language model instance
     * @var \gplcart\core\models\Language $language
     */
    protected $language;

    /**
     * @param Config $config
     * @param LanguageModel $language
     */
    public function __construct(Config $config, LanguageModel $language)
    {
        $this->config = $config;
        $this->language = $language;
    }

    /**
     * Returns an array of modules required for Base installation profile
     * @return array
     */
    public function getRequiredModules()
    {
        static $modules = null;

        if (!isset($modules)) {
            $modules = require __DIR__ . '/../config/modules.php';
        }

        return $modules;
    }

    /**
     * Whether the current distribution contains all the required modules
     * @return bool
     */
    public function hasAllRequiredModules()
    {
        $required = $this->getRequiredModules();
        $available = array_keys($this->config->getModules());
        $difference = array_diff($required, array_intersect($required, $available));

        return empty($difference);
    }

    /**
     * Install all required modules
     * @return bool
     */
    public function installModules()
    {
        $installed = 0;
        $modules = $this->getRequiredModules();

        /* @var $model \gplcart\core\models\Module */
        $model = Container::get('gplcart\\core\\models\\Module');

        foreach ($modules as $module_id) {
            $result = $model->install($module_id);
            $installed += (int) ($result === true);
        }

        return $installed == count($modules);
    }

    /**
     * Sets settings for a given module
     * @param string $module_id
     * @param array $settings
     * @return bool
     */
    public function setModuleSettings($module_id, array $settings)
    {
        /* @var $model \gplcart\core\models\Module */
        $model = Container::get('gplcart\\core\\models\\Module');
        return $model->setSettings($module_id, $settings);
    }

    /**
     * Create demo content using Demo module
     * @param integer $store_id
     * @param string $handler_id
     * @return bool|string
     */
    public function createDemo($store_id, $handler_id)
    {
        /* @var $module \gplcart\modules\demo\Demo */
        $module = $this->config->getModuleInstance('demo');
        return $module->create($store_id, $handler_id);
    }

    /**
     * Returns an array of demo handlers
     * @return array
     */
    public function getDemoHandlers()
    {
        /* @var $module \gplcart\modules\demo\Demo */
        $module = $this->config->getModuleInstance('demo');
        return $module->getHandlers();
    }

}
