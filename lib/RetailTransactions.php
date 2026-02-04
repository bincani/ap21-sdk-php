<?php
/**
 * class RetailTransactions
 */

namespace PHPAP21;

use PHPAP21\Exception\ApiException;

class RetailTransactions extends HTTPXMLResource
{
    protected $resourceKey = 'retailtransactions';

    protected $transactions = [];
    protected $totalTransactions = 0;

    /**
     * pluralizeKey
     *
     * API response root element is <Transactions>, not <retailtransactionss>
     *
     * @return string
     */
    public function pluralizeKey()
    {
        return 'Transactions';
    }

    /**
     * processResponse
     *
     * @param SimpleXMLElement $xml
     * @param string $dataKey
     * @return array
     */
    public function processResponse($xml, $dataKey = null) {

        if (empty($xml)) {
            if (in_array($this->getMethod(), ['GET'])) {
                $message = sprintf("%s->no response for method %s", __METHOD__, $this->getMethod());
                throw new ApiException($message, CurlRequest::$lastHttpCode);
            }
            else {
                return '';
            }
        }

        // sanity check
        if ($dataKey && strcasecmp($dataKey, $xml->getName()) !== 0) {
            throw new \Exception(
                sprintf("invalid response %s! expecting %s", $xml->getName(), $dataKey)
            );
        }

        // process collection or single entity
        if (strcasecmp($this->pluralizeKey(), $xml->getName()) === 0) {
            $att = $xml->attributes();
            if (isset($att['TotalRows'])) {
                $this->totalTransactions = (int)$att['TotalRows'];
            }
            return $this->processCollection($xml);
        }
        else {
            return $this->processEntity($xml);
        }
    }

    /**
     * processEntity
     *
     * Parse a single <Transaction> element
     *
     * @param SimpleXMLElement $transaction
     * @return array
     */
    protected function processEntity($transaction) {
        // parse details
        $details = [];
        if (isset($transaction->Details)) {
            foreach ($transaction->Details->children() as $detail) {
                $discounts = [];
                if (isset($detail->Discounts)) {
                    foreach ($detail->Discounts->children() as $discount) {
                        $discounts[] = [
                            'id'         => (string)$discount->Id,
                            'sequence'   => (string)$discount->Sequence,
                            'type'       => (string)$discount->Type,
                            'reason'     => (string)$discount->Reason,
                            'amount'     => (string)$discount->Amount,
                            'percentage' => (string)$discount->Percentage,
                        ];
                    }
                }
                $details[] = [
                    'id'          => (string)$detail->Id,
                    'sequence'    => (string)$detail->Sequence,
                    'productCode' => (string)$detail->ProductCode,
                    'productName' => (string)$detail->ProductName,
                    'colourCode'  => (string)$detail->ColourCode,
                    'colourName'  => (string)$detail->ColourName,
                    'sizeCode'    => (string)$detail->SizeCode,
                    'quantity'    => (string)$detail->Quantity,
                    'price'       => (string)$detail->Price,
                    'value'       => (string)$detail->Value,
                    'taxPercentage' => (string)$detail->TaxPercentage,
                    'discounts'   => $discounts,
                ];
            }
        }

        return [
            'id'          => (string)$transaction->Id,
            'rowNumber'   => (string)$transaction->RowNumber,
            'number'      => (string)$transaction->Number,
            'orderNumber' => (string)$transaction->OrderNumber,
            'type'        => (string)$transaction->Type,
            'saleDate'    => (string)$transaction->SaleDate,
            'storeCode'   => (string)$transaction->StoreCode,
            'storeName'   => (string)$transaction->StoreName,
            'currency'    => isset($transaction->Currency) ? [
                'code'   => (string)$transaction->Currency->Code,
                'format' => (string)$transaction->Currency->Format,
            ] : null,
            'carrier'     => (string)$transaction->Carrier,
            'carrierUrl'  => (string)$transaction->CarrierUrl,
            'conNote'     => (string)$transaction->ConNote,
            'serviceType' => (string)$transaction->ServiceType,
            'details'     => $details,
        ];
    }

    /**
     * processCollection
     *
     * @param SimpleXMLElement $xml
     * @return array
     */
    protected function processCollection($xml) {
        foreach ($xml->children() as $transaction) {
            $id = (string)$transaction->Id;
            $this->transactions[$id] = $this->processEntity($transaction);
        }
        return $this->transactions;
    }

    /**
     * getTotalTransactions
     *
     * @return int
     */
    public function getTotalTransactions(): int {
        return $this->totalTransactions;
    }
}
