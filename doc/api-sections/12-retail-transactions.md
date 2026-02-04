# Retail Transactions (Sales History)

SDK Class: `lib/RetailTransactions.php` (HTTPXMLResource) - **BROKEN: processResponse has bug**

## GET Sales History for a Person
```
GET /Persons/{personId}/RetailTransactions/?countryCode={cc}&startRow={n}&pageRows={n}&OrderNumber={orderNum}
Content-type: text/xml
Accept: version_2.0
```

## Parameters
| Field | Required | Comment |
|-------|----------|---------|
| personId | Mandatory | Person Id |
| CountryCode | Mandatory | |
| startRow | Optional | Starting position |
| pageRows | Optional | Number of rows |
| OrderNumber | Optional | Filter by web order number |

## Transaction Header Fields
| Field | Format | Max | GET | Comment |
|-------|--------|-----|-----|---------|
| RowNumber | Integer | 10 | Yes | Position in list |
| Id | Integer | 22 | Yes | Transaction identifier (RET_TRANH) |
| Number | Alpha | 30 | Yes | Transaction number (W=web, H=mail, D=docket) |
| OrderNumber | Alpha | 30 | Yes | Web order number (links to original order) |
| Type | Alpha | 30 | Yes | Sale, Layby, Layby Update, Special Order, Special Order Update |
| SaleDate | Date | | Yes | Date sale completed (YYYY-MM-DD) |
| StoreCode | Integer | 3 | Yes | Retail store code |
| StoreName | Alpha | 30 | Yes | Store name |
| Currency > Code | Alpha | 15 | Yes | Currency code (AUD) |
| Currency > Format | Alpha | | Yes | Display format |
| Carrier | Alpha | | Yes | Freight carrier name (eComm only) |
| CarrierUrl | Alpha | | Yes | Carrier tracking URL |
| ConNote | Alpha | | Yes | Consignment note number |
| ServiceType | Alpha | | Yes | Carrier service type |

## Transaction Detail Fields
| Field | Format | Max | GET | Comment |
|-------|--------|-----|-----|---------|
| Id | Integer | 22 | Yes | Detail row identifier (RET_TRAND) |
| Sequence | Integer | 10 | Yes | POS order sequence |
| ProductCode | Alpha | 10 | Yes | |
| ProductName | Alpha | 30 | Yes | |
| ColourCode | Alpha | 21 | Yes | |
| ColourName | Alpha | 61 | Yes | |
| SizeCode | Alpha | 7 | Yes | |
| Quantity | Num(22,2) | 22 | Yes | (negative for returns) |
| Price | Num(22,2) | 22 | Yes | Unit price inc GST before discounts |
| Value | Num(22,2) | 22 | Yes | Total paid inc GST inc discounts |
| TaxPercentage | Num(22,2) | 22 | Yes | |

## Discount Sub-element
| Field | Format | Max | GET | Comment |
|-------|--------|-----|-----|---------|
| Id | Alpha | 22 | Yes | Discount identifier |
| Sequence | Alpha | 22 | Yes | POS sequence |
| Type | Alpha | 50 | Yes | Discount, Promotion, or Loyalty |
| Reason | Alpha | | Yes | Description of discount reason |
| Amount | Num(22,2) | 22 | Yes | Discount value |
| Percentage | Num(22,2) | 22 | Yes | Discount percentage |

## Response Example
```xml
<Transactions Type="Array" TotalRows="15" PageStartRow="0" PageRows="0">
  <Transaction>
    <RowNumber>1</RowNumber>
    <Id>13748</Id>
    <Number>D55500000130</Number>
    <OrderNumber>W12345</OrderNumber>
    <Type>Sale</Type>
    <SaleDate>2011-08-26T00:00:00</SaleDate>
    <StoreCode>555</StoreCode>
    <StoreName>Curtis 2011.2 POS</StoreName>
    <Currency>
      <Code>AUD</Code>
      <Format>#,##0.00 'AUD'</Format>
    </Currency>
    <Carrier>Ipec</Carrier>
    <CarrierUrl>https://online.toll.com.au/trackandtrace/index.jsp</CarrierUrl>
    <ConNote>TND112131231</ConNote>
    <ServiceType>Road</ServiceType>
    <Details Type="Array" TotalRows="1">
      <Detail>
        <Id>15882</Id>
        <Sequence>0</Sequence>
        <ProductCode>AUTOITEM1</ProductCode>
        <ProductName>Automation - 1 Style</ProductName>
        <ColourCode>-</ColourCode>
        <ColourName>-</ColourName>
        <SizeCode>-</SizeCode>
        <Quantity>-1</Quantity>
        <Price>45.9</Price>
        <Value>-45.9</Value>
        <TaxPercentage>12.5</TaxPercentage>
        <Discounts Type="Array" TotalRows="0" />
      </Detail>
    </Details>
  </Transaction>
</Transactions>
```

## Errors
| HTTP | Code | Text |
|------|------|------|
| 404 | | Not found |
| 400 | 5034 | Invalid pagination parameters |

## SDK Notes - ISSUES
- `resourceKey = 'retailtransactions'` (lowercase)
- **processResponse() is broken** - references undefined `$xml` variable, returns empty array
- Needs proper implementation: parse `<Transactions>` > `<Transaction>` > `<Details>` > `<Detail>` > `<Discounts>`
- Accessed as child of Person: `$ap21->Person($id)->RetailTransactions->get()`
- Transactions NOT included: wholesale orders, debtor sales, customer orders (until paid), account payments
