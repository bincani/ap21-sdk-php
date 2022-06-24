#!/bin/bash

set -x

# load env into environment vars
if [ -f ./../.env ]; then
    export $(cat ./../.env | grep -v '#' | sed 's/\r$//' | awk '/=/ {print $1}' )
fi

curl --insecure --silent \
  -X GET "$ApiUrl/Persons/1145?CountryCode=$CountryCode" \
  -u "$ApiUser:$ApiPassword" \
  -H "Content-Type: application/xml"

curl --insecure --silent \
  -X GET "$ApiUrl/Persons/1145/RetailTransactionss?CountryCode=$CountryCode" \
  -u "$ApiUser:$ApiPassword" \
  -H "Content-Type: application/xml"
