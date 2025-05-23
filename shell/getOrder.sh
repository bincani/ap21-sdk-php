#!/bin/bash

set -x

# load env into environment vars
if [ -f ./../.env ]; then
    export $(cat ./../.env | grep -v '#' | sed 's/\r$//' | awk '/=/ {print $1}' )
fi

PersonId=1793938
OrderId=2999863

#PersonId=1792922
#OrderId=2996714

curl --silent \
  -X GET "${ApiUrl}Persons/${PersonId}/Orders/${OrderId}?countryCode=$CountryCode" \
  -u "$ApiUser:$ApiPassword" \
  -H "Accept: version_4.0" \
  -H "Content-Type: application/xml"

#  > order-3334.xml &
