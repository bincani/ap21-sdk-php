#!/bin/bash

set -x

# load env into environment vars
if [ -f ./../.env ]; then
    export $(cat ./../.env | grep -v '#' | sed 's/\r$//' | awk '/=/ {print $1}' )
fi

PersonId=1215452
PathToXml=../data/post/order.xml

# --silent
# -X POST "$ApiUrl/Order/?CountryCode=$CountryCode"

curl --insecure \
  -X POST "$ApiUrl/Persons/$PersonId/Orders/?CountryCode=$CountryCode" \
  -u "$ApiUser:$ApiPassword" \
  -H "Content-Type: text/xml" \
  -H "Accept:version_2.0" \
  -d @$PathToXml
