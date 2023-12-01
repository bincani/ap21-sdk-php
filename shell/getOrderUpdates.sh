#!/bin/bash

set -x

# load env into environment vars
if [ -f ./../.env ]; then
    export $(cat ./../.env | grep -v '#' | sed 's/\r$//' | awk '/=/ {print $1}' )
fi

UpdatedAfter=2023-11-28T12:00:00

curl --insecure --silent \
  -X GET "$ApiUrl/Persons/2641/Orders/3334?countryCode=$CountryCode&updatedAfter=$UpdatedAfter" \
  -u "$ApiUser:$ApiPassword" \
  -H "Accept: version_4.0" \
  -H "Content-Type: application/xml"
