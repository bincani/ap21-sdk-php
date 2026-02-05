# Free Stock

SDK Classes: `lib/Freestock.php`, `lib/Freestock/AllStyles.php`, `lib/Freestock/Style.php`, `lib/Freestock/Clr.php`, `lib/Freestock/Sku.php`

## GET Freestock by Style
```
GET /Freestock/style/{styleId}?countryCode={cc}
Content-type: text/xml
Accept: version_2.0
```

## GET Freestock by Colour
```
GET /Freestock/clr/{clrId}?countryCode={cc}
```

## GET Freestock by SKU
```
GET /Freestock/sku/{skuId}?countryCode={cc}
```

## GET All Styles Freestock
```
GET /Freestock/AllStyles?countryCode={cc}&startRow={n}&pageRows={n}
GET /Freestock/AllStyles/{StoreId}?countryCode={cc}&startRow={n}&pageRows={n}
```

## Response Structure
Data is in XML **attributes** (not element values):
```xml
<Style Name="Jeans &amp; Sizes" StyleIdx="6186">
  <Clr Name="APRICOT" ClrIdx="7116">
    <Sku Name="1" SkuIdx="13210">
      <Store Name="Swanston St Shop" StoreId="7501" StoreNumber="234" FreeStock="991" />
      <Store Name="George St Store" StoreId="108864" StoreNumber="212" FreeStock="879" />
    </Sku>
  </Clr>
</Style>
```

## XML Hierarchy Rules
| Request By | Coloured+Sized | Coloured Only | Style Only |
|------------|----------------|---------------|------------|
| Style | Root=Style, Children=Clr/Sku | Root=Style, Children=Clr | Root=Style |
| Colour | Root=Clr, Children=Sku | Root=Clr | Root=Style |
| SKU | Root=Sku | Root=Sku | Root=Style |

## AllStyles Response
```xml
<FreeStock Type="Array" TotalRows="442" PageStartRow="1" PageRows="50">
  <Style Name="DOUBLE PKT SHIRT" StyleIdx="1101">
    <Clr Name="MULTICOLOURED" ClrIdx="1322">
      <Sku Name="6" SkuIdx="1783">
        <Store Name="Central Warehouse" StoreId="9521" StoreNumber="999" FreeStock="100" />
      </Sku>
    </Clr>
  </Style>
</FreeStock>
```

## Errors
| HTTP | Code | Text |
|------|------|------|
| 404 | 5008 | No data found |
| 400 | 5081 | Index should be an integer |
| 400 | 5080 | No security for requested store |

## SDK Notes
- Freestock base class handles all parsing (processEntity, processClr, processSku, processStore)
- AllStyles has own pagination (startRow/pageRows, default 500)
- get() sets Accept to `version_4.0`
- Child resources (Style, Clr, Sku) delegate to parent processing methods
- Only stores with positive free stock are returned
