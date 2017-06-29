<?php

namespace GTrader\Indicators;

/** Moving Average */
class Ma extends Trader
{

    public function __construct(array $params = [])
    {
        parent::__construct($params);
        $this->setParam(
            'adjustable.type.options',
            \Config::get('GTrader.Indicators.Trader.MA_TYPES')
        );
    }

    public function getMaType()
    {
        return $this->getParam('indicator.type');
    }

    public function getNormalizeParams()
    {
        if ($this->inputFromIndicator()) {
            if ($ind = $this->getOwner()->getOrAddIndicator($this->getInput())) {
                return $ind->getNormalizeParams();
            }
        }
        return parent::getNormalizeParams();
    }


    public function traderCalc(array $values)
    {
        if (!($values = trader_ma(
            $values[$this->getInput()],
            $this->getParam('indicator.length', 1),
            $this->getMaType()
        ))) {
            error_log('trader_ma returned false');
            return [];
        }
        return [$values];
    }
}
