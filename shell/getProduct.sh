#!/bin/bash

set -x

# load env into environment vars
if [ -f ./../.env ]; then
    export $(cat ./../.env | grep -v '#' | sed 's/\r$//' | awk '/=/ {print $1}' )
fi

productId="14484"

#curl --insecure --silent \
curl --insecure \
  -X GET "$ApiUrl/Products/$productId?CountryCode=$CountryCode&CustomData=true" \
  -u "$ApiUser:$ApiPassword" \
  -H "Accept: version_4.0" \
  -H "Content-Type: application/xml" > "product-${productId}.xml" &

cat "product-${productId}.xml

