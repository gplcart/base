<?php

/**
 * @package Base
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\modules\base\models;

use gplcart\core\Module,
    gplcart\core\Container;
use gplcart\core\models\Translation as TranslationModel;
use gplcart\core\exceptions\Dependency as DependencyException;

/**
 * Installer model
 */
class Install
{

    /**
     * Translation UI model instance
     * @var \gplcart\core\models\Translation $translation
     */
    protected $translation;

    /**
     * Module class instance
     * @var \gplcart\core\Module $module
     */
    protected $module;

    /**
     * @param Module $module
     * @param TranslationModel $translation
     */
    public function __construct(Module $module, TranslationModel $translation)
    {
        $this->module = $module;
        $this->translation = $translation;
    }

    /**
     * Returns an array of modules required for Base installation profile
     * @return array
     */
    public function getRequiredModules()
    {
        return gplcart_config_get(__DIR__ . '/../config/modules.php');
    }

    /**
     * Whether the current distribution contains all the required modules
     * @return bool
     */
    public function hasAllRequiredModules()
    {
        $required = $this->getRequiredModules();
        $available = array_keys($this->module->getList());
        $difference = array_diff($required, array_intersect($required, $available));

        return empty($difference);
    }

    /**
     * Install all required modules
     * @return bool
     */
    public function installModules()
    {
        foreach ($this->getRequiredModules() as $module_id) {
            if ($this->getModuleModel()->install($module_id) !== true) {
                return $this->translation->text('Failed to install module @module_id', array('@module_id' => $module_id));
            }
        }

        return true;
    }

    /**
     * Create demo content using Demo module
     * @param integer $store_id
     * @param string $handler_id
     * @return bool|string
     */
    public function createDemo($store_id, $handler_id)
    {
        return $this->getDemoModule()->create($store_id, $handler_id);
    }

    /**
     * Returns an array of demo handlers
     * @return array
     */
    public function getDemoHandlers()
    {
        return $this->getDemoModule()->getHandlers();
    }

    /**
     * Returns Demo module instance
     * @return \gplcart\modules\demo\Main
     */
    public function getDemoModule()
    {
        /* @var \gplcart\modules\demo\Main $instance */
        $instance = $this->module->getInstance('demo');
        return $instance;
    }

    /**
     * Returns Module model class instance
     * @return \gplcart\core\models\Module
     */
    protected function getModuleModel()
    {
        return Container::get('gplcart\\core\\models\\Module');
    }

}
