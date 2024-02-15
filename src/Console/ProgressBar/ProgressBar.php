<?php

namespace Solital\Core\Console\ProgressBar;

class ProgressBar
{
    /**
     * @var object
     */
    private object $style;

    /**
     * @var mixed
     */
    private mixed $initialmax;
    
    /**
     * @var mixed
     */
    private readonly mixed $starttime;
    
    /**
     * @var mixed
     */
    private mixed $value;
    
    /**
     * @var bool|null|null
     */
    private ?bool $pause = null;

    /**
     * Constructor for the Advanced progressbar object
     * @param object $ProgressbarStyle Style object
     * @param float $initialmax Intial max number
     */
    public function __construct(object $ProgressbarStyle, float $initialmax)
    {
        if ($ProgressbarStyle != NULL) {
            $this->style = $ProgressbarStyle;
        } else {
            throw new ProgressBarException("Variable [" . gettype($ProgressbarStyle)  . " ProgressbarStyle]  was not passed to the constructor!");
        }

        if ($initialmax >= 0 and !empty($initialmax)) {
            $this->initialmax = $initialmax;
        } else {
            throw new ProgressBarException("Variable [" . gettype($initialmax)  . " initialmax]  was not passed to the constructor or it is negative number!");
        }

        $this->starttime = time();
        $this->value = 0;
    }

    /**
     * Calculates the current progress, returns as float example (0.**)
     * @return float
     */
    private function calculateProgress(): float
    {
        if ($this->initialmax > 0) {
            return $this->value / $this->initialmax;
        }
        
        throw new ProgressBarException("Numeric error, cannot divide a zero!");
    }

    /**
     * Builds the progressbar as a string
     * @return string
     */
    private function constructProgressbar(): string
    {
        //Get predefined variables
        //list($length, $progress) = [$this->style->getLength(), $this->calculateProgress()];
        [$length, $progress] = [$this->style->getLength(), $this->calculateProgress()];

        //Calculate variables
        //list($wholewidth, $remainderwidth) = [floor($progress * $length), floatval("0." . explode(".", number_format($progress * $length, 2))[1])];
        [$wholewidth, $remainderwidth] = [floor($progress * $length), floatval("0." . explode(".", number_format($progress * $length, 2))[1])];

        //Get the desired char depending on the current progress.
        $char = [" ", "▌"][floor($remainderwidth * 2)];

        //Clears the last space after the progressbar is completed.
        if (($length - $wholewidth - 1) < 0) {
            $char = "";
        }

        return $this->style->getName() . " " . number_format($progress * 100, 0) . "% │" . str_repeat("█", $wholewidth) . $char . str_repeat(" ", ($length - $wholewidth)) . "│";
    }

    /**
     * Constructs the iteration part of the progressbar string
     * @return string
     */
    private function constructIterationString(): string
    {
        if (empty($this->style->datatype)) {
            return ("{$this->value}/{$this->initialmax}");
        } else {
            return ("{$this->value}/{$this->initialmax} {$this->style->getDatatype()}");
        }
    }

    /**
     * Constructs the runtime of the progressbar string
     * @return string
     */
    private function constructTimeString(): string
    {
        $time_elapsed = time() - $this->starttime;
        $eta = intdiv($time_elapsed, $this->value) * $this->initialmax;
        /* list($hours_eta, $mins_eta, $secs_eta) = [intdiv($eta, 3600), intdiv($eta, 60) % 60, $eta % 60];
        list($hours, $mins, $secs) = [intdiv($time_elapsed, 3600), intdiv($time_elapsed, 60) % 60, $time_elapsed % 60]; */

        [$hours_eta, $mins_eta, $secs_eta] = [intdiv($eta, 3600), intdiv($eta, 60) % 60, $eta % 60];
        [$hours, $mins, $secs] = [intdiv($time_elapsed, 3600), intdiv($time_elapsed, 60) % 60, $time_elapsed % 60];

        return " (" . sprintf('%02d:%02d:%02d', $hours, $mins, $secs) . "/" . sprintf('%02d:%02d:%02d', $hours_eta, $mins_eta, $secs_eta) . ")";
    }

    /**
     * Increases the progressbar by one step. Triggers the update function automatically if not disabled.
     * @param bool Defines if the update function should be trigger on each step
     * @return void
     */
    public function step(bool $autoupdate = true): void
    {
        if ($this->value < $this->initialmax) {
            $this->value++;
        } else {
            //Show only warning because this is not critical 
            trigger_error("Value cannot be increased over initial max at line " . __LINE__, E_USER_WARNING);
        }
        if ($autoupdate) {
            $this->update();
        }
    }

    /**
     * Increases the progressbar by the defined step.
     * Triggers the update function automatically if not disabled.
     * 
     * @param float $step Step size
     * @param bool Defines if the update function should be trigger on each step
     * @return void
     */
    public function stepBy(float $step, bool $autoupdate = true): void
    {
        //var_dump();exit;
        if ($step > 0) {
            if ($step <= abs($this->initialmax - $this->value)) {
                $this->value += $step;
            } else {
                throw new ProgressBarException("Step cannot be greater than the initial max!");
            }
        } else {
            throw new ProgressBarException("Step must be positive number and it can't be a zero!");
        }

        if ($autoupdate) {
            $this->update();
        }
    }

    /**
     * Sets the progressbar to the defined step. Triggers the update function automatically if not disabled.
     * @param $target Target step
     * @param bool Defines if the update function should be trigger on each step
     * @return void
     */
    public function stepTo(float $target, bool $autoupdate = true): void
    {
        if ($target >= 0) {
            if ($target <= $this->initialmax) {
                $this->value = $target;
            } else {
                throw new ProgressBarException("Invalid target value given, target cannot be greater than initial max!");
            }
        } else {
            throw new ProgressBarException("Target cannot be below zero!");
        }
        if ($autoupdate) {
            $this->update();
        }
    }

    /**
     * Terminates the progressbar and resets the object.
     * @return void
     */
    public function terminateProgressbar(): void
    {
        $this->resetProgressbar();
        echo "\033[1K"; //Clear the row
    }

    /**
     * Enables pause on the progressbar
     * @return void
     */
    public function pauseProgressbar(): void
    {
        if (!$this->pause) {
            $this->pause = true;
            $this->update();
        } else {
            trigger_error("Progressbar cannot be paused at line " . __LINE__ . ", because it is already paused!", E_USER_NOTICE);
        }
    }

    /**
     * Resets the whole progressbar object
     * @return void
     */
    public function resetProgressbar(): void
    {
        $this->value = 0;
        unset($this->initialmax);
        unset($this->style);
    }

    /**
     * Returns the current progressbar value
     * @return float
     */
    public function getValue(): float
    {
        return floatval($this->value);
    }

    /**
     * Gets the initial max of the progressbar object
     * @return float
     */
    public function getInitialMax(): float
    {
        return floatval($this->initialmax);
    }

    /**
     * Redraw the progressbar to the CLI
     * @return void
     */
    public function update(): void
    {
        if ($this->pause) {
            echo ("\033[1K\r{$this->style->getColor()}{$this->constructProgressbar()}\e[1m {$this->constructIterationString()} [PAUSED]\e[0m");
            $this->pause = false; //Pause will be disabled after the first execution.
        } else {
            echo ("\r{$this->style->getColor()}{$this->constructProgressbar()}\e[1m {$this->constructIterationString()}{$this->constructTimeString()}\e[0m");
        }
    }
}
