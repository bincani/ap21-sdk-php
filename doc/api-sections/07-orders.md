# Orders

SDK Classes: `lib/Order.php`, `lib/Person/Orders.php` (HTTPXMLResource)

## GET Orders Updated Since
```
GET /Orders/?countryCode={cc}&startRow={n}&pageRows={n}&updatedAfter={timestamp}
Content-type: text/xml
Accept: version_2.0
```
Returns only web/mail orders (not POS/wholesale). Reads from Sales Order tables (latest status).

## GET Orders for a Person
```
GET /Persons/{personId}/Orders/?countryCode={cc}&startRow={n}&pageRows={n}&updatedAfter={timestamp}
```

## GET Single Order for a Person
```
GET /Persons/{personId}/Orders/{orderId}/?countryCode={cc}
```
Reads from temporary websales table (for confirmation only, not latest status).

## GET Order by Order Number (NOT IN SDK)
```
GET /OrderNumbers/{OrderNumber}?countryCode={cc}
```
Personless order lookup from websales table.

## POST Create Order
```
POST /Persons/{personId}/Orders/?countryCode={cc}
Content-type: text/xml
Accept: version_2.0
```
Returns: 201 with Location header `/Persons/{personId}/Orders/{orderId}/?countryCode={cc}`

## Order Header Fields
| Field | Format | Max | GET | POST | Comment |
|-------|--------|-----|-----|------|---------|
| Id | Integer | 22 | Yes | No | Unique Order Id |
| OrderNumber | Alpha | 30 | Yes | Optional | Auto-generated if blank |
| PartnerOrderId | Alpha | 30 | Yes | Optional | External system ID (e.g. Shopify) |
| NeedReview | True/False | | Yes | Optional | Flags for manual review (fraud) |
| ReviewNotes | Alpha | 250 | Yes | Optional | Reason for review |
| PersonId | Integer | 22 | Yes | Yes | Person Id |
| WarehouseId | Integer | 22 | Yes | No | Web Store Id |
| OrderDateTime | DateTime | | Yes | Optional | Website order date |
| TotalQuantity | Num(22,2) | | Yes | No | |
| TotalTax | Num(22,2) | | Yes | No | |
| TotalDiscount | Num(22,2) | | Yes | No | |
| TotalDue | Num(22,2) | | Yes | No | |
| TotalPayment | Num(22,2) | | Yes | No | |
| PickupStoreId | Num(22,2) | | Yes | Optional | For Click & Collect |
| DespatchType | Alpha | 20 | Yes | Optional | ClickAndCollect / DespatchFromStore / DespatchFromWarehouse |
| ExpectedDeliveryDate | Date | | Yes | Optional | yyyy-MM-dd |
| DeliveryInstructions | Alpha | 250 | Yes | Optional | |
| UnattendedDeliveryOption | Alpha | 16 | Yes | Yes | AuthorityToLeave or None |
| PricesIncludeTax | True/False | | No | No | v2.0 - for US extended tax |
| UseSubmittedTaxRate | True/False | | Yes | Optional | If true, TaxPercent per item required |

## Carrier/ServiceType/SLA (POST optional)
```xml
<Carrier><Id>1156</Id><Code>AUS</Code></Carrier>
<ServiceType><Id>162</Id><Code>Road</Code></ServiceType>
<SLA><Id>16784</Id><Code>SameDay</Code></SLA>
```

## Addresses Sub-element
```xml
<Addresses>
  <Billing>
    <AddressLine1>73 Linlithgow Avenue</AddressLine1>
    <AddressLine2 />
    <City>GREYTHORN</City>
    <State>VIC</State>
    <Postcode>3152</Postcode>
    <Country>AUSTRALIA</Country>
  </Billing>
  <Delivery>
    <ContactName>Jane Smith</ContactName>
    <CompanyName>Apparel21</CompanyName>
    <AddressLine1>37 Swan Street</AddressLine1>
    <AddressLine2 />
    <City>GREYTHORN</City>
    <State>VIC</State>
    <Postcode>3152</Postcode>
    <Country>AUSTRALIA</Country>
  </Delivery>
</Addresses>
```
POST: At least 1 Billing and 1 Delivery field required.

