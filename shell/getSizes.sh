#!/bin/bash

set -x

# load env into environment vars
if [ -f ./../.env ]; then
    export $(cat ./../.env | grep -v '#' | sed 's/\r$//' | awk '/=/ {print $1}' )
fi

# Sizes?countryCode=AU&query=1,1506&includeOutOfStock=true
# Query - Optional - Sequence and Product Reference Type. Up to 5 combinations can be entered.

# GET ALL colours
curl --insecure --silent \
  -X GET "$ApiUrl/Sizes?CountryCode=$CountryCode" \
  -u "$ApiUser:$ApiPassword" \
  -H "Content-Type: application/xml" > sizes.xml &
