<?php

/**
 * @package Base
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2017, Iurii Makukh <gplcart.software@gmail.com>
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0+
 */

namespace gplcart\modules\base;

use gplcart\core\Config,
    gplcart\core\Container;

/**
 * Main class for Base module
 */
class Module
{

    /**
     * Config class instance
     * @var \gplcart\core\Config $config
     */
    protected $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Implements hook "route.list"
     * @param array $routes
     */
    public function hookRouteList(array &$routes)
    {
        $routes['install/(\d+)'] = array(
            'handlers' => array(
                'controller' => array('gplcart\\modules\\base\\controllers\\Install', 'stepInstall')
            )
        );
    }

    /**
     * Implements hook "install.handlers"
     * @param array $handlers
     */
    public function hookInstallHandlers(array &$handlers)
    {
        $this->setInstallHandlers($handlers);
    }

    /**
     * Implements hook "install.before"
     * @param array $data
     * @param mixed $result
     */
    public function hookInstallBefore(array $data, &$result)
    {
        $this->checkRequiredModules($data, $result);
    }

    /**
     * Implements hook "template.render"
     * @param array $templates
     */
    public function hookTemplateRender(array &$templates)
    {
        $this->replaceTemplates($templates);
    }

    /**
     * Adds installation handlers
     * @param array $handlers
     */
    protected function setInstallHandlers(array &$handlers)
    {
        $language = $this->getTranslationModel();

        $handlers['base'] = array(
            'module' => 'base',
            'title' => $language->text('Base'),
            'steps' => array(
                1 => array('title' => $language->text('Configure modules')),
                2 => array('title' => $language->text('Create demo')),
                3 => array('title' => $language->text('Finish installation'))
            ),
            'handlers' => array(
                'install' => array('gplcart\\modules\\base\\handlers\\Install', 'install'),
                'install_1' => array('gplcart\\modules\\base\\handlers\\Install', 'installModules'),
                'install_2' => array('gplcart\\modules\\base\\handlers\\Install', 'installDemo'),
                'install_3' => array('gplcart\\modules\\base\\handlers\\Install', 'installFinish')
            )
        );
    }

    /**
     * Check if all required modules in place
     * @param array $data
     * @param array $result
     */
    protected function checkRequiredModules(array $data, &$result)
    {
        if ($data['installer'] === 'base' && empty($data['step']) && !$this->getModel()->hasAllRequiredModules()) {
            $result = array(
                'redirect' => '',
                'severity' => 'warning',
                'message' => $this->getTranslationModel()->text('You cannot use this installer because some modules are missed in your distribution')
            );
        }
    }

    /**
     * Replace system templates
     * @param array $templates
     */
    protected function replaceTemplates(array &$templates)
    {
        if (substr($templates[0], -15) === 'dashboard/intro' && $this->config->get('installer') === 'base') {
            $templates[0] = __DIR__ . '/templates/intro';
        }
    }

    /**
     * Returns the module model
     * @return \gplcart\modules\base\models\Install
     */
    protected function getModel()
    {
        return Container::get('gplcart\\modules\\base\\models\\Install');
    }

    /**
     * Translation UI model instance
     * @return \gplcart\core\models\Translation
     */
    protected function getTranslationModel()
    {
        return Container::get('gplcart\\core\\models\\Translation');
    }

}
