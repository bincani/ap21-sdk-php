#!/bin/bash

set -x

# load env into environment vars
if [ -f ./../.env ]; then
    export $(cat ./../.env | grep -v '#' | sed 's/\r$//' | awk '/=/ {print $1}' )
fi

StoreId=5441
ChangedSince=2023-11-29T12:13:00

curl --insecure \
  -X GET "$ApiUrl/StockChanged?ChangedSince=$ChangedSince&StoreId=$StoreId&CountryCode=$CountryCode" \
  -u "$ApiUser:$ApiPassword" \
  -H "Accept: version_4.0" \
  -H "Content-Type: application/xml"
# > stock.xml &
