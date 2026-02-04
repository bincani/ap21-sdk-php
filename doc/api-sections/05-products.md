# Products

SDK Class: `lib/Product.php` (HTTPXMLResource)

## GET All Products
```
GET /Products/?countryCode={cc}&startRow={n}&pageRows={n}
Content-type: text/xml
Accept: version_2.0
```

## GET Single Product
```
GET /Products/{productId}/?countryCode={cc}
```

## GET Products Updated Since
```
GET /Products/?countryCode={cc}&updatedAfter={timestamp}
```

## Pagination
- Uses `startRow`/`pageRows` parameters (NOT Link headers)
- Response has `TotalRows` attribute: `<Products Type="Array" TotalRows="498">`
- Default pageRows in SDK: 500
- **No /count endpoint** - uses TotalRows from response

## Product Fields
| Field | Format | Max | GET | Version | Comment |
|-------|--------|-----|-----|---------|---------|
| Id | Integer | 22 | Yes | 1.0 | Unique Product Id (style idx) |
| Code | Alpha | 10 | Yes | 1.0 | Product Code |
| Name | Alpha | 30 | Yes | 1.0 | Product Name |
| UpdateTimeStamp | DateTime | | Yes | 1.0 | Last update timestamp |
| SizeRange | Alpha | | Yes | 2.0 | Size range name |
| Season | Alpha | 10 | Yes | 2.0 | Season code |
| SeasonName | Alpha | 30 | Yes | 2.0 | Season name |
| Brand | Alpha | 10 | Yes | 2.0 | Brand code |
| BrandName | Alpha | 30 | Yes | 2.0 | Brand name |
| Division | Alpha | 10 | Yes | 2.0 | Division code |
| DivisionName | Alpha | 30 | Yes | 2.0 | Division name |
| Description | Alpha | 4000 | Yes | 1.0 | Product description |
| Weight | Decimal | | Yes | 2.0 | Product weight |

## Colour/SKU Sub-elements
| Field | Format | GET | Comment |
|-------|--------|-----|---------|
| Colours > Colour | | Yes | Array of colours |
| Colour > Id | Integer | Yes | Colour Id |
| Colour > Code | Alpha | Yes | Colour Code |
| Colour > Name | Alpha | Yes | Colour Name |
| Colour > Skus > Sku | | Yes | Array of SKUs per colour |
| Sku > Id | Integer | Yes | SKU Id |
| Sku > Barcode | Alpha(50) | Yes | Primary barcode |
| Sku > SizeCode | Alpha(7) | Yes | Size code |
| Sku > Prices > Price | | Yes | Price with CurrencyCode attribute |
| Sku > FuturePrice | | Yes | Future price if scheduled |

## References Sub-element
```xml
<References Type="Array">
  <Reference>
    <ReferenceTypeId>1</ReferenceTypeId>
    <ReferenceTypeCode>Carrier</ReferenceTypeCode>
    <ReferenceId>1156</ReferenceId>
    <ReferenceCode>AUS</ReferenceCode>
    <ReferenceName>Australia Post</ReferenceName>
  </Reference>
</References>
```

## CustomData Sub-element
```xml
<CustomData>
  <Card Name="Web Data" Sequence="1">
    <CustomField Name="WebCategory" Value="Dresses" />
    <CustomField Name="WebSubCategory" Value="Maxi" />
  </Card>
</CustomData>
```

## Response Example (abbreviated)
```xml
<Products Type="Array" TotalRows="498" PageStartRow="1" PageRows="500">
  <Product>
    <Id>6184</Id>
    <Code>AUTOITEM1</Code>
    <Name>Automation - Style Only</Name>
    <UpdateTimeStamp>2021-03-15T10:30:00</UpdateTimeStamp>
    <Colours Type="Array">
      <Colour>
        <Id>7109</Id>
        <Code>-</Code>
        <Name>-</Name>
        <Skus Type="Array">
          <Sku>
            <Id>17081</Id>
            <Barcode>9312345111111</Barcode>
            <SizeCode>-</SizeCode>
            <Prices>
              <Price CurrencyCode="AUD">45.90</Price>
            </Prices>
          </Sku>
        </Skus>
      </Colour>
    </Colours>
  </Product>
</Products>
```

## Errors
| HTTP | Code | Text |
|------|------|------|
| 404 | | Not found |
| 403 | 5015 | Database error (often bad params) |

## SDK Notes
- `countEnabled = false` (Products doesn't support /count endpoint)
- Default pagination: startRow=1, pageRows=500
- Tracks: totalProducts, totalPages, currentPage
- processEntity() parses full product hierarchy: colours > SKUs > prices
- processCustomData() parses Card/CustomField elements

## Child Resources

### Product/FuturePrice
```
GET /Products/{productId}/FuturePrice/?countryCode={cc}
```
SDK Class: `lib/Product/FuturePrice.php`

### Not Implemented
- `GET /ProductsSimple/?countryCode={cc}` - Simplified product list
- `GET /ProductNotes/{id}/?countryCode={cc}` - Product notes
- `GET /ProductColourNotes/{id}/?countryCode={cc}` - Product colour notes
- `GET /Products/{id}/YouMightLikeProducts/?countryCode={cc}` - Related products
- `GET /Products/{id}/UpdateTimeStamp/?countryCode={cc}` - Just the timestamp
