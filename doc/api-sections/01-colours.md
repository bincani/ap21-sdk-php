# Colours

SDK Class: `lib/Colour.php` (HTTPXMLResource)

## GET All Colours
```
GET /Colours/?countryCode={cc}
Content-type: text/xml
Accept: version_2.0
```

## GET Single Colour
```
GET /Colours/{colourId}/?countryCode={cc}
```

## Fields
| Field | Format | Max | GET | Comment |
|-------|--------|-----|-----|---------|
| Id | Integer | 22 | Yes | Unique Colour Id |
| Code | Alpha | 21 | Yes | Colour Code |
| Name | Alpha | 61 | Yes | Colour Name |

## Response Example
```xml
<Colours Type="Array" TotalRows="879" PageStartRow="1" PageRows="200">
  <Colour>
    <Id>1322</Id>
    <Code>001</Code>
    <Name>MULTICOLOURED</Name>
  </Colour>
</Colours>
```

## Errors
| HTTP | Code | Text |
|------|------|------|
| 404 | | Not found |

## SDK Notes
- `countEnabled = false`, `readOnly = true`
- processEntity() maps: id, code, name
- Returns keyed array by colour id
