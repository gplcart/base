<?php

/**
 * @package Base
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\modules\base\handlers;

use Exception;
use gplcart\core\handlers\install\Base as BaseInstaller;
use gplcart\core\Module;
use gplcart\modules\base\models\Install as ModuleInstallModel;
use UnexpectedValueException;

/**
 * Contains methods for installing Base profile
 */
class Install extends BaseInstaller
{

    /**
     * Base module installer model
     * @var \gplcart\modules\base\models\Install $installer
     */
    protected $install_model;

    /**
     * Module class instance
     * @var \gplcart\core\Module $module
     */
    protected $module;

    /**
     * @param Module $module
     * @param ModuleInstallModel $install_model
     */
    public function __construct(Module $module, ModuleInstallModel $install_model)
    {
        parent::__construct();

        $this->module = $module;
        $this->install_model = $install_model;
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
            $this->installCli();
            return array();
        }

        try {

            $this->start();
            $this->process();

            return array(
                'message' => '',
                'severity' => 'success',
                'redirect' => 'install/' . ($this->data['step'] + 1)
            );

        } catch (Exception $ex) {
            return array(
                'redirect' => '',
                'severity' => 'warning',
                'message' => $ex->getMessage()
            );
        }
    }

    /**
     * Install in CLI mode
     */
    protected function installCli()
    {
        try {

            $this->process();
            $this->installCliStep1();
            $this->installCliStep2();
            $this->installCliStep3();

        } catch (Exception $ex) {
            $this->cli->error($ex->getMessage());
        }
    }

    /**
     * Process step 1 in CLI mode
     * @throws UnexpectedValueException
     */
    protected function installCliStep1()
    {
        $this->data['step'] = 1;
        $this->setCliMessage('Configuring modules...');
        $result = $this->installModules($this->data, $this->db);

        if ($result['severity'] !== 'success') {
            throw new UnexpectedValueException($result['message']);
        }
    }

    /**
     * Process step 2 in CLI mode
     * @throws UnexpectedValueException
     */
    protected function installCliStep2()
    {
        $title = $this->translation->text('Please select a demo content package (enter a number)');
        $this->data['demo_handler_id'] = $this->cli->menu($this->getDemoOptions(), '', $title);

        if (!empty($this->data['demo_handler_id'])) {

            $this->data['step'] = 2;
            $this->setCliMessage('Installing demo content...');
            $result = $this->installDemo($this->data, $this->db);

            if ($result['severity'] !== 'success') {
                throw new UnexpectedValueException($result['message']);
            }

            $this->setContext('demo_handler_id', $this->data['demo_handler_id']);
        }
    }

    /**
     * Precess step 3 in CLI mode
     * @throws UnexpectedValueException
     */
    protected function installCliStep3()
    {
        $this->data['step'] = 3;
        $result = $this->installFinish($this->data, $this->db);

        if ($result['severity'] !== 'success') {
            throw new UnexpectedValueException($result['message']);
        }
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

        try {

            $this->install_model->installModules();
            $this->configureModules();

            return array(
                'message' => '',
                'severity' => 'success',
                'redirect' => 'install/' . ($this->data['step'] + 1)
            );

        } catch (Exception $ex) {

            $this->setContextError($this->data['step'], $ex->getMessage());

            return array(
                'redirect' => '',
                'severity' => 'danger',
                'message' => $ex->getMessage()
            );
        }
    }

    /**
     * Install a demo-content. Step 2
     * @param array $data
     * @param \gplcart\core\Database $db
     * @return array
     */
    public function installDemo(array $data, $db)
    {
        set_time_limit(0);

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

        $result = $this->install_model->getDemoModule()->create($this->getContext('store_id'), $data['demo_handler_id']);

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

        if (empty($errors)) {
            if ($this->getContext('demo_handler_id')) {
                $store_id = $this->getContext('store_id');
                $this->getStoreModel()->update($store_id, array('status' => 1));
            }
        } else {
            $result['severity'] = 'warning';
            $result['message'] = implode(PHP_EOL, $errors);
        }

        return $result;
    }

    /**
     * Returns an array of demo content options
     * @return array
     */
    protected function getDemoOptions()
    {
        $options = array('' => $this->translation->text('No demo'));

        foreach ($this->install_model->getDemoHandlers() as $id => $handler) {
            $options[$id] = $handler['title'];
        }

        return $options;
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
     */
    protected function configureModuleDevice()
    {
        $store_id = $this->getContext('store_id');

        $settings = array();
        $settings['theme'][$store_id]['mobile'] = 'mobile';
        $settings['theme'][$store_id]['tablet'] = 'mobile';

        $this->module->setSettings('device', $settings);
    }

    /**
     * Configure Google Analytics Report module settings
     */
    protected function configureModuleGaReport()
    {
        $info = $this->module->getInfo('ga_report');

        $info['settings']['dashboard'] = array(
            'visit_date',
            'pageview_date',
            'content_statistic',
            'top_pages',
            'source',
            'keyword',
            'audience'
        );

        $this->module->setSettings('ga_report', $info['settings']);
    }

}
