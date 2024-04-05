<?php

declare(strict_types=1);

namespace Solital\Core\Console;

use Solital\Core\Console\Output\ColorsEnum;

class Table
{
    /**
     * @var array
     */
    private array $cellStyle = [];

    /**
     * @var array
     */
    private array $rows = [];

    /**
     * @var array|object
     */
    private array|object $borderStyle = [];

    /**
     * @var array
     */
    private array $columnCellStyle = [];

    /**
     * @var array
     */
    private array $headerStyle = [
        ColorsEnum::BOLD->value,
    ];

    /**
     * @var array
     */
    private array $chars = [
        'top'          => '═',
        'top-mid'      => '╤',
        'top-left'     => '╔',
        'top-right'    => '╗',
        'bottom'       => '═',
        'bottom-mid'   => '╧',
        'bottom-left'  => '╚',
        'bottom-right' => '╝',
        'left'         => '║',
        'left-mid'     => '╟',
        'mid'          => '─',
        'mid-mid'      => '┼',
        'right'        => '║',
        'right-mid'    => '╢',
        'middle'       => '│ ',
    ];

    public function __toString()
    {
        return $this->render();
    }

    /**
     * Formatted lines with some information
     * 
     * @param array $data
     * @param int $space
     * @param bool $margin
     * 
     * @return void
     */
    public static function formattedRowData(array $data, int $space = 30, bool $margin = false): void
    {
        ($margin == true) ? $margin_string = "  " : $margin_string = "";

        foreach ($data as $name => $value) {
            $value = self::validateData($value);
            echo $margin_string . ColorsEnum::GREEN->value . str_pad($name, $space) . ColorsEnum::RESET->value . $value . PHP_EOL;
        }
    }

    /**
     * @param ColorsEnum ...$format
     * 
     * @return self
     */
    public function setHeaderStyle(ColorsEnum ...$format): self
    {
        $styles = [];

        foreach ($format as $style) {
            $styles[] = $style->value;
        }

        $this->headerStyle = $styles;
        return $this;
    }

    /**
     * @param ColorsEnum ...$format
     * 
     * @return self
     */
    public function setCellStyle(ColorsEnum ...$format): self
    {
        $styles = [];

        foreach ($format as $style) {
            $styles[] = $style->value;
        }

        $this->cellStyle = $styles;
        return $this;
    }

    /**
     * @param ColorsEnum ...$format
     * 
     * @return self
     */
    public function setBorderStyle(ColorsEnum ...$format): self
    {
        $styles = [];

        foreach ($format as $style) {
            $styles[] = $style->value;
        }

        $this->borderStyle = $styles;
        return $this;
    }

    /**
     * @param string $column
     * @param ColorsEnum ...$format
     * 
     * @return self
     */
    public function setColumnCellStyle(string $column, ColorsEnum ...$format): self
    {
        $styles = [];

        foreach ($format as $style) {
            $styles[] = $style->value;
        }

        $this->columnCellStyle[$column] = $styles;
        return $this;
    }

    /**
     * Generate dynamic rows with header and values
     *
     * @param array $header
     * @param array $rows
     * 
     * @return self
     */
    public function dynamicRows(array $header, array $rows): self
    {
        foreach ($rows as $row) {
            $full_values = array_combine($header, $row);
            $this->row($full_values);
        }

        return $this;
    }

    /**
     * @param array $assoc
     * 
     * @return self
     */
    public function row(array $assoc): self
    {
        $row = [];

        foreach ($assoc as $key => $value) {
            $value = self::validateData($value);
            $key = trim((string)$key);
            $row[$key] = trim($value);
        }

        $this->rows[] = $row;
        return $this;
    }

    /**
     * @return string
     */
    public function render(): string
    {
        $columnLengths = [];
        $headerData = [];

        foreach ($this->rows as $row) {
            $keys = array_keys($row);

            foreach ($keys as $key) {
                if (isset($headerData[$key])) {
                    continue;
                }

                $headerData[$key] = $key;
                $columnLengths[$key] = $this->verifyStrlen($key);
            }
        }

        foreach ($this->rows as $row) {
            foreach ($headerData as $column) {
                $len = max($columnLengths[$column], $this->verifyStrlen($row[$column]));

                if ($len % 2 !== 0) {
                    ++$len;
                }

                $columnLengths[$column] = $len;
            }
        }

        foreach ($columnLengths as &$length) {
            $length += 4;
        }

        $res = $this->getTableTopContent($columnLengths)
            . $this->getFormattedRowContent($headerData, $columnLengths, implode('', $this->headerStyle), true)
            . $this->getTableSeparatorContent($columnLengths);

        foreach ($this->rows as $row) {
            foreach ($headerData as $column) {
                if (!isset($row[$column])) {
                    $row[$column] = '[NULL]';
                }
            }

            $res .= $this->getFormattedRowContent($row, $columnLengths, implode(';', $this->cellStyle));
        }

        return $res . $this->getTableBottomContent($columnLengths);
    }

