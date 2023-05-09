#!/bin/bash

set -x

# load env into environment vars
if [ -f ./../.env ]; then
    export $(cat ./../.env | grep -v '#' | sed 's/\r$//' | awk '/=/ {print $1}' )
fi

# /References/{referencetypeId}/{refcodeId}?CountryCode={countryCode}

# 1342 | 426
referencetypeId=426
refcodeId=

curl --insecure --silent \
  -X GET "$ApiUrl/References/$referencetypeId?CountryCode=$CountryCode" \
  -u "$ApiUser:$ApiPassword" \
  -H "Content-Type: application/xml" > reference-$referencetypeId.xml &
