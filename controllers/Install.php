<?php

/**
 * @package Base
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2017, Iurii Makukh <gplcart.software@gmail.com>
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0+
 */

namespace gplcart\modules\base\controllers;

use gplcart\core\Controller as BaseController;
use gplcart\core\models\Install as InstallModel;
use gplcart\modules\base\models\Install as ModuleModel;

/**
 * Handles incoming requests and outputs data related to Base module
 */
class Install extends BaseController
{

    /**
     * Install model instance
     * @var \gplcart\core\models\Install $install
     */
    protected $install;

    /**
     * Base module model instance
     * @var \gplcart\modules\base\models\Installer $base_model
     */
    protected $base_model;

    /**
     * An array of installation data set in the session
     * @var array
     */
    protected $data_install;

    /**
     * The current installation step
     * @var integer
     */
    protected $data_step;

    /**
     * An array of the current handler
     * @var array
     */
    protected $data_handler;

    /**
     * Whether to enable the current step
     * @var bool
     */
    protected $data_status = true;

    /**
     * @param InstallModel $install
     * @param ModuleModel $base_model
     */
    public function __construct(InstallModel $install, ModuleModel $base_model)
    {
        parent::__construct();

        $this->install = $install;
        $this->base_model = $base_model;
    }

    /**
     * Displays step pages
     * @param integer $step
     */
    public function stepInstall($step)
    {
        $this->data_step = $step;
        $this->data_install = $this->session->get('install');
        $this->data_handler = $this->install->getHandler('base');

        $this->controlAccessStepInstall();
        $this->submitStepInstall();

        $this->setData('status', $this->data_status);
        $this->setData('handler', $this->data_handler);
        $this->setData('install', $this->data_install);
        $this->setData('demo_handlers', $this->base_model->getDemoHandlers());

        $this->setJsStepInstall();
        $this->setCssStepInstall();

        $this->setTitleStepInstall();
        $this->outputStepInstall();
    }

    /**
     * Sets titles on the step page
     */
    protected function setTitleStepInstall()
    {
        $this->setTitle($this->data_handler['steps'][$this->data_step]['title']);
    }

    /**
     * Render and output the step page
     */
    protected function outputStepInstall()
    {
        $this->output(array('body' => "base|step{$this->data_step}"));
    }

    /**
     * Sets CSS on the step page
     */
    protected function setCssStepInstall()
    {
        $this->setCss('system/modules/base/css/common.css');
    }

    /**
     * Sets Java-Scripts on the step page
     */
    protected function setJsStepInstall()
    {
        $this->setJs('system/modules/base/js/common.js');
    }

    /**
     * Handles submits
     */
    protected function submitStepInstall()
    {
        if (!$this->isPosted('next')) {
            return null;
        }

        $this->setSubmitted('step');

        $this->data_install['data']['step'] = $this->data_step;
        $this->data_install['data'] = array_merge($this->data_install['data'], $this->getSubmitted());
        $this->session->set('install', $this->data_install);

        $result = $this->install->process($this->data_install['data']);

        if (!empty($result['redirect'])) {
            $this->redirect($result['redirect'], $result['message'], $result['severity']);
        }

        $this->data_status = false;
        $this->setMessage($result['message'], $result['severity']);
    }

    /**
     * Sets and validates the installation step
     */
    protected function controlAccessStepInstall()
    {
        if (!$this->config->isInitialized()) {
            $this->redirect('install');
        }

        if ($this->data_step > count($this->data_handler['steps'])) {
            $this->redirect('install');
        }

        if (isset($this->data_install['data']['step']) && ($this->data_step - $this->data_install['data']['step']) != 1) {
            $this->data_status = false;
        }
    }

}
