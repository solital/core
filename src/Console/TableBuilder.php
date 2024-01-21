<?php

namespace Solital\Core\Console;

use Codedungeon\PHPCliColors\Color;

class TableBuilder
{
    /**
     * Get an array of rendered table rows
     *
     * @param array|array[]|object[] $tableData Array of arrays/objects representing the table rows and corresponding data
     * @param array|string[] $headers Array of table header strings
     */
    public function getTableRows(array $tableData, array $headers)
    {
        $lines = [];
        $columnWidths = $this->calculateColumnWidths($tableData);
        $rowSeparator = $this->renderRowSeparator($columnWidths);

        $lines[] = $rowSeparator; // Add the top border of the table
        $lines[] = $this->renderHeader($headers, $columnWidths); // Add the rendered table header
        $lines[] = $rowSeparator; // Add the separator under the header

        foreach ($tableData as $item) {
            $lines[] = $this->renderRow($item, $columnWidths); // Add rendered data rows
        }

        $lines[] = $rowSeparator; // Add bottom border of the table

        $this->echoTableRows($lines);
        #return $lines;
    }

    /**
     * @param array $data
     * 
     * @return void
     */
    public static function formattedArray(array $data, int $space = 30, bool $margin = false): void
    {
        foreach ($data as $name => $work) {
            if ($margin == true) {
                echo "  "  . Color::light_green() . str_pad($name, $space) . Color::white() . $work . PHP_EOL;
            } else {
                echo Color::light_green() . str_pad($name, $space) . Color::white() . $work . PHP_EOL;
            }
        }
    }

    /**
     * Echo the rendered table rows
     *
     * @param array|string[] $renderedTableRows List of rendered table row strings
     */
    private function echoTableRows(array $renderedTableRows): void
    {
        $eol = \PHP_EOL;
        $green = '[32m'; // TODO: Make this configurable
        $defaultColor = '[39m';

        $i = 0;
        foreach ($renderedTableRows as $rowStr) {
            $isHeaderRow = $i === 1;
            $rowColor = $isHeaderRow ? $green : $defaultColor; // Make the header row green
            echo "\033{$rowColor}{$rowStr}{$eol}";
            $i++;
        }
    }

    /**
     * Set the table column widths using the greatest width checking the header and data values
     *
     * @param array|array[]|object[] $tableData Array of table row data (row data may be an array or object)
     * @return array List of column widths uses column name as the key
     */
    private function calculateColumnWidths(array $tableData): array
    {
        $columnWidths = [];

        foreach ($tableData as $rowData) {
            foreach ($rowData as $column => $data) {
                if ($data == null || empty($data)) {
                    $data = '-';
                }

                // Set the column width using the largest length be that from the data or the column header name
                $columnWidths[$column] = max($columnWidths[$column] ?? strlen($column), strlen($data));
            }
        }

        return $columnWidths;
    }

    /**
     * Create horizontal row seperator string
     *
     * @param array $columnWidths List of column widths
     * @param string $rowColumnIntersect Text character to be used at the intersection of rows and columns, defaults to '+'
     * @return string Horizontal row separator
     */
    private function renderRowSeparator(array $columnWidths, string $rowColumnIntersect = '+'): string
    {
        $spacesAroundData = 2;
        $separatorStr = $rowColumnIntersect;

        foreach ($columnWidths as $width) {
            $separatorStr .= $this->strRepeat('-', $width + $spacesAroundData);
            $separatorStr .= $rowColumnIntersect;
        }

        return $separatorStr;
    }

    /**
     * Append a string to itself a given number of times
     *
     * @param string $str String to be repeated
     * @param int $count The number of times to repeat the string
     * @return string The repeated string
     */
    private function strRepeat(string $str, int $count): string
    {
        $str2 = '';
        for ($i = $count; $i > 0; $i--) {
            $str2 .= $str;
        }

        return $str2;
    }

    /**
     * Render the table header
     *
     * @param array $headers List of table headers
     * @param array $columnSize List of column widths
     * @return string Rendered table header
     */
    private function renderHeader(array $headers, array $columnSize): string
    {
        return $this->renderRow($headers, $columnSize, STR_PAD_BOTH);
    }

    /**
     * Create table row strings
     *
     * @param array|object $rowData Array or Object of key value row data
     * @param array $columnSize List of column widths
     * @return string Rendered table row
     */
    private function renderRow($rowData, array $columnSize, int $padType = STR_PAD_RIGHT): string
    {
        $rowString = '';

        foreach ($rowData as $column => $data) {
            if ($data == null || empty($data)) {
                $data = '-';
            }
            // This accounts for headers not having the corresponding column keys as the data itself is the column key
            $rowWidth = $columnSize[$column] ?? $columnSize[$data];
            $rowString .= '| ' . str_pad($data, $rowWidth, ' ', $padType) . ' ';

            // Add the outer right side of the table if on last column
            $headers = array_keys($rowData);
            // If two columns have the same data, this prevents incorrectly adding the '|'
            $rowString .= end($headers) === $column ? '|' : '';
        }

        return $rowString;
    }

    /**
     * Add two numeric values
     *
     * @param int $a Value to be added
     * @param int $b Value to be added
     * @return int The summed value of the arguments
     */
    private function sum(?int $a, int $b): int
    {
        return ($a ?? 0) + $b;
    }
}
