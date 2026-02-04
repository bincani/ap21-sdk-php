# Carts

**SDK Status: NOT IMPLEMENTED**

## PUT Submit Cart for Pricing
```
PUT /Carts/{id}?countryCode={cc}
Content-type: text/xml
Accept: version_2.0
```
**Note**: Cart contents are NOT stored by the API. The PUT submits items for pricing/tax/discount/freight calculation and returns calculated values.

## Cart Fields (Request)
| Field | Format | POST | Comment |
|-------|--------|------|---------|
| PersonId | Integer | Optional | Person for loyalty/rewards |
| CartDetails | | Yes | List of CartDetail items |
| CartDetail > SkuId | Integer | Yes | **Required** |
| CartDetail > Quantity | Integer | Yes | **Required** |
| SelectedFreightOption | | Optional | Freight option selection |
| FreightOptions | | No | Returned in response |

## Response
Returns the same cart with calculated:
- Prices per item (including promotions/loyalty discounts)
- Tax amounts
- Freight options and values
- Discount breakdowns

## Errors
| HTTP | Code | Text |
|------|------|------|
| 403 | 5033 | No products in cart |
| 403 | 5014 | Field too long |

## Implementation Notes
- PUT only (no GET/POST/DELETE)
- Would need a new `Cart.php` extending HTTPXMLResource
- `resourceKey = 'Cart'`
- Requires ID in URL (the cart identifier)
- Response parsing needs to handle FreightOptions array
