#!/bin/bash

set -x

# load env into environment vars
if [ -f ./../.env ]; then
    export $(cat ./../.env | grep -v '#' | sed 's/\r$//' | awk '/=/ {print $1}' )
fi

#ChangedSince=2023-09-29T12%3A15%3A00
#ChangedSince=2023-09-29T13%3A15%3A00
#ChangedSince=2024-05-03T15%3A47%3A30
#ChangedSince=2015-05-29T11:56:00
ChangedSince=2024-11-18T11:00:00
#StoreId=5501
StoreId=12250

curl --insecure --silent \
  -X GET "$ApiUrl/StockChanged?ChangedSince=$ChangedSince&StoreId=$StoreId&CountryCode=$CountryCode" \
  -u "$ApiUser:$ApiPassword" \
  -H "Accept: version_2.0" \
  -H "Content-Type: application/xml" \
  > store-stock-change-$StoreId.xml