## Contacts Sub-element
```xml
<Contacts>
  <Email>john.smith@test.com.au</Email>
  <Phones>
    <Home>012351235</Home>
    <Mobile>012341234</Mobile>
    <Work />
  </Phones>
</Contacts>
<DeliveryContacts>
  <Email>delivery@test.com</Email>
  <Phones><Home>...</Home></Phones>
</DeliveryContacts>
```
Email is mandatory for POST.

## OrderDetail Fields
| Field | Format | Max | GET | POST | Comment |
|-------|--------|-----|-----|------|---------|
| Id | Integer | 22 | Yes | No | Unique OrderDetail Id |
| Sequence | Integer | 10 | Yes | No | Position in list |
| ProductId | Integer | 22 | Yes | No | |
| ColourId | Integer | 22 | Yes | No | |
| SkuId | Integer | 22 | Yes | Yes | **Required** |
| ProductCode | Alpha | 10 | Yes | No | |
| ProductName | Alpha | 30 | Yes | No | |
| ColourCode | Alpha | 21 | Yes | No | |
| ColourName | Alpha | 61 | Yes | No | |
| SizeCode | Alpha | 7 | Yes | No | |
| Barcode | Alpha | 50 | Yes | No | |
| Quantity | Num(22,2) | 22 | Yes | Yes | **Required** (negative for returns) |
| Price | Num(22,2) | 22 | Yes | Yes | **Required** |
| Value | Num(22,2) | 22 | Yes | Yes | **Required** (net value) |
| Discount | Num(22,2) | 22 | Yes | No | |
| TaxPercent | Num(22,2) | 22 | Yes | Conditional | Required if PricesIncludeTax=false or UseSubmittedTaxRate=true |
| Customisation | Alpha | 100 | Yes | Optional | |
| GiftWrap | Alpha | 5 | Yes | Optional | true/false |
| GiftWrapMessage | Alpha | 1000 | Yes | Optional | |
| Status | Alpha | 10 | Yes | No | Processing/Shipped/Cancelled/Returned |
| ReturnReasonId | Integer | 22 | Yes | For returns | From ReferenceTypeId=272 |
| ReturnReasonNotes | Alpha | | Yes | Optional | |
| NonReturnable | Alpha | 5 | Yes | Optional | true/false |

## Discounts Sub-element of OrderDetail
```xml
<Discounts>
  <Discount>
    <DiscountTypeId>1</DiscountTypeId>  <!-- 1=Manual, 2=Promotion, 3=Loyalty, 4=Reward, 5=Coupon -->
    <Value>9.9</Value>
    <ReasonId>1231</ReasonId>           <!-- only for type 1, from ReferenceTypeId=271 -->
    <PromoId>1010</PromoId>             <!-- only for type 2 -->
    <LoyaltyId>2020</LoyaltyId>        <!-- only for type 3 -->
    <WebDiscount>Shop and save</WebDiscount>
    <WebPromotion>SHOPNSAVE</WebPromotion>
    <WebCampaign>W1231321</WebCampaign>
    <WebCoupon>QUICDISC</WebCoupon>
    <Redemption>                         <!-- for type 4/5 -->
      <AccountId>861</AccountId>
      <Amount>10</Amount>
      <GiftId>261</GiftId>
      <RequestId>6A4CFBB9-094E-422F-8EC6-947946455C0F</RequestId>
      <CouponCode>NRL21</CouponCode>
    </Redemption>
  </Discount>
</Discounts>
```
**Discount sequence must be: type 1, 2, 3, 4, 5 when multiple apply.**