    /**
     * @param array $data
     * @param array $lengths
     * @param string $format
     * @param bool $isHeader
     * 
     * @return string
     */
    private function getFormattedRowContent(
        array $data,
        array $lengths,
        string $format = '',
        bool $isHeader = false
    ): string {
        $res = $this->getChar('left') . ' ';
        $rows = [];
        $rows_formatted = [];

        foreach ($data as $key => $value) {
            $customFormat = '';
            $value = ' ' . $value;
            $len = $this->verifyStrlen($value) - $lengths[$key] + 1;

            if ($isHeader === false && isset($this->columnCellStyle[$key]) && !empty($this->columnCellStyle[$key])) {
                $customFormat = implode("", $this->columnCellStyle[$key]);
            }

            $rows[] = ($format !== '' ? $format : '')
                . ($customFormat !== '' ? $customFormat : '')
                . $value
                . ($format !== '' || $customFormat !== '' ? ColorsEnum::RESET->value : '')
                . str_repeat(' ', (int)abs($len));
        }

        /* if ($isHeader == true) {
            foreach ($rows as $row) {
                if (str_contains($row, "\033") && str_contains($row, "m;")) {
                    $rows_formatted[] = str_replace("m;", "m", $row);
                }
            }
        } */

        /* if (!empty($rows_formatted)) {
            $rows = $rows_formatted;
        } */

        $res .= implode($this->getChar('middle'), $rows);
        return $res . $this->getChar('right') . PHP_EOL;
    }

    /**
     * @param array $lengths
     * 
     * @return string
     */
    private function getTableTopContent(array $lengths): string
    {
        $res = $this->getChar('top-left');
        $rows = [];

        foreach ($lengths as $length) {
            $rows[] = $this->getChar('top', $length);
        }

        $res .= implode($this->getChar('top-mid'), $rows);
        return  $res . $this->getChar('top-right') . PHP_EOL;
    }

    /**
     * @param array $lengths
     * 
     * @return string
     */
    private function getTableBottomContent(array $lengths): string
    {
        $res = $this->getChar('bottom-left');
        $rows = [];

        foreach ($lengths as $length) {
            $rows[] = $this->getChar('bottom', $length);
        }

        $res .= implode($this->getChar('bottom-mid'), $rows);
        return $res . $this->getChar('bottom-right') . PHP_EOL;
    }

    /**
     * @param array $lengths
     * 
     * @return string
     */
    private function getTableSeparatorContent(array $lengths): string
    {
        $res = $this->getChar('left-mid');
        $rows = [];

        foreach ($lengths as $length) {
            $rows[] = $this->getChar('mid', $length);
        }

        $res .= implode($this->getChar('mid-mid'), $rows);
        return $res . $this->getChar('right-mid') . PHP_EOL;
    }

    /**
     * @param string $char
     * @param int $len
     * 
     * @return string
     */
    private function getChar(string $char, int $len = 1): string
    {
        //dd($this->borderStyle);
        if (!isset($this->chars[$char])) {
            return '';
        }

        //$res = (empty($this->borderStyle) ? '' : "\e[" . \implode(";", $this->borderStyle) . "m");
        $res = (empty($this->borderStyle) ? '' : implode(";", $this->borderStyle));

        if ($len === 1) {
            $res .= $this->chars[$char];;
        } else {
            $res .= str_repeat($this->chars[$char], $len);
        }

        $res .= empty($this->borderStyle) ? '' : ColorsEnum::RESET->value;
        return $res;
    }

    /**
     * @param string $str
     * 
     * @return int
     */
    private function verifyStrlen(string $str): int
    {
        if (!function_exists('mb_strlen')) {
            return strlen($str);
        }

        return mb_strlen($str);
    }

    /**
     * @param mixed $value
     * 
     * @return mixed
     */
    private static function validateData(mixed $value): mixed
    {
        if (!is_string($value)) {
            if (is_object($value)) {
                $value = get_class($value);
            }

            if (is_resource($value)) {
                $value = '[RESOURCE]';
            }

            if (is_callable($value)) {
                $value = '[CALLABLE]';
            }

            if (is_null($value)) {
                $value = '[NULL]';
            }

            if (is_bool($value)) {
                $value = $value === false ? '[FALSE]' : '[TRUE]';
            } else {
                $value = (string)$value;
            }
        }

        return $value;
    }
}
