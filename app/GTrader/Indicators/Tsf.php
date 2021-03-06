<?php

namespace GTrader\Indicators;

use GTrader\Log;

/** Time Series Forecast */
class Tsf extends Trader
{
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
        if (!($values = trader_tsf(
            $values[$this->getInput()],
            $this->getParam('indicator.period')
        ))) {
            Log::error('trader_tsf returned false');
            return [];
        }
        return [$values];
    }
}
