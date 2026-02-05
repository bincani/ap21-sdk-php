# Coupons

**SDK Status: NOT IMPLEMENTED**

All coupons endpoints use `Accept: version_1.0`.

## Endpoints Summary
| Method | URI | Description |
|--------|-----|-------------|
| POST | /Coupons/Redemptions?countryCode={cc} | Validate or reserve coupon |
| DELETE | /Coupons/Redemptions/{requestId}?countryCode={cc} | Unreserve (cancel) coupon |
| GET | /Coupons?countryCode={cc} | Retrieve coupons list |

## Coupon Workflow
1. **Validate**: POST with `<Reserved>false</Reserved>` - checks coupon code validity
2. **Reserve (lock)**: POST with `<Reserved>true</Reserved>` + cart items - locks coupon for 24hrs, returns discount breakdown
3. **Submit order**: POST Order with coupon discount details from step 2
4. **Cancel (if needed)**: DELETE with requestId to unreserve

Notes:
- Only **unique** coupons need reserving; generic coupons don't get locked
- Lock expires after 24 hours automatically
- Lock released when order posted into AP21 or lock expires

## POST Validate Payload (simple)
```xml
<CouponRedemption>
  <RequestId>1f2e558a-a062-4e6c-84bc-697dd5fcfd462</RequestId>
  <PersonId>1841</PersonId>
  <CouponCode>BLACK21</CouponCode>
  <Reserved>false</Reserved>
</CouponRedemption>
```

### Validate Response
```xml
<CouponRedemption>
  <RequestId>1f2e558a-a062-4e6c-84bc-697dd5fcfd462</RequestId>
  <PersonId>1841</PersonId>
  <CouponCode>BLACK21</CouponCode>
  <CouponName>BLACK FRIDAY 21</CouponName>
  <CouponDescription>Black Friday sale 2021</CouponDescription>
  <Reserved>False</Reserved>
</CouponRedemption>
```

## POST Reserve Payload (with cart items)
```xml
<CouponRedemption>
  <RequestId>1f2e558a-a062-4e6c-84bc-697dd5fcfd10</RequestId>
  <PersonId>1841</PersonId>
  <CouponCode>NRL21</CouponCode>
  <Reserved>true</Reserved>
  <Transaction>
    <Detail>
      <Key>NF02</Key>
      <SkuId>12431</SkuId>
      <Quantity>1</Quantity>
      <Price>129.99</Price>
    </Detail>
    <Detail>
      <Key>NF02</Key>
      <SkuId>33069</SkuId>
      <Quantity>1</Quantity>
      <Price>79.99</Price>
    </Detail>
  </Transaction>
</CouponRedemption>
```

### Reserve Response (with discount breakdown)
```xml
<CouponRedemption>
  <RequestId>1f2e558a-a062-4e6c-84bc-697dd5fcfd10</RequestId>
  <PersonId>1841</PersonId>
  <CouponCode>NRL21</CouponCode>
  <CouponName>NRL21</CouponName>
  <CouponDescription>NRL 2021</CouponDescription>
  <Reserved>True</Reserved>
  <Transaction Type="Array" TotalRows="2">
    <Detail>
      <Key>NF02</Key>
      <SkuId>12431</SkuId>
      <Price>129.99</Price>
      <Quantity>1</Quantity>
      <NewBreakdown Type="Array" TotalRows="1">
        <NewDetail>
          <SkuId>12431</SkuId>
          <Price>129.99</Price>
          <Quantity>1</Quantity>
          <Discount>26.00</Discount>
          <Value>103.99</Value>
          <Discounts Type="Array" TotalRows="1">
            <Discount>
              <DiscountTypeId>5</DiscountTypeId>
              <DiscountType>CouponDiscount</DiscountType>
              <Percentage>20.0</Percentage>
              <Value>26.00</Value>
              <Redemption>
                <CouponCode>NRL21</CouponCode>
                <RequestId>1f2e558a-a062-4e6c-84bc-697dd5fcfd10</RequestId>
              </Redemption>
            </Discount>
          </Discounts>
        </NewDetail>
      </NewBreakdown>
    </Detail>
  </Transaction>
  <Applied>True</Applied>
</CouponRedemption>
```

## DELETE Unreserve
```
DELETE /Coupons/Redemptions/{requestId}?countryCode={cc}
```
Uses the RequestId (GUID) from the reserve POST.

## GET Retrieve Coupons

### Parameters
| Field | Required | Comment |
|-------|----------|---------|
| countryCode | Mandatory | Country code |
| personId | Optional | Filter by person |
| ValidCoupon | Optional | `true`=only valid (not expired/redeemed), `false`=all (default) |
| UpdatedAfter | Optional | Timestamp filter (YYYY-MM-DDTHH:MM:SS) |
| StartRow | Optional | Pagination start |
| PageRows | Optional | Pagination size |

