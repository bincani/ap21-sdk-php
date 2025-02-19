#!/bin/bash

set -x

# load env into environment vars
if [ -f ./../.env ]; then
    export $(cat ./../.env | grep -v '#' | sed 's/\r$//' | awk '/=/ {print $1}' )
fi

#curl --insecure --silent \
#  -X GET "$ApiUrl/Products?CountryCode=$CountryCode" \
#  -u "$ApiUser:$ApiPassword" \
#  -H "Content-Type: application/xml" > products.xml &

## --connect-to api.factoryx.com.au:9090:150.242.136.248:9090

curl --insecure \
  --resolve api.factoryx.com.au:9090:150.242.136.248 \
  -X GET "$ApiUrl/Products?CountryCode=$CountryCode&styleCode=KGFW028" \
  -u "$ApiUser:$ApiPassword" \
  -H "Content-Type: application/xml"
