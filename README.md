# PHP Ap21 SDK

PHP Ap21 SDK is a simple SDK implementation of Ap21 API. It helps accessing the API in an object oriented way.

## Installation
Install with Composer
```shell
composer require bincani/ap21-sdk-php
```

### Requirements

Uses curl extension for handling http calls. So you need to have the curl extension installed and enabled with PHP.

>However if you prefer to use any other available package library for handling HTTP calls, you can easily do so by modifying 1 line in each of the `get()`, `post()`, `put()`, `delete()` methods in `PHPAP21\HttpRequest` class.

You can pass additional curl configuration to `Ap21SDK`

```php
$config = array(
    'ApiUrl'       => 'https://api21.end.pount/',
    'ApiUser'      => 'apiuser',
    'ApiPassword'  => 'password',
    'CountryCode'  => 'code',
    'Curl' => array(
        CURLOPT_TIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => true
    )
);

PHPAP21\Ap21SDK::config($config);
```
## Usage

You can use PHPAP21 in a pretty simple object oriented way.

#### Configure Ap21SDK

```php
$config = array(
    'ApiUrl'       => 'https://api21.end.pount/',
    'ApiUser'      => 'apiuser',
    'ApiPassword'  => 'password',
    'CountryCode'  => 'code',
);

PHPAP21\Ap21SDK::config($config);
```

#### Get the Ap21SDK Object

```php
$ap21 = new PHPAP21\Ap21SDK;
```

