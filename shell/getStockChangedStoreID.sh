#!/bin/bash

set -x

# load env into environment vars
if [ -f ./../.env ]; then
    export $(cat ./../.env | grep -v '#' | sed 's/\r$//' | awk '/=/ {print $1}' )
fi

StoreID=5507

curl --insecure --silent \
  -X GET "$ApiUrl/StockChanged?ChangedSince=2022-08-02T11:00:00&StoreId=$StoreID&CountryCode=$CountryCode" \
  -u "$ApiUser:$ApiPassword" \
  -H "Accept: version_2.0" \
  -H "Content-Type: application/xml"

