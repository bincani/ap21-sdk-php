#!/bin/bash

set -x

# load env into environment vars
if [ -f ./../.env ]; then
    export $(cat ./../.env | grep -v '#' | sed 's/\r$//' | awk '/=/ {print $1}' )
fi

curl --insecure --silent \
  -X GET "$ApiUrl/Products?CountryCode=$CountryCode" \
  -u "$ApiUser:$ApiPassword" \
  -H "Content-Type: application/xml" > products.xml

#curl --silent \
#  -X GET "$ApiUrl/Products?CountryCode=$CountryCode&startRow=1&pageRows=40" \
#  -u "$ApiUser:$ApiPassword" \
#  -H "Content-Type: application/xml" > products.xml

