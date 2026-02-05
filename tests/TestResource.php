<?php
/**
 * class SimpleResource
 */

namespace PHPAP21;

class TestResource extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Ap21SDK $ap21;
     */
    public static $ap21;

    /**
     * setUpBeforeClass
     */
    public static function setUpBeforeClass(): void
    {
        $config = array(
            'ApiUrl'       => getenv('ApiUrl'),
            'ApiUser'      => getenv('ApiUser'),
            'ApiPassword'  => getenv('ApiPassword'),
            'CountryCode'  => getenv('CountryCode')
        );

        self::$ap21 = Ap21SDK::config($config);
        //Ap21SDK::checkApiCallLimit();
    }

    /**
     * tearDownAfterClass
     */
    public static function tearDownAfterClass(): void
    {
        self::$ap21 = null;
    }

    /**
     * Output a response data summary to STDERR so it displays during test runs.
     *
     * @param string $label  Test/resource label
     * @param mixed  $result API response data
     */
    protected function summary(string $label, $result): void
    {
        $lines = [];
        $lines[] = sprintf("  ── %s ──", $label);

        if (is_array($result)) {
            $count = count($result);
            $lines[] = sprintf("  records: %d", $count);

            if ($count > 0) {
                $first = reset($result);
                $last = end($result);

                $lines[] = sprintf("  fields:  %s", implode(', ', array_keys($this->toArray($first))));
                $lines[] = "  ┌─ first record:";
                $lines = array_merge($lines, $this->formatRecord($first));

                if ($count > 1) {
                    $lines[] = "  ├─ last record:";
                    $lines = array_merge($lines, $this->formatRecord($last));
                }
            }
        } elseif (is_object($result)) {
            $vars = get_object_vars($result);
            $lines[] = sprintf("  type:    object");
            $lines[] = sprintf("  fields:  %s", implode(', ', array_keys($vars)));
            $lines[] = "  ┌─ record:";
            $lines = array_merge($lines, $this->formatRecord($result));
        } elseif (is_string($result) || is_numeric($result)) {
            $lines[] = sprintf("  value:   %s", $result);
        } else {
            $lines[] = sprintf("  type:    %s", gettype($result));
        }

        fwrite(STDERR, "\n" . implode("\n", $lines) . "\n");
    }

    /**
     * Convert a record (array or object) to an associative array.
     */
    private function toArray($record): array
    {
        if (is_array($record)) {
            return $record;
        }
        if (is_object($record)) {
            return get_object_vars($record);
        }
        return [];
    }

    /**
     * Format a single record's key-value pairs for display.
     *
     * @param mixed $record  Array or object
     * @return array  Formatted lines
     */
    private function formatRecord($record): array
    {
        $data = $this->toArray($record);
        $lines = [];
        foreach ($data as $key => $value) {
            $display = $this->formatValue($value);
            $lines[] = sprintf("  │  %-20s %s", $key . ':', $display);
        }
        return $lines;
    }

    /**
     * Format a value for display, truncating long strings and summarising nested structures.
     */
    private function formatValue($value, int $maxLen = 80): string
    {
        if (is_null($value)) {
            return 'null';
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }
        if (is_string($value)) {
            if (strlen($value) > $maxLen) {
                return '"' . substr($value, 0, $maxLen) . '..."';
            }
            return '"' . $value . '"';
        }
        if (is_array($value)) {
            $count = count($value);
            if ($count === 0) {
                return '[]';
            }
            // Check if sequential or associative
            if (array_keys($value) === range(0, $count - 1)) {
                return sprintf('[%d items]', $count);
            }
            return sprintf('{%d keys: %s}', $count, implode(', ', array_keys($value)));
        }
        if (is_object($value)) {
            $vars = get_object_vars($value);
            return sprintf('{%s: %d keys}', get_class($value), count($vars));
        }
        return gettype($value);
    }
}