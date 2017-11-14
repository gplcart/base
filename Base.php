<?php

/**
 * @package Base
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2017, Iurii Makukh <gplcart.software@gmail.com>
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0+
 */

namespace gplcart\modules\base;

use gplcart\core\Module,
    gplcart\core\Config;

/**
 * Main class for Base module
 */
class Base extends Module
{

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        parent::__construct($config);
    }

    /* ---------------------- Hooks ---------------------- */

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

    /* ---------------------- Helpers ---------------------- */

    /**
     * Adds installation handlers
     * @param array $handlers
     */
    protected function setInstallHandlers(array &$handlers)
    {
        $language = $this->getLanguage();

        $handlers['base'] = array(
            'module' => 'base',
            'title' => $language->text('Base'),
            'steps' => array(
                1 => array('title' => $language->text('Configure modules')),
                2 => array('title' => $language->text('Create demo')),
                3 => array('title' => $language->text('Finish installation'))
            ),
            'handlers' => array(
                'install' => array('gplcart\\modules\\base\\handlers\\Installer', 'install'),
                'install_1' => array('gplcart\\modules\\base\\handlers\\Installer', 'installModules'),
                'install_2' => array('gplcart\\modules\\base\\handlers\\Installer', 'installDemo'),
                'install_3' => array('gplcart\\modules\\base\\handlers\\Installer', 'installFinish')
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
        /* @var $model \gplcart\modules\base\models\Installer */
        $model = $this->getModel('Installer', 'base');

        if ($data['installer'] === 'base' && empty($data['step']) && !$model->hasAllRequiredModules()) {
            $result = array(
                'redirect' => '',
                'severity' => 'warning',
                'message' => $this->getLanguage()->text('You cannot use this installer because some modules are missed in your distribution')
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
            $templates[0] = $this->getTemplate('base', 'intro');
        }
    }

}
