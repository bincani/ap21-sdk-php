#!/bin/bash

set -x

# load env into environment vars
if [ -f ./../.env ]; then
    export $(cat ./../.env | grep -v '#' | sed 's/\r$//' | awk '/=/ {print $1}' )
fi

StyleCode=BWFY120

curl --silent \
  -X GET "${ApiUrl}Products?CountryCode=${CountryCode}&styleCode=${StyleCode}" \
  -u "$ApiUser:$ApiPassword" \
  -H "Accept: version_4.0" \
  -H "Content-Type: application/xml" > product-${StyleCode}.xml

cat product-${StyleCode}.xml
