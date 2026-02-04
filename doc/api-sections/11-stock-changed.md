# Stock Changed

SDK Class: `lib/StockChanged.php` (HTTPXMLResource)

## GET Stock Changes Since Date
```
GET /StockChanged?ChangedSince={datetime}&StoreId={storeId}&countryCode={cc}
Content-type: text/xml
Accept: version_1.0
```

## Parameters
| Field | Required | Comment |
|-------|----------|---------|
| ChangedSince | Mandatory | Date/time to get changes since |
| StoreId | Optional | Filter to single warehouse/store |
| CountryCode | Mandatory | |

## Response
Data is in XML **attributes** (not element values):
```xml
<stockByStore changedSince="2018-05-28T11:00:00">
  <store storeid="8981" lastChanged="2018-05-29T14:20:07.76">
    <sku skuid="34145" freeStock="4" />
    <sku skuid="34447" freeStock="-15" />
    <sku skuid="31460" freeStock="-1" />
  </store>
  <store storeid="9001" lastChanged="2018-05-29T14:57:39.514">
    <sku skuid="19702" freeStock="-207" />
    <sku skuid="35723" freeStock="1" />
  </store>
</stockByStore>
```

**Note**: FreeStock value is the NEW available quantity (not a delta). Negative values are valid. Designed for delta feeds - not optimised for full stock feed.

## Errors
| HTTP | Code | Text |
|------|------|------|
| 400 | 5147 | Warehouse doesn't exist / Invalid ChangedSince format |
| 400 | 5148 | No company attached to warehouse |
| 403 | 5097 | Stock formula not set up |

## SDK Notes
- `pluralizeKey()` returns `'StockChanged'` (no pluralization)
- `childResource = ['stockByStore']`
- get() sets Accept to `version_1.0`
- processEntity() extracts storeid, lastChanged, and SKU freestock data
- processCollection() loops through stores
