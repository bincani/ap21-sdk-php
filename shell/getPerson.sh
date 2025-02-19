#!/bin/bash

set -x

# load env into environment vars
if [ -f ./../.env ]; then
    export $(cat ./../.env | grep -v '#' | sed 's/\r$//' | awk '/=/ {print $1}' )
fi

PersonId=1215452
#PersonId=628176

curl --insecure --silent \
  -X GET "$ApiUrl/Persons/$PersonId?CountryCode=$CountryCode" \
  -u "$ApiUser:$ApiPassword" \
  -H "Content-Type: application/xml"

curl --insecure --silent \
  -X GET "$ApiUrl/Persons/$PersonId/RetailTransactionss?CountryCode=$CountryCode" \
  -u "$ApiUser:$ApiPassword" \
  -H "Content-Type: application/xml"
