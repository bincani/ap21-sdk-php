# Gift Vouchers

**SDK Status: NOT IMPLEMENTED**

## Voucher Enquiry (Balance Check)
```
GET /Voucher/{voucherNumber}?pin={pin}&countryCode={cc}
Content-type: text/xml
Accept: version_2.0
```

## Voucher Validation (for payment)
```
GET /Voucher/GVValid/{voucherNumber}?pin={pin}&amount={amount}&countryCode={cc}
```
Returns ValidationId needed for payment. Amount is only used to check "give change" vouchers are used in full.

## Voucher Locking (optional)
```
PUT /Voucher/Lock/{voucherNumber}?ValidationId={id}&LockSeconds={sec}&countryCode={cc}
```
- Default lock: 3600 seconds (1 hour)
- LockSeconds=0 to unlock
- Lock released when order posted into AP21 or lock expires

## Voucher Fields
| Field | Format | GET (enquiry) | GET (validation) | Comment |
|-------|--------|--------------|-----------------|---------|
| VoucherNumber | Alpha(50) | Yes | Yes | |
| ExpiryDate | DateTime | Yes | Yes | |
| OriginalAmount | Num(22,2) | Yes | Yes | |
| UsedAmount | Num(22,2) | Yes | Yes | |
| AvailableAmount | Num(22,2) | Yes | Yes | May be affected by expiry/write-off |
| ValidationId | Alpha(36) | No | Yes | GUID, valid until next validation request |

## Enquiry Response
```xml
<Voucher>
  <VoucherNumber>6000226</VoucherNumber>
  <ExpiryDate>2017-02-17T00:00:00</ExpiryDate>
  <OriginalAmount>100</OriginalAmount>
  <UsedAmount>0</UsedAmount>
  <AvailableAmount>100</AvailableAmount>
</Voucher>
```

## Validation Response
```xml
<Voucher>
  <VoucherNumber>6080044</VoucherNumber>
  <ExpiryDate>2017-02-17T00:00:00</ExpiryDate>
  <OriginalAmount>100</OriginalAmount>
  <UsedAmount>0</UsedAmount>
  <AvailableAmount>100</AvailableAmount>
  <ValidationId>2f093406-eab4-4ada-bb4b-aa2786ef2a01</ValidationId>
</Voucher>
```

## Using Voucher as Payment (in Order POST)
```xml
<PaymentDetail>
  <Origin>GiftVoucher</Origin>
  <VoucherNumber>7060120</VoucherNumber>
  <ValidationId>3e5d3a6c-1efc-4e7b-adcf-10761c7fdda2</ValidationId>
  <Amount>6.56</Amount>
</PaymentDetail>
```

## Enquiry Error Codes
| HTTP | Code | Text |
|------|------|------|
| 403 | 5053 | PIN is invalid |
| 403 | 5054 | Already redeemed |
| 403 | 5055 | Cannot be used online (not secure) |
| 403 | 5056 | Does not exist (suspended) |
| 403 | 5057 | Has not been issued |
| 403 | 5059 | No longer valid |
| 403 | 5068 | Not supported on current database |
| 403 | 5219 | Currency mismatch |

## Validation Error Codes (additional)
| HTTP | Code | Text |
|------|------|------|
| 403 | 5060 | Invalid request amount |
| 403 | 5065 | Expired |
| 403 | 5066 | Must be used in full |
| 403 | 5067 | Pending payments |

## Lock Error Codes
| HTTP | Code | Text |
|------|------|------|
| 403 | 5061 | Incorrect validation id |

## VouchersSimple (extra licensing, v4.0)
```
GET /Vouchers/VouchersSimple?countryCode={cc}&updatedAfter={ts}&startRow={n}&pageRows={n}
Accept: version_4.0
```
Returns: VoucherNumber, ExpiryDate, OriginalAmount, UsedAmount, AvailableAmount, Pin (encrypted)

## Implementation Notes
- Would need `Voucher.php` resource with child resources for GVValid and Lock
- GVValid is GET (not POST) with query params
- Lock is PUT
- All are read/validate operations, not standard CRUD
- Custom URL patterns: `/Voucher/GVValid/{num}` and `/Voucher/Lock/{num}`
