<?php

/**
 * @package Base
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\modules\base\handlers;

use gplcart\core\Module;
use gplcart\core\handlers\install\Base as BaseInstaller;
use gplcart\modules\base\models\Install as ModuleModel;

/**
 * Contains methods for installing Base profile
 */
class Install extends BaseInstaller
{

    /**
     * Base module installer model
     * @var \gplcart\modules\base\models\Installer $installer
     */
    protected $model;

    /**
     * Module class instance
     * @var \gplcart\core\Module $module
     */
    protected $module;

    /**
     * @param Module $module
     * @param ModuleModel $model
     */
    public function __construct(Module $module, ModuleModel $model)
    {
        parent::__construct();

        $this->model = $model;
        $this->module = $module;
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
        $result = $this->process();

        if ($result !== true) {
            return $result;
        }

        $result1 = $this->installCliStep1();

        if ($result1['severity'] !== 'success') {
            return $result1;
        }

        $this->installCliStep2();
        return $this->installCliStep3();
    }

    /**
     * Process step 1 in CLI mode
     * @return array
     */
    protected function installCliStep1()
    {
        $this->data['step'] = 1;
        $this->setCliMessage('Configuring modules...');
        return $this->installModules($this->data, $this->db);
    }

    /**
     * Process step 2 in CLI mode
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
                $this->cli->error($result['message']);
            }
        }
    }

    /**
     * Precess step 3 in CLI mode
     * @return array
     */
    protected function installCliStep3()
    {
        $this->data['step'] = 3;
        return $this->installFinish($this->data, $this->db);
    }

    /**
     * Returns an array of demo content options
     * @return array
     */
    protected function getDemoOptions()
    {
        $options = array(
            '' => $this->translation->text('No demo'));

        foreach ($this->model->getDemoHandlers() as $id => $handler) {
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

        $result = $this->model->installModules();

        if ($result === true) {

            $this->configureModules();

            return array(
                'message' => '',
                'severity' => 'success',
                'redirect' => 'install/' . ($this->data['step'] + 1)
            );
        }

        $this->setContextError($this->data['step'], $result);

        return array(
            'redirect' => '',
            'severity' => 'danger',
            'message' => $result
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
        $store_id = $this->getContext('store_id');

        $settings = array();
        $settings['theme'][$store_id]['mobile'] = 'mobile';
        $settings['theme'][$store_id]['tablet'] = 'mobile';

        return $this->module->setSettings('device', $settings);
    }

    /**
     * Configure Google Analytics Report module settings
     * @return bool
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

        return $this->module->setSettings('ga_report', $info['settings']);
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

        $result = $this->model->getDemoModule()->create($this->getContext('store_id'), $data['demo_handler_id']);

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
