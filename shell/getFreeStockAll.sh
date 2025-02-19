#!/bin/bash

set -x

# load env into environment vars
if [ -f ./../.env ]; then
    export $(cat ./../.env | grep -v '#' | sed 's/\r$//' | awk '/=/ {print $1}' )
fi

SECONDS=0

curl --silent \
  -X GET "$ApiUrl/Freestock/AllStyles?CountryCode=$CountryCode" \
  -u "$ApiUser:$ApiPassword" \
  -H "Accept: version_2.0" \
  -H "Content-Type: application/xml" > /var/www/brands/ap21-sdk-php/data/freeStock-AllStyles.xml

echo "completed in $SECONDS seconds"