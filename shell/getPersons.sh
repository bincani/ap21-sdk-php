#!/bin/bash

set -x

# load env into environment vars
if [ -f ./../.env ]; then
    export $(cat ./../.env | grep -v '#' | sed 's/\r$//' | awk '/=/ {print $1}' )
fi

#Query=&surname={surname}&firstname={firstname}&phone={phone}&email={email}&code={code}&password={password}
Query="&surname=Incani"

curl --insecure --silent \
  -X GET "$ApiUrl/Persons?CountryCode=$CountryCode$Query" \
  -u "$ApiUser:$ApiPassword" \
  -H "Content-Type: application/xml"
