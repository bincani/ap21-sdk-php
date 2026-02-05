# Freight Options

**SDK Status: NOT IMPLEMENTED**

## GET Freight Options
```
GET /FreightOptions?countryCode={cc}
Content-type: text/xml
Accept: version_4.0
```

## Fields
| Field | Format | GET | Comment |
|-------|--------|-----|---------|
| Id | Integer | Yes | Freight option Id (pass in order submission) |
| Name | Alpha | Yes | Description |
| Carrier > Id | Integer | Yes | AP21 carrier id |
| Carrier > Code | Alpha | Yes | Carrier code |
| Carrier > Name | Alpha | Yes | Carrier name |
| Carrier > Url | Alpha | Yes | Tracking URL |
| ServiceType > Id | Integer | Yes | Service type id |
| ServiceType > Code | Alpha | Yes | Service type code |
| ServiceType > Name | Alpha | Yes | Service type description |
| SLA > Id | Integer | Yes | SLA id |
| SLA > Code | Alpha | Yes | SLA code |
| SLA > Name | Alpha | Yes | SLA description |

## Response Example
```xml
<FreightOptions Type="Array">
  <FreightOption>
    <Id>884</Id>
    <Name>eParcel Standard Signature + Optional ATL</Name>
    <Carrier>
      <Id>18936</Id>
      <Code>AUS</Code>
      <Name>Australia Post</Name>
      <Url />
    </Carrier>
    <ServiceType>
      <Id>18934</Id>
      <Code>50S1</Code>
      <Name>eParcel Standard</Name>
    </ServiceType>
    <SLA>
      <Id>19252</Id>
      <Code>Std</Code>
      <Name>Standard SLA's</Name>
    </SLA>
  </FreightOption>
</FreightOptions>
```

## Implementation Notes
- Simple GET-only, read-only resource
- Requires `Accept: version_4.0`
- No pagination needed
- Used when submitting orders with `<SelectedFreightOption><Id>884</Id></SelectedFreightOption>`
