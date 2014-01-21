<?php
class avgController extends bsControllerBase
{
    private $avg;

    public function __construct()
    {
        parent::__construct();
        $this->avg = new AvgModel();
        $this->config = bsFactory::get('config');
        $this->view->set_formatter('json');
    }

    public function indexAction($args)
    {
        $curr = isset($args['currency']) ? $args['currency'] : 'USD';
        $this->view->values = $this->avg->get_by_currency($curr);
    }

    public function pushAction()
    {
        $this->avg->push(json_decode(file_get_contents($this->config->btcavg_url), true));
    }
}

