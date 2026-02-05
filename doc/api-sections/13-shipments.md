# Shipments

SDK Classes: `lib/Shipment.php` (minimal), `lib/Person/Shipments.php` (full implementation)

## GET Shipment Details for an Order
```
GET /Persons/{PersonId}/Shipments/{OrderId}?countryCode={cc}
Content-type: text/xml
Accept: version_2.0
```

## Parameters
| Field | Required | Comment |
|-------|----------|---------|
| PersonId | Mandatory | Person Id |
| OrderId | Mandatory | Order Id |
| CountryCode | Mandatory | |

## Shipment Fields
| Field | Format | Max | GET | Comment |
|-------|--------|-----|-----|---------|
| CarrierName | Alpha | 22 | Yes | Name of carrier |
| CarrierUrl | Alpha | 30 | Yes | URL (only if valid ref code in AP21) |
| ConNote | Alpha | 50 | Yes | Consignment note number |
| DespatchDate | Date | | Yes | Date of despatch |

## Contents Sub-element
| Field | Format | GET | Comment |
|-------|--------|-----|---------|
| ProductCode | Alpha | Yes | |
| ColourCode | Alpha | Yes | |
| SizeCode | Alpha | Yes | |
| SkuId | Integer | Yes | |
| Quantity | Num(22,2) | Yes | |

## PackedCartonContents Sub-element
| Field | Format | GET | Comment |
|-------|--------|-----|---------|
| TrackingNumber | Alpha | Yes | Per-carton tracking |
| CartonSSCC | Number | Yes | SSCC for carton |
| ProductCode | Alpha | Yes | |
| ColourCode | Alpha | Yes | |
| SizeCode | Alpha | Yes | |
| SkuId | Integer | Yes | |
| Quantity | Num(22,2) | Yes | Quantity in carton |

## Response Example
```xml
<Shipments Type="Array">
  <Shipment>
    <CarrierName>Australia Post</CarrierName>
    <CarrierUrl>https://AustraliaPost/mypost/track/111Z02129729</CarrierUrl>
    <ConNote>111Z02129729</ConNote>
    <DespatchDate>2023-08-18T00:00:00</DespatchDate>
    <Contents>
      <Content>
        <ProductCode>ADIDASRugbyNZY</ProductCode>
        <ColourCode>RED</ColourCode>
        <SizeCode>2</SizeCode>
        <SkuId>214743</SkuId>
        <Quantity>10</Quantity>
      </Content>
    </Contents>
    <PackedCartonContents>
      <PackedCartonContent>
        <TrackingNumber>1183391740</TrackingNumber>
        <CartonSSCC>00393144421104571740</CartonSSCC>
        <ProductCode>ADIDASRugbyNZY</ProductCode>
        <ColourCode>RED</ColourCode>
        <SizeCode>2</SizeCode>
        <SkuId>2</SkuId>
        <Quantity>2</Quantity>
      </PackedCartonContent>
    </PackedCartonContents>
  </Shipment>
</Shipments>
```

## SDK Notes
- Person/Shipments is fully implemented with processEntity parsing contents and packedCartonContents
- Standalone Shipment class is minimal (inherits defaults, no custom parsing)
- Accessed as: `$ap21->Person($id)->Shipments($orderId)->get()`