### GET Response
```xml
<Coupons Type="Array" TotalRows="2" PageStartRow="1" PageRows="2">
  <Coupon>
    <CouponCode>TESTBLKDAY</CouponCode>
    <CouponDescription>test- generic</CouponDescription>
    <CouponStatus>Enabled</CouponStatus>
    <CouponType>Generic</CouponType>
    <CouponName>new test</CouponName>
    <ValidFrom>2023-02-20T00:00:00</ValidFrom>
    <ValidTo>2023-12-29T00:00:00</ValidTo>
    <PersonId />
    <FirstName />
    <LastName />
    <PhoneNumber />
    <Email />
    <CreatedDate>2023-02-20T11:24:55</CreatedDate>
    <LastDateModified>2023-02-20T11:25:06</LastDateModified>
  </Coupon>
  <Coupon>
    <CouponCode>SEASONXPFCMVQ4FJ</CouponCode>
    <CouponDescription>new season coupon</CouponDescription>
    <CouponStatus>Enabled</CouponStatus>
    <CouponType>Unique</CouponType>
    <CouponName>season coupon-$ 2 off</CouponName>
    <ValidFrom>2023-02-20T00:00:00</ValidFrom>
    <ValidTo>2023-03-20T00:00:00</ValidTo>
    <PersonId>11385</PersonId>
    <FirstName>amala</FirstName>
    <LastName>Jithin</LastName>
    <PhoneNumber>0470396583</PhoneNumber>
    <Email />
    <CreatedDate>2023-02-20T13:24:50</CreatedDate>
    <LastDateModified />
  </Coupon>
</Coupons>
```

## CouponRedemption Fields
| Field | Format | POST | DELETE | Comment |
|-------|--------|------|--------|---------|
| RequestId | Alpha(250) | Yes (mandatory) | Yes (mandatory) | GUID |
| PersonId | Number | Yes | No | |
| CouponCode | Alpha(250) | Yes | No | |
| Reserved | Alpha(5) | Yes | No | `true` to lock, `false` to validate only |
| Transaction > Detail > Key | Alpha | Yes | No | Developer tracking key |
| Transaction > Detail > SkuId | Number | Yes | No | |
| Transaction > Detail > Quantity | Number | Yes | No | |
| Transaction > Detail > Price | Number | Yes | No | |

### Response-only Fields
| Field | Comment |
|-------|---------|
| CouponName | Campaign name |
| CouponDescription | Campaign description |
| Applied | `True` if coupon conditions met for provided products |
| NewBreakdown > NewDetail > Discount | Discount value |
| NewBreakdown > NewDetail > Value | Price after discount |
| Discounts > Discount > DiscountTypeId | 5 = Coupon discount |
| Discounts > Discount > DiscountType | "CouponDiscount" |
| Discounts > Discount > Percentage | Discount percentage |
| Discounts > Discount > Value | Discount amount |
| Discounts > Discount > Redemption | Contains CouponCode + RequestId |

## Error Codes
| HTTP | Code | Text |
|------|------|------|
| 403 | 5224 | Error redeeming coupon |
| 403 | 5225 | Invalid coupon code |
| 403 | 5226 | Coupon is not enabled |
| 403 | 5227 | Too early to redeem coupon |
| 403 | 5228 | Coupon has expired |
| 403 | 5229 | Coupon has already been redeemed |
| 403 | 5230 | Coupon has already been locked for redemption |
| 403 | 5231 | Coupon is not valid for PersonId |
| 403 | 5232 | Coupon is not valid for webstore |
| 403 | 5233 | Coupon redemption RequestId already finalised |
| 403 | 5234 | Coupon redemption RequestId is not valid |
| 403 | 5235 | Redemption element missing from Coupon discount |
| 403 | 5236 | Redemption element must specify RequestId or CouponCode |
| 403 | 5237 | Cannot redeem multiple coupons on same order |
| 403 | 5243 | Invalid PersonID (negative value) |
| 403 | 5244 | ValidCoupon flag invalid (must be true/false) |
| 403 | 5245 | UpdatedAfter date format invalid |

## Implementation Notes
- Complex resource: POST creates/validates, DELETE cancels, GET lists
- POST endpoint doubles as both validate and reserve depending on `<Reserved>` flag
- Response includes discount breakdown when cart items provided
- DiscountTypeId 5 = Coupon discount (used in order submission)
- GET supports pagination (StartRow/PageRows) and timestamp filtering
- Two coupon types: Generic (shared codes) and Unique (per-person codes)
