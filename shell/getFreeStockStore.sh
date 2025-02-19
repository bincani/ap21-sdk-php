#!/bin/bash

set -x

# load env into environment vars
if [ -f ./../.env ]; then
    export $(cat ./../.env | grep -v '#' | sed 's/\r$//' | awk '/=/ {print $1}' )
fi

SECONDS=0

StoreId=5503
StoreId=5418
StoreId=5501
OutputFile="/var/www/brands/ap21-sdk-php/data/freeStock-AllStyles-${StoreId}.xml"

curl --silent \
  -X GET "${ApiUrl}Freestock/AllStyles/${StoreId}?countryCode=${CountryCode}" \
  -u "$ApiUser:$ApiPassword" \
  -H "Accept: version_2.0" \
  -H "Content-Type: application/xml" > $OutputFile

echo "completed in $SECONDS seconds"
