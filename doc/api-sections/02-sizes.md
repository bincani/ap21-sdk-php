# Sizes

SDK Class: `lib/Size.php` (HTTPXMLResource)

## GET All Sizes
```
GET /Sizes/?countryCode={cc}
Content-type: text/xml
Accept: version_2.0
```

## GET Single Size
```
GET /Sizes/{sizeId}/?countryCode={cc}
```

## Fields
| Field | Format | Max | GET | Comment |
|-------|--------|-----|-----|---------|
| Id | Integer | 22 | Yes | Unique Size Id |
| Code | Alpha | 7 | Yes | Size Code |

## Response Example
```xml
<Sizes Type="Array" TotalRows="72" PageStartRow="1" PageRows="200">
  <Size>
    <Id>1</Id>
    <Code>-</Code>
  </Size>
  <Size>
    <Id>2</Id>
    <Code>1</Code>
  </Size>
</Sizes>
```

## SDK Notes
- `countEnabled = false`, `readOnly = true`
- processEntity() maps: id, code
