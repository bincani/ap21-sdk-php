# Returns

SDK Class: `lib/Person/Orders/Returns.php` (extends Person/Orders)

## POST Create Return
```
POST /Persons/{PersonID}/Orders/{OrderID}/Returns?countryCode={cc}
Content-type: text/xml
Accept: version_2.0
```

## GET Return Status
```
GET /Persons/{personId}/Orders/{orderId}/Returns/{returnId}?countryCode={cc}
Content-type: text/xml
Accept: version_2.0
```

## DELETE Return (reverse exchange order)
```
DELETE /Returns/{returnId}?CountryCode={cc}
Content-type: text/xml
Accept: version_2.0
```
Only works when return status=9 (Return Merged) and original status=6 (Exchange).

## ReturnOrder Fields
| Field | Format | Max | POST | DELETE | Comment |
|-------|--------|-----|------|--------|---------|
| Id | Integer | 22 | No | Yes | Return order Id |
| OrderNumber | Alpha | 30 | Optional | No | Auto-generated if blank |
| OrigOrderNum | Alpha | 30 | Yes | No | Original web order number |
| PersonId | Integer | 22 | Yes | No | |
| ReturnAuthorisationNumber | Integer | 22 | No | No | GET only - exchange order number |
| StockUpdate | True/False | | Optional | No | False = auto-finalise (no stock return to warehouse) |

## OrderDetail (Return Items)
| Field | Format | Max | POST | Comment |
|-------|--------|-----|------|---------|
| SkuId | Integer | 22 | Yes | Must be on original order |
| Quantity | Num(22,2) | 22 | Yes | **Negative** (e.g. -2) |
| ReturnReasonId | Integer | 22 | Yes | From ReferenceTypeId=272 |
| ReturnReasonNotes | Alpha | 250 | Optional | |
| Carrier | Alpha | 12 | Optional | Return carrier name |
| ConNote | Alpha | 50 | Optional | Return tracking number |

## Optional Payments (for refund at return time, v4.0)
```xml
<Payments>
  <PaymentDetail>
    <MerchantId>Refundid</MerchantId>
    <CardType>Refundid</CardType>
    <Stan>0938</Stan>
    <Origin>CreditCard</Origin>
    <Amount>-80.00</Amount>
    <Reference>Refundid TEST 0938</Reference>
  </PaymentDetail>
</Payments>
```

## POST Example
```xml
<ReturnOrder>
  <OrderNumber>2017080706R</OrderNumber>
  <OrigOrderNum>20170807-06</OrigOrderNum>
  <PersonId>1241</PersonId>
  <StockUpdate>True</StockUpdate>
  <OrderDetails>
    <OrderDetail>
      <SkuId>1784</SkuId>
      <Quantity>-1</Quantity>
      <ReturnReasonId>10365</ReturnReasonId>
      <Carrier>EMS</Carrier>
      <ConNote>ABC1234</ConNote>
    </OrderDetail>
  </OrderDetails>
</ReturnOrder>
```

## POST Error Codes
| HTTP | Code | Text |
|------|------|------|
| 403 | 5052 | Order number already exists |
| 403 | 5085 | Return reason id invalid |
| 403 | 5133 | Original order number not valid |
| 403 | 5135 | Person ID missing |
| 403 | 5136 | Person ID URL/payload mismatch |
| 403 | 5138 | Original order number missing |
| 403 | 5139 | SKU not found on original order |
| 403 | 5140 | Quantities must be negative |
| 403 | 5141 | ConNote too long |
| 403 | 5142 | Carrier name too long |
| 403 | 5143 | Quantities missing or zero |
| 403 | 5211 | Invalid StockUpdate value |

## DELETE Order Status Codes
| Status | Value | Can Delete? |
|--------|-------|-------------|
| 0 | Pending | No - not imported |
| 5 | Completed | No - already finalised |
| 9 | Return Merged | **Yes** |
| 10 | Return Received | No - already received |

## SDK Notes
- Returns extends Person/Orders, reuses parent processEntity/processCollection
- Root element is `<ReturnOrder>` not `<Order>`
- DELETE endpoint uses `/Returns/{id}` (not nested under Person/Orders)
