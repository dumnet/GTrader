<?php

namespace GTrader;

use PHPlot_truecolor;
use GTrader\Util;

class Plot
{
    use Skeleton;

    protected $_plot;

    public function toHTML(string $content = '')
    {
        return $this->getImage();
    }


    public function getImage()
    {
        $width = $this->getParam('width');
        $height = $this->getParam('height');
        if ($width <= 1 || $height <= 1) {
            error_log('Plot::getImage(): Missing width or height.');
            return '';
        }
        $data = $this->getParam('data');
        if (!is_array($data)) {
            error_log('Plot::getImage(): data is not an array.');
            return '';
        }
        if (!count($data)) {
            error_log('Plot::getImage(): data is empty.');
            return '';
        }

        $this->initPlot($width, $height);
        $this->_plot->SetPrintImage(false);
        $this->_plot->SetFailureImage(false);
        $this->plot($data);
        return '<img class="img-responsive" src="'.
                $this->_plot->EncodeImage().'">';
    }


    protected function initPlot($width, $height)
    {
        $this->_plot = new PHPlot_truecolor($width, $height);
        return $this;
    }


    protected function plot(array $data)
    {
        $this->_plot->SetMarginsPixels(30, 30, 15);
        $this->_plot->SetBackgroundColor('black');
        $this->_plot->SetGridColor('DarkGreen:100');
        $this->_plot->SetLightGridColor('DimGrey:120');
        $this->_plot->setTitleColor('DimGrey:80');
        $this->_plot->SetTickColor('DarkGreen');
        $this->_plot->SetTextColor('grey');
        $this->_plot->SetDataType('data-data');

        $out = ['left' => [], 'right' => []];
        reset($data);
        while (list($label, $item) = each($data)) {
            if (!is_array($item)) {
                continue;
            }
            if (!is_array($values = $item['values'])) {
                continue;
            }
            if (!count($values)) {
                continue;
            }
            $dir = 'left';
            if ($ypos = Util::arrEl($item, ['display', 'y_axis_pos'])) {
                $dir = $ypos;
            }

            $out[$dir]['dim'] = [
                'xmin' => min(min(array_keys($values)), Util::arrEl($out, [$dir, 'dim', 'xmin'])),
                'xmax' => max(max(array_keys($values)), Util::arrEl($out, [$dir, 'dim', 'xmax'])),
                'ymin' => min(min($values), Util::arrEl($out, [$dir, 'dim', 'ymin'])),
                'ymax' => max(max($values), Util::arrEl($out, [$dir, 'dim', 'ymax'])),
            ];

            $out[$dir][$label] = [];
            reset($values);
            while (list($xvalue, $yvalue) = each($values)) {
                $out[$dir]['values'][$label][] = ['', $xvalue, $yvalue];
            }
        }



        foreach (['left', 'right'] as $dir) {
            $this->setWorld($out[$dir]['dim']);
            //error_log($dir.' world: '.json_encode($out[$dir]['dim']));

            foreach ($out[$dir]['values'] as $label => $values) {
                //error_log($dir.' label: '.$label);
                if (!count($values)) {
                    continue;
                }
                $color = self::nextColor();
                $this->_plot->SetDataValues($values);
                $this->_plot->SetLineWidths(2);
                $this->_plot->setPlotType('lines');
                $this->_plot->SetDataColors([$color]);
                $this->_plot->SetTickLabelColor($color);

                if ('right' === $dir) {
                    //$this->setWorld($out[$dir]['dim'], 'x');
                    $this->_plot->SetYTickPos('plotright');
                    $this->_plot->SetYTickLabelPos('plotright');
                    //$this->_plot->TuneYAutoRange(0);
                }

                //$this->_plot->TuneYAutoRange(0);
                $this->_plot->SetLegendPixels(35, self::nextLegendY());
                $this->_plot->SetLegend([$label]);
                $this->_plot->DrawGraph();
            }
        }
        return $this;
    }

    public static function nextColor()
    {
        static $index = 0;
        $colors = ['#22226640', 'yellow:110', 'maroon:70', 'brown:70'];
        $color = $colors[$index];
        $index ++;
        if ($index >= count($colors)) {
            $index = 0;
        }
        return $color;
    }

    public static function nextLegendY(int $step = 1)
    {
        static $y = 25;
        $ret = $y;
        $y += 25 * $step;
        return $ret;
    }

    protected function setWorld(array $new_world = [], string $set_axes = 'xy')
    {
        static $world = [];

        $world = array_replace($world, $new_world);

        //error_log('setWorld() axes: '.$set_axes.' world: '.json_encode($world));

        $xmin = $ymin = $xmax = $ymax = null;
        if (strstr($set_axes, 'x')) {
            $xmin = Util::arrEl($world, ['xmin']);
            $xmax = Util::arrEl($world, ['xmax']);
        }
        if (strstr($set_axes, 'y')) {
            $ymin = Util::arrEl($world, ['ymin']);
            $ymax = Util::arrEl($world, ['ymax']);
        }
        $this->_plot->setPlotAreaWorld(
            $xmin,
            $ymin,
            $xmax,
            $ymax
        );
        return $this;
    }
}