You can provide the configuration as a parameter while instantiating the object (if you didn't configure already by calling `config()` method)

```php
$ap21 = new PHPAP21\Ap21SDK($config);
```

##### Now you can do `get()`, `post()`, `put()`, `delete()` calling the resources in the object oriented way. All resources are named as same as it is named in shopify API reference. (See the resource map below.)
> All the requests returns an array (which can be a single resource array or an array of multiple resources) if succeeded. When no result is expected (for example a DELETE request), an empty array will be returned.

- Get all product list (GET request)

```php
$products = $ap21->Product->get();
```

- Get any specific product with ID (GET request)

```php
$productID = 23564666666;
$product = $ap21->Product($productID)->get();
```

You can also filter the results by using the url parameters (as specified by Shopify API Reference for each specific resource).

- For example get the list of cancelled orders after a specified date and time (and `fields` specifies the data columns for each row to be rendered) :

```php
$params = array(
    'status' => 'cancelled',
    'created_at_min' => '2016-06-25T16:15:47-04:00',
    'fields' => 'id,line_items,name,total_price'
);

$orders = $ap21->Order->get($params);
```

- Create a new order (POST Request)

```php
$order = array (
    "email" => "foo@example.com",
    "fulfillment_status" => "unfulfilled",
    "line_items" => [
      [
          "variant_id" => 27535413959,
          "quantity" => 5
      ]
    ]
);

$ap21->Order->post($order);
```

> Note that you don't need to wrap the data array with the resource key (`order` in this case), which is the expected syntax from Shopify API. This is automatically handled by this SDK.


- Update an order (PUT Request)

```php
$updateInfo = array (
    "fulfillment_status" => "fulfilled",
);

$ap21->Order($orderID)->put($updateInfo);
```

- Remove a Webhook (DELETE request)

```php
$webHookID = 453487303;

$ap21->Webhook($webHookID)->delete();
```


### The child resources can be used in a nested way.
> You must provide the ID of the parent resource when trying to get any child resource

- For example, get the images of a product (GET request)

```php
$productID = 23564666666;
$productImages = $ap21->Product($productID)->Image->get();
```

- Add a new address for a customer (POST Request)

```php
$address = array(
    "address1" => "129 Oak St",
    "city" => "Ottawa",
    "province" => "ON",
    "phone" => "555-1212",
    "zip" => "123 ABC",
    "last_name" => "Lastnameson",
    "first_name" => "Mother",
    "country" => "CA",
);

$customerID = 4425749127;

$ap21->Customer($customerID)->Address->post($address);
```

- Create a fulfillment event (POST request)

```php
$fulfillmentEvent = array(
    "status" => "in_transit"
);

$ap21->Order($orderID)->Fulfillment($fulfillmentID)->Event->post($fulfillmentEvent);
```

- Update a Blog article (PUT request)

```php
$blogID = 23564666666;
$articleID = 125336666;
$updateArtilceInfo = array(
    "title" => "My new Title",
    "author" => "Your name",
    "tags" => "Tags, Will Be, Updated",
    "body_html" => "<p>Look, I can even update through a web service.<\/p>",
);
$ap21->Blog($blogID)->Article($articleID)->put($updateArtilceInfo);
```

- Delete any specific article from a specific blog (DELETE request)

```php
$blogArticle = $ap21->Blog($blogID)->Article($articleID)->delete();
```

### GraphQL <sup>*v1.1*</sup>
The GraphQL Admin API is a GraphQL-based alternative to the REST-based Admin API, and makes the functionality of the Shopify admin available at a single GraphQL endpoint. The full set of supported types can be found in the [GraphQL Admin API reference](https://help.shopify.com/en/api/graphql-admin-api/reference).
You can simply call the GraphQL resource and make a post request with a GraphQL string:

> The GraphQL Admin API requires an access token for making authenticated requests. You can obtain an access token either by creating a private app and using that app's API password, or by following the OAuth authorization process. See [GraphQL Authentication Guide](https://help.shopify.com/en/api/graphql-admin-api/getting-started#authentication)

```php
$graphQL = <<<Query
query {
  shop {
    name
    primaryDomain {
      url
      host
    }
  }
}
Query;

$data = $ap21->GraphQL->post($graphQL);
```
##### Variables
If you want to use [GraphQL variables](https://shopify.dev/concepts/graphql/variables), you need to put the variables in an array and give it as the 4th argument of the `post()` method. The 2nd and 3rd arguments don't have any use in GraphQL, but are there to keep similarity with other requests, you can just keep those as `null`. Here is an example:

```php
$graphQL = <<<Query
mutation ($input: CustomerInput!) {
  customerCreate(input: $input)
  {
    customer {
      id
      displayName
    }
    userErrors {
      field
      message
    }
  }
}
Query;

$variables = [
  "input" => [
    "firstName" => "Greg",
    "lastName" => "Variables",
    "email" => "gregvariables@teleworm.us"
  ]
]
$ap21->GraphQL->post($graphQL, null, null, $variables);
```


##### GraphQL Builder
This SDK only accepts a GraphQL string as input. You can build your GraphQL from [Shopify GraphQL Builder](https://help.shopify.com/en/api/graphql-admin-api/graphiql-builder)


### Resource Mapping
Some resources are available directly, some resources are only available through parent resources and a few resources can be accessed both ways. It is recommended that you see the details in the related Shopify API Reference page about each resource. Each resource name here is linked to related Shopify API Reference page.
> Use the resources only by listed resource map. Trying to get a resource directly which is only available through parent resource may end up with errors.

- [AbandonedCheckout](https://help.shopify.com/api/reference/abandoned_checkouts)
- [ApplicationCharge](https://help.shopify.com/api/reference/applicationcharge)
- [Blog](https://help.shopify.com/api/reference/blog/)
- Blog -> [Article](https://help.shopify.com/api/reference/article/)
- Blog -> Article -> [Event](https://help.shopify.com/api/reference/event/)
- Blog -> Article -> [Metafield](https://help.shopify.com/api/reference/metafield)
- Blog -> [Event](https://help.shopify.com/api/reference/event/)
- Blog -> [Metafield](https://help.shopify.com/api/reference/metafield)
- [CarrierService](https://help.shopify.com/api/reference/carrierservice/)-
- [Cart](https://shopify.dev/docs/themes/ajax-api/reference/cart) (read only)
- [Collect](https://help.shopify.com/api/reference/collect/)
- [Comment](https://help.shopify.com/api/reference/comment/)
- Comment -> [Event](https://help.shopify.com/api/reference/event/)
- [Country](https://help.shopify.com/api/reference/country/)
- Country -> [Province](https://help.shopify.com/api/reference/province/)
- [Currency](https://help.shopify.com/en/api/reference/store-properties/currency)
- [CustomCollection]()
- CustomCollection -> [Event](https://help.shopify.com/api/reference/event/)
- CustomCollection -> [Metafield](https://help.shopify.com/api/reference/metafield)
- [Customer](https://help.shopify.com/api/reference/customer/)
- Customer -> [Address](https://help.shopify.com/api/reference/customeraddress/)
- Customer -> [Metafield](https://help.shopify.com/api/reference/metafield)
- Customer -> [Order](https://help.shopify.com/api/reference/order)
- [CustomerSavedSearch](https://help.shopify.com/api/reference/customersavedsearch/)
- CustomerSavedSearch -> [Customer](https://help.shopify.com/api/reference/customer/)
- [DraftOrder](https://help.shopify.com/api/reference/draftorder)
- [Discount](https://help.shopify.com/api/reference/discount) _(Shopify Plus Only)_
- [DiscountCode](https://help.shopify.com/en/api/reference/discounts/discountcode)
- [Event](https://help.shopify.com/api/reference/event/)
- [FulfillmentService](https://help.shopify.com/api/reference/fulfillmentservice)
- [GiftCard](https://help.shopify.com/api/reference/gift_card) _(Shopify Plus Only)_
- [InventoryItem](https://help.shopify.com/api/reference/inventoryitem)
- [InventoryLevel](https://help.shopify.com/api/reference/inventorylevel)
- [Location](https://help.shopify.com/api/reference/location/) _(read only)_
- Location -> [InventoryLevel](https://help.shopify.com/api/reference/inventorylevel)
- [Metafield](https://help.shopify.com/api/reference/metafield)
- [Multipass](https://help.shopify.com/api/reference/multipass) _(Shopify Plus Only, API not available yet)_
- [Order](https://help.shopify.com/api/reference/order)
- Order -> [Fulfillment](https://help.shopify.com/api/reference/fulfillment)
- Order -> Fulfillment -> [Event](https://help.shopify.com/api/reference/fulfillmentevent)
- Order -> [Risk](https://help.shopify.com/api/reference/order_risks)
- Order -> [Refund](https://help.shopify.com/api/reference/refund)
- Order -> [Transaction](https://help.shopify.com/api/reference/transaction)
- Order -> [Event](https://help.shopify.com/api/reference/event/)
- Order -> [Metafield](https://help.shopify.com/api/reference/metafield)
- [Page](https://help.shopify.com/api/reference/page)
- Page -> [Event](https://help.shopify.com/api/reference/event/)
- Page -> [Metafield](https://help.shopify.com/api/reference/metafield)
- [Policy](https://help.shopify.com/api/reference/policy) _(read only)_
- [Product](https://help.shopify.com/api/reference/product)
- Product -> [Image](https://help.shopify.com/api/reference/product_image)
- Product -> [Variant](https://help.shopify.com/api/reference/product_variant)
- Product -> Variant -> [Metafield](https://help.shopify.com/api/reference/metafield)
- Product -> [Event](https://help.shopify.com/api/reference/event/)
- Product -> [Metafield](https://help.shopify.com/api/reference/metafield)
- [ProductListing](https://help.shopify.com/api/reference/sales_channels/productlisting)
- [ProductVariant](https://help.shopify.com/api/reference/product_variant)
- ProductVariant -> [Metafield](https://help.shopify.com/api/reference/metafield)
- [RecurringApplicationCharge](https://help.shopify.com/api/reference/recurringapplicationcharge)
- RecurringApplicationCharge -> [UsageCharge](https://help.shopify.com/api/reference/usagecharge)
- [Redirect](https://help.shopify.com/api/reference/redirect)
- [ScriptTag](https://help.shopify.com/api/reference/scripttag)
- [ShippingZone](https://help.shopify.com/api/reference/shipping_zone) _(read only)_
- [Shop](https://help.shopify.com/api/reference/shop) _(read only)_
- [SmartCollection](https://help.shopify.com/api/reference/smartcollection)
- SmartCollection -> [Event](https://help.shopify.com/api/reference/event/)
- [ShopifyPayment](https://shopify.dev/docs/admin-api/rest/reference/shopify_payments/)
- ShopifyPayment -> [Dispute](https://shopify.dev/docs/admin-api/rest/reference/shopify_payments/dispute/) _(read only)_
- [Theme](https://help.shopify.com/api/reference/theme)
- Theme -> [Asset](https://help.shopify.com/api/reference/asset/)
- [User](https://help.shopify.com/api/reference/user) _(read only, Shopify Plus Only)_
- [Webhook](https://help.shopify.com/api/reference/webhook)
- [GraphQL](https://help.shopify.com/en/api/graphql-admin-api/reference)

### Custom Actions
There are several action methods which can be called without calling the `get()`, `post()`, `put()`, `delete()` methods directly, but eventually results in a custom call to one of those methods.

- For example, get count of total products
```php
$productCount = $ap21->Product->count();
```

- Make an address default for the customer.
```php
$ap21->Customer($customerID)->Address($addressID)->makeDefault();
```

- Search for customers with keyword "Bob" living in country "United States".
```php
$ap21->Customer->search("Bob country:United States");
```

#### Custom Actions List
The custom methods are specific to some resources which may not be available for other resources.  It is recommended that you see the details in the related Shopify API Reference page about each action. We will just list the available actions here with some brief info. each action name is linked to an example in Shopify API Reference which has more details information.

- (Any resource type except _ApplicationCharge, CarrierService, FulfillmentService, Location, Policy, RecurringApplicationCharge, ShippingZone, Shop, Theme_) ->
    - [count()](https://help.shopify.com/api/reference/product#count)
    Get a count of all the resources.
    Unlike all other actions, this function returns an integer value.

- Comment ->
    - [markSpam()](https://help.shopify.com/api/reference/comment#spam)
    Mark a Comment as spam
    - [markNotSpam()](https://help.shopify.com/api/reference/comment#not_spam)
    Mark a Comment as not spam
    - [approve()](https://help.shopify.com/api/reference/comment#approve)
    Approve a Comment
    - [remove()](https://help.shopify.com/api/reference/comment#remove)
    Remove a Comment
    - [restore()](https://help.shopify.com/api/reference/comment#restore)
    Restore a Comment

- Customer ->
    - [search()](https://help.shopify.com/api/reference/customer#search)
    Search for customers matching supplied query
    - [send_invite($data)](https://help.shopify.com/en/api/reference/customers/customer#send_invite)
    Sends an account invite to a customer.

- Customer -> Address ->
    - [makeDefault()](https://help.shopify.com/api/reference/customeraddress#default)
    Sets the address as default for the customer
    - [set($params)](https://help.shopify.com/api/reference/customeraddress#set)
    Perform bulk operations against a number of addresses

- DraftOrder ->
    - [send_invoice($data)]()
    Send the invoice for a DraftOrder
    - [complete($data)]()
    Complete a DraftOrder

- Discount ->
    - [enable()]()
    Enable a discount
    - [disable()]()
    Disable a discount

- DiscountCode ->
    - [lookup($data)]()
    Retrieves the location of a discount code.

- Fulfillment ->
    - [complete()](https://help.shopify.com/api/reference/fulfillment#complete)
    Complete a fulfillment
    - [open()](https://help.shopify.com/api/reference/fulfillment#open)
    Open a pending fulfillment
    - [cancel()](https://help.shopify.com/api/reference/fulfillment#cancel)
    Cancel a fulfillment

- GiftCard ->
    - [disable()](https://help.shopify.com/api/reference/gift_card#disable)
    Disable a gift card.
    - [search()](https://help.shopify.com/api/reference/gift_card#search)
    Search for gift cards matching supplied query

- InventoryLevel ->
    - [adjust($data)](https://help.shopify.com/api/reference/inventorylevel#adjust)
    Adjust inventory level.
    - [connect($data)](https://help.shopify.com/api/reference/inventorylevel#connect)
    Connect an inventory item to a location.
    - [set($data)](https://help.shopify.com/api/reference/inventorylevel#set)
    Set an inventory level for a single inventory item within a location.

- Order ->
    - [close()](https://help.shopify.com/api/reference/order#close)
    Close an Order
    - [open()](https://help.shopify.com/api/reference/order#open)
    Re-open a closed Order
    - [cancel($data)](https://help.shopify.com/api/reference/order#cancel)
    Cancel an Order

- Order -> Refund ->
    - [calculate()](https://help.shopify.com/api/reference/refund#calculate)
    Calculate a Refund.

- ProductListing ->
    - [productIds()](https://help.shopify.com/api/reference/sales_channels/productlisting#product_ids)
    Retrieve product_ids that are published to your app.

- RecurringApplicationCharge ->
    - [activate()](https://help.shopify.com/api/reference/recurringapplicationcharge#activate)
    Activate a recurring application charge
    - [customize($data)](https://help.shopify.com/api/reference/recurringapplicationcharge#customize)
    Customize a recurring application charge

- SmartCollection ->
    - [sortOrder($params)](https://help.shopify.com/api/reference/smartcollection#order)
    Set the ordering type and/or the manual order of products in a smart collection

- User ->
    - [current()](https://help.shopify.com/api/reference/user#current)
    Get the current logged-in user

### Shopify API features headers
To send `X-Shopify-Api-Features` headers while using the SDK, you can use the following:

```
$config['ShopifyApiFeatures'] = ['include-presentment-prices'];
$ap21 = new PHPAP21\Ap21SDK($config);
```

## Reference
- [Ap21 API Reference](doc/Retail API Guide - latest.pdf)
- [seldaek/monolog](https://github.com/seldaek/monolog)
- [vlucas/phpdotenv](https://github.com/vlucas/phpdotenv)
