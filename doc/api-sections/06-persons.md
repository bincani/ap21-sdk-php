# Persons

SDK Class: `lib/Person.php` (HTTPXMLResource)

## GET All Persons (Updated Since)
```
GET /Persons/?countryCode={cc}&UpdatedAfter={timestamp}&startRow={n}&pageRows={n}
Content-type: text/xml
Accept: version_2.0
```

## GET Single Person
```
GET /Persons/{personId}/?countryCode={cc}
```

## GET Person UpdateTimeStamp
```
GET /Persons/{personId}/UpdateTimeStamp/?countryCode={cc}
```

## POST Create Person
```
POST /Persons/?countryCode={cc}
Content-type: text/xml
Accept: version_2.0
```
Returns: 201 with Location header `/Persons/{newPersonId}/?countryCode={cc}`

## PUT Update Person
```
PUT /Persons/{personId}?countryCode={cc}
Content-type: text/xml
Accept: version_2.0
```
**IMPORTANT**: All person data NOT supplied is CLEARED (except CustomData). Must include UpdateTimeStamp matching current record (optimistic concurrency).

## Person Fields
| Field | Format | Max | GET | POST | PUT | Comment |
|-------|--------|-----|-----|------|-----|---------|
| Id | Integer | 22 | Yes | No | No | Unique Person Id |
| Code | Alpha | 10 | Yes | Optional | Optional | Person Code |
| Firstname | Alpha | 30 | Yes | Yes | Yes | First name |
| Surname | Alpha | 50 | Yes | Yes | Yes | Surname |
| Email | Alpha | 250 | Yes | Yes | Yes | Email (unique identifier) |
| UpdateTimeStamp | DateTime | | Yes | No | Yes (mandatory) | Must match for PUT |
| DateOfBirth | Date | | Yes | Optional | Optional | YYYY-MM-DD |
| Gender | Alpha | 6 | Yes | Optional | Optional | Male/Female |
| CompanyName | Alpha | 50 | Yes | Optional | Optional | |
| AccountId | Integer | | Yes | No | No | Reward account id |

## Address Sub-elements (Billing & Delivery)
| Field | Format | Max | Comment |
|-------|--------|-----|---------|
| AddressLine1 | Alpha | 50 | |
| AddressLine2 | Alpha | 50 | |
| City | Alpha | 50 | |
| State | Alpha | 50 | |
| Postcode | Alpha | 10 | |
| Country | Alpha | 20 | |

## Contact Sub-elements
| Field | Format | Max | Comment |
|-------|--------|-----|---------|
| Email | Alpha | 250 | Primary email |
| Phones > Home | Alpha | 250 | |
| Phones > Mobile | Alpha | 250 | |
| Phones > Work | Alpha | 250 | |

## References Sub-element
```xml
<References Type="Array">
  <Reference>
    <PersonReferenceTypeId>123</PersonReferenceTypeId>
    <PersonReferenceTypeCode>VIP</PersonReferenceTypeCode>
    <Value>Gold</Value>
  </Reference>
</References>
```

## CustomData Sub-element
```xml
<CustomData>
  <Card Name="Web Preferences" Sequence="1">
    <CustomField Name="Newsletter" Value="Yes" />
  </Card>
</CustomData>
```
Note: CustomData is NOT cleared on PUT (unlike other fields).

## Custom Data Templates
```
GET /Persons/customdatatemplates?countryCode={cc}
GET /Persons/customdatatemplates/{template name}?countryCode={cc}
```

## POST Error Codes
| HTTP | Code | Text |
|------|------|------|
| 400 | 5000 | Firstname mandatory |
| 400 | 5001 | Surname mandatory |
| 400 | 5002 | Email mandatory |
| 400 | 5003 | Billing address mandatory |
| 400 | 5014 | Field too long |
| 403 | 5200 | Email already exists for another person |
| 403 | 5201 | Invalid gender |
| 403 | 5202 | Invalid date of birth |
| 403 | 5205 | Person reference type invalid |
| 403 | 5206 | Person reference value invalid |

## PUT Error Codes
| HTTP | Code | Text |
|------|------|------|
| 403 | 5010 | UpdateTimeStamp mismatch |
| 403 | 5011 | UpdateTimeStamp mandatory for PUT |

## SDK Notes
- Pagination: startRow/pageRows (default 500)
- Tracks: totalPersons, totalPages
- processEntity() maps: code, firstname, surname, addresses, contacts
- Child resources: `Orders`, `Shipments`, `RetailTransactions`
