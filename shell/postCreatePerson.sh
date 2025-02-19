#!/bin/bash

set -x

# load env into environment vars
if [ -f ./../.env ]; then
    export $(cat ./../.env | grep -v '#' | sed 's/\r$//' | awk '/=/ {print $1}' )
fi

PathToXml=../data/post/person-nz.xml

curl --insecure --silent \
  -X POST "$ApiUrl/Persons/?CountryCode=$CountryCode" \
  -u "$ApiUser:$ApiPassword" \
  -H "Content-Type: text/xml" \
  -H "Accept:version_2.0" \
  -d @$PathToXml
