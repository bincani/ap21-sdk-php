#!/bin/bash

set -x

# load env into environment vars
if [ -f ./../.env ]; then
    export $(cat ./../.env | grep -v '#' | sed 's/\r$//' | awk '/=/ {print $1}' )
fi

# GET ALL Product Colour References
curl --insecure --silent \
  -X GET "$ApiUrl/ProductColourReferences?CountryCode=$CountryCode" \
  -u "$ApiUser:$ApiPassword" \
  -H "Content-Type: application/xml" > productColourReference.xml &

# GET One Product Colour Reference
refcodeId=1247
curl --insecure --silent \
  -X GET "$ApiUrl/ProductColourReferences/$refcodeId/?CountryCode=$CountryCode" \
  -u "$ApiUser:$ApiPassword" \
  -H "Content-Type: application/xml" > productColourReference-$refcodeId.xml &
