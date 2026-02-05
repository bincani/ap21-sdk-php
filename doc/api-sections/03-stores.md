# Stores

SDK Class: `lib/Store.php` (HTTPXMLResource)

## GET All Stores
```
GET /Stores/?countryCode={cc}
Content-type: text/xml
Accept: version_2.0
```

## GET Single Store
```
GET /Stores/{StoreId}?countryCode={cc}
```

## Fields
| Field | Format | Max | GET | Comment |
|-------|--------|-----|-----|---------|
| StoreId | Integer | 22 | Yes | Store identifier |
| Code | Alpha | 30 | Yes | Store Code |
| StoreNo | Integer | 3 | Yes | Store Number |
| Name | Alpha | 30 | Yes | Retail Store Name |
| Address1 | Alpha | 50 | Yes | 1st line address |
| Address2 | Alpha | 50 | Yes | 2nd line address |
| City | Alpha | 50 | Yes | City |
| State | Alpha | 50 | Yes | State |
| Postcode | Integer | 10 | Yes | Postcode |
| Country | Alpha | 20 | Yes | Country |
| Email | Alpha | 250 | Yes | Email address |

## Response Example
```xml
<Store>
  <StoreId>108864</StoreId>
  <Code>GEORGESTS</Code>
  <StoreNo>100</StoreNo>
  <Name>George St Store</Name>
  <Address1>110 Cremorne St</Address1>
  <Address2>Level 2</Address2>
  <City>Cremorne</City>
  <State>VIC</State>
  <Postcode>3131</Postcode>
  <Country>Australia</Country>
  <Email>GeorgeSt@Appare21.com</Email>
</Store>
```

## Errors
| HTTP | Code | Text |
|------|------|------|
| 404 | | Not found |
| 400 | 5081 | Store ID should be an integer |
| 403 | 5080 | No security for requested store |

## SDK Notes
- `countEnabled = false`, `readOnly = true`
- get() sets Accept header to `version_4.0`
- processEntity() maps all fields listed above
