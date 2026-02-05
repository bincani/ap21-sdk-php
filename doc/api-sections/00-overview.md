# AP21 Retail API - Overview

Source: Retail API Guide 2025.1

## Base URL
```
https://{hostname}/RetailAPI/
```

## Authentication
- Basic Auth: `ApiUser` / `ApiPassword`
- Token-based: `X-Ap21-Access-Token` header

## Common Headers
```
Content-type: text/xml
Accept: version_X.0
```

## Required Parameters
- `CountryCode` is MANDATORY on every API request

## API Versioning
Version is specified via Accept header:
- `Accept: version_1.0` (original)
- `Accept: version_2.0` (most endpoints)
- `Accept: version_4.0` (Stores, FreightOptions, VouchersSimple)

## Pagination (XML Resources)
- `startRow` - starting position (1-based)
- `pageRows` - number of records per page
- Response includes `TotalRows` attribute on collection root element

## Common Error Codes
| HTTP | API Code | Description |
|------|----------|-------------|
| 403 | 5015 | SQL Error |
| 403 | 5029 | Country not setup at head office |
| 403 | 5030 | No security for requested store |
| 403 | 5031 | Final customer not defined for country |
| 403 | 5036 | Invalid warehouse code in API config |
| 403 | 5122 | Request content type is not XML |
| 403 | 5121 | Cannot deserialise payload |
| 403 | 5014 | Size of field too long |
| 400 | 5034 | Invalid pagination parameters |
| 404 | 5008 | No data found |

## SDK Implementation Status

### Implemented
| Resource | Class | Endpoints |
|----------|-------|-----------|
| Colour | `Colour` | GET |
| Size | `Size` | GET |
| Store | `Store` | GET |
| Reference | `Reference` | GET |
| ReferenceType | `ReferenceType` | GET |
| Product | `Product` | GET |
| Product/FuturePrice | `Product/FuturePrice` | GET |
| Person | `Person` | GET/POST/PUT |
| Person/Orders | `Person/Orders` | GET/POST |
| Person/Orders/Returns | `Person/Orders/Returns` | GET/POST |
| Person/Shipments | `Person/Shipments` | GET |
| Freestock | `Freestock` | GET (style/clr/sku) |
| Freestock/AllStyles | `Freestock/AllStyles` | GET |
| Order | `Order` | GET |
| StockChanged | `StockChanged` | GET |
| RetailTransactions | `RetailTransactions` | GET |
| Shipment | `Shipment` | GET (minimal) |
| ProductColourReference | `ProductColourReference` | GET |
| Info | `Info` | GET |

### Not Implemented
| Resource | Endpoints | Priority |
|----------|-----------|----------|
| Carts | PUT /Carts/{id} | High |
| Voucher | GET enquiry, GET validation, PUT lock | High |
| OrderNumbers | GET /OrderNumbers/{orderNum} | Medium |
| FreightOptions | GET /FreightOptions | Medium |
| Coupons | POST/DELETE/GET /Coupons | Medium |
| Rewards (full suite) | 12+ endpoints under /Rewards/ | Medium |
| ProductsSimple | GET /ProductsSimple | Medium |
| ReferenceTree | GET /ReferenceTree | Low |
| PersonReferenceTypes | GET /PersonReferenceTypes | Low |
| ProductNotes | GET /ProductNotes/{id} | Low |
| ProductColourNotes | GET /ProductColourNotes/{id} | Low |
| YouMightLikeProducts | GET /Products/{id}/YouMightLikeProducts | Low |
| ProductColourReferenceTypes | GET /ProductColourReferenceTypes | Low |
| VouchersSimple | GET /Vouchers/VouchersSimple | Low |
| RewardsSimple | GET /Rewards/RewardsSimple | Low |
| ExpiredRewardsSimple | GET /Rewards/ExpiredRewardsSimple | Low |
