<?php
/**
 * class CustomDataTemplate
 *
 * Retrieves custom data template definitions for products.
 * GET /Products/customdatatemplates?countryCode={cc}
 * GET /Products/customdatatemplates/{templateName}?countryCode={cc}
 */

namespace PHPAP21\Product;

use PHPAP21\Product as Product;

class CustomDataTemplate extends Product
{
    protected $resourceKey = 'CustomDataTemplate';

    public $countEnabled = false;

    /**
     * pluralizeKey
     *
     * API response root element is <CustomDataTemplates>
     *
     * @return string
     */
    public function pluralizeKey()
    {
        return 'CustomDataTemplates';
    }

    /**
     * processResponse
     *
     * @param SimpleXMLElement $xml
     * @param string $dataKey
     * @return array
     */
    public function processResponse($xml, $dataKey = null)
    {
        if (empty($xml)) {
            return [];
        }

        $templates = [];
        // Collection: <CustomDataTemplates><CustomDataTemplate>...</CustomDataTemplate>...</CustomDataTemplates>
        if (strcasecmp($this->pluralizeKey(), $xml->getName()) === 0) {
            foreach ($xml->children() as $template) {
                $templates[] = $this->parseTemplate($template);
            }
        }
        // Single: <CustomDataTemplate>...</CustomDataTemplate>
        else {
            $templates[] = $this->parseTemplate($xml);
        }

        return $templates;
    }

    /**
     * parseTemplate
     *
     * @param SimpleXMLElement $template
     * @return array
     */
    protected function parseTemplate($template)
    {
        $fields = [];
        if (isset($template->Fields)) {
            foreach ($template->Fields->children() as $field) {
                $fieldData = [
                    'name' => (string)($field['Name'] ?? $field->Name ?? ''),
                    'type' => (string)($field['Type'] ?? $field->Type ?? ''),
                ];
                // List values if present
                if (isset($field->ListValues)) {
                    $values = [];
                    foreach ($field->ListValues->children() as $v) {
                        $val = trim((string)$v);
                        if ($val !== '') {
                            $values[] = $val;
                        }
                    }
                    $fieldData['listValues'] = $values;
                }
                $fields[] = $fieldData;
            }
        }

        return [
            'name'     => (string)($template['Name'] ?? $template->Name ?? ''),
            'sequence' => (string)($template['Sequence'] ?? $template->Sequence ?? ''),
            'fields'   => $fields,
        ];
    }
}
