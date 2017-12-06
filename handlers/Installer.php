<?php

/**
 * @package Base
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\modules\base\handlers;

use gplcart\core\Config,
    gplcart\core\Module;
use gplcart\core\helpers\Cli as CliHelper,
    gplcart\core\helpers\Session as SessionHelper;
use gplcart\core\models\Install as InstallModel,
    gplcart\core\models\Language as LanguageModel;
use gplcart\core\handlers\install\Base as BaseInstaller;
use gplcart\modules\base\models\Installer as BaseModuleModel;

/**
 * Contains methods for installing Base profile
 */
class Installer extends BaseInstaller
{

    /**
     * Base module installer model
     * @var \gplcart\modules\base\models\Installer $installer
     */
    protected $base_model;

    /**
     * Module class instance
     * @var \gplcart\core\Module $module
     */
    protected $module;

    /**
     * @param Config $config
     * @param InstallModel $install
     * @param LanguageModel $language
     * @param SessionHelper $session
     * @param CliHelper $cli
     * @param Module $module
     * @param BaseModuleModel $base_model
     */
    public function __construct(Config $config, InstallModel $install, LanguageModel $language,
            SessionHelper $session, CliHelper $cli, Module $module, BaseModuleModel $base_model)
    {
        parent::__construct($config, $install, $language, $session, $cli);

        $this->module = $module;
        $this->base_model = $base_model;
    }

    /**
     * Performs initial system installation. Step 0
     * @param array $data
     * @param \gplcart\core\Database $db
     * @return array
     */
    public function install(array $data, $db)
    {
        $this->db = $db;
        $this->data = $data;
        $this->data['step'] = 0;

        if (GC_CLI) {
            return $this->installCli();
        }

        $this->start();

        $result = $this->process();

        if ($result !== true) {
            return $result;
        }

        return array(
            'message' => '',
            'severity' => 'success',
            'redirect' => 'install/' . ($this->data['step'] + 1)
        );
    }

    /**
     * Install in CLI mode
     * @return array
     */
    protected function installCli()
    {
        $this->cli->line($this->language->text('Initial configuration...'));
        $result = $this->process();

        if ($result !== true) {
            return $result;
        }

        $this->data['step'] = 1;
        $this->cli->line($this->language->text('Configuring modules...'));
        $result_modules = $this->installModules($this->data, $this->db);

        if ($result_modules['severity'] !== 'success') {
            return $result_modules;
        }

        $title = $this->language->text('Please select a demo content package (enter a number)');
        $this->data['demo_handler_id'] = $this->cli->menu($this->getDemoOptions(), '', $title);

        if (!empty($this->data['demo_handler_id'])) {

            $this->data['step'] = 2;
            $this->cli->line($this->language->text('Installing demo content...'));
            $result_demo = $this->installDemo($this->data, $this->db);

            if ($result_demo['severity'] !== 'success') {
                $this->cli->error($result_demo['message']);
            }
        }

        $this->data['step'] = 3;
        return $this->installFinish($this->data, $this->db);
    }

    /**
     * Returns an array of demo content options
     * @return array
     */
    protected function getDemoOptions()
    {
        $options = array('' => $this->language->text('No demo'));

        foreach ($this->base_model->getDemoHandlers() as $id => $handler) {
            $options[$id] = $handler['title'];
        }

        return $options;
    }

    /**
     * Installs required modules. Step 1
     * @param array $data
     * @param \gplcart\core\Database $db
     * @return array
     */
    public function installModules(array $data, $db)
    {
        $this->db = $db;
        $this->data = $data;

        $result = $this->base_model->installModules();

        if ($result === true) {

            $this->configureModules();

            return array(
                'message' => '',
                'severity' => 'success',
                'redirect' => 'install/' . ($this->data['step'] + 1)
            );
        }

        $message = $this->language->text('An error occurred during installing required modules');
        $this->setContextError($this->data['step'], $message);

        return array(
            'redirect' => '',
            'severity' => 'danger',
            'message' => $message
        );
    }

    /**
     * Configure module settings
     */
    protected function configureModules()
    {
        $this->configureModuleDevice();
        $this->configureModuleGaReport();
    }

    /**
     * Configure Device module settings
     * @return bool
     */
    protected function configureModuleDevice()
    {
        $settings = array();
        $store_id = $this->getContext('store_id');
        $settings['theme'][$store_id]['mobile'] = 'mobile';
        $settings['theme'][$store_id]['tablet'] = 'mobile';

        return $this->base_model->setModuleSettings('device', $settings);
    }

    /**
     * Configure Google Analytics Report module settings
     * @return bool
     */
    protected function configureModuleGaReport()
    {
        $settings = array(
            'dashboard' => array('visit_date', 'pageview_date',
                'content_statistic', 'top_pages', 'source', 'keyword', 'audience')
        );

        return $this->base_model->setModuleSettings('ga_report', $settings);
    }

    /**
     * Install a demo-content. Step 2
     * @param array $data
     * @param \gplcart\core\Database $db
     * @return array
     */
    public function installDemo(array $data, $db)
    {
        $this->db = $db;
        $this->data = $data;

        $success_result = array(
            'message' => '',
            'severity' => 'success',
            'redirect' => 'install/' . ($this->data['step'] + 1)
        );

        if (empty($data['demo_handler_id'])) {
            return $success_result;
        }

        /* @var $module \gplcart\modules\demo\Demo */
        $module = $this->module->getInstance('demo');
        $result = $module->create($this->getContext('store_id'), $data['demo_handler_id']);

        if ($result !== true) {
            $this->setContextError($this->data['step'], $result);
        }

        return $success_result;
    }

    /**
     * Performs final tasks. Step 3
     * @param array $data
     * @param \gplcart\core\Database $db
     * @return array
     */
    public function installFinish(array $data, $db)
    {
        $this->db = $db;
        $this->data = $data;

        $result = $this->finish();
        $errors = $this->getContextErrors();

        if (!empty($errors)) {
            $result['message'] = $errors;
            $result['severity'] = 'warning';
        }

        return $result;
    }

}
