#!/bin/bash

set -x

# load env into environment vars
if [ -f ./../.env ]; then
    export $(cat ./../.env | grep -v '#' | sed 's/\r$//' | awk '/=/ {print $1}' )
fi

# Colours?countryCode={countryCode}&query={queryString}
# Query - Optional - Sequence and Product Reference Type. Up to 5 combinations can be entered.

# GET ALL colours
curl --insecure --silent \
  -X GET "$ApiUrl/Colours?CountryCode=$CountryCode" \
  -u "$ApiUser:$ApiPassword" \
  -H "Content-Type: application/xml" > colours.xml &
