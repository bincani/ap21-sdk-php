# References & Reference Types

SDK Classes: `lib/Reference.php`, `lib/ReferenceType.php`

## Reference Types

### GET All Reference Types
```
GET /ReferenceTypes/?countryCode={cc}
Content-type: text/xml
Accept: version_2.0
```

### GET Single Reference Type
```
GET /ReferenceTypes/{ReferenceTypeId}/?countryCode={cc}
```

### Reference Type Fields
| Field | Format | Max | GET | Comment |
|-------|--------|-----|-----|---------|
| Id | Integer | 22 | Yes | Unique Reference Type Id |
| Code | Alpha | 30 | Yes | Reference Type Code |
| Name | Alpha | 250 | Yes | Reference Type Name |

### Known Reference Type IDs
| ID | Purpose |
|----|---------|
| 271 | Discount reason codes |
| 272 | Return reason codes |
| 366 | Loyalty program codes |

---

## References

### GET References for a Type
```
GET /References/{ReferenceTypeId}/?countryCode={cc}
```

### Reference Fields
| Field | Format | Max | GET | Comment |
|-------|--------|-----|-----|---------|
| Id | Integer | 22 | Yes | Unique Reference Id |
| Code | Alpha | 30 | Yes | Reference Code |
| Name | Alpha | 250 | Yes | Reference Name |

### Response Example
```xml
<ReferenceType>
  <Id>1</Id>
  <Code>Carrier</Code>
  <Name>Carrier</Name>
  <References Type="Array">
    <Reference>
      <Id>1156</Id>
      <Code>AUS</Code>
      <Name>Australia Post</Name>
    </Reference>
  </References>
</ReferenceType>
```

## SDK Notes
- ReferenceType: JSON resource (HTTPResource), `countEnabled = false`, `readOnly = true`
- ReferenceType has custom `getByCode($code)` method
- Reference: XML resource (HTTPXMLResource), `countEnabled = false`, `readOnly = true`
- Reference has custom `getValue($id)` method

## Not Implemented
- `GET /ReferenceTree/?countryCode={cc}` - Navigation tree (hierarchical)
- `GET /PersonReferenceTypes/?countryCode={cc}` - Person-specific reference types
- `GET /ProductColourReferenceTypes/?countryCode={cc}` - Product colour reference types
