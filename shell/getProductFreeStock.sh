#!/bin/bash

set -x

# load env into environment vars
if [ -f ./../.env ]; then
    export $(cat ./../.env | grep -v '#' | sed 's/\r$//' | awk '/=/ {print $1}' )
fi

#curl --insecure --silent \
curl --insecure \
  -X GET "$ApiUrl/Freestock/style/19050?CountryCode=$CountryCode" \
  -u "$ApiUser:$ApiPassword" \
  -H "Accept: version_4.0" \
  -H "Content-Type: application/xml"

# > product-free-stock-19050.xml &

#curl --insecure \
#  -X GET "$ApiUrl/Freestock/AllStyles?CountryCode=$CountryCode" \
#  -u "$ApiUser:$ApiPassword" \
#  -H "Accept: version_4.0" \
#  -H "Content-Type: application/xml"