## Gift Voucher in OrderDetail
```xml
<ExtraVoucherInformation>
  <VoucherType>GV_API_EMAIL</VoucherType>
  <VoucherNumber>123456789</VoucherNumber>   <!-- optional -->
  <EmailSubject>Happy Birthday!</EmailSubject>
  <Email>recipient@example.com</Email>
  <PersonalisedMessage>Enjoy!</PersonalisedMessage>
  <SenderName>Mummy</SenderName>
  <ReceiverName>Daddy</ReceiverName>
  <DeliveryDate>2021-11-14</DeliveryDate>    <!-- future email delivery -->
</ExtraVoucherInformation>
```

## SelectedFreightOption
```xml
<SelectedFreightOption>
  <Id>123</Id>
  <Name>Fox Freight Service</Name>
  <Value>156</Value>
  <TaxPercent>13</TaxPercent>
</SelectedFreightOption>
```

## PaymentDetail Fields
| Field | Format | Max | GET | POST | Comment |
|-------|--------|-----|-----|------|---------|
| Origin | Alpha | 50 | Yes | Yes | **Required**: CreditCard, DirectDebit, Zip, GiftVoucher, AdyenWallet, Openpay |
| Amount | Num(22,2) | 22 | Yes | Yes | **Must equal order total** |
| CardType | Alpha | 250 | Yes | Optional | VISA, etc. For AdyenWallet: wallet payment method |
| Stan | Alpha | 250 | Yes | Optional | Transaction ID (mandatory for refunds) |
| AuthCode | Alpha | 250 | Yes | Optional | Authorization code |
| Reference | Alpha | 250 | Yes | Optional | Mandatory for SecurePay/PayPal refunds |
| MerchantId | Alpha | 100 | Yes | Optional | Mandatory for PayPal/AfterPay/Braintree/Adyen/Openpay |
| AccountId | Alpha | 100 | Yes | Optional | Mandatory for Braintree |
| Settlement | Alpha | 250 | Yes | Optional | |
| Message | Alpha | 4000 | Yes | Optional | |
| VoucherNumber | Alpha | 50 | Yes | No | For GV payment |
| ValidationId | Alpha | 50 | No | Yes (GV) | From voucher validation |
| VoucherGateway | Alpha | 15 | Yes | Optional | 3rd party GV gateway |

## PointsPartner
```xml
<PointsPartner>
  <Id>16044</Id>
  <Name>Qantas</Name>
  <MembershipNumber>1234999629624</MembershipNumber>
</PointsPartner>
```

## Currency (GET only)
```xml
<Currency>
  <Code>AUD</Code>
  <Format>#,##0.00 'AUD'</Format>
</Currency>
```

## POST Error Codes (selected)
| HTTP | Code | Text |
|------|------|------|
| 400 | 5004 | Email is required |
| 400 | 5005 | Billing address is required |
| 400 | 5021 | Delivery address is required |
| 400 | 5025 | Person Id mismatch |
| 400 | 5026 | Payment does not match order due |
| 403 | 5028 | Invalid payment origin |
| 403 | 5082 | Invalid pickup store |
| 403 | 5083 | Gift vouchers cannot be on click & collect |
| 403 | 5094 | Order value does not match Sku price |
| 403 | 5114 | Invalid despatch type |
| 403 | 5129 | SKU not valid |
| 403 | 5421 | Requires personId, id, or updatedAfter |

## GET Error Codes
| HTTP | Code | Text |
|------|------|------|
| 400 | 5034 | Invalid pagination parameters |
| 403 | 5421 | Requires personId, id, or updatedAfter |

## SDK Notes
- Person/Orders extends Person, sets Accept to version_2.0
- POST returns order ID from Location header
- processEntity() parses full order structure including addresses, contacts, items, payments
- Order class extends Person/Orders with close/open/cancel magic methods
- **OrderNumbers endpoint not implemented** - needs new resource class
