<?php

namespace GTrader\Indicators;

use GTrader\Indicators\Trader;
use GTrader\Series;

/** Hilbert Transform */
class Ht extends Trader
{
    public function __construct()
    {
        parent::__construct();
        $this->init();
    }

    public function __wakeup()
    {
        parent::__wakeup();
        $this->init();
    }


    public function init()
    {
        $mode = $this->getParam('indicator.mode');
        if (!is_array($sel = $this->getParam('modes.'.$mode))) {
            error_log('Ht::init() mode not found: '.$mode);
            return $this;
        }
        if (isset($sel['display']['y_axis_pos'])) {
            $this->setParam('display.y_axis_pos', $sel['display']['y_axis_pos']);
        }
        $this->setParam('outputs', isset($sel['outputs']) ? $sel['outputs'] : ['']);

        return $this;
    }


    public function getInputs()
    {
        $this->init();

        $mode = $this->getParam('indicator.mode');
        $sources = $this->getParam('modes.'.$mode.'.sources', []);
        $active_inputs = [];
        foreach (parent::getInputs() as $input_key => $input_val) {
            if (in_array($input_key, $sources)) {
                $active_inputs[$input_key] = $input_val;
            }
        }
        return $active_inputs;
    }

    public function getDisplaySignature(string $format = 'long')
    {
        $this->init();

        $name = parent::getDisplaySignature('short');
        if ('short' === $format) {
            return $name;
        }
        $inputs = array_keys($this->getInputs());
        $except = [];
        foreach ($this->getParam('indicator') as $key => $param) {
            if ('input_' === substr($key, 0, 6) && !in_array($key, $inputs)) {
                $except[] = $key;
            }
        }
        return ($param_str = $this->getParamString($except)) ? $name.' ('.$param_str.')' : $name;
    }


    public function runDependencies(bool $force_rerun = false)
    {
        return $this;
    }

    public function traderCalc(array $values)
    {
        $this->init();

        $func = 'trader_ht_'.$this->getParam('indicator.mode');
        if (!function_exists($func)) {
            error_log('Ht::traderCalc() function not found: '.$func);
            return [];
        }

        $args = [];
        foreach ($this->getInputs() as $input) {
            $args[] = $values[$input];
        }

        if (!$values = call_user_func_array($func, $args)) {
            error_log('Ht::traderCalc() '.$func.' returned false');
            return [];
        }
        //dd($values);
        //dd($this->getParams());
        return 1 < count($this->getParam('outputs', [])) ? $values : [$values];
    }


}
